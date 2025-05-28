<?php
// Fetch events from the agenda
$eventsByDate = json_decode(get_option('teecontrol_course_agenda', '[]'), true);

// Resolve value of certain attributes
$elementAttributes = [
    'style' => ''
];
foreach (
    [
        'colorAnnouncementBackground' => '--teecontrol-announcement-background-color',
        'colorAnnouncementText' => '--teecontrol-announcement-text-color',
        'colorOddItems' => '--teecontrol-course-agenda-odd-items-background-color',
        'colorEvenItems' => '--teecontrol-course-agenda-even-items-background-color',
    ] as $attributeName => $cssVar
) {
    if (isset($attributes[$attributeName])) {
        $elementAttributes['style'] .= "{$cssVar}:{$attributes[$attributeName]};";
    }
}

?>
<div <?php echo wp_kses_data(get_block_wrapper_attributes($elementAttributes)); ?>>
    <?php foreach ($eventsByDate as $eventsDate) { ?>
        <div class="teecontrol--course-agenda-event-date">
            <span class="teecontrol--course-agenda-date">
                <?php echo esc_html(wp_date(get_option('date_format'), strtotime($eventsDate['date']))) ?>
            </span>
            <?php if (!empty($eventsDate['announcements'])) { ?>
                <div class="teecontrol--course-agenda-announcements">
                    <?php foreach ($eventsDate['announcements'] as $announcement) { ?>
                        <div class="teecontrol--course-agenda-announcements-item">
                            <?php echo esc_html($announcement) ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php if (!empty($eventsDate['events'])) { ?>
                <div class="teecontrol--course-agenda-events">
                    <?php foreach ($eventsDate['events'] as $event) { ?>
                        <div class="teecontrol--course-agenda-events-item <?php echo esc_attr("teecontrol--course-agenda-event-type-{$event['type']}") ?>">
                            <span class="teecontrol--course-agenda-event-name">
                                <?php echo esc_html($event['name']) ?>
                            </span>
                            <div class="teecontrol--course-agenda-locations">
                                <?php
                                $locationTypes = ['facilities'];
                        if ($attributes['showLoopsOrSets'] == 'loops' || empty($event['sets'])) {
                            $locationTypes[] = 'loops';
                        } else {
                            $locationTypes[] = 'sets';
                        }
                        foreach ($locationTypes as $locationType) {
                            foreach ($event[$locationType] as $location) {
                                ?>
                                    <div class="teecontrol--course-agenda-location-item <?php echo esc_attr("teecontrol-course-agenda-location-{$locationType}") ?>">
                                        <span class="teecontrol--course-agenda-location-name">
                                            <?php echo esc_html($location['name']) ?>
                                        </span>
                                        <div class="teecontrol--course-agenda-duration">
                                            <?php foreach (['start_time', 'end_time'] as $timestamp) { ?>
                                                <span class="teecontrol--course-agenda-time">
                                                    <?php echo esc_html(wp_date(get_option('time_format'), strtotime($location[$timestamp]['timestamp']))) ?>
                                                </span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php
                            }
                        }
                        ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>