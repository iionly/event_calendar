<?php

$event = $vars['event'];
$users = event_calendar_get_users_for_event($event->guid, 0);
$guids = [];
foreach($users as $user) {
	$guids[] = $user->guid;
}

// TODO: if the event container is a group need to restrict user picker to the members of the group?
$content = elgg_view_field([
	'#type' => 'userpicker',
	'values' => $guids,
]);
$content .= elgg_view_field([
	'#type' => 'hidden',
	'name' => 'event_guid',
	'value' => $event->guid,
]);
$content .= elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('submit'),
	'name' => 'submit_manage_subscribers',
	'id' => 'submit-manage-subscribers',
]);

echo $content;
