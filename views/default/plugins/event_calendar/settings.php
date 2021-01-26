<?php

require_once(elgg_get_plugins_path() . 'event_calendar/models/model.php');

if (event_calendar_is_upgrade_available()) {
	echo '<div class="elgg-admin-notices mbl">';
	echo '<p>';
	echo elgg_view('output/url', [
		'text' => elgg_echo('event_calendar:upgrade'),
		'href' => 'action/event_calendar/upgrade',
		'is_action' => true,
	]);
	echo '</p>';
	echo '</div>';
}

$yn_options = [
	elgg_echo('event_calendar:settings:yes') => 'yes',
	elgg_echo('event_calendar:settings:no') => 'no',
];

$time_format_options = [
	elgg_echo('event_calendar:time_format:12hour') => '12',
	elgg_echo('event_calendar:time_format:24hour') => '24',
];

$membership_options = [
	elgg_echo('event_calendar:personal_manage:open') => 'open',
	elgg_echo('event_calendar:personal_manage:closed') => 'closed',
	elgg_echo('event_calendar:personal_manage:private') => 'private',
	elgg_echo('event_calendar:personal_manage:by_event') => 'by_event',
];

$access_options = [
	elgg_echo("PRIVATE") => ACCESS_PRIVATE,
	elgg_echo("LOGGED_IN") => ACCESS_LOGGED_IN,
	elgg_echo("PUBLIC") => ACCESS_PUBLIC,
];

$listing_options = [
	elgg_echo('event_calendar:settings:paged') => 'paged',
	elgg_echo('event_calendar:settings:agenda') => 'agenda',
	elgg_echo('event_calendar:settings:month') => 'month',
	elgg_echo('event_calendar:settings:full') => 'full',
];

$body = '';

$event_calendar_hide_access = elgg_get_plugin_setting('hide_access', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:hide_access:title'),
	'name' => 'params[hide_access]',
	'value' => $event_calendar_hide_access,
	'options' => $yn_options,
]);

$event_calendar_default_access = elgg_get_plugin_setting('default_access', 'event_calendar', ACCESS_LOGGED_IN);
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:default_access:title'),
	'name' => 'params[default_access]',
	'value' => $event_calendar_default_access,
	'options' => $access_options,
]);

$event_calendar_hide_end = elgg_get_plugin_setting('hide_end', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:hide_end:title'),
	'name' => 'params[hide_end]',
	'value' => $event_calendar_hide_end,
	'options' => $yn_options,
]);

$event_calendar_listing_format = elgg_get_plugin_setting('listing_format', 'event_calendar', 'month');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:listing_format:title'),
	'name' => 'params[listing_format]',
	'value' => $event_calendar_listing_format,
	'options' => $listing_options,
]);

$event_calendar_repeated_events = elgg_get_plugin_setting('repeated_events', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:repeated_events:title'),
	'name' => 'params[repeated_events]',
	'value' => $event_calendar_repeated_events,
	'options' => $yn_options,
]);

$event_calendar_reminders = elgg_get_plugin_setting('reminders', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:reminders:title'),
	'name' => 'params[reminders]',
	'value' => $event_calendar_reminders,
	'options' => $yn_options,
]);

$event_calendar_times = elgg_get_plugin_setting('times', 'event_calendar', 'yes');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:times:title'),
	'name' => 'params[times]',
	'value' => $event_calendar_times,
	'options' => $yn_options,
]);

$event_calendar_time_format = elgg_get_plugin_setting('timeformat', 'event_calendar', '24');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:timeformat:title'),
	'name' => 'params[timeformat]',
	'value' => $event_calendar_time_format,
	'options' => $time_format_options,
]);

$event_calendar_autopersonal = elgg_get_plugin_setting('autopersonal', 'event_calendar', 'yes');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:autopersonal:title'),
	'name' => 'params[autopersonal]',
	'value' => $event_calendar_autopersonal,
	'options' => $yn_options,
]);

$event_calendar_autogroup = elgg_get_plugin_setting('autogroup', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:autogroup:title'),
	'name' => 'params[autogroup]',
	'value' => $event_calendar_autogroup,
	'options' => $yn_options,
]);

$event_calendar_add_to_group_calendar = elgg_get_plugin_setting('add_to_group_calendar', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:add_to_group_calendar:title'),
	'name' => 'params[add_to_group_calendar]',
	'value' => $event_calendar_add_to_group_calendar,
	'options' => $yn_options,
]);

$event_calendar_venue_view = elgg_get_plugin_setting('venue_view', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:venue_view:title'),
	'name' => 'params[venue_view]',
	'value' => $event_calendar_venue_view,
	'options' => $yn_options,
]);

$event_calendar_fewer_fields = elgg_get_plugin_setting('fewer_fields', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:fewer_fields:title'),
	'name' => 'params[fewer_fields]',
	'value' => $event_calendar_fewer_fields,
	'options' => $yn_options,
]);

$options = [
	elgg_echo('event_calendar:settings:no') => 'no',
	elgg_echo('event_calendar:settings:site_calendar:admin') => 'admin',
	elgg_echo('event_calendar:settings:site_calendar:loggedin') => 'loggedin',
];
$event_calendar_site_calendar = elgg_get_plugin_setting('site_calendar', 'event_calendar', 'admin');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:site_calendar:title'),
	'name' => 'params[site_calendar]',
	'value' => $event_calendar_site_calendar,
	'options' => $options,
]);

$options = [
	elgg_echo('event_calendar:settings:no') => 'no',
	elgg_echo('event_calendar:settings:group_calendar:admin') => 'admin',
	elgg_echo('event_calendar:settings:group_calendar:members') => 'members',
];
$event_calendar_group_calendar = elgg_get_plugin_setting('group_calendar', 'event_calendar', 'members');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:group_calendar:title'),
	'name' => 'params[group_calendar]',
	'value' => $event_calendar_group_calendar,
	'options' => $options,
]);

$options = [
	elgg_echo('event_calendar:settings:group_default:yes') => 'yes',
	elgg_echo('event_calendar:settings:group_default:no') => 'no',
];
$event_calendar_group_default = elgg_get_plugin_setting('group_default', 'event_calendar', 'yes');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:group_default:title'),
	'name' => 'params[group_default]',
	'value' => $event_calendar_group_default,
	'options' => $options,
]);

$event_calendar_group_always_display = elgg_get_plugin_setting('group_always_display', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:group_always_display:title'),
	'name' => 'params[group_always_display]',
	'value' => $event_calendar_group_always_display,
	'options' => $yn_options,
]);

$event_calendar_add_users = elgg_get_plugin_setting('add_users', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:add_users:title'),
	'name' => 'params[add_users]',
	'value' => $event_calendar_add_users,
	'options' => $yn_options,
]);

$event_calendar_add_users_notify = elgg_get_plugin_setting('add_users_notify', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:add_users_notify:title'),
	'name' => 'params[add_users_notify]',
	'value' => $event_calendar_add_users_notify,
	'options' => $yn_options,
]);

$event_calendar_personal_manage = elgg_get_plugin_setting('personal_manage', 'event_calendar', 'open');
if ($event_calendar_personal_manage == 'yes') {
	$event_calendar_personal_manage = 'open';
} else if ($event_calendar_personal_manage == 'no') {
	$event_calendar_personal_manage = 'closed';
}
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:personal_manage:title'),
	'name' => 'params[personal_manage]',
	'value' => $event_calendar_personal_manage,
	'options' => $membership_options,
	'#help' => elgg_echo('event_calendar:settings:personal_manage:description'),
]);

$event_calendar_spots_display = elgg_get_plugin_setting('spots_display', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:spots_display:title'),
	'name' => 'params[spots_display]',
	'value' => $event_calendar_spots_display,
	'options' => $yn_options,
]);

$event_calendar_no_collisions = elgg_get_plugin_setting('no_collisions', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:no_collisions:title'),
	'name' => 'params[no_collisions]',
	'value' => $event_calendar_no_collisions,
	'options' => $yn_options,
]);

$event_calendar_collision_length = (int)elgg_get_plugin_setting('collision_length', 'event_calendar', 3600);
$body .= elgg_view_field([
	'#type' => 'number',
	'#label' => elgg_echo('event_calendar:settings:collision_length:title'),
	'name' => 'params[collision_length]',
	'value' => (int)$event_calendar_collision_length,
	'min' => 1,
	'step' => 1,
]);

$event_calendar_region_display = elgg_get_plugin_setting('region_display', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:region_display:title'),
	'name' => 'params[region_display]',
	'value' => $event_calendar_region_display,
	'options' => $yn_options,
]);

$event_calendar_region_list = elgg_get_plugin_setting('region_list', 'event_calendar', '');
$body .= elgg_view_field([
	'#type' => 'plaintext',
	'#label' => elgg_echo('event_calendar:settings:region_list:title'),
	'name' => 'params[region_list]',
	'value' => $event_calendar_region_list,
]);

$event_calendar_region_list_handles = elgg_get_plugin_setting('region_list_handles', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:region_list_handles:title'),
	'name' => 'params[region_list_handles]',
	'value' => $event_calendar_region_list_handles,
	'options' => $yn_options,
]);

$event_calendar_type_display = elgg_get_plugin_setting('type_display', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:type_display:title'),
	'name' => 'params[type_display]',
	'value' => $event_calendar_type_display,
	'options' => $yn_options,
]);

$event_calendar_type_list = elgg_get_plugin_setting('type_list', 'event_calendar', '');
$body .= elgg_view_field([
	'#type' => 'plaintext',
	'#label' => elgg_echo('event_calendar:settings:type_list:title'),
	'name' => 'params[type_list]',
	'value' => $event_calendar_type_list,
	'#help' => elgg_echo('event_calendar:settings:type_list:desc'),
]);

$event_calendar_type_list_handles = elgg_get_plugin_setting('type_list_handles', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:type_list_handles:title'),
	'name' => 'params[type_list_handles]',
	'value' => $event_calendar_type_list_handles,
	'options' => $yn_options,
]);

$event_calendar_first_date = elgg_get_plugin_setting('first_date', 'event_calendar', '');
$body .= elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('event_calendar:settings:first_date:title'),
	'name' => 'params[first_date]',
	'value' => $event_calendar_first_date,
]);

$event_calendar_last_date = elgg_get_plugin_setting('last_date', 'event_calendar', '');
$body .= elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('event_calendar:settings:last_date:title'),
	'name' => 'params[last_date]',
	'value' => $event_calendar_last_date,
]);

$event_calendar_more_required = elgg_get_plugin_setting('more_required', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:more_required:title'),
	'name' => 'params[more_required]',
	'value' => $event_calendar_more_required,
	'options' => $yn_options,
]);

$event_calendar_ical_import_export = elgg_get_plugin_setting('ical_import_export', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:ical_import_export'),
	'name' => 'params[ical_import_export]',
	'value' => $event_calendar_ical_import_export,
	'options' => $yn_options,
]);

$ical_auth_file_name = elgg_get_plugin_setting('ical_auth_file_name', 'event_calendar');
$body .= elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('event_calendar:ical_auth_file_name:title'),
	'name' => 'params[ical_auth_file_name]',
	'value' => $ical_auth_file_name,
]);

$event_calendar_bbb_server_url = elgg_get_plugin_setting('bbb_server_url', 'event_calendar');
$body .= elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('event_calendar:bbb_server_url'),
	'name' => 'params[bbb_server_url]',
	'value' => $event_calendar_bbb_server_url,
]);

$event_calendar_bbb_security_salt = elgg_get_plugin_setting('bbb_security_salt', 'event_calendar');
$body .= elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('event_calendar:bbb_security_salt'),
	'name' => 'params[bbb_security_salt]',
	'value' => $event_calendar_bbb_security_salt,
]);

$event_calendar_allow_view_change = elgg_get_plugin_setting('allow_view_change', 'event_calendar', 'no');
$body .= elgg_view_field([
	'#type' => 'radio',
	'#label' => elgg_echo('event_calendar:settings:allow_view_change:title'),
	'name' => 'params[allow_view_change]',
	'value' => $event_calendar_allow_view_change,
	'options' => $yn_options,
]);

echo $body;
