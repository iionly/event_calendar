<?php

/**
 * Elgg event_calendar object view
 *
 * @package event_calendar
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Kevin Jardine <kevin@radagast.biz>
 * @copyright Radagast Solutions 2008
 * @link http://radagast.biz/
 *
 */

$event = elgg_extract('entity', $vars);
$full = elgg_extract('full_view', $vars, false);


if (!($event instanceof EventCalendar)) {
	return true;
}

if ($full) {
	$owner_icon = elgg_view_entity_icon($event->getOwnerEntity(), 'tiny');

	$params = [
		'entity' => $event,
		'title' => false,
	];
	$list_body = elgg_view('object/elements/summary', $params);

	$summary = elgg_view_image_block($owner_icon, $list_body, ['class' => 'mbl']);

	echo $summary;

	if ($event->web_conference) {
		$body = elgg_view('event_calendar/conference_button', ['event' => $event]);
		echo elgg_format_element('div', ['class' => 'mts'], $body);
	}

	$event_items = event_calendar_get_formatted_full_items($event);
	foreach($event_items as $item) {
		$value = $item->value;
		if (!empty($value)) {
			$body = elgg_format_element('label', [], $item->title . ': ');
			echo elgg_format_element('div', ['class' => 'mts'], $body . $item->value);
		}
	}

	if ($event->long_description) {
		$body = elgg_view('output/longtext', [
			'value' => $event->long_description,
			'class' => 'mtm',
		]);
		echo elgg_format_element('div', ['class' => 'mts'], $body);
	} else if ($event->description) {
		$body = elgg_view('output/longtext', [
			'value' => $event->description,
			'class' => 'mtm',
			]);
		echo elgg_format_element('div', ['class' => 'mts'], $body);
	}

	if (elgg_get_plugin_setting('add_to_group_calendar', 'event_calendar') == 'yes') {
		$body = elgg_view('event_calendar/forms/add_to_group', ['event' => $event]);
		echo elgg_format_element('div', ['class' => 'mts mbs'], $body);
	}

	echo elgg_view_comments($event);

} else {

	$info = event_calendar_get_formatted_time($event);

	if ($event->description) {
		$info .= "<br>" . $event->description;
	}

	if ($event_calendar_venue_view = elgg_get_plugin_setting('venue_view', 'event_calendar') == 'yes') {
		if ($event->venue) {
			$info .= "<br>" . $event->venue;
		}
	}

	$params = [
		'subtitle' => $info,
		'icon' => true,
	];
	$params = $params + $vars;
	echo elgg_view('object/elements/summary', $params);
}
