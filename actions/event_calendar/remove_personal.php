<?php

$event_guid = (int) get_input('guid', 0);
$event = get_entity($event_guid);

if (!($event instanceof EventCalendar)) {
	return elgg_error_response(elgg_echo('event_calendar:remove_from_my_calendar_response:error'), REFERER);
}

$user_guid = elgg_get_logged_in_user_guid();
event_calendar_remove_personal_event($event_guid, $user_guid);

return elgg_ok_response('', elgg_echo('event_calendar:remove_from_my_calendar_response'), REFERER);
