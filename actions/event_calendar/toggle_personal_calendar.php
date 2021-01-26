<?php

require_once(elgg_get_plugins_path() . 'event_calendar/models/model.php');

$event_guid = get_input('event_guid', 0);
$user_guid = get_input('user_guid', elgg_get_logged_in_user_guid());
$other = get_input('other', '');

if ($other) {
	$remove_response = elgg_echo('event_calendar:added_to_the_calendar');
	$add_response = elgg_echo('event_calendar:removed_from_the_calendar');
	$add_error = elgg_echo('event_calendar:add_to_the_calendar_error');
} else {
	$remove_response = elgg_echo('event_calendar:remove_from_my_calendar_response');
	$add_response = elgg_echo('event_calendar:add_to_my_calendar_response');
	$add_error = elgg_echo('event_calendar:add_to_my_calendar_error');
}

if (event_calendar_has_personal_event($event_guid, $user_guid)) {
	event_calendar_remove_personal_event($event_guid, $user_guid);
	$response = ['success' => true, 'message' => $remove_response];
} else {
	if (event_calendar_add_personal_event($event_guid, $user_guid)) {
		$event_calendar_add_users_notify = elgg_get_plugin_setting('add_users_notify', 'event_calendar');
		if ($event_calendar_add_users_notify == 'yes') {
			if ($user_guid != elgg_get_logged_in_user_guid()) {
				$user = get_user($user_guid);
				$user_language = ($user->language) ? $user->language : (($site_language = elgg_get_config('language')) ? $site_language : 'en');
				$subject = elgg_echo('event_calendar:add_users_notify:subject', [], $user_language);
				$event = get_entity($event_guid);
				$message = elgg_echo('event_calendar:add_users_notify:body', [$user->name, $event->title, $event->getURL()], $user_language);
				notify_user($user_guid, elgg_get_logged_in_user_guid(), $subject, $message, [
					'object' => $event,
					'action' => 'subscribe',
					'summary' => $subject,
				]);
			}
		}
		$response = ['success' => true, 'message' => $add_response];
	} else {
		$response = ['success' => false, 'message' => $add_error];
	}
}

echo json_encode($response);

exit;
