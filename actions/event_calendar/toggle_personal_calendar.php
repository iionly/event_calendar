<?php

elgg_load_library('elgg:event_calendar');

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
	$button_text = elgg_echo('event_calendar:add_to_the_calendar');
	event_calendar_remove_personal_event($event_guid, $user_guid);
	$response = array('success' => true, 'message' => $remove_response, 'button_text' => $button_text, 'button_class' => 'elgg-button elgg-button-submit event-calendar-personal-calendar-toggle');
} else {
	if (event_calendar_add_personal_event($event_guid, $user_guid)) {
		$event_calendar_add_users_notify = elgg_get_plugin_setting('add_users_notify', 'event_calendar');
		if ($event_calendar_add_users_notify == 'yes') {
			if ($user_guid != elgg_get_logged_in_user_guid()) {
				$subject = elgg_echo('event_calendar:add_users_notify:subject');
				$event = get_entity($event_guid);
				$user = get_user($user_guid);
				$message = elgg_echo('event_calendar:add_users_notify:body', array($user->name, $event->title, $event->getURL()));
				notify_user($user_guid, elgg_get_logged_in_user_guid(), $subject, $message, array(
					'object' => $event,
					'action' => 'event_calendar_notification_subscribe',
					'summary' => $subject
				));
			}
		}
		$button_text = elgg_echo('event_calendar:remove_from_the_calendar_button');
		$response = array('success' => true, 'message' => $add_response, 'button_text' => $button_text, 'button_class' => 'elgg-button elgg-button-delete event-calendar-personal-calendar-toggle');
	} else {
		$response = array('success' => false, 'message' => $add_error);
	}
}

echo json_encode($response);

exit;
