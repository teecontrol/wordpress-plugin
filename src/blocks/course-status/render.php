<?php
// Fetch course status attributes
$courseStatusAttributes = json_decode(get_option('teecontrol_course_status', '[]'), true);

// Resolve the most recent update timestamp
$updates = [];
foreach ($courseStatusAttributes as $attribute) {
    $updates[] = new DateTime($attribute['updated_at']);
}
$lastUpdateTime = empty($updates) ? null : max($updates);

// Resolve value of certain attributes
$elementAttributes = [
    'style' => ''
];
foreach (['colorEnabled' => '--teecontrol-enabled-color', 'colorDisabled' => '--teecontrol-disabled-color'] as $attributeName => $cssVar) {
    if (isset($attributes[$attributeName])) {
        $elementAttributes['style'] .= "{$cssVar}:{$attributes[$attributeName]};";
    }
}
?>
<div <?php echo get_block_wrapper_attributes($elementAttributes); ?>>
    <?php foreach ($courseStatusAttributes as $attribute) { ?>
        <div class="teecontrol--course-status-attribute <?php echo esc_attr($attribute['is_enabled'] ? 'teecontrol--course-status-enabled' : 'teecontrol--course-status-disabled') ?>">
            <span class="teecontrol--course-status-attribute-label"><?php echo esc_html(isset($attribute['loop']) ? $attribute['loop']['name'] : $attribute['name']) ?></span>
            <span class="teecontrol--course-status-attribute-status"><?php echo esc_html($attribute['content']) ?></span>
        </div>
    <?php } ?>
    <?php if ($lastUpdateTime) { ?>
        <div class="teecontrol--course-status-timestamp"><?php printf(
                                                                /* translators: %1$s will be replaced by date, %2$s will by replaced by time. */
                                                                __('Last update: %1$s %2$s', 'teecontrol'),
                                                                wp_date(get_option('date_format'), $lastUpdateTime->getTimestamp()),
                                                                wp_date(get_option('time_format'), $lastUpdateTime->getTimestamp())
                                                            ) ?></div>
    <?php } ?>
</div>