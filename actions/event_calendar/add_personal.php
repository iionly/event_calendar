<?php

$event_guid = (int) get_input('guid', 0);
$event = get_entity($event_guid);

if ($event instanceof EventCalendar) {
	$user_guid = elgg_get_logged_in_user_guid();
	
	if (!event_calendar_has_personal_event($event_guid, $user_guid)) {
		if (event_calendar_add_personal_event($event_guid, $user_guid)) {
			return elgg_ok_response('', elgg_echo('event_calendar:add_to_my_calendar_response'), REFERER);
		}
	}
}

return elgg_error_response(elgg_echo('event_calendar:add_to_my_calendar_error'), REFERER);
