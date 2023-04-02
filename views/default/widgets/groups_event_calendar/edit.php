<?php

$widget = elgg_extract('entity', $vars);

echo elgg_view('object/widget/edit/num_display', [
	'entity' => $widget,
	'name' => 'events_count',
	'label' => elgg_echo('event_calendar:num_display'),
	'max' => 20,
	'default' => 4,
]);
