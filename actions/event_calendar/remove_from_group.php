<?php

$event_id = (int) get_input("event_id", 0);
$group_id = (int) get_input("group_id", 0);
$event = get_entity($event_id);

remove_entity_relationship($event_id, "display_on_group", $group_id );

return elgg_ok_response('', elgg_echo('event_calendar:remove_from_group:success'), $event->getUrl());
