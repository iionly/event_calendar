<?php

require_once(elgg_get_plugins_path() . 'event_calendar/models/model.php');

// display a list of all the members of the container of $event_guid and allowing
// adding or removing them

// TODO: make this an optional feature, toggled off
elgg_require_js('event_calendar/event_calendar');

$event_guid = elgg_extract('guid', $vars, '');

$event = get_entity($event_guid);
$limit = 10;
$offset = get_input('offset', 0);

$event_calendar_add_users = elgg_get_plugin_setting('add_users', 'event_calendar');
if ($event_calendar_add_users != 'yes') {
	register_error(elgg_echo('event_calendar:feature_not_activated'));
	forward();
	exit;
}

elgg_push_breadcrumb(elgg_echo('item:object:event_calendar'), 'event_calendar/list');

if (!elgg_instanceof($event, 'object', 'event_calendar')) {
	$content = elgg_echo('event_calendar:error_nosuchevent');
	$title = elgg_echo('event_calendar:generic_error_title');
} else {
	$title = elgg_echo('event_calendar:manage_users:title', [$event->title]);
	$event_container = get_entity($event->container_guid);
	if ($event_container->canEdit()) {
		if (elgg_instanceof($event_container, 'group')) {
			elgg_set_page_owner_guid($event->container_guid);
			elgg_push_breadcrumb($event_container->name, 'event_calendar/group/' . $event->container_guid);
			if ($event_container->canEdit()) {
				event_calendar_handle_menu($event_guid);
			}
			elgg_register_menu_item('title', [
				'name' => 'remove_from_group_members',
				'href' => elgg_add_action_tokens_to_url('action/event_calendar/remove_from_group_members?event_guid='.$event_guid),
				'text' => elgg_echo('event_calendar:remove_from_group_members:button'),
				'link_class' => 'elgg-button elgg-button-action',
			]);
			elgg_register_menu_item('title', [
				'name' => 'add_to_group_members',
				'href' => elgg_add_action_tokens_to_url('action/event_calendar/add_to_group_members?event_guid='.$event_guid),
				'text' => elgg_echo('event_calendar:add_to_group_members:button'),
				'link_class' => 'elgg-button elgg-button-action',
			]);
			if(event_calendar_can_add($event_container->getGUID())) {
				elgg_register_menu_item('title', [
					'name' => 'add',
					'href' => "event_calendar/add/".$event_container->getGUID(),
					'text' => elgg_echo('event_calendar:add'),
					'link_class' => 'elgg-button elgg-button-action event-calendar-button-add',
				]);
			}
			$users = $event_container->getMembers(['limit' => $limit, 'offset' => $offset]);
			$count = $event_container->getMembers(['limit' => $limit, 'offset' => $offset, 'count' => true]);

			set_input('guid', $event->guid);
			elgg_extend_view('user/elements/summary', 'event_calendar/calendar_toggle');

			$options = [
				'full_view' => false,
				'list_type_toggle' => false,
				'limit' => $limit,
				'offset' => $offset,
				'event_calendar_event' => $event,
				'pagination' => true,
				'count' => $count,
			];
			$content .= elgg_view_entity_list($users, $options);
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
			$content = '<p>'.elgg_echo('event_calendar:manage_users:description').'</p>';
			$content .= elgg_view_form('event_calendar/manage_subscribers', [], ['event' => $event]);
		}
		elgg_push_breadcrumb($event->title, $event->getURL());
		elgg_push_breadcrumb(elgg_echo('event_calendar:manage_users:breadcrumb'));

	} else {
		$content = elgg_echo('event_calendar:manage_users:unauthorized');
	}
}
$params = [
	'title' => $title,
	'content' => $content,
	'filter' => '',
];

$body = elgg_view_layout("content", $params);

echo elgg_view_page($title, $body);