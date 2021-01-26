<?php

$count = sanitise_int($vars["entity"]->events_count, false);
if(empty($count)){
	$count = 4;
}

echo elgg_view_field([
	'#type' => 'number',
	'#label' => elgg_echo("event_calendar:num_display"),
	'name' => 'params[events_count]',
	'value' => $count,
	'min' => '1',
	'max' => '20',
	'step' => '1',
]);