<?php

/**
 * Elgg event calendar widget
 *
 * @package event_calendar
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Kevin Jardine <kevin@radagast.biz>
 * @copyright Radagast Solutions 2008
 * @link http://radagast.biz/
 *
 */

$widget = elgg_extract('entity', $vars);

$limit = (int) $widget->num_display ?: 4;

// Get the events
$one_day = time() - 60*60*24;
$events = elgg_get_entities([
	'type' => 'object',
	'subtype' => EventCalendar::SUBTYPE,
	'relationship' => 'personal_event',
	'relationship_guid' => elgg_get_page_owner_guid(),
	'metadata_name_value_pairs' => [
		['name' => 'start_date', 'value' => $one_day,  'operand' => '>'],
		['name' => 'end_date', 'value' => $one_day,  'operand' => '>'],
	],
	'metadata_name_value_pairs_operator' => 'OR',
	'order_by_metadata' => [
		'name' => 'start_date',
		'direction' => 'ASC',
		'as' => 'integer',
	],
	'limit' => $limit,
]);

// If there are any events to view, view them
if (is_array($events) && sizeof($events) > 0) {
	echo "<div id=\"widget_calendar\">";
	foreach($events as $event) {
		echo elgg_view("object/event_calendar", ['entity' => $event]);
	}
	echo "</div>";

	$more_link = elgg_view('output/url', [
		'href' => "event_calendar/owner/" . elgg_get_page_owner_entity()->username,
		'text' => elgg_echo('link:view:all'),
		'is_trusted' => true,
	]);
	echo "<div class=\"elgg-widget-more\">$more_link</div>";
} else {
	echo '<p>' . elgg_echo('event_calendar:no_events_found') . '</p>';
}
