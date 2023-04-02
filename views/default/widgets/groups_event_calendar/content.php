<?php

$widget = elgg_extract('entity', $vars);

$limit = (int) $widget->events_count ?: 4;

// Get the events
$owner = elgg_get_page_owner_entity();

$container_guid = elgg_get_page_owner_guid();
$group = get_entity($container_guid);

elgg_push_context('groups');
$one_day = time() - 60*60*24;
$events = elgg_get_entities([
	'type' => 'object',
	'subtype' => EventCalendar::SUBTYPE,
	'container_guid' => $container_guid,
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
elgg_pop_context();

if ($group->canWriteToContainer(0, 'object', EventCalendar::SUBTYPE)) {
	echo elgg_view('output/url', [
		'href' => "event_calendar/add/$group->guid",
		'text' => elgg_echo('event_calendar:new'),
	]);
}
