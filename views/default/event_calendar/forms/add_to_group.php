<?php

// Game plan - display two drop downs: one with a list of groups
// without this event, one with - can add using one and remove using
// the other
// the user must have the authority to edit the groups

$event_id = $vars['event']->guid;
$event_container = $vars['event']->container_guid;

// get the list of all groups:

if (elgg_is_admin_logged_in()) {
	$groups = elgg_get_entities([
		'type' => 'group',
		'limit' => 5000,
	]);
} else {
	$groups = elgg_get_entities([
		'type' => 'group',
		'owner_guid' => elgg_get_logged_in_user_guid(),
		'limit' => 5000,
	]);
}

// split the group list into two lists

$add_options = [];
$remove_options = [];
$remove_group = elgg_get_entities([
	'relationship' => 'display_on_group',
	'relationship_guid' => $event_id,
	'inverse_relationship' => false,
	'limit' => 5000,
]);
$remove_group_ids = [];
foreach ($remove_group as $group) {
	$remove_group_ids[] = $group->guid;
	if ($group->guid != $event_container && $group->canEdit()) {
		$remove_options[$group->guid] = $group->name; 
	}
}

if ($remove_group) {
	foreach($groups as $group) {
		if (($group->guid != $event_container) && !in_array($group->guid, $remove_group_ids)) {
			$add_options[$group->guid] = $group->name;
		}
	}
} else {
	foreach($groups as $group) {
		if ($group->guid != $event_container && $group->canEdit()) {
			$add_options[$group->guid] = $group->name;
		}
	}
}

if ($add_options || $remove_options) {
	echo '<div class="mtl"><div class="event-calendar-edit-form-block"></div>';
	$event_bit = elgg_view_field([
		'#type' => 'hidden',
		'name' => 'event_id',
		'value' => $event_id,
	]);

	if ($add_options) {
		$add_pulldown = elgg_view_field([
			'#type' => 'select',
			'#label' => elgg_echo('event_calendar:add_to_group:add_group_title'),
			'name' => 'group_id',
			'options_values' => $add_options,
		]);
		$submit_add = elgg_view_field([
			'#type' => 'submit',
			'value' => elgg_echo('event_calendar:add_to_group:add_group_button'),
			'class' => 'mbl',
		]);
		echo elgg_view('input/form', [
			"body" => $event_bit.$add_pulldown.$submit_add,
			"action" => elgg_get_site_url()."action/event_calendar/add_to_group",
		]);
	}
	
	if ($remove_options) {
		$remove_pulldown = elgg_view_field([
			'#type' => 'select',
			'#label' => elgg_echo('event_calendar:add_to_group:remove_group_title'),
			'name' => 'group_id',
			'options_values' => $remove_options,
		]);
		$submit_remove = elgg_view_field([
			'#type' => 'submit',
			'value' => elgg_echo('event_calendar:add_to_group:remove_group_button'),
			'class' => 'mbl',
		]);
		echo elgg_view('input/form', [
			"body" => $event_bit.$remove_pulldown.$submit_remove,
			"action" => elgg_get_site_url()."action/event_calendar/remove_from_group",
		]);
	}
	echo '<div class="event-calendar-edit-form-block"></div></div>';
}
