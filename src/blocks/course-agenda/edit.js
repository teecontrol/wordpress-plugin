/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import ServerSideRender from "@wordpress/server-side-render";
import {
    useBlockProps,
    InspectorControls,
    PanelColorSettings,
} from "@wordpress/block-editor";
import { PanelBody, SelectControl } from "@wordpress/components";

const Edit = ({ attributes, setAttributes }) => {
    const {
        colorAnnouncementBackground,
        colorAnnouncementText,
        colorOddItems,
        colorEvenItems,
        showLoopsOrSets,
    } = attributes;

    const styles = {
        "--teecontrol-announcement-background-color":
            colorAnnouncementBackground,
        "--teecontrol-announcement-text-color": colorAnnouncementText,
    };

    const onChangeColorAnnouncementBackground = (val) => {
        setAttributes({ colorAnnouncementBackground: val });
    };
    const onChangeColorAnnouncementText = (val) => {
        setAttributes({ colorAnnouncementText: val });
    };
    const onChangeColorOddItems = (val) => {
        setAttributes({ colorOddItems: val });
    };
    const onChangeColorEvenItems = (val) => {
        setAttributes({ colorEvenItems: val });
    };
    const onChangeShowLoopsOrSets = (val) => {
        setAttributes({ showLoopsOrSets: val });
    };

    return (
        <>
            <InspectorControls>
                <PanelColorSettings
                    title={__("Color settings", "teecontrol")}
                    colorSettings={[
                        {
                            value: colorAnnouncementBackground,
                            onChange: onChangeColorAnnouncementBackground,
                            label: __("Announcement background", "teecontrol"),
                        },
                        {
                            value: colorAnnouncementText,
                            onChange: onChangeColorAnnouncementText,
                            label: __("Announcement text", "teecontrol"),
                        },
                        {
                            value: colorOddItems,
                            onChange: onChangeColorOddItems,
                            /* translators: %1$s is an index integer. */
                            label: __("Event color %1$s", "teecontrol").replace('%1$s', 1),
                        },
                        {
                            value: colorEvenItems,
                            onChange: onChangeColorEvenItems,
                            /* translators: %1$s is an index integer. */
                            label: __("Event color %1$s", "teecontrol").replace('%1$s', 2),
                        },
                    ]}
                />
                <PanelBody title={__("Settings", "teecontrol")}>
                    <SelectControl
                        label={__("Show loops or rounds", "teecontrol")}
                        value={showLoopsOrSets}
                        options={[
                            {
                                value: "loops",
                                label: __("Loops", "teecontrol"),
                            },
                            { value: "sets", label: __("Rounds", "teecontrol") },
                        ]}
                        onChange={onChangeShowLoopsOrSets}
                    />
                </PanelBody>
            </InspectorControls>
            <div {...useBlockProps({ style: styles })}>
                <ServerSideRender
                    block="teecontrol/course-agenda"
                    attributes={attributes}
                />
            </div>
        </>
    );
};
export default Edit;
