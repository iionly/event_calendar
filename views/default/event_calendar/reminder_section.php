<?php

$fd = $vars['form_data'];
$event_calendar_reminders = elgg_get_plugin_setting('reminders', 'event_calendar');

if ($event_calendar_reminders == 'yes') {
	if ($fd['send_reminder']) {
		$body = elgg_view('input/checkbox', [
			'name' => 'send_reminder',
			'checked' => 'checked',
			'value' => 1,
		]);
	} else {
		$body = elgg_view('input/checkbox', [
			'name' => 'send_reminder',
			'value' => 1,
		]);
	}

	$body .= ' ' . elgg_echo('elgg_calendar:send_reminder_label') . ' ';
	$numbers = [];
	for ($i=1;$i<60;$i++) {
		$numbers[$i] = $i;
	}
	$intervals = [
		1 => elgg_echo('event_calendar:interval:minute'),
		60 => elgg_echo('event_calendar:interval:hour'),
		60*24 => elgg_echo('event_calendar:interval:day'),
	];

	$body .= elgg_view('input/select', [
		'name' => 'reminder_number',
		'options' => $numbers,
		'value' => $fd['reminder_number'],
	]);
	$body .= elgg_view('input/select', [
		'name' => 'reminder_interval',
		'options_values' => $intervals,
		'value' => $fd['reminder_interval'],
		]);
	$body .= " " . elgg_echo('elgg_calendar:send_reminder_before');

	echo elgg_format_element('div', ['class' => 'event-calendar-edit-reminder-wrapper'], $body);
}
