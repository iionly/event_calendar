<?php

$page = elgg_extract('page', $vars);

$event_calendar_listing_format_allow_user_change = elgg_get_plugin_setting('listing_format_allow_user_change', 'event_calendar', 'yes');
if ($event_calendar_listing_format_allow_user_change == 'yes') {
	$url = current_page_url();
	if (substr_count($url, '?')) {
		$pos = strpos($url, '?');
		$url = substr($url, 0, $pos);
	}
	$url .= "?";

	elgg_register_menu_item('page', [
		'name' => 'A10_event_calendar',
		'text' => elgg_echo('event_calendar:format:paged'),
		'href' => $url . 'format=paged',
		'section' => 'A'
	]);
	elgg_register_menu_item('page', [
		'name' => 'A20_event_calendar_agenda',
		'text' => elgg_echo('event_calendar:format:agenda'),
		'href' => $url . 'format=agenda',
		'section' => 'A'
	]);
	elgg_register_menu_item('page', [
		'name' => 'A30_event_calendar_month',
		'text' => elgg_echo('event_calendar:format:month'),
		'href' => $url . 'format=month',
		'section' => 'A'
	]);
	elgg_register_menu_item('page', [
		'name' => 'A40_event_calendar_full',
		'text' => elgg_echo('event_calendar:format:full'),
		'href' => $url . 'format=full',
		'section' => 'A'
	]);
}

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
