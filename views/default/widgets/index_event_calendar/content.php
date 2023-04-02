<?php

$widget = elgg_extract('entity', $vars);

$limit = (int) $widget->events_count ?: 4;

// Display $num (personal and group) events
// but don't show events that have been over for more than a day
// TODO How to deal with recurring events?
$one_day = time() - 60*60*24;
$events = elgg_get_entities([
	'type' => 'object',
	'subtype' => EventCalendar::SUBTYPE,
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
	'distinct' => false,
]);

// If there are any events to view, view them
if (is_array($events) && sizeof($events) > 0) {
	echo "<div id=\"widget_calendar\">";
	foreach($events as $event) {
		echo elgg_view("object/event_calendar", ['entity' => $event]);
	}
	echo "</div>";
} else {
	echo '<p>' . elgg_echo('event_calendar:no_events_found') . '</p>';
}
