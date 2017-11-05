<?php

elgg_require_js('event_calendar/event_calendar');
elgg_register_rss_link();

$container_guid = elgg_extract('container_guid', $vars, 0);
$start_date = elgg_extract('start_date', $vars, '');
$display_mode = elgg_extract('display_mode', $vars, '');
$filter = elgg_extract('filter_mode', $vars, '');
$region = elgg_extract('region', $vars, '-');

if (!$container_guid) {
	elgg_gatekeeper();
	$container_guid = elgg_get_logged_in_user_guid();
}

elgg_push_breadcrumb(elgg_echo('item:object:event_calendar'), 'event_calendar/list');

$owner = get_entity($container_guid);
elgg_set_page_owner_guid($container_guid);
elgg_push_breadcrumb($owner->name, 'event_calendar/owner/' . $owner->username);
if (event_calendar_can_add()) {
	elgg_register_menu_item('title', [
		'name' => 'add',
		'href' => "event_calendar/add",
		'text' => elgg_echo('event_calendar:add'),
		'link_class' => 'elgg-button elgg-button-action event-calendar-button-add',
	]);
}

$params = event_calendar_generate_listing_params('owner', $container_guid, $start_date, $display_mode, $filter, $region);
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