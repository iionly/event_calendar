<?php

function event_calendar_handle_join(\Elgg\Event $event) {
	$object = $event->getObject();
	$group = $object['group'];
	$user = $object['user'];

	$user_guid = $user->getGUID();

	$events = elgg_get_entities([
		'type' => 'object',
		'subtype' => EventCalendar::SUBTYPE,
		'container_guid' => $group->getGUID(),
		'limit' => false,
		'batch' => true,
	]);

	foreach ($events as $single_event) {
		$event_id = $single_event->getGUID();
		event_calendar_add_personal_event($event_id, $user_guid);
	}
}

function event_calendar_handle_leave(\Elgg\Event $event) {
	$object = $event->getObject();
	$group = $object['group'];
	$user = $object['user'];

	$user_guid = $user->getGUID();

	$events = elgg_get_entities([
		'type' => 'object',
		'subtype' => EventCalendar::SUBTYPE,
		'container_guid' => $group->getGUID(),
		'limit' => false,
		'batch' => true,
	]);

	foreach ($events as $single_event) {
		$event_id = $single_event->getGUID();
		event_calendar_remove_personal_event($event_id, $user_guid);
	}
}
