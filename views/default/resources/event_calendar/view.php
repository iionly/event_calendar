<?php

$event_guid = elgg_extract('guid', $vars, '');

// add personal calendar button and links
elgg_push_context('event_calendar:view');
$event = get_entity($event_guid);

if (elgg_get_plugin_setting('ical_import_export', 'event_calendar') == "yes") {
	set_input('ical_calendar_title_menu', true);
}

elgg_push_breadcrumb(elgg_echo('item:object:event_calendar'), 'event_calendar/list');

if (!($event instanceof EventCalendar)) {
	$content = elgg_echo('event_calendar:error_nosuchevent');
	$title = elgg_echo('event_calendar:generic_error_title');
} else {
	$title = htmlspecialchars($event->title);
	$event_container = get_entity($event->container_guid);
	if ($event_container instanceof ElggGroup) {
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

$body = elgg_view_layout('default', $params);

echo elgg_view_page($title, $body);
