<?php

echo elgg_format_element('div', ['class' => 'mlb'], elgg_format_element('h3', [], elgg_echo('event_calendar:export:settings')));

// export which calendar
$options_values = [];

if (elgg_get_plugin_setting('site_calendar', 'event_calendar') != 'no') {
	$options_values[0] = elgg_echo('event_calendar:site_calendar');
}

$groups = elgg_get_logged_in_user_entity()->getGroups(['limit' => false]);
if ($groups) {
	foreach ($groups as $group) {
		if (event_calendar_activated_for_group($group)) {
			$options_values[$group->guid] = elgg_echo('group') . ': ' . $group->name;
		}
	}
}

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('event_calendar:export:type'),
	'name' => 'container_guid',
	'value' => $vars['group_guid'],
	'options_values' => $options_values,
]);

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('event_calendar:filter'),
	'name' => 'filter',
	'value' => $vars['filter'],
	'options_values' => [
		'all' => elgg_echo('event_calendar:show_all'),
		'mine' => elgg_echo('event_calendar:show_mine'),
		'friends' => elgg_echo('event_calendar:show_friends'),
	],
]);

$region_list = trim(elgg_get_plugin_setting('region_list', 'event_calendar'));
// make sure that we are using Unix line endings
$region_list = str_replace("\r\n","\n", $region_list);
$region_list = str_replace("\r","\n", $region_list);
if ($region_list) {
	$options_values_region = ['-' => elgg_echo('event_calendar:all')];
	foreach(explode("\n", $region_list) as $region_item) {
		$region_item = trim($region_item);
		$options_values_region[$region_item] = $region_item;
	}

	echo elgg_view_field([
		'#type' => 'select',
		'#label' => elgg_echo('event_calendar:region_filter_by_label'),
		'name' => 'region',
		'value' => $vars['region'],
		'options_values' => $options_values_region,
	]);
}

// determine default dates - start/end based on interval day/week/month
// start will be at 00:00 at the beginning of the day/week/month
// end will be at 23:59 at the end of day
// $date[0] = year, 1 = month, 2 = day
$date = explode('-', $vars['date']);
switch ($vars['interval']) {
	case 'day':
		$start_date = $end_date = $vars['date'];
		break;
	case 'week':
		// need to adjust start_date to be the beginning of the week
		$start_ts = strtotime($vars['date']);
		$start_ts -= date("w", $start_ts) * 60 * 60 * 24;
		$end_ts = $start_ts + 6 * 60 * 60 * 24;

		$start_date = date('Y-m-d', $start_ts);
		$end_date = date('Y-m-d', $end_ts);
		break;
	case 'month':
	default:
		$start_date = $date[0] . '-' . $date[1] . '-1';
		$end_date = $date[0] . '-' . $date[1] . '-' . getLastDayOfMonth($date[1], $date[0]);
		break;
}

// start/end date
echo elgg_view_field([
	'#type' => 'date',
	'#label' => elgg_echo('event_calendar:start_date'),
	'name' => 'start_date',
	'value' => $start_date,
	'style' => 'width: 120px',
]);

echo elgg_view_field([
	'#type' => 'date',
	'#label' => elgg_echo('event_calendar:end_date'),
	'name' => 'end_date',
	'value' => $end_date,
	'style' => 'width: 120px',
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('event_calendar:export'),
]);

elgg_set_form_footer($footer);
