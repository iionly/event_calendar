<?php

elgg_require_js('event_calendar/event_calendar');
elgg_load_library('event_calendar:ical');

$container_guid = elgg_extract('container_guid', $vars, 0);
$start_date = elgg_extract('start_date', $vars, '');
$display_mode = elgg_extract('display_mode', $vars, '');
$filter = elgg_extract('filter_mode', $vars, '');
$region = elgg_extract('region', $vars, '-');

if (!$container_guid) {
	elgg_gatekeeper();
	elgg_group_gatekeeper();
	$container_guid = elgg_get_page_owner_guid();
}

$group = get_entity($container_guid);
if (!event_calendar_activated_for_group($group)) {
	forward();
}

$events = event_calendar_get_ical_events('group', $container_guid, $start_date, $display_mode, $filter, $region);

if (!$events) {
	register_error(elgg_echo('event_calendar:no_events_found'));
	forward(REFERER);
}

$events = event_calendar_flatten_event_structure($events);

$timezone = date_default_timezone_get();

$config = [
	'unique_id' => elgg_get_site_url(),
	'filename'=> 'Calendar.ics',
	'TZID' => $timezone,
];

$v = new vcalendar($config);

$v->setProperty( 'method', 'PUBLISH' );
$v->setProperty( "X-WR-TIMEZONE", date_default_timezone_get() );
$v->setProperty( "version", "2.0" );
$v->setProperty( "X-WR-CALNAME", elgg_get_logged_in_user_entity()->username . "Calendar" );
$v->setProperty( "X-WR-CALDESC", elgg_get_logged_in_user_entity()->username . "Calendar" );

$xprops = [ "X-LIC-LOCATION" => $timezone ];
iCalUtilityFunctions::createTimezone($v, $timezone, $xprops);

foreach($events as $event) {
	//set default beginning and ending time
	$hb = 8;
	$he = 18;
	$mb = $me = $sb = $se = 0;
	if ($event->start_time) {
		$hb = (int)($event->start_time / 60);
		$mb = $event->start_time % 60;
	}

	if ($event->end_time) {
		$he = (int)($event->end_time / 60);
		$me = $event->end_time % 60;
	}

	$vevent = $v->newComponent('vevent');
	
	$vevent->setProperty('uid', elgg_get_site_url() . 'event_calendar/view/' . $event->guid);

	if (!isset($event->end_date)) {
		$event_end_date = $event->start_date;
	} else {
		$event_end_date = $event->end_date;
	}

	$start = [
		'year' => date('Y', (int)$event->start_date),
		'month' => date('m', (int)$event->start_date),
		'day' => date('d', (int)$event->start_date),
		'hour' => $hb,
		'min' => $mb,
		'sec' => $sb,
	];

	$vevent->setProperty('dtstart', $start);

	$end = [
		'year' => date('Y', (int)$event_end_date),
		'month' => date('m', (int)$event_end_date),
		'day' => date('d', (int)$event_end_date),
		'hour' => $he,
		'min' => $me,
		'sec' => $se,
	];

	$vevent->setProperty('dtend', $end);
	$vevent->setProperty('LOCATION', $event->venue);
	$vevent->setProperty('LAST_MODIFIED', $event->time_updated);
	$vevent->setProperty('summary', $event->title);

	$description = (isset($event->description) && $event->description != "") ? $event->description : '';
	if (!$description && $event->long_description) {
		$description = $event->long_description;
	}

	$organiser = (isset($event->organiser) && $event->organiser != "") ? $event->organiser : $event->getOwnerEntity()->name;

	if (is_array($event->tags)) {
		$tags = implode(',' , $event->tags);
	} else {
		$tags = '';
	}

	$vevent->setProperty('description', $description);
	$vevent->setProperty('organizer', $event->getOwnerEntity()->email, ['CN' => $organiser]);
	$vevent->setProperty( "X-PROP-REGION", $event->region );
	$vevent->setProperty( "X-PROP-TYPE", $event->event_type );
	$vevent->setProperty( "X-PROP-FEES", $event->fees );
	$vevent->setProperty( "X-PROP-TAGS", $tags);
	$vevent->setProperty( "X-PROP-CONTACT", $event->contact );
	$vevent->setProperty( "X-PROP-LONG-DESC", $event->long_description);
}

$v->returnCalendar();