<?php

require_once(elgg_get_plugins_path() . 'event_calendar/models/model.php');
require_once(elgg_get_plugins_path() . 'event_calendar/vendors/iCalcreator/iCalcreator.php');

$filter = get_input('filter', 'mine');
$container_guid = get_input('container_guid', 0);
$region = get_input('region');
$start_date = get_input('start_date', date('Y-n-j'));
$end_date = get_input('end_date', date('Y-n-j'));
$start_ts = strtotime($start_date . " " . date_default_timezone_get());
$end_ts = strtotime($end_date . " " . date_default_timezone_get()) + 60 * 60 * 24 - 1;
$user_guid = elgg_get_logged_in_user_guid();

switch ($filter) {
	case 'mine':
		$events = event_calendar_get_events_for_user_between($start_ts, $end_ts, false, 0, 0, $user_guid, $container_guid, $region);
		break;
	case 'friends':
		$events = event_calendar_get_events_for_friends_between($start_ts, $end_ts, false, 0, 0, $user_guid, $container_guid, $region);
		break;
	case 'site':
		$container_guid = 0;
	case 'all':
		$events = event_calendar_get_events_between($start_ts, $end_ts, false, 0, 0, $container_guid, $region);
		break;
	default:
		// see if we're exporting just a single event
		$events = false;
		$event = get_entity($filter);
		if (elgg_instanceof($event, 'object', 'event_calendar')) {
			$events = [['event' => $event]];
		}
		break;
}

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
$v->setProperty( "calscale", "GREGORIAN" );
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
	if (elgg_get_plugin_setting('set_organizer', 'event_calendar', 'yes') == 'yes')  
		$vevent->setProperty('organizer', $event->getOwnerEntity()->email, ['CN' => $organiser]);
	$vevent->setProperty( "X-PROP-REGION", $event->region );
	$vevent->setProperty( "X-PROP-TYPE", $event->event_type );
	$vevent->setProperty( "X-PROP-FEES", $event->fees );
	$vevent->setProperty( "X-PROP-TAGS", $tags);
	$vevent->setProperty( "X-PROP-CONTACT", $event->contact );
	$vevent->setProperty( "X-PROP-LONG-DESC", $event->long_description);
}

$v->returnCalendar();
exit();