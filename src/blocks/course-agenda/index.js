/**
 * WordPress dependencies
 */
import { registerBlockType } from "@wordpress/blocks";
import { __ } from "@wordpress/i18n";

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import "./style.scss";

/**
 * Internal dependencies
 */
import json from "./block.json";
import Edit from "./edit";

// Destructure the json file to get the name of the block
// For more information on how this works, see: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Operators/Destructuring_assignment
const { apiVersion, icon, category, name } = json;

// Register the block
registerBlockType(name, {
    apiVersion,
    title: __("Course Agenda", "teecontrol"),
    icon,
    category,
    edit: Edit,
});
