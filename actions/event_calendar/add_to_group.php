<?php

$event_id = (int) get_input("event_id", 0);
$group_id = (int) get_input("group_id", 0);
$event = get_entity($event_id);
$group = get_entity($group_id);

if (!(($group instanceof ElggGroup) && $group->canEdit())) {
	forward($event->getUrl());
	return elgg_error_response(elgg_echo('event_calendar:add_to_group:error'), $event->getUrl());
}
	
add_entity_relationship($event_id, "display_on_group", $group_id);

return elgg_ok_response('', elgg_echo('event_calendar:add_to_group:success'), $event->getUrl());
