<?php

require_once(elgg_get_plugins_path() . 'event_calendar/models/model.php');

$event_guid = elgg_extract('guid', $vars, '');

// add personal calendar button and links
elgg_push_context('event_calendar:view');
$event = get_entity($event_guid);

elgg_push_breadcrumb(elgg_echo('item:object:event_calendar'), 'event_calendar/list');

if (!elgg_instanceof($event, 'object', 'event_calendar')) {
	$content = elgg_echo('event_calendar:error_nosuchevent');
	$title = elgg_echo('event_calendar:generic_error_title');
} else {
	$title = htmlspecialchars($event->title);
	$event_container = get_entity($event->container_guid);
	if (elgg_instanceof($event_container, 'group')) {
		if ($event_container->canEdit()) {
			event_calendar_handle_menu($event_guid);
		}
		elgg_push_breadcrumb($event_container->name, 'event_calendar/group/' . $event->container_guid);
		if(event_calendar_can_add($event_container->getGUID())) {
			elgg_register_menu_item('title', [
				'name' => 'add',
				'href' => "event_calendar/add/".$event_container->getGUID(),
				'text' => elgg_echo('event_calendar:add'),
				'link_class' => 'elgg-button elgg-button-action event-calendar-button-add',
			]);
		}
	} else {
		if ($event->canEdit()) {
			event_calendar_handle_menu($event_guid);
		}
		elgg_push_breadcrumb($event_container->name, 'event_calendar/owner/' . $event_container->username);
		if(event_calendar_can_add()) {
			elgg_register_menu_item('title', [
				'name' => 'add',
				'href' => "event_calendar/add",
				'text' => elgg_echo('event_calendar:add'),
				'link_class' => 'elgg-button elgg-button-action event-calendar-button-add',
			]);
		}
	}

	elgg_push_breadcrumb($event->title);
	$content = elgg_view_entity($event, ['full_view' => true]);
}

$params = [
	'title' => $title,
	'content' => $content,
	'filter' => '',
	'sidebar' => elgg_view('event_calendar/sidebar', ['page' => 'full_view']),
];

$body = elgg_view_layout("content", $params);

echo elgg_view_page($title, $body);