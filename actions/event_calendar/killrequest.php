<?php
// this action allows an admin or event owner to reject a calendar request

$user_guid = (int) get_input('user_guid', elgg_get_logged_in_user_guid());
$event_guid = (int) get_input('event_guid');

$user = get_entity($user_guid);
$event = get_entity($event_guid);

if (!(($event instanceof EventCalendar)	&& ($user instanceof ElggUser) && $event->canEdit()	&& check_entity_relationship($user_guid, 'event_calendar_request', $event_guid))) {
	return elgg_error_response(elgg_echo('event_calendar:review_requests:error:reject'), REFERER);
}

remove_entity_relationship($user->guid, 'event_calendar_request', $event_guid);

return elgg_ok_response('', elgg_echo('event_calendar:requestkilled'), REFERER);
