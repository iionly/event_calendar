<?php

elgg_require_js('event_calendar/event_calendar');

$page_type = elgg_extract('page_type', $vars);
$guid = elgg_extract('guid', $vars, 0);
$start_date = elgg_extract('start_date', $vars, '');

$vars = [];
$vars['id'] = 'event-calendar-edit';
$vars['name'] = 'event_calendar_edit';
// just in case a feature adds an image upload
$vars['enctype'] = 'multipart/form-data';

$body_vars = [];

elgg_push_breadcrumb(elgg_echo('item:object:event_calendar'), 'event_calendar/list');

if ($page_type == 'edit') {
	$title = elgg_echo('event_calendar:manage_event_title');
	$event = get_entity((int)$guid);
	if (($event instanceof EventCalendar) && $event->canEdit()) {
		$body_vars['event'] = $event;
		$body_vars['form_data'] =  event_calendar_prepare_edit_form_vars($event, $page_type);

		$event_container = get_entity($event->container_guid);
		if ($event_container instanceof ElggGroup) {
			elgg_push_breadcrumb($event_container->name, 'event_calendar/group/' . $event->container_guid);
			$body_vars['group_guid'] = $event_container->guid;
		} else {
			elgg_push_breadcrumb($event_container->name, 'event_calendar/owner/' . $event_container->username);
			$body_vars['group_guid'] = 0;
		}
		elgg_push_breadcrumb($event->title, $event->getURL());
		elgg_push_breadcrumb(elgg_echo('event_calendar:manage_event_title'));

		$content = elgg_view_form('event_calendar/edit', $vars, $body_vars);
	} else {
		$content = elgg_echo('event_calendar:error_event_edit');
	}
} else {
	$title = elgg_echo('event_calendar:add_event_title');

	if ($guid) {
		// add to group
		$group = get_entity($guid);
		if ($group instanceof ElggGroup) {
			$body_vars['group_guid'] = $guid;
			elgg_push_breadcrumb($group->name, 'event_calendar/group/' . $guid);
			elgg_push_breadcrumb(elgg_echo('event_calendar:add_event_title'));
			$body_vars['form_data'] = event_calendar_prepare_edit_form_vars(null, $page_type, $start_date);
			$content = elgg_view_form('event_calendar/edit', $vars, $body_vars);
		} else {
			$content = elgg_echo('event_calendar:no_group');
		}
	} else {
		$body_vars['group_guid'] = 0;
		elgg_push_breadcrumb(elgg_echo('event_calendar:add_event_title'));
		$body_vars['form_data'] = event_calendar_prepare_edit_form_vars(null, $page_type, $start_date);

		$content = elgg_view_form('event_calendar/edit', $vars, $body_vars);
	}
}

$params = [
	'title' => $title,
	'content' => $content,
	'filter' => '',
];

$body = elgg_view_layout('default', $params);

echo elgg_view_page($title, $body);
