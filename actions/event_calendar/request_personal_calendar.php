<?php
// asks the event owner to add you to the event

$event_guid = (int) get_input('guid', 0);
$user_guid = elgg_get_logged_in_user_guid();
$event = get_entity($event_guid);

if (!($event instanceof EventCalendar)) {
	return elgg_error_response(elgg_echo('event_calendar:request_event_error'), REFERER);
}

if (event_calendar_send_event_request($event, $user_guid)) {
	return elgg_ok_response('', elgg_echo('event_calendar:request_event_response'), REFERER);
}

return elgg_error_response(elgg_echo('event_calendar:request_event_error'), REFERER);
