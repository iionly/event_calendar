<?php

/**
 * Elgg show events view
 *
 * @package event_calendar
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Kevin Jardine <kevin@radagast.biz>
 * @copyright Radagast Solutions 2008
 * @link http://radagast.biz/
 *
 */

$listing_format = $vars['listing_format'];

if ($vars['events']) {
	if ($listing_format == 'agenda') {
		$vars['events'] = event_calendar_flatten_event_structure($vars['events']);
		$event_list = elgg_view('event_calendar/agenda_view', $vars);
	} else if ($listing_format == 'paged') {
		$vars['events'] = event_calendar_flatten_event_structure($vars['events']);
		$event_list = elgg_view('event_calendar/paged_view', $vars);
	} else if ($listing_format == 'full') {
		$event_list = elgg_view('event_calendar/full_calendar_view', $vars);
	} else {
		$vars['events'] = event_calendar_flatten_event_structure($vars['events']);
		$options = [
			'list_class' => 'elgg-list-entity',
			'full_view' => false,
			'pagination' => true,
			'list_type' => 'listing',
			'list_type_toggle' => false,
			'offset' => $vars['offset'],
			'limit' => $vars['limit'],
		];
		$event_list = elgg_view_entity_list($vars['events'], $options);
	}
} else {
	if ($listing_format == 'full') {
		// show the empty calendar
		$event_list = elgg_view('event_calendar/full_calendar_view', $vars);
	} else {
		$event_list = '<p>' . elgg_echo('event_calendar:no_events_found') . '</p>';
	}
}
if ($listing_format == 'paged' || $listing_format == 'full') {
	echo $event_list;
} else {
	$output = elgg_format_element('div', ['id' => 'event_list', 'style' => 'float:left'], $event_list);
	$output .= elgg_format_element('div', ['style' => 'float:right'], elgg_view('event_calendar/calendar', $vars));
	echo elgg_format_element('div', ['style' => 'width:100%'], $output);
}
