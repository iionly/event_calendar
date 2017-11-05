<?php

/**
 * Elgg event_calendar widget
 *
 * @package event_calendar
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Kevin Jardine <kevin@radagast.biz>
 * @copyright Radagast Solutions 2008
 * @link http://radagast.biz/
 *
 */

if (!$vars['entity']->num_display) {
	$num_display = 4;
} else {
	$num_display = $vars['entity']->num_display;
}

echo elgg_view_field([
	'#type' => 'number',
	'#label' => elgg_echo("event_calendar:num_display"),
	'name' => 'params[num_display]',
	'value' => $num_display,
	'min' => '1',
	'max' => '20',
	'step' => '1',
]);