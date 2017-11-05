<?php

//the number of events to display
$num = (int) $vars['entity']->events_count;
if (!$num) {
	$num = 4;
}

// Display $num (personal and group) events
// but don't show events that have been over for more than a day
// TODO How to deal with recurring events?
$one_day = time() - 60*60*24;
$options = [
	'type' => 'object',
	'subtype' => 'event_calendar',
	'metadata_name_value_pairs' => [
		['name' => 'start_date', 'value' => $one_day,  'operand' => '>'],
		['name' => 'end_date', 'value' => $one_day,  'operand' => '>'],
	],
	'metadata_name_value_pairs_operator' => 'OR',
	'order_by_metadata' => [
		'name' => 'start_date',
		'direction' => ASC,
		'as' => 'integer',
	],
	'limit' => $num,
];

$events = elgg_get_entities_from_metadata($options);

// If there are any events to view, view them
if (is_array($events) && sizeof($events) > 0) {
	echo "<div id=\"widget_calendar\">";
	foreach($events as $event) {
		echo elgg_view("object/event_calendar", ['entity' => $event]);
	}
	echo "</div>";
} else {
	echo elgg_echo('event_calendar:no_events_found');
}