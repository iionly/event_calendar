<?php
// extended logic for BBB

$event_guid = (int) get_input('event_guid');
$event = get_entity($event_guid);

if ($event instanceof EventCalendar) {
	// make sure that the conference still exists, and if not, try recreating it
	if (event_calendar_conference_exists($event)) {
		event_calendar_join_conference($event);
	} else if (event_calendar_create_bbb_conf($event)) {
		event_calendar_join_conference($event);
	} else {
		return elgg_error_response(elgg_echo('event_calendar:couldnotjoin'), $event->getURL());
	}
} else {
	return elgg_error_response(elgg_echo('event_calendar:error_nosuchevent'), 'event_calendar/list');
}
