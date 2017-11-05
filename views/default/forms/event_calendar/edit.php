<?php

$event = $vars['event'];
$fd = $vars['form_data'];

$schedule_options = [
	elgg_echo('event_calendar:all_day_label') => 'all_day',
	elgg_echo('event_calendar:schedule_type:fixed') => 'fixed',
];

if (elgg_is_active_plugin('event_poll')) {
	$schedule_options = array_merge([elgg_echo('event_calendar:schedule_type:poll') => 'poll'], $schedule_options);
}

$event_calendar_fewer_fields = elgg_get_plugin_setting('fewer_fields', 'event_calendar');
$event_calendar_repeating_events = elgg_get_plugin_setting('repeating_events', 'event_calendar');

$event_calendar_region_display = elgg_get_plugin_setting('region_display', 'event_calendar');
$event_calendar_type_display = elgg_get_plugin_setting('type_display', 'event_calendar');
$event_calendar_spots_display = elgg_get_plugin_setting('spots_display', 'event_calendar');

$event_calendar_more_required = elgg_get_plugin_setting('more_required', 'event_calendar');
$event_calendar_bbb_server_url = elgg_get_plugin_setting('bbb_server_url', 'event_calendar');

if ($event_calendar_more_required == 'yes') {
	$required_fields = ['title', 'venue', 'description', 'tags', 'start_date', 'fees', 'contact', 'organiser'];
} else {
	$required_fields = ['title', 'start_date'];
}
$all_fields = ['title', 'venue', 'start_time', 'start_date', 'end_time', 'end_date',
	'description', 'tags', 'region', 'event_type', 'fees', 'contact', 'organiser',
	'long_description', 'spots', 'personal_manage'];

if ($event) {
	$event_action = 'manage_event';
	$event_guid = $event->guid;
} else {
	$event_action = 'add_event';
	$event_guid = 0;
}

$title = $fd['title'];
$description = $fd['description'];
$venue = $fd['venue'];

$fees = $fd['fees'];
if ($event_calendar_spots_display) {
	$spots = $fd['spots'];
}
if ($event_calendar_region_display) {
	$region = $fd['region'];
}
if ($event_calendar_type_display) {
	$event_type = $fd['event_type'];
}
$contact = $fd['contact'];
$organiser = $fd['organiser'];
$tags = $fd['tags'];
$all_day = $fd['all_day'];
$schedule_type = $fd['schedule_type'];
$long_description = $fd['long_description'];

$body = '<div class="event-calendar-edit-form">';

$body .= elgg_view_field([
	'#type' => 'hidden',
	'name' => 'event_action',
	'value' => $event_action,
]);
$body .= elgg_view_field([
	'#type' => 'hidden',
	'name' => 'event_guid',
	'value' => $event_guid,
]);

$body .= '<div class="event-calendar-edit-form-block">';

$body .= elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo("event_calendar:title_label"),
	'name' => 'title',
	'value' => $title,
	'required' => true,
]);

$body .= elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo("event_calendar:venue_label"),
	'name' => 'venue',
	'value' => $venue,
	'required' => in_array('venue', $required_fields),
]);

$body .= elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo("event_calendar:description_label"),
	'name' => 'description',
	'value' => $description,
	'required' => in_array('description', $required_fields),
]);

$body .= elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo("event_calendar:tags_label"),
	'name' => 'tags',
	'value' => $tags,
	'required' => in_array('tags', $required_fields),
]);

if ($vars['group_guid']) {
	$body .= elgg_view_field([
		'#type' => 'hidden',
		'name' => 'group_guid',
		'value' => $vars['group_guid'],
	]);
} else {
	$body .= elgg_view_field([
		'#type' => 'hidden',
		'name' => 'group_guid',
		'value' => 0,
	]);
}

if($event_calendar_bbb_server_url) {
	if ($fd['web_conference']) {
		$body .= elgg_view_field([
			'#type' => 'checkbox',
			'#label' => elgg_echo('event_calendar:web_conference_label'),
			'name' => 'web_conference',
			'value' => 1,
			'checked' => 'checked',
		]);
	} else {
		$body .= elgg_view_field([
			'#type' => 'checkbox',
			'#label' => elgg_echo('event_calendar:web_conference_label'),
			'name' => 'web_conference',
			'value' => 1,
		]);
	}
}

$body .= '</div>';

$body .= '<div class="event-calendar-edit-form-block">';
$body .= '<div class="mbs"><h2>'.elgg_echo('event_calendar:schedule:header').'</h2></div>';
$body .= '<ul class="elgg-input-radios elgg-vertical event-calendar-edit-schedule-type">';
foreach($schedule_options as $label => $key) {
  if ($key == $schedule_type) {
    $checked = "checked \"checked\"";
  } else {
    $checked = '';
  }
  $body .= '<li class="mbm"><label><input type="radio" name="schedule_type" class="elgg-input-radio" value="'.$key.'" '.$checked.' />';
  $body .= ' ' . $label . '</label></li>';
  if ($key == 'all_day') {
    $body .= '<div class="event-calendar-edit-all-day-date-wrapper mbm">';
    $body .= elgg_view("event_calendar/input/date_local", [
		'autocomplete' => 'off',
		'class' => 'event-calendar-compressed-date',
		'name' => 'start_date_for_all_day',
		'value' => $fd['start_date']
	]);
    $body .= '</div>';
  }
}
$body .= '</ul>';

$body .= elgg_view('event_calendar/schedule_section', $vars);

if ($event_calendar_spots_display == 'yes') {
	$body .= elgg_view_field([
		'#type' => 'number',
		'#label' => elgg_echo("event_calendar:spots_label"),
		'name' => 'spots',
		'value' => $spots,
		'min' => '1',
		'step' => '1',
	]);
}

$body .= '<div class="event-calendar-edit-bottom"></div>';
$body .= '</div>';

$body .= elgg_view('event_calendar/personal_manage_section', $vars);

$body .= elgg_view('event_calendar/share_section', $vars);

if ($event_calendar_region_display == 'yes' || $event_calendar_type_display == 'yes' || $event_calendar_fewer_fields != 'yes') {
	$body .= '<div class="event-calendar-edit-form-block event-calendar-edit-form-other-block mbm">';

	if ($event_calendar_region_display == 'yes') {
		$region_list = trim(elgg_get_plugin_setting('region_list', 'event_calendar'));
		$region_list_handles = elgg_get_plugin_setting('region_list_handles', 'event_calendar');
		// make sure that we are using Unix line endings
		$region_list = str_replace("\r\n","\n", $region_list);
		$region_list = str_replace("\r","\n", $region_list);
		if ($region_list) {
			$options = [];
			$options[] = '-';
			foreach(explode("\n", $region_list) as $region_item) {
				$region_item = trim($region_item);
				if ($region_list_handles == 'yes') {
					$options[$region_item] = elgg_echo('event_calendar:region:'.$region_item);
				} else {
					$options[$region_item] = $region_item;
				}
			}
			$body .= '<div class="mbm"><label>' . elgg_echo("event_calendar:region_label") . ' ' . '</label>';
			$body .= elgg_view("input/select", [
				'name' => 'region',
				'value' => $region,
				'options_values' => $options,
			]);
			$body .= '</div>';
		}
	}

	if ($event_calendar_type_display == 'yes') {
		$type_list = trim(elgg_get_plugin_setting('type_list', 'event_calendar'));
		$type_list_handles = elgg_get_plugin_setting('type_list_handles', 'event_calendar');

		// make sure that we are using Unix line endings
		$type_list = str_replace("\r\n", "\n", $type_list);
		$type_list = str_replace("\r", "\n", $type_list);

		if ($type_list) {
			$options = [];
			$options[] = '-';

			foreach (explode("\n", $type_list) as $type_item) {
				$type_item = explode('|', $type_item);
				$type_name = trim($type_item[0]);

				if ($type_list_handles == 'yes') {
					// Use translation system to resolve the type names
					$options[$type_name] = elgg_echo("event_calendar:type:$type_name");
				} else {
					$options[$type_name] = $type_name;
				}
			}

			$body .= '<div class="mbm"><label>'.elgg_echo("event_calendar:type_label").' '.'</label>';
			$body .= elgg_view("input/select", [
				'name' => 'event_type',
				'value' => $event_type,
				'options_values' => $options,
			]);
			$body .= '</div>';
		}
	}

	if ($event_calendar_fewer_fields != 'yes') {
		$body .= elgg_view_field([
			'#type' => 'text',
			'#label' => elgg_echo("event_calendar:fees_label"),
			'name' => 'fees',
			'value' => $fees,
			'required' => in_array('fees', $required_fields),
		]);

		$body .= elgg_view_field([
			'#type' => 'text',
			'#label' => elgg_echo("event_calendar:contact_label"),
			'name' => 'contact',
			'value' => $contact,
			'required' => in_array('contact', $required_fields),
		]);

		$body .= elgg_view_field([
			'#type' => 'text',
			'#label' => elgg_echo("event_calendar:organiser_label"),
			'name' => 'organiser',
			'value' => $organiser,
			'required' => in_array('organiser', $required_fields),
		]);

		$body .= elgg_view_field([
			'#type' => 'longtext',
			'#label' => elgg_echo("event_calendar:long_description_label"),
			'name' => 'long_description',
			'value' => $long_description,
		]);
	}

	$body .= '</div>';
}

$body .= elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('event_calendar:submit'),
	'name' => 'submit',
]);

$body .= '</div>';

echo $body;
