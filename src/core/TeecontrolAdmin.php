<?php

namespace Teecontrol;

class TeecontrolAdmin
{
    public const NONCE = 'teecontrol-update-key';

    private static $initiated = false;

    public static function init()
    {
        // Stop when initiation has already been done
        if (static::$initiated) {
            return;
        }

        static::$initiated = true;

        add_action('admin_init', [static::class, 'admin_init']);
        add_action('admin_menu', [static::class, 'admin_menu']);
        add_filter(
            'plugin_action_links_' . plugin_basename(plugin_dir_path(__FILE__) . 'teecontrol.php'),
            [static::class, 'admin_plugin_settings_link']
        );
    }

    public static function admin_init()
    {
        if (get_option('Activated_Teecontrol')) {
            delete_option('Activated_Teecontrol');
            if (! headers_sent()) {
                $admin_url = static::get_page_url();
                wp_redirect($admin_url);
            }
        }

        load_plugin_textdomain('teecontrol');

        // Define save action
        add_action('admin_post_teecontrol_settings', [static::class, 'save_settings']);
    }

    public static function admin_menu()
    {
        if (! current_user_can('manage_options')) {
            return;
        };

        add_options_page(
            __('Teecontrol', 'teecontrol'),
            __('Teecontrol', 'teecontrol'),
            'manage_options',
            'teecontrol',
            [static::class, 'settings_page_html']
        );
        add_settings_section(
            'teecontrol',
            __('Teecontrol', 'teecontrol'),
            [static::class, 'settings_page'],
            'teecontrol'
        );
        add_settings_field(
            'teecontrol_api_key',
            __('API Key', 'teecontrol'),
            [static::class, 'settings_input_field'],
            'teecontrol',
            'teecontrol',
            'api_key'
        );
        add_settings_field(
            'teecontrol_api_url',
            __('API URL', 'teecontrol'),
            [static::class, 'settings_input_field'],
            'teecontrol',
            'teecontrol',
            'api_url'
        );
        add_settings_field(
            'init_sync',
            __('Update course information', 'teecontrol'),
            [static::class, 'settings_toggle_field'],
            'teecontrol',
            'teecontrol',
            'init_sync'
        );
    }

    public static function admin_plugin_settings_link($links)
    {
        $additionalLinks = [];

        $title = __('Settings', 'teecontrol');
        $style = '';
        if (!get_option('teecontrol_api_key')) {
            $style = 'font-weight: bold;';
        }
        $additionalLinks['settings'] = str_replace(
            [':url', ':style', ':title'],
            [static::get_page_url(), $style, $title],
            '<a href=":url" style=":style" title=":title">:title</a>'
        );

        return array_merge($additionalLinks, $links);
    }

    public static function settings_page()
    {
        echo esc_html(__('Define your Teecontrol settings to enable the Teecontrol widgets.', 'teecontrol'));
    }

    public static function settings_input_field(string $setting, ?string $value = null)
    {
        $value = get_option("teecontrol_{$setting}");
        if ($setting == 'api_key') {
            $value = Teecontrol::decrypt($value);
        }

        Teecontrol::view('settings/input', compact('value', 'setting'));

        if ($setting == 'api_key') {
            Teecontrol::view('settings/description', [
                'text' => __('API keys can be managed in Teecontrol at "Integrations" > "Player app".', 'teecontrol'),
            ]);
        }
    }

    public static function settings_toggle_field(string $key, ?bool $value = null)
    {
        if (is_null($value)) {
            $value = get_option("teecontrol_{$key}");
        }

        $checked = (bool) $value;

        Teecontrol::view('settings/toggle', compact('key', 'checked'));

        if ($key == 'init_sync') {
            Teecontrol::view('settings/description', [
                'text' => __('All Teecontrol information is synced hourly. Using this option you can fetch the information manually.', 'teecontrol'),
            ]);
        }
    }

    public static function get_page_url()
    {
        $args = ['page' => 'teecontrol'];

        return add_query_arg($args, menu_page_url('teecontrol', false));
    }

    public static function settings_page_html()
    {
        // check user capabilities
        if (! current_user_can('manage_options')) {
            return;
        }

        return Teecontrol::view('settings/form');
    }

    public static function save_settings()
    {
        // Make sure user it authorized
        if (!current_user_can('manage_options')) {
            wp_die("You do not have permission to view this page.");
        }

        // Validate nonce
        check_admin_referer('teecontrol_settings_verify');

        if (isset($_POST['teecontrol_api_url'])) {
            // Get values from POST
            $values = [];
            foreach (['teecontrol_api_key', 'teecontrol_api_url'] as $setting) {
                $values[$setting] = isset($_POST[$setting])
                    ? sanitize_text_field($_POST[$setting])
                    : null;
            }

            // Encrypt API key
            if (!empty($values['teecontrol_api_key'])) {
                $values['teecontrol_api_key'] = Teecontrol::encrypt($values['teecontrol_api_key']);
            }

            // Register the values
            foreach ($values as $key => $value) {
                if (!empty($value)) {
                    // Value is not empty, set or update it.
                    if (get_option($key)) {
                        update_option($key, $value);
                    } else {
                        add_option($key, $value);
                    }
                } else {
                    // Value is empty. Delete it when it has been set.
                    if (get_option($key)) {
                        delete_option($key);
                    }
                }
            }

            // Sync the course status when requested
            if (isset($_POST['teecontrol_init_sync']) && $_POST['teecontrol_init_sync'] == '1') {
                Teecontrol::sync_course_agenda();
                Teecontrol::sync_course_status();
            }
        }

        // Redirect to same page with status=1 to show our options updated banner
        add_settings_error('teecontrol', 'settings_updated', __('Settings saved.', 'teecontrol'), 'success');

        // Redirect back to the settings page that was submitted.
        $goback = add_query_arg('settings-updated', 'true', wp_get_referer());
        wp_redirect($goback);
    }
}
