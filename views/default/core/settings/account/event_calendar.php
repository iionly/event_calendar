<?php
/**
 * Select the calendar format used by Event Calendar plugin
 *
 */

$event_calendar_listing_format_allow_user_change = elgg_get_plugin_setting('listing_format_allow_user_change', 'event_calendar', 'yes');

if ($event_calendar_listing_format_allow_user_change != 'yes') {
	return;
}

$user = elgg_get_page_owner_entity();

if (!($user instanceof ElggUser)) {
	return;
}

if (!$user->event_calendar_format) {
	$user->event_calendar_format = $event_calendar_listing_format = elgg_get_plugin_setting('listing_format', 'event_calendar', 'month');
}

$listing_options = [
	elgg_echo('event_calendar:settings:paged') => 'paged',
	elgg_echo('event_calendar:settings:agenda') => 'agenda',
	elgg_echo('event_calendar:settings:month') => 'month',
	elgg_echo('event_calendar:settings:full') => 'full',
];

$content = elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('user:event_calendar_format:label'),
	'name' => 'event_calendar_format',
	'value' => $user->event_calendar_format,
	'options' => $listing_options,
]);

echo elgg_view_module('info', elgg_echo('user:set:event_calendar_format'), $content);