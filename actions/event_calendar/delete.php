<?php

$event_guid = (int) get_input('guid', 0);
$event = get_entity($event_guid);

if (!(($event instanceof EventCalendar) && $event->canEdit())) {
	return elgg_error_response(elgg_echo('event_calendar:error_delete'), REFERER);
}

if (get_input('cancel', '')) {
	return elgg_ok_response('', elgg_echo('event_calendar:delete_cancel_response'), REFERER);
} else {
	$container = get_entity($event->container_guid);
	$event->delete();

	$forward = 'event_calendar/list';
	if ($container instanceof ElggGroup) {
		$forward = 'event_calendar/group/' . $container->guid;
	}

	return elgg_ok_response('', elgg_echo('event_calendar:delete_response'), $forward);
}
