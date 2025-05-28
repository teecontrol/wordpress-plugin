<?php

namespace Teecontrol;

class Teecontrol
{
    // const API_HOST = 'api.teecontrol.com';
    // const API_PORT = 443;

    public const API_HOST = 'api.teecontrol.site';
    public const API_PORT = 80;

    private static $initiated = false;

    private static $autoloader;

    private static $encrypter;

    public static function init()
    {
        // Stop when initiation has already been done
        if (static::$initiated) {
            return;
        }

        static::$initiated = true;

        static::autoload_composer();

        static::$encrypter = new Encrypter();

        add_action('teecontrol_sync_course_status', [static::class, 'sync_course_status']);
        add_action('teecontrol_sync_course_agenda', [static::class, 'sync_course_agenda']);

        static::register_blocks();

        static::register_settings();
    }

    /**
     * Load translation of the textdomain
     *
     * @return void
     */
    public static function load_textdomain()
    {
        $plugin_path = str_replace('\\', '/', TEECONTROL__PLUGIN_DIR);
        $mu_path = str_replace('\\', '/', WPMU_PLUGIN_DIR);

        if (stripos($plugin_path, $mu_path) !== false) {
            load_muplugin_textdomain('teecontrol', dirname(TEECONTROL__BASEFILE) . '/languages');
        } else {
            load_plugin_textdomain('teecontrol', false, dirname(TEECONTROL__BASEFILE) . '/languages');
        }
    }

    public static function autoload_composer()
    {
        // Autoloader has already been defined
        if (static::$autoloader) {
            return;
        }

        $autoloadFile = TEECONTROL__PLUGIN_DIR . 'vendor/autoload.php';

        if (is_readable($autoloadFile)) {
            static::$autoloader = require $autoloadFile;
        }
    }

    /**
     * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
     *
     * @static
     */
    public static function plugin_activation()
    {
        // Make sure the minimum version matches
        if (version_compare($GLOBALS['wp_version'], TEECONTROL__MINIMUM_WP_VERSION, '<')) {
            load_plugin_textdomain('teecontrol');

            $message = '<strong>' .
                /* translators: %1$s will be replaced by current Teecontrol version number, %2$s will be replaced by minimum required WordPress version number. */
                sprintf(esc_html__('Teecontrol %1$s requires WordPress %2$s or higher.', 'teecontrol'), TEECONTROL__VERSION, TEECONTROL__MINIMUM_WP_VERSION) . '</strong> ' .
                /* translators: %1$s will be replaced by wordPress documentation URL. */
                sprintf(__('Please <a href="%1$s">upgrade WordPress</a> to a current version.', 'teecontrol'), 'https://codex.wordpress.org/Upgrading_WordPress');

            static::bail_activation($message);
        } elseif (! empty($_SERVER['SCRIPT_NAME']) && false !== strpos($_SERVER['SCRIPT_NAME'], '/wp-admin/plugins.php')) {
            // Set temporary action so redirect in TeecontrolAdmin::admin_init will be triggered
            add_option('Activated_Teecontrol', true);

            static::register_defaults();
            static::register_cronjobs();
        }
    }

    private static function bail_activation($message, $deactivate = true)
    {
        static::view('bail_activation', compact('message'));

        if ($deactivate) {
            $plugins = get_option('active_plugins');
            $teecontrol = plugin_basename(TEECONTROL__PLUGIN_DIR . 'teecontrol.php');
            $update  = false;
            foreach ($plugins as $i => $plugin) {
                if ($plugin === $teecontrol) {
                    $plugins[ $i ] = false;
                    $update        = true;
                }
            }

            if ($update) {
                update_option('active_plugins', array_filter($plugins));
            }
        }
        exit;
    }

    /**
     * Register all default settings
     *
     * @return void
     */
    public static function register_defaults()
    {
        add_option('teecontrol_api_url', 'https://api.teecontrol.com');
    }

    /**
     * Removes all connection options
     *
     * @static
     */
    public static function plugin_deactivation()
    {
        // Delete all plugin options
        delete_option('teecontrol_api_url');
        delete_option('teecontrol_api_key');
        delete_option('teecontrol_course_status');
        delete_option('teecontrol_course_agenda');

        // Deactivate all upcoming cronjobs
        foreach (['teecontrol_sync_course_status', 'teecontrol_sync_course_agenda'] as $hook) {
            $timestamp = wp_next_scheduled($hook);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $hook);
            }
        }
    }

    public static function add_block_categories($categories)
    {
        $categories[] = [
            'slug'  => 'teecontrol-blocks',
            'title' => sprintf(
                /* translators: %1$s will be replaced by "Teecontrol". */
                __('%1$s Data Blocks', 'teecontrol'),
                __('Teecontrol', 'teecontrol')
            ),
        ];

        return $categories;
    }

    public static function register_cronjobs()
    {
        $cronjobs = [
            'teecontrol_sync_course_status',
            'teecontrol_sync_course_agenda',
        ];

        foreach ($cronjobs as $hook => $settings) {
            if (is_string($settings)) {
                $hook = $settings;
                $settings = [];
            }

            if (!wp_next_scheduled($hook)) {
                wp_schedule_event(
                    $settings['at'] ?? time(),
                    $settings['interval'] ?? 'hourly',
                    $hook
                );
            }
        }
    }

    public static function sync_course_status()
    {
        // Ignore when settings are not fulfilled
        if (!get_option('teecontrol_api_key')) {
            return;
        }

        // Fetch the course status and store it in an option
        $response = wp_remote_get(
            rtrim(get_option('teecontrol_api_url', 'https://api.teecontrol.com'), '/') . '/golf-course/course-status',
            [
                'headers' => [
                    'X-GolfCourse' => static::decrypt(get_option('teecontrol_api_key')),
                    'Accept' => 'application/json',
                    'Accept-Language' => get_locale(),
                ],
            ]
        );

        // Store course status when the request was successful
        if ($response['response']['code'] >= 200 && $response['response']['code'] < 300) {
            $body = $response['body'] ?? [];
            $option = 'teecontrol_course_status';
            if (!empty($body)) {
                // Fill the option with the resulting body
                if (get_option($option)) {
                    update_option($option, $body);
                } else {
                    add_option($option, $body);
                }
            } else {
                // Clear option to make sure it no longer exists
                if (get_option($option)) {
                    delete_option($option);
                }
            }
        }
    }

    public static function sync_course_agenda()
    {
        // Ignore when settings are not fulfilled
        if (!get_option('teecontrol_api_key')) {
            return;
        }

        // Fetch the course status and store it in an option
        $response = wp_remote_get(
            rtrim(get_option('teecontrol_api_url', 'https://api.teecontrol.com'), '/') . '/golf-course/agenda',
            [
                'headers' => [
                    'X-GolfCourse' => static::decrypt(get_option('teecontrol_api_key')),
                    'Accept' => 'application/json',
                    'Accept-Language' => get_locale(),
                ],
            ]
        );

        // Store course status when the request was successful
        if ($response['response']['code'] >= 200 && $response['response']['code'] < 300) {
            $body = $response['body'] ?? [];
            $option = 'teecontrol_course_agenda';
            if (!empty($body)) {
                // Fill the option with the resulting body
                if (get_option($option)) {
                    update_option($option, $body);
                } else {
                    add_option($option, $body);
                }
            } else {
                // Clear option to make sure it no longer exists
                if (get_option($option)) {
                    delete_option($option);
                }
            }
        }
    }

    public static function register_blocks()
    {
        $manifestData = require TEECONTROL__SRC_DIR . '/blocks-manifest.php';
        if (!empty($manifestData)) {
            add_filter('block_categories_all', function ($categories) {

                // Adding a new category.
                $categories[] = [
                    'slug'  => 'teecontrol',
                    'title' => __('Teecontrol', 'teecontrol')
                ];

                return $categories;
            });
        }
        foreach ($manifestData as $blockType => $blockData) {
            $pluginUrl = plugin_dir_url(TEECONTROL__SRC_DIR);
            $blockRoot = $pluginUrl . "build/blocks/{$blockType}";

            $customSettings = [];

            // Register script and replace it with the alias
            if (isset($blockData['editorScript'])) {
                wp_register_script(
                    "teecontrol-{$blockType}",
                    $blockRoot . str_replace('file:.', '', $blockData['editorScript']),
                    ['wp-blocks', 'wp-i18n', 'wp-block-editor', 'wp-components', 'wp-server-side-render']
                );
                $customSettings['editorScript'] = "teecontrol-{$blockType}";
            }

            // Register the block
            register_block_type(
                TEECONTROL__SRC_DIR . "blocks/{$blockType}",
                $customSettings
            );

            // Set translations on editor scripts
            if (isset($customSettings['editorScript'])) {
                wp_set_script_translations(
                    $customSettings['editorScript'],
                    'teecontrol',
                    TEECONTROL__PLUGIN_DIR . 'languages'
                );
            }
        }
    }

    public static function register_settings()
    {
        register_setting('teecontrol', 'teecontrol_api_key', [
            'type' => 'string',
            'label' => __('API Key', 'teecontrol'),
            'show_in_rest' => false,
        ]);
        register_setting('teecontrol', 'teecontrol_api_url', [
            'type' => 'string',
            'label' => __('API URL', 'teecontrol'),
            'show_in_rest' => false,
        ]);
    }

    public static function view(string $template, array $arguments = [])
    {
        $path = TEECONTROL__SRC_DIR . "/views/{$template}.php";

        if (file_exists($path)) {
            // Create variables for all arguments so they can be used in the template.
            foreach ($arguments as $_argkey => $_argval) {
                $$_argkey = $_argval;
            }

            include $path;
        }
    }

    public static function encrypt($payload, $serialize = true)
    {
        return static::$encrypter->encrypt($payload, $serialize);
    }

    public static function decrypt($payload, $unserialize = true)
    {
        return static::$encrypter->decrypt($payload, $unserialize);
    }
}
