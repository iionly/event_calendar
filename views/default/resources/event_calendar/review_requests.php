<?php

$event_guid = elgg_extract('event_guid', $vars);
$event = get_entity($event_guid);

elgg_push_breadcrumb(elgg_echo('item:object:event_calendar'), 'event_calendar/list');

if (!elgg_instanceof($event, 'object', 'event_calendar')) {
	$content = elgg_echo('event_calendar:error_nosuchevent');
	$title = elgg_echo('event_calendar:generic_error_title');
} else {
	$title = elgg_echo('event_calendar:review_requests_title', [htmlspecialchars($event->title)]);
	$event_container = get_entity($event->container_guid);
	if (elgg_instanceof($event_container, 'group')) {
		elgg_set_page_owner_guid($event->container_guid);
		elgg_push_breadcrumb($event_container->name, 'event_calendar/group/' . $event->container_guid);
		if ($event_container->canEdit()) {
			event_calendar_handle_menu($event_guid);
		}
		if(event_calendar_can_add($event_container->getGUID())) {
			elgg_register_menu_item('title', [
				'name' => 'add',
				'href' => "event_calendar/add/".$event_container->getGUID(),
				'text' => elgg_echo('event_calendar:add'),
				'link_class' => 'elgg-button elgg-button-action event-calendar-button-add',
			]);
		}
	} else {
		elgg_push_breadcrumb($event_container->name, 'event_calendar/owner/' . $event_container->username);
		if ($event->canEdit()) {
			event_calendar_handle_menu($event_guid);
		}
		if(event_calendar_can_add()) {
			elgg_register_menu_item('title', [
				'name' => 'add',
				'href' => "event_calendar/add",
				'text' => elgg_echo('event_calendar:add'),
				'link_class' => 'elgg-button elgg-button-action event-calendar-button-add',
			]);
		}
	}
	elgg_push_breadcrumb($event->title, $event->getURL());
	elgg_push_breadcrumb(elgg_echo('event_calendar:review_requests_menu_title'));

	if ($event->canEdit()) {
		$requests = elgg_get_entities_from_relationship([
			'relationship' => 'event_calendar_request',
			'relationship_guid' => $event_guid,
			'inverse_relationship' => true,
			'limit' => false,
		]);
		if ($requests) {
			$content = elgg_view('event_calendar/review_requests', ['requests' => $requests, 'entity' => $event]);
		} else {
			$content = elgg_echo('event_calendar:review_requests_request_none');
		}
	} else {
		$content = elgg_echo('event_calendar:review_requests_error');
	}
}
$params = [
	'title' => $title,
	'content' => $content,
	'filter' => '',
];

$body = elgg_view_layout("content", $params);

echo elgg_view_page($title, $body);