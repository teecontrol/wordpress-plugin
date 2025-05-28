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

const Edit = ({ attributes, setAttributes }) => {
    const { colorEnabled, colorDisabled } = attributes;

    const styles = {
        "--teecontrol-enabled-color": colorEnabled,
        "--teecontrol-disabled-color": colorDisabled,
    };

    const onChangeColorEnabled = (val) => {
        setAttributes({ colorEnabled: val });
    };
    const onChangeColorDisabled = (val) => {
        setAttributes({ colorDisabled: val });
    };

    return (
        <>
            <InspectorControls>
                <PanelColorSettings
                    title={__("Color settings", "teecontrol")}
                    colorSettings={[
                        {
                            value: colorEnabled,
                            onChange: onChangeColorEnabled,
                            label: __("Enabled", "teecontrol"),
                        },
                        {
                            value: colorDisabled,
                            onChange: onChangeColorDisabled,
                            label: __("Disabled", "teecontrol"),
                        },
                    ]}
                />
            </InspectorControls>
            <div {...useBlockProps({ style: styles })}>
                <ServerSideRender
                    block="teecontrol/course-status"
                    attributes={attributes}
                />
            </div>
        </>
    );
};
export default Edit;
