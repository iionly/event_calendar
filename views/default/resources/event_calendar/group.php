<?php

require_once(elgg_get_plugins_path() . 'event_calendar/models/model.php');

elgg_require_js('event_calendar/event_calendar');
elgg_register_rss_link();

$container_guid = elgg_extract('container_guid', $vars, 0);
$start_date = elgg_extract('start_date', $vars, '');
$display_mode = elgg_extract('display_mode', $vars, '');
$filter = elgg_extract('filter_mode', $vars, '');
$region = elgg_extract('region', $vars, '-');

set_input('ical_group_id', $container_guid);
set_input('ical_date', $start_date);
set_input('ical_interval', $display_mode);
set_input('ical_region', $region == '-' ? '' : $region );
set_input('ical_calendar_filter', $filter_mode == '' ? 'all' : $filter_mode);

if (!$container_guid) {
	elgg_gatekeeper();
	elgg_group_gatekeeper();
	$container_guid = elgg_get_page_owner_guid();
}

elgg_push_breadcrumb(elgg_echo('item:object:event_calendar'), 'event_calendar/list');

$group = get_entity($container_guid);
if (!event_calendar_activated_for_group($group)) {
	forward();
}
elgg_push_breadcrumb($group->name, 'event_calendar/group/' . $group->getGUID());
elgg_set_page_owner_guid($container_guid);
if(event_calendar_can_add($container_guid)) {
	elgg_register_menu_item('title', [
		'name' => 'add',
		'href' => "event_calendar/add/".$container_guid,
		'text' => elgg_echo('event_calendar:add'),
		'link_class' => 'elgg-button elgg-button-action event-calendar-button-add',
	]);
}

$params = event_calendar_generate_listing_params('group', $container_guid, $start_date, $display_mode, $filter, $region);
$title = $params['title'];

$body = elgg_view_layout("content", $params);

echo elgg_view_page($title, $body);