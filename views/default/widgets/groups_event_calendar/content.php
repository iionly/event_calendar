<?php

//the number of events to display
$num = (int) $vars['entity']->events_count;
if (!$num) {
	$num = 4;
}

// Get the events
$owner = elgg_get_page_owner_entity();
if(elgg_instanceof($owner, 'group')) {
	$one_day = time() - 60*60*24;
	$options = [
		'type' => 'object',
		'subtype' => 'event_calendar',
		'container_guid' => $owner->getGUID(),
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
}

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

if (elgg_is_logged_in()) {
	$group = get_entity(elgg_get_page_owner_guid());
	if ($group->isMember(elgg_get_logged_in_user_entity())) {
		echo elgg_view('output/url', [
			'href' => "event_calendar/add/$group->guid",
			'text' => elgg_echo('event_calendar:new'),
		]);
	}
}