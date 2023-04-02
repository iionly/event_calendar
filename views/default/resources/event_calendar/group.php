<?php

elgg_require_js('event_calendar/event_calendar');
elgg_register_rss_link();

$container_guid = elgg_extract('container_guid', $vars, 0);
$start_date = elgg_extract('start_date', $vars, '');
$display_mode = elgg_extract('display_mode', $vars, '');
$filter = elgg_extract('filter_mode', $vars, '');
$region = elgg_extract('region', $vars, '-');
$format = elgg_extract('format', $vars, false);

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

$params = event_calendar_generate_listing_params('group', $container_guid, $start_date, $display_mode, $filter, $region, $format);
$title = $params['title'];

if (elgg_get_plugin_setting('ical_import_export', 'event_calendar') == "yes") {
	$url = current_page_url();
	if (substr_count($url, '?')) {
		$url .= "&view=ical";
	} else {
		$url .= "?view=ical";
	}

	$url = elgg_format_url($url);
	$menu_options = [
		'name' => 'ical',
		'id' => 'event-calendar-ical-link',
		'text' => '<img src="' . elgg_get_simplecache_url('event_calendar/ics.png') . '" />',
		'href' => $url,
		'title' => elgg_echo('feed:ical'),
		'priority' => 800,
	];
	$menu_item = ElggMenuItem::factory($menu_options);
	elgg_register_menu_item('extras', $menu_item);
}

$body = elgg_view_layout("content", $params);

echo elgg_view_page($title, $body);