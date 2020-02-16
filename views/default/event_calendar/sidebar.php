<?php

if (elgg_get_plugin_setting('ical_import_export', 'event_calendar') == "yes") {
	$url = current_page_url();
	if (substr_count($url, '?')) {
		$url .= "&view=ical";
	} else {
		$url .= "?view=ical";
	}

	// $url = elgg_format_element($url);
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
	echo elgg_view_menu(
		'extras'
	);
}

if (elgg_extract('listing_format', $vars) === 'agenda') {
	echo elgg_view('event_calendar/calendar', $vars);
}

$page = elgg_extract('page', $vars);
switch ($page) {
	case 'all':
	case 'open':
		echo elgg_view('page/elements/comments_block', [
			'subtypes' => 'event_calendar',
		]);
		echo elgg_view('page/elements/tagcloud_block', [
			'subtypes' => 'event_calendar',
		]);
		break;
	case 'owner':
	case 'mine':
	case 'group':
		echo elgg_view('page/elements/comments_block', [
			'subtypes' => 'event_calendar',
			'owner_guid' => elgg_get_page_owner_guid(),
		]);
		echo elgg_view('page/elements/tagcloud_block', [
			'subtypes' => 'event_calendar',
			'owner_guid' => elgg_get_page_owner_guid(),
		]);
		break;
	case 'full_view':
		echo elgg_view('page/elements/tagcloud_block', [
			'subtypes' => 'event_calendar',
		]);
		break;
}
