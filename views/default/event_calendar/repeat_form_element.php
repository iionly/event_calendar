<?php

$event = $vars['event'];
$fd = $vars['form_data'];

echo '<div class="event-calendar-repeat-section">';

if ($fd['repeats'] == 'yes') {
	echo elgg_view('input/checkbox', [
		'name' => 'repeats',
		'value' => 'yes',
		'checked' => 'checked',
	]);
} else {
	echo elgg_view('input/checkbox', [
		'name' => 'repeats',
		'value' => 'yes',
	]);
}

echo ' ' . elgg_echo('event_calendar:repeat_interval_label') . ' ';

echo elgg_view('input/select', [
	'name' => 'repeat_interval',
	'value' => $fd['repeat_interval'],
	'options_values' => [
		'1' => '1',
		'2' => '2',
		'3' => '3',
		'4' => '4',
		'5' => '5',
		'6' => '6',
		'7' => '7',
		'8' => '8',
	],
]);

echo ' ' . elgg_echo('event_calendar:repeat_weeks');
echo ' ' . elgg_echo('event_calendar:on_these_days');

echo '<div class="event-calendar-repeating-wrapper" name="event-calendar-repeating-anchor">';
echo '<a id="event-calendar-repeating-monday" href="#event-calendar-repeating-anchor" class="event-calendar-repeating-unselected">';
echo elgg_echo('event_calendar:day_abbrev:monday');
echo '</a>'." ";
echo '<a id="event-calendar-repeating-tuesday" href="#event-calendar-repeating-anchor" class="event-calendar-repeating-unselected">';
echo elgg_echo('event_calendar:day_abbrev:tuesday');
echo '</a>'." ";
echo '<a id="event-calendar-repeating-wednesday" href="#event-calendar-repeating-anchor" class="event-calendar-repeating-unselected">';
echo elgg_echo('event_calendar:day_abbrev:wednesday');
echo '</a>'." ";
echo '<a id="event-calendar-repeating-thursday" href="#event-calendar-repeating-anchor" class="event-calendar-repeating-unselected">';
echo elgg_echo('event_calendar:day_abbrev:thursday');
echo '</a>'." ";
echo '<a id="event-calendar-repeating-friday" href="#event-calendar-repeating-anchor" class="event-calendar-repeating-unselected">';
echo elgg_echo('event_calendar:day_abbrev:friday');
echo '</a>'." ";
echo '<a id="event-calendar-repeating-saturday" href="#event-calendar-repeating-anchor" class="event-calendar-repeating-unselected">';
echo elgg_echo('event_calendar:day_abbrev:saturday');
echo '</a>'." ";
echo '<a id="event-calendar-repeating-sunday" href="#event-calendar-repeating-anchor" class="event-calendar-repeating-unselected">';
echo elgg_echo('event_calendar:day_abbrev:sunday');
echo '</a>';
echo '</div>';

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'event-calendar-repeating-monday-value',
	'value' => $fd['event-calendar-repeating-monday-value'],
]);
echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'event-calendar-repeating-tuesday-value',
	'value' => $fd['event-calendar-repeating-tuesday-value'],
]);
echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'event-calendar-repeating-wednesday-value',
	'value' => $fd['event-calendar-repeating-wednesday-value'],
]);
echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'event-calendar-repeating-thursday-value',
	'value' => $fd['event-calendar-repeating-thursday-value'],
]);
echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'event-calendar-repeating-friday-value',
	'value' => $fd['event-calendar-repeating-friday-value'],
]);
echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'event-calendar-repeating-saturday-value',
	'value' => $fd['event-calendar-repeating-saturday-value'],
]);
echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'event-calendar-repeating-sunday-value',
	'value' => $fd['event-calendar-repeating-sunday-value'],
]);

echo "</div>";
