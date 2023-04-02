<?php

elgg_require_js('event_calendar/event_calendar');

$event_guid = elgg_extract('guid', $vars, '');

$event = get_entity($event_guid);

elgg_push_breadcrumb(elgg_echo('item:object:event_calendar'), 'event_calendar/list');

if (!($event instanceof EventCalendar)) {
	$content = elgg_echo('event_calendar:error_nosuchevent');
	$title = elgg_echo('event_calendar:generic_error_title');
} else {
	$title = elgg_echo('event_calendar:users_for_event_title', [htmlspecialchars($event->title)]);
	$event_container = get_entity($event->container_guid);
	if ($event_container instanceof ElggGroup) {
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
	elgg_push_breadcrumb(elgg_echo('event_calendar:users_for_event_breadcrumb'));
	$limit = 12;
	$offset = get_input('offset', 0);
	$users = event_calendar_get_users_for_event($event_guid, $limit, $offset, false);
	$options = [
		'full_view' => false,
		'list_type_toggle' => false,
		'limit' => $limit,
		'offset' => $offset,
		'event_calendar_event' => $event,
	];

	set_input('guid', $event->guid);
	elgg_extend_view('user/elements/summary', 'event_calendar/calendar_toggle');

	$content = elgg_view_entity_list($users, $options);
}
$params = [
	'title' => $title,
	'content' => $content,
	'filter' => '',
];

$body = elgg_view_layout('default', $params);

echo elgg_view_page($title, $body);
