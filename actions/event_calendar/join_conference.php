<?php
// extended logic for BBB

require_once(elgg_get_plugins_path() . 'event_calendar/models/model.php');

$event_guid = get_input('event_guid');
$event = get_entity($event_guid);

if (elgg_instanceof($event, 'object', 'event_calendar')) {
	// make sure that the conference still exists, and if not, try recreating it
	if (event_calendar_conference_exists($event)) {
		event_calendar_join_conference($event);
	} else if (event_calendar_create_bbb_conf($event)) {
		event_calendar_join_conference($event);
	} else {
		register_error(elgg_echo('event_calendar:couldnotjoin'));
		forward($event->getURL());
	}
} else {
	register_error(elgg_echo('event_calendar:error_nosuchevent'));
	forward('event_calendar/list');
}
