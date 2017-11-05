<?php

$start_date = elgg_extract('start_date', $vars);
$end_date = elgg_extract('end_date', $vars);
$filter = elgg_extract('filter', $vars, 'all');
$container_guid = elgg_extract('container_guid', $vars, 0);
$region = elgg_extract('region', $vars, '-');

$start_ts = strtotime($start_date);
$end_ts = strtotime($end_date);
if ($filter == 'all') {
	$events = event_calendar_get_events_between($start_ts, $end_ts, false, 0, 0, $container_guid, $region);
} else if ($filter == 'open') {
	$events = event_calendar_get_open_events_between($start_ts, $end_ts, false, 0, 0, $container_guid, $region);
} else if ($filter == 'friends') {
	$user_guid = elgg_get_logged_in_user_guid();
	$events = event_calendar_get_events_for_friends_between($start_ts, $end_ts, false, 0, 0, $user_guid, $container_guid, $region);
} else if ($filter == 'mine') {
	$user_guid = elgg_get_logged_in_user_guid();
	$events = event_calendar_get_events_for_user_between($start_ts, $end_ts, false, 0, 0, $user_guid, $container_guid, $region);
}
$event_array = [];
$times_supported = elgg_get_plugin_setting('times', 'event_calendar') != 'no';
$type_display = elgg_get_plugin_setting('type_display', 'event_calendar');
$polls_supported = elgg_is_active_plugin('event_poll');

foreach($events as $e) {
	$event = $e['event'];
	$event_data = $e['data'];
	$c = count($event_data);
	foreach($event_data as $ed) {
		$event_item = [
			'guid' => $event->guid,
			'title' => $event->title,
			'start' => date('c', $ed['start_time']),
			'end' => date('c', $ed['end_time']),
		];
		if (!$times_supported || ($event->schedule_type == 'all_day')) {
			$event_item['allDay'] = true;
		} else {
			$event_item['allDay'] = false;
		}

		if ($type_display == 'yes' && $event->event_type) {
			$color = event_calendar_map_type_to_color($event->event_type);

			if ($color) {
				$event_item['backgroundColor'] = $color;
				$event_item['borderColor'] = $color;
			}
		}

		if ($polls_supported && isset($e['is_event_poll']) && $e['is_event_poll']) {
			$event_item['className'] = 'event-poll-class';
			$event_item['title'] .= ' '.elgg_echo('event_calendar:poll_suffix');
			$event_item['is_event_poll'] = true;
			$event_item['url'] = elgg_get_site_url().'event_poll/vote/'.$event->guid;
			$event_item['minutes'] = $ed['minutes'];
			$event_item['iso_date'] = $ed['iso_date'];
		} else {
			$event_item['id'] = $event->guid;
			$event_item['is_event_poll'] = false;
			$event_item['url'] = elgg_get_site_url().'ajax/view/event_calendar/popup?guid='.$event->guid;
		}

		// Allow other plugins to modify the data
		$params = ['entity' => $event];
		$event_item = elgg_trigger_plugin_hook('prepare', 'event_calendar:json', $params, $event_item);

		$event_array[] = $event_item;
	}
}

$json_events_string = json_encode($event_array);
echo $json_events_string;