<?php

elgg_require_js('event_calendar/event_calendar');
elgg_register_rss_link();

$start_date = elgg_extract('start_date', $vars, '');
$display_mode = elgg_extract('display_mode', $vars, '');
$filter = elgg_extract('filter_mode', $vars, '');
if ($filter && !in_array($filter, ['mine', 'friends', 'all'])) {
	$filter = 'all';
}
$region = elgg_extract('region', $vars, '-');

set_input('ical_date', $start_date);
set_input('ical_interval', $display_mode);
set_input('ical_calendar_filter', $filter);
$ical_region = ($region == '-') ? '' : $region;
set_input('ical_region', $ical_region);

$format = get_input('format', false);
if ($format && !in_array($format, ['paged', 'agenda', 'month', 'full'])) {
	$format = false;
}

elgg_push_breadcrumb(elgg_echo('item:object:event_calendar'), 'event_calendar/list');

if ($filter == 'mine') {
	$logged_in_user = elgg_get_logged_in_user_entity();
	elgg_push_breadcrumb($logged_in_user->name, 'event_calendar/owner/' . $logged_in_user->username);
} else if ($filter == 'friends') {
	$logged_in_user = elgg_get_logged_in_user_entity();
	elgg_push_breadcrumb($logged_in_user->name, 'event_calendar/owner/' . $logged_in_user->username);
	elgg_push_breadcrumb(elgg_echo('friends'));
} else if ($filter == 'open') {
	elgg_push_breadcrumb(elgg_echo('event_calendar:show_open'));
}
if(event_calendar_can_add()) {
	elgg_register_menu_item('title', [
		'name' => 'add',
		'href' => "event_calendar/add",
		'text' => elgg_echo('event_calendar:add'),
		'link_class' => 'elgg-button elgg-button-action event-calendar-button-add',
	]);
}

$params = event_calendar_generate_listing_params('list', 0, $start_date, $display_mode, $filter, $region, $format);
$title = $params['title'];

if (elgg_get_plugin_setting('ical_import_export', 'event_calendar') == "yes") {
	set_input('ical_calendar_title_menu', true);
	$url = current_page_url();
	if (substr_count($url, '?')) {
		$url .= "&view=ical";
	} else {
		$url .= "?view=ical";
	}

	$url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8', false);
	$menu_options = [
		'name' => 'ical',
		'id' => 'event-calendar-ical-link',
		'text' => '<img src="' . elgg_get_simplecache_url('event_calendar/ics.png') . '" />',
		'href' => $url,
		'title' => elgg_echo('feed:ical'),
		'priority' => 800,
	];
	$menu_item = ElggMenuItem::factory($menu_options);
	elgg_register_menu_item('footer', $menu_item);
}

$body = elgg_view_layout('default', $params);

echo elgg_view_page($title, $body);
