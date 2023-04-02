<?php

$event_guid = (int) get_input("event_guid", 0);
$event = get_entity($event_guid);
$group = get_entity($event->container_guid);

if (!(($group instanceof ElggGroup) && ($event instanceof EventCalendar) && $group->canEdit())) {
	return elgg_error_response(elgg_echo('event_calendar:remove_from_group_members:error'), "event_calendar/manage_users/$event_guid");
}

$members = $group->getMembers(['limit' => false]);
foreach($members as $member) {
	event_calendar_remove_personal_event($event->guid, $member->guid);
}

return elgg_ok_response('', elgg_echo('event_calendar:remove_from_group_members:success'), "event_calendar/manage_users/$event_guid");
