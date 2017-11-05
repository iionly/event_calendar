<?php

/**
 * Elgg event model
 *
 * @package event_calendar
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Kevin Jardine <kevin@radagast.biz>
 * @copyright Radagast Solutions 2008
 * @link http://radagast.biz/
 *
 */

// converts to time in minutes since midnight
function event_calendar_convert_to_time($hour, $minute, $meridian) {
	if ($meridian) {
		if ($meridian == 'am') {
			if ($hour == 12) {
				$hour = 0;
			}
		} else {
			if ($hour < 12) {
				$hour += 12;
			}
		}
	}
	return 60*$hour+$minute;
}

// returns the event or false
function event_calendar_set_event_from_form($event_guid, $group_guid) {

	$event_calendar_times = elgg_get_plugin_setting('times', 'event_calendar');
	$event_calendar_region_display = elgg_get_plugin_setting('region_display', 'event_calendar');
	$event_calendar_type_display = elgg_get_plugin_setting('type_display', 'event_calendar');
	$event_calendar_spots_display = elgg_get_plugin_setting('spots_display', 'event_calendar');
	$event_calendar_hide_end = elgg_get_plugin_setting('hide_end', 'event_calendar');
	$event_calendar_fewer_fields = elgg_get_plugin_setting('fewer_fields', 'event_calendar');
	$event_calendar_more_required = elgg_get_plugin_setting('more_required', 'event_calendar');
	$event_calendar_personal_manage = elgg_get_plugin_setting('personal_manage', 'event_calendar');
	$event_calendar_repeating_events = elgg_get_plugin_setting('repeating_events', 'event_calendar');
	// temporary place to store values
	$e = new stdClass();
	$e->schedule_type = get_input('schedule_type');

	if ($event_calendar_more_required == 'yes') {
		$required_fields = ['title', 'venue', 'description', 'tags'];

		if ($event_calendar_fewer_fields != 'no') {
			$required_fields[] = 'fees';
			$required_fields[] = 'contact';
			$required_fields[] = 'organiser';
		}
	} else {
		$required_fields = ['title'];
	}

	if ($event_guid) {
		$event = get_entity($event_guid);
		if (!elgg_instanceof($event, 'object', 'event_calendar')) {
			// do nothing because this is a bad event guid
			return false;
		}
	} else {
		$user_guid = elgg_get_logged_in_user_guid();
		$event = new ElggObject();
		$event->subtype = 'event_calendar';
		$event->owner_guid = $user_guid;
		if ($group_guid) {
			$event->container_guid = $group_guid;
		} else {
			$event->container_guid = $event->owner_guid;
		}
	}

	// Check if repeating event and if yes if day(s) of repeat has been selected
	$repeats = get_input('repeats');
	if ($repeats == 'yes') {
		$no_day_selected = true;
		$dow = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
		foreach ($dow as $w) {
			$v = 'event-calendar-repeating-'.$w.'-value';
			$selected = get_input($v);
			if ($selected) {
				$no_day_selected = false;
			}
		}
		if ($no_day_selected) {
			register_error(elgg_echo('event_calander:repeating_event:error'));
			return false;
		}
	}

	if ($e->schedule_type != 'poll') {
		// added start_date as required field as sanity check
		// adding here because event poll events have no start_date at this point
		$required_fields[] = 'start_date';

		if ($e->schedule_type == 'all_day') {
			$start_date_text = trim(get_input('start_date_for_all_day'));
		} else {
			$start_date_text = trim(get_input('start_date'));
		}
		// TODO: is the timezone bit necessary?
		$e->start_date = strtotime($start_date_text." ".date_default_timezone_get());
		$end_date_text = trim(get_input('end_date', ''));
		if ($end_date_text && ($e->schedule_type != 'all_day')) {
			$e->end_date = strtotime($end_date_text." ".date_default_timezone_get());
		} else {
			$e->end_date = '';
		}

		if ($e->schedule_type != 'all_day' && $event_calendar_times != 'no') {
			$hour = get_input('start_time_hour', '');
			$minute = get_input('start_time_minute', '');
			$meridian = get_input('start_time_meridian', '');
			if (is_numeric($hour) && is_numeric($minute)) {
				$e->start_time = event_calendar_convert_to_time($hour, $minute, $meridian);
			} else {
				$e->start_time = '';
			}
			$hour = get_input('end_time_hour', '');
			$minute = get_input('end_time_minute', '');
			$meridian = get_input('end_time_meridian', '');
			if (is_numeric($hour) && is_numeric($minute)) {
				$e->end_time = event_calendar_convert_to_time($hour, $minute, $meridian);
			} else {
				$e->end_time = '';
			}
			if (is_numeric($e->start_date) && is_numeric($e->start_time)) {
				// Set start date to the Unix start time, if set.
				// This allows sorting by date *and* time.
				$e->start_date += $e->start_time*60;
			}
		} else {
			$e->start_time = 0;
			$e->end_time = '';
		}
	}

	$e->access_id = get_input('access_id');
	$e->title = get_input('title');
	$e->description = get_input('description');
	$e->venue = get_input('venue');
	$e->fees = get_input('fees');
	$e->contact = get_input('contact');
	$e->organiser = get_input('organiser');
	$e->tags = get_input('tags');
	$e->long_description = get_input('long_description');
	$e->send_reminder = get_input('send_reminder');
	$e->reminder_number = get_input('reminder_number');
	$e->reminder_interval = get_input('reminder_interval');
	$e->web_conference = get_input('web_conference');
	$e->real_end_time = event_calendar_get_end_time($e);

	// sanity check
	if ($e->schedule_type == 'fixed' && $e->real_end_time <= $e->start_date) {
		register_error(elgg_echo('event_calander:end_before_start:error'));
		return false;
	}

	foreach ($required_fields as $fn) {
		if (!trim($e->$fn)) {
			return false;
			break;
		}
	}

	// Make tags string an array only now after required_fields check
	$e->tags = string_to_tag_array($e->tags);

	// ok, the input passes the validation so put the values in the real event object

	$keys = [
		'title',
		'description',
		'access_id',
		'start_date',
		'start_time',
		'end_date',
		'end_time',
		'venue',
		'fees',
		'contact',
		'organiser',
		'tags',
		'long_description',
		'send_reminder',
		'reminder_number',
		'reminder_interval',
		'web_conference',
		'real_end_time',
		'schedule_type',
	];

	foreach ($keys as $key) {
		$event->$key = $e->$key;
	}

	if ($event_calendar_spots_display == 'yes') {
		$event->spots = trim(get_input('spots'));
	}
	if ($event_calendar_region_display == 'yes') {
		$event->region = get_input('region');
	}
	if ($event_calendar_type_display == 'yes') {
		$event->event_type = get_input('event_type');
	}
	if ($event_calendar_personal_manage == 'by_event') {
		$event->personal_manage = get_input('personal_manage');
	}
	if ($event_calendar_repeating_events != 'no') {
		$repeats = get_input('repeats');
		$event->repeats = $repeats;
		if ($repeats == 'yes') {
			$event->repeat_interval = get_input('repeat_interval');
			$dow = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
			foreach ($dow as $w) {
				$v = 'event-calendar-repeating-'.$w.'-value';
				$event->$v = get_input($v);
			}
		}
	}

	if ($event->save()) {
		if (!$event_guid && $event->web_conference) {
			if (!event_calendar_create_bbb_conf($event)) {
				register_error(elgg_echo('event_calendar:conference_create_error'));
			}
		}
		if ($group_guid && (elgg_get_plugin_setting('autogroup', 'event_calendar') == 'yes')) {
			event_calendar_add_personal_events_from_group($event->guid, $group_guid);
		}
	}
	return $event;
}

function event_calendar_get_events_between($start_date, $end_date, $is_count=false, $limit=10, $offset=0, $container_guid=0, $region='-') {
	$polls_supported = elgg_is_active_plugin('event_poll');
	if ($is_count) {
		$count = event_calendar_get_entities_from_metadata_between($start_date, $end_date, 0, $container_guid, $limit, $offset, false, true, $region);
		return $count;
	} else {
		$events = event_calendar_get_entities_from_metadata_between($start_date, $end_date, 0, $container_guid, $limit, $offset, false, false, $region);

		$repeating_events = event_calendar_get_repeating_events_between($start_date, $end_date, $container_guid, $region);

		$all_events = event_calendar_merge_repeating_events($events, $repeating_events);
		if ($polls_supported) {
			elgg_load_library('elgg:event_poll');
			$all_events = event_poll_merge_poll_events($all_events, $start_date, $end_date);
		}

		return $all_events;
	}
}

// Merge non-repeating events found within a period of time with repeating events found within that time
// but do not return duplicates event entries.
// It also has to be taken care of that there might be no (non-repeating) events within that time but repeating events or
// that there might be events but no repeating events or that there are neither events nor repeating events in the corresponding
// function parameter variables.
function event_calendar_merge_repeating_events($events, $repeating_events) {
	$non_repeating_events = []; // temp array to collect non-repeating events
	if (is_array($events) && count($events) > 0) { // do we have any events?
		foreach($events as $e) {
			if ($e->repeats != 'yes') { // is it non-repeating?
				$non_repeating_events[] = ['event' => $e,'data' => [['start_time' => $e->start_date, 'end_time' => $e->real_end_time]]];
			}
		}
		if (is_array($repeating_events) && count($repeating_events) > 0) { // do we have also repeating events?
			if (count($non_repeating_events) > 0) { // anything to merge?
				return array_merge($non_repeating_events, $repeating_events); // return merged array of non-repeating and repeating events
			} else {
				return $repeating_events; // only repeating events, so return only these
			}
		}
	} else if (is_array($repeating_events) && count($repeating_events) > 0) { // do we have at least repeating events after we have no non-repeating events?
		return $repeating_events; // then return the array of the repeating events
	}
	// if we got here we have either only non-repeating events (then return them)
	// or we have neither non-repeating nor repeating events and the temp array is still empty so return the empty temp array
	return $non_repeating_events;
}

function event_calendar_get_repeating_events_between($start_date, $end_date, $container_guid, $region) {
	// game plan: get all repeating events with start date <= $end_date and then generate all possible events
	// sanity check
	if ($start_date <= $end_date) {
		$options = [
			'type' => 'object',
			'subtype' => 'event_calendar',
			'limit' => false,
			'metadata_name_value_pairs' => [
				['name' => 'start_date', 'value' => $end_date, 'operand' => '<='],
				['name' => 'repeats', 'value' => 'yes'],
			],
		];
		if ($container_guid) {
			if (is_array($container_guid)) {
				$options['container_guids'] = $container_guid;
			} else {
				$options['container_guid'] = $container_guid;
			}
		}

		if ($region && $region != '-') {
			$options['metadata_name_value_pairs'][] = [
				'name' => 'region',
				'value' => sanitize_string($region)
			];
		}

		$events = elgg_get_entities_from_metadata($options);
	}
	return event_calendar_get_repeating_event_structure($events, $start_date, $end_date);
}

function event_calendar_get_repeating_event_structure($events, $start_date, $end_date) {
	$dow = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
	$repeating_events = [];
	if ($events) {
		foreach($events as $e) {
			$incs = [];
			$repeat_data = [];
			$day_num = date('N', $e->start_date)-1;
			for($d=0;$d<7;$d++) {
				$fn = 'event-calendar-repeating-'.$dow[$d].'-value';
				if ($e->$fn) {
					$increment = $d - $day_num;
					$incs[] = $increment;
				}
			}
			if ($incs) {
				sort($incs);

				$repeat_interval = $e->repeat_interval;
				$event_start_time = $e->start_date;
				$event_end_time = $e->real_end_time;
				$week = 0;
				if ($event_start_time <= $event_end_time) {
					$more_to_do = true;
					$cur_start_time = $event_start_time;
					$cur_end_time = $event_end_time;
					// keep generating events until after $end_date
					// repeat_times is a sanity check to prevent infinite loops in case of bad data
					$repeat_times = 0;
					do {
						foreach($incs as $inc) {
							//$seconds = $inc*60*60*24;
							if ($inc >= 0) {
								$tinc = "+ " . $inc;
							} else {
								$tinc = $inc;
							}
							$this_start_time = strtotime($tinc . " day", $cur_start_time);
							$this_end_time = strtotime($tinc . " day", $cur_end_time);
							if ($this_start_time > $end_date) {
								$more_to_do = false;
								break;
							}
							if ($this_start_time >= $event_start_time) {
								$repeat_data[] = [
									'start_time' => $this_start_time,
									'end_time' => $this_end_time,
								];
							}
						}
						// repeat_interval weeks later
						$week += $repeat_interval;
						$cur_start_time = strtotime("+" . $week . " week", $event_start_time);
						$cur_end_time = strtotime("+" . $week ." week", $event_end_time);
						$repeat_times += 1;
					} while ($repeat_times < 1000 && $more_to_do);
				}
			}
			$repeating_events[] = ['event' => $e, 'data' => $repeat_data];
		}
	}
	return $repeating_events;
}

function event_calendar_get_open_events_between($start_date, $end_date, $is_count=false, $limit=10, $offset=0, $container_guid=0, $region='-', $meta_max = 'spots', $relationship_name = 'personal_event') {
	if ($is_count) {
		$count = event_calendar_get_entities_from_metadata_between($start_date, $end_date, 0, $container_guid, $limit, $offset, false, true, $region, $meta_max, $relationship_name);
		return $count;
	} else {
		$events = event_calendar_get_entities_from_metadata_between($start_date, $end_date, 0, $container_guid, $limit, $offset, false, false, $region, $meta_max, $relationship_name);
		$repeating_events = event_calendar_get_open_repeating_events_between($start_date, $end_date, $container_guid, $region);
		$all_events = event_calendar_merge_repeating_events($events, $repeating_events);
		return $all_events;
	}
}

function event_calendar_get_open_repeating_events_between($start_date, $end_date, $container_guid, $region) {
	$db_prefix = elgg_get_config('dbprefix');
	$meta_max = 'spots';
	$relationship_name = 'personal_event';
	$joins = [];
	$wheres = [];

	$meta_max_n = elgg_get_metastring_id($meta_max);
	if (!$meta_max_n) {
		if ($count) {
			return 0;
		} else {
			return false;
		}
	}

	$joins[] = "LEFT JOIN {$db_prefix}metadata m4 ON (e.guid = m4.entity_guid AND m4.name_id = $meta_max_n)";
	$joins[] = "LEFT JOIN {$db_prefix}metastrings ms4 ON (m4.value_id = ms4.id)";
	$wheres[] = "((ms4.string IS NULL) OR (ms4.string = \"\") OR (CONVERT(ms4.string,SIGNED) > (SELECT COUNT(id) FROM {$db_prefix}entity_relationships rela WHERE rela.guid_two = e.guid AND rela.relationship = \"{$relationship_name}\" GROUP BY rela.guid_two)))";

	// sanity check
	if ($start_date <= $end_date) {
		$options = [
			'type' => 'object',
			'subtype' => 'event_calendar',
			'limit' => false,
			'metadata_name_value_pairs' => [
				['name' => 'start_date', 'value' => $end_date, 'operand' => '<='],
				['name' => 'repeats', 'value' => 'yes'],
			],
			'joins' => $joins,
			'wheres' => $wheres,
		];

		if ($container_guid) {
			if (is_array($container_guid)) {
				$options['container_guids'] = $container_guid;
			} else {
				$options['container_guid'] = $container_guid;
			}
		}

		if ($region && $region != '-') {
			$options['metadata_name_value_pairs'][] = [
				'name' => 'region',
				'value' => sanitize_string($region),
			];
		}

		$events = elgg_get_entities_from_metadata($options);
	}
	return event_calendar_get_repeating_event_structure($events, $start_date, $end_date);
}

function event_calendar_get_events_for_user_between($start_date, $end_date, $is_count, $limit=10, $offset=0, $user_guid, $container_guid=0, $region='-') {
	$options = [
		'type' => 'object',
		'subtype' => 'event_calendar',
		'relationship' => 'personal_event',
		'relationship_guid' => $user_guid,
		'metadata_name_value_pairs' => [
			['name' => 'start_date', 'value' => $start_date, 'operand' => '>='],
			['name' => 'real_end_time', 'value' => $end_date, 'operand' => '<='],
		],
	];

	if ($container_guid) {
		$options['container_guid'] = $container_guid;
	}
	if ($region && $region != '-') {
		$options['metadata_name_value_pairs'][] = [
			'name' => 'region',
			'value' => sanitize_string($region),
		];
	}
	if ($is_count) {
		$options['count'] = true;
		$count = elgg_get_entities_from_relationship($options);
		return $count;
	} else {
		$options['limit'] = $limit;
		$options['offset'] = $offset;
		$options['order_by_metadata'] = [
			['name' => 'start_date', 'direction' => 'ASC', 'as' => 'integer'],
		];
		$events = elgg_get_entities_from_relationship($options);
		$repeating_events = event_calendar_get_repeating_events_for_user_between($user_guid, $start_date, $end_date, $container_guid, $region);
		$all_events = event_calendar_merge_repeating_events($events, $repeating_events);
		return $all_events;
	}
}

function event_calendar_get_repeating_events_for_user_between($user_guid, $start_date, $end_date, $container_guid, $region) {
	$options = [
		'type' => 'object',
		'subtype' => 'event_calendar',
		'relationship' => 'personal_event',
		'relationship_guid' => $user_guid,
		'metadata_name_value_pairs' => [
			['name' => 'start_date', 'value' => $end_date, 'operand' => '<='],
			['name' => 'repeats', 'value' => 'yes'],
		],
	];

	if ($container_guid) {
		if (is_array($container_guid)) {
			$options['container_guids'] = $container_guid;
		} else {
			$options['container_guid'] = $container_guid;
		}
	}

	if ($region && $region != '-') {
		$options['metadata_name_value_pairs'][] = [
			'name' => 'region',
			'value' => sanitize_string($region),
		];
	}

	$events = elgg_get_entities_from_relationship($options);
	return event_calendar_get_repeating_event_structure($events, $start_date, $end_date);
}

function event_calendar_get_repeating_events_for_friends_between($user_guid, $friend_list, $start_date, $end_date, $container_guid=0, $region='-') {

	$db_prefix = elgg_get_config('dbprefix');
	$options = 	[
		'type' => 'object',
		'subtype' => 'event_calendar',
		'metadata_name_value_pairs' => [
			['name' => 'start_date', 'value' => $end_date, 'operand' => '<='],
			['name' => 'repeats', 'value' => 'yes'],
		],
		'joins' => ["JOIN {$db_prefix}entity_relationships r ON (r.guid_two = e.guid)"],
		'wheres' => ["r.relationship = 'personal_event'","r.guid_one IN ($friend_list)"],
	];

	if ($container_guid) {
		if (is_array($container_guid)) {
			$options['container_guids'] = $container_guid;
		} else {
			$options['container_guid'] = $container_guid;
		}
	}
	if ($region && $region != '-') {
		$options['metadata_name_value_pairs'][] = [
			'name' => 'region',
			'value' => sanitize_string($region),
		];
	}

	$events = elgg_get_entities_from_relationship($options);
	return event_calendar_get_repeating_event_structure($events, $start_date, $end_date);
}

function event_calendar_get_events_for_friends_between($start_date, $end_date, $is_count, $limit=10, $offset=0, $user_guid, $container_guid=0, $region='-') {
	if ($user_guid) {
		$user = get_user($user_guid);

		$friends = $user->getFriends(['limit' => false]);

		if ($friends) {
			$friend_guids = [];
			foreach($friends as $friend) {
				$friend_guids[] = $friend->getGUID();
			}
			$friend_list = implode(",", $friend_guids);
			// elgg_get_entities_from_relationship does not take multiple relationship guids, so need some custom joins and wheres
			$db_prefix = elgg_get_config('dbprefix');
			$options = [
				'type' => 'object',
				'subtype' => 'event_calendar',
				'metadata_name_value_pairs' => [
					['name' => 'start_date', 'value' => $start_date, 'operand' => '>='],
					['name' => 'real_end_time', 'value' => $end_date, 'operand' => '<='],
				],
				'joins' => ["JOIN {$db_prefix}entity_relationships r ON (r.guid_two = e.guid)"],
				'wheres' => ["r.relationship = 'personal_event'","r.guid_one IN ($friend_list)"],
			];

			if ($container_guid) {
				$options['container_guid'] = $container_guid;
			}
			if ($region && $region != '-') {
				$options['metadata_name_value_pairs'][] = [
					'name' => 'region',
					'value' => sanitize_string($region),
				];
			}
			if ($is_count) {
				$options['count'] = true;
				$count = elgg_get_entities_from_metadata($options);
				return $count;
			} else {
				$options['limit'] = $limit;
				$options['offset'] = $offset;
				$options['order_by_metadata'] = [
					[
						'name' => 'start_date',
						'direction' => 'ASC',
						'as' => 'integer',
					],
				];
				$events = elgg_get_entities_from_metadata($options);
				$repeating_events = event_calendar_get_repeating_events_for_friends_between($user_guid, $friend_list, $start_date, $end_date, $container_guid, $region);
				$all_events = event_calendar_merge_repeating_events($events, $repeating_events);
				return $all_events;
			}
		}
	}
	return [];
}

function event_calendar_vsort($original, $field, $descending = false) {
	if (!$original) {
		return $original;
	}
	$sortArr = [];

	foreach ( $original as $key => $item ) {
		$sortArr[ $key ] = $item->$field;
	}

	if ( $descending ) {
		arsort( $sortArr );
	} else {
		asort( $sortArr );
	}

	$resultArr = [];
	foreach ( $sortArr as $key => $value ) {
		$resultArr[ $key ] = $original[ $key ];
	}

	return $resultArr;
}

// adds any related events (has the display_on_group relation) that meet the appropriate criteria
function event_calendar_get_entities_from_metadata_between_related($start_date, $end_date, $owner_guid=0, $container_guid=0, $limit=10, $offset=0, $filter=false, $count=false, $region='-', $main_events) {

	$main_list = [];
	if ($main_events) {
		foreach ($main_events as $event) {
			$main_list[$event->guid] = $event;
		}
	}
	$related_list = [];
	$related_events = elgg_get_entities_from_relationship([
		'relationship' => 'display_on_group',
		'relationship_guid' => $container_guid,
		'inverse_relationship' => true,
	]);
	if ($related_events) {
		foreach ($related_events as $event) {
			$related_list[$event->guid] = $event;
		}
	}
	// get all the events (across all containers) that meet the criteria
	$all_events = event_calendar_get_entities_from_metadata_between($start_date, $end_date, $owner_guid, 0, $limit, $offset, $filter, $count, $region, "", "");

	if ($all_events) {
		foreach($all_events as $event) {
			if (array_key_exists($event->guid, $related_list)
			&& !array_key_exists($event->guid, $main_list)) {
				// add to main events
				$main_events[] = $event;
			}
		}
	}
	return event_calendar_vsort($main_events, 'start_date');
}

/**
 * Return a list of event calendar entities based on the given search criteria.
 * In this case, returns events between start date and end date that match (optional) additional criteria.
 *
 * @param mixed $start_date - lower limit of metadata value range
 * @param mixed $end_date - upper limit of metadata value range
 * @param mixed $owner_guid - either one integer user guid or an array of user guids
 * @param int $container_guid - if supplied, the result is restricted to events associated with a specific container
 * @param int $limit
 * @param int $offset
 * @param boolean $filter - filter by events in personal calendar if true
 * @param true|false $is_count - if set to true, returns the total number of entities rather than a list (default: false)
 * @param string $meta_max - metadata name containing maximum relationship count
 * @param string $relationship_name - relationship name to count
 *
 * @return int|array A list of entities, or a count if $is_count is set to true
 */
function event_calendar_get_entities_from_metadata_between($start_date, $end_date, $owner_guid=0, $container_guid=0, $limit=10, $offset=0, $filter=false, $is_count=false, $region='-', $meta_max='', $relationship_name='') {

	// This should not be possible, but a sanity check just in case
	if (!is_numeric($start_date) || !is_numeric($end_date)) {
		return false;
	}

	// further sanity check
	if ($start_date > $end_date) {
		if ($is_count) {
			return 0;
		} else {
			return false;
		}
	}

	$options = [
		'type' => 'object',
		'subtype' => 'event_calendar',
		'metadata_name_value_pairs' => [
			['name' => 'start_date', 'value' => $start_date, 'operand' => '>='],
			['name' => 'end_date', 'value' => $end_date, 'operand' => '<='],
		],
	];

	if ($region && $region != '-') {
		$region = sanitize_string($region);
		$region_n = elgg_get_metastring_id('region');
		$region_value_n = elgg_get_metastring_id($region);
		if (!$region_n || !$region_value_n) {
			if ($is_count) {
				return 0;
			} else {
				return false;
			}
		} else {
			$options['metadata_name_value_pairs'][] = [
				'name' => 'region',
				'value' => $region,
			];
		}
	}

	if ($container_guid) {
		if (is_array($container_guid)) {
			$options['container_guids'] = $container_guid;
		} else {
			$options['container_guid'] = $container_guid;
		}
	}

	$joins = [];
	$wheres = [];
	$db_prefix = elgg_get_config('dbprefix');

	if ($filter) {
		$joins[] = "JOIN {$db_prefix}entity_relationships r ON (r.guid_two = e.guid)";
		$joins[] = "JOIN {$db_prefix}metastrings ms ON (r.guid_one = ms.id)";
		if (is_array($owner_guid)) {
			$wheres[] = "ms.string IN (".implode(",", $owner_guid).")";
		} else if ($owner_guid > 0) {
			$wheres[] = "ms.string = {$owner_guid}";
		}

		$wheres[] = "r.relationship = 'personal_event'";
	} else {
		if ($owner_guid) {
			if (is_array($owner_guid)) {
				$options['owner_guids'] = $owner_guid;
			} else {
				$options['owner_guid'] = $owner_guid;
			}
		}
	}

	if ($meta_max && $relationship_name) {
		// This groups events for which the meta max name is defined
		// perhaps this should be a left join and accept null values?
		// so it would return groups with no spots defined as well
		$meta_max_n = elgg_get_metastring_id($meta_max);
		if (!$meta_max_n) {
			if ($is_count) {
				return 0;
			} else {
				return false;
			}
		}
		$joins[] = "LEFT JOIN {$db_prefix}metadata m4 ON (e.guid = m4.entity_guid AND m4.name_id = $meta_max_n)";
		$joins[] = "LEFT JOIN {$db_prefix}metastrings ms4 ON (m4.value_id = ms4.id)";
		$wheres[] = "((ms4.string IS NULL) OR (ms4.string = \"\") OR (CONVERT(ms4.string,SIGNED) > (SELECT COUNT(id) FROM {$db_prefix}entity_relationships rela WHERE rela.guid_two = e.guid AND rela.relationship = \"{$relationship_name}\" GROUP BY rela.guid_two)))";
	}

	$options['joins'] = $joins;
	$options['wheres'] = $wheres;
	
	if ($is_count) {
		$options['count'] = true;
		$count = elgg_get_entities_from_metadata($options);
		return $count;
	} else {
		$options['limit'] = (int)$limit;
		$options['offset'] = (int)$offset;
		$options['order_by_metadata'] = [
			[
				'name' => 'start_date',
				'direction' => 'ASC',
				'as' => 'integer',
			],
		];
		$entities = elgg_get_entities_from_metadata($options);

		if (elgg_get_plugin_setting('add_to_group_calendar', 'event_calendar') == 'yes') {
			if ($container_guid && !is_array($container_guid) && (get_entity($container_guid) instanceOf ElggGroup)) {
				$entities = event_calendar_get_entities_from_metadata_between_related($start_date, $end_date, $owner_guid, $container_guid, false, 0, false, false, '-', $entities);
			}
		}

		return $entities;
	}

	return false;
}

function event_calendar_has_personal_event($event_guid, $user_guid) {
	if (check_entity_relationship($user_guid, 'personal_event', $event_guid)) {
		return true;
	}
	return false;
}

function event_calendar_add_personal_event($event_guid, $user_guid) {
	if ($event_guid && $user_guid) {
		if (!event_calendar_has_personal_event($event_guid, $user_guid) && !event_calendar_has_collision($event_guid,$user_guid)) {
			if (!event_calendar_is_full($event_guid)) {
				add_entity_relationship($user_guid,'personal_event', $event_guid);
				return true;
			}
		}
	}
	return false;
}

function event_calendar_add_personal_events_from_group($event_guid, $group_guid) {
	$group = get_entity($group_guid);

	$members = $group->getMembers(['limit' => false]);

	foreach($members as $member) {
		event_calendar_add_personal_event($event_guid, $member->guid);
	}
}

function event_calendar_remove_personal_event($event_guid, $user_guid) {
	remove_entity_relationship($user_guid, 'personal_event', $event_guid);
}

function event_calendar_get_users_for_event($event_guid, $limit, $offset=0, $is_count=false) {
	$options = [
		'type' => 'user',
		'relationship' => 'personal_event',
		'relationship_guid' => $event_guid,
		'inverse_relationship' => true,
		'limit' => false,
	];
	if ($is_count) {
		$options ['count'] = true;
		$count = elgg_get_entities_from_relationship($options);
		return $count;
	} else {
		$users = elgg_get_entities_from_relationship($options);
		return $users;
	}
}

function event_calendar_convert_time($time) {
	$event_calendar_time_format = elgg_get_plugin_setting('timeformat', 'event_calendar');
	if ($event_calendar_time_format == '12') {
		$hour = floor($time/60);
		$minute = sprintf("%02d", $time-60*$hour);
		if ($hour < 12) {
		  if ($hour == 0) {
		    $hour = 12;
		  }
			return "$hour:$minute am";
		} else {
			$hour -= 12;
			if ($hour == 0) {
			  $hour = 12;
			}
			return "$hour:$minute pm";
		}
	} else {
		$hour = floor($time/60);
		$minute = sprintf("%02d", $time-60*$hour);
		return "$hour:$minute";
	}
}

function event_calendar_format_time($date, $time1, $time2='') {
	if (is_numeric($time1)) {
		$t = event_calendar_convert_time($time1);
		if (is_numeric($time2)) {
			$t .= " - ".event_calendar_convert_time($time2);
		}
		return "$t, $date";
	} else {
		return $date;
	}
}

function event_calender_get_gmt_from_server_time($server_time) {
	$gmtime = $server_time - (int)substr(date('O'),0,3)*60*60;
}

function event_calendar_activated_for_group($group) {
	$group_calendar = elgg_get_plugin_setting('group_calendar', 'event_calendar');
	$group_default = elgg_get_plugin_setting('group_default', 'event_calendar');
	if ($group && (!$group_calendar || $group_calendar != 'no')) {
		if ( ($group->event_calendar_enable == 'no') || (!$group->event_calendar_enable && $group_default == 'no')) {
			return false;
		}
		return true;
	}
	return false;
}

function event_calendar_get_region($event) {
	$event_calendar_region_list_handles = elgg_get_plugin_setting('region_list_handles', 'event_calendar');
	$region = trim($event->region);
	if ($event_calendar_region_list_handles == 'yes') {
		$region = elgg_echo('event_calendar:region:'.$region);
	}
	return htmlspecialchars($region);
}

function event_calendar_get_type($event) {
	$event_calendar_type_list_handles = elgg_get_plugin_setting('type_list_handles', 'event_calendar');
	$type = trim($event->event_type);
	if ($type) {
		if ($event_calendar_type_list_handles == 'yes') {
			$type = elgg_echo('event_calendar:type:'.$type);
		}
		return htmlspecialchars($type);
	} else {
		return $type;
	}
}

function event_calendar_get_formatted_full_items($event) {
	$time_bit = event_calendar_get_formatted_time($event);
	$event_calendar_region_display = elgg_get_plugin_setting('region_display', 'event_calendar');
	$event_calendar_type_display = elgg_get_plugin_setting('type_display', 'event_calendar');
	$event_items = [];
	if ($time_bit) {
		$item = new stdClass();
		$item->title = elgg_echo('event_calendar:when_label');
		$item->value = $time_bit;
		$event_items[] = $item;
	}
	$item = new stdClass();
	$item->title = elgg_echo('event_calendar:venue_label');
	$item->value = htmlspecialchars($event->venue);
	$event_items[] = $item;
	if ($event_calendar_region_display == 'yes') {
		$item = new stdClass();
		$item->title = elgg_echo('event_calendar:region_label');
		$item->value = event_calendar_get_region($event);
		$event_items[] = $item;
	}
	if ($event_calendar_type_display == 'yes') {
		$event_type = event_calendar_get_type($event);
		if ($event_type) {
			$item = new stdClass();
			$item->title = elgg_echo('event_calendar:type_label');
			$item->value = event_calendar_get_type($event);
			$event_items[] = $item;
		}
	}
	$item = new stdClass();
	$item->title = elgg_echo('event_calendar:fees_label');
	$item->value = htmlspecialchars($event->fees);
	$event_items[] = $item;
	$item = new stdClass();
	$item->title = elgg_echo('event_calendar:organiser_label');
	$item->value = htmlspecialchars($event->organiser);
	$event_items[] = $item;
	$item = new stdClass();
	$item->title = elgg_echo('event_calendar:contact_label');
	$item->value = htmlspecialchars($event->contact);
	$event_items[] = $item;

	return $event_items;
}

function event_calendar_get_formatted_time($event) {
	if (!$event->start_date) {
		return '';
	}
	$date_format = 'j M Y';
	$event_calendar_times = elgg_get_plugin_setting('times', 'event_calendar') != 'no';

	$start_date = date($date_format, $event->start_date);
	if ($event->schedule_type == 'all_day') {
	  $time_bit = $start_date . ' ' . elgg_echo('event_calendar:all_day_bit');
	} else {
		if ($event->end_date) {
			$end_date = date($date_format, $event->end_date);
		}
		if ((!$event->end_date) || ($end_date == $start_date)) {
			if (!$event->all_day && $event_calendar_times) {
				$start_date = event_calendar_format_time($start_date, $event->start_time, $event->end_time);
			}
			$time_bit = $start_date;
		} else {
			if (!$event->all_day && $event_calendar_times) {
				$start_date = event_calendar_format_time($start_date, $event->start_time);
				$end_date = event_calendar_format_time($end_date, $event->end_time);
			}
			$time_bit = "$start_date - $end_date";
		}
	}

	if ($event->repeats == 'yes') {
		$dow = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
		$r = [];
		foreach ($dow as $w) {
			$fn = 'event-calendar-repeating-'.$w.'-value';
			if ($event->$fn) {
				$r[] = elgg_echo('event_calendar:dow:full:'.$w);
			}
		}
		$week_bit = implode(", ", $r);
		if ($event->repeat_interval > 1) {
			$week_bit .= ' '.elgg_echo('event_calendar:repeated_event:week_interval', [$event->repeat_interval]);
		} else {
			$week_bit .= ' '.elgg_echo('event_calendar:repeated_event:week_single');
		}
		$time_bit = elgg_echo('event_calendar:repeated_event:format', [$time_bit, $week_bit]);
	}

	return $time_bit;
}

function event_calendar_get_formatted_date($ts) {
	// TODO: make the date format configurable
	return date('j/n/Y',$ts);
}

function event_calendar_is_full($event_id) {
	$event_calendar_spots_display = elgg_get_plugin_setting('spots_display', 'event_calendar');
	if ($event_calendar_spots_display == 'yes') {
		$count = event_calendar_get_users_for_event($event_id, 0, 0, true);
		$event = get_entity($event_id);
		if ($event) {
			$spots = $event->spots;
			if (is_numeric($spots)) {
				if ($count >= $spots) {
					return true;
				}
			}
		}
	}
	return false;
}

function event_calendar_has_collision($event_id, $user_id) {
	$no_collisions = elgg_get_plugin_setting('no_collisions', 'event_calendar');
	if ($no_collisions == 'yes') {
		$event = get_entity($event_id);
		if ($event) {
			$start_time = $event->start_date;
			$end_time = event_calendar_get_end_time($event);
			// look to see if the user already has events within this period
			$count = event_calendar_get_events_for_user_between($start_time, $end_time, true, 10, 0, $user_id);
			if ($count > 0) {
				return true;
			} else {
				return false;
			}
		}
	}

	return false;
}

// this complicated bit of code determines the event end time
function event_calendar_get_end_time($event) {
	$default_length = (int)elgg_get_plugin_setting('collision_length', 'event_calendar', 3600);
	$start_time = $event->start_date;
	$end_time = $event->end_time;
	$end_date = $event->end_date;
	if($end_date) {
		if ($end_time) {
			$end_time = $end_date + $end_time*60;
		} else if ($start_time == $end_date) {
			$end_time = $end_date + $default_length;
		} else {
			$end_time = $end_date;
		}
	} else {
		if ($end_time) {
			if ($event->start_time) {
				$end_time = $start_time + ($end_time*60 - $event->start_time*60);
			} else {
				$end_time = $start_time + $end_time*60;
			}
		} else {
			$end_time = $start_time + $default_length;
		}
	}

	return $end_time;
}

// a version to allow for some customised options
function event_calendar_view_entity_list($entities, $count, $offset, $limit, $full_view = true, $list_type_toggle = true, $pagination = true) {
	$count = (int) $count;
	$limit = (int) $limit;

	// do not require views to explicitly pass in the offset
	if (!$offset = (int) $offset) {
		$offset = sanitise_int(get_input('offset', 0));
	}

	$context = elgg_get_context();

	$html = elgg_view('event_calendar/entities/entity_list', [
		'entities' => $entities,
		'count' => $count,
		'offset' => $offset,
		'limit' => $limit,
		'base_url' => $_SERVER['REQUEST_URI'],
		'full_view' => $full_view,
		'context' => $context,
		'list_type_toggle' => $list_type_toggle,
		'viewtype' => get_input('search_viewtype','list'),
		'pagination' => $pagination,
	]);

	return $html;
}

// returns open, closed or private for the given event and user
function event_calendar_personal_can_manage($event, $user_id) {
	$status = 'private';
	$event_calendar_personal_manage = elgg_get_plugin_setting('personal_manage', 'event_calendar');
	if (!$event_calendar_personal_manage
		|| $event_calendar_personal_manage == 'open'
		|| $event_calendar_personal_manage == 'yes'
		|| (($event_calendar_personal_manage == 'by_event' && (!$event->personal_manage || ($event->personal_manage == 'open'))))) {
		$status = 'open';
	} else {
		// in this case only admins or event owners can manage events on their personal calendars
		if(elgg_is_admin_logged_in()) {
			$status = 'open';
		} else if ($event && ($event->owner_guid == $user_id)) {
			$status = 'open';
		} else if (($event_calendar_personal_manage == 'closed')
			|| ($event_calendar_personal_manage == 'no')
			|| (($event_calendar_personal_manage == 'by_event') && ($event->personal_manage == 'closed'))) {
			$status = 'closed';
		}
	}

	return $status;
}

function event_calendar_send_event_request($event, $user_guid) {
	$result = false;
	if(add_entity_relationship($user_guid, 'event_calendar_request', $event->guid)) {
		$event_owner = get_user($event->owner_guid);
		$event_owner_language = ($event_owner->language) ? $event_owner->language : (($site_language = elgg_get_config('language')) ? $site_language : 'en');
		$subject = elgg_echo('event_calendar:request_subject', [], $event_owner_language);
		$name = get_entity($user_guid)->name;
		$title = $event->title;
		$url = $event->getUrl();
		$link = elgg_get_site_url().'event_calendar/review_requests/'.$event->guid;
		$message = elgg_echo('event_calendar:request_message', [$name, $title, $url, $link], $event_owner_language);
		notify_user($event->owner_guid, elgg_get_logged_in_user_guid(), $subject, $message, [
			'object' => $event,
			'action' => 'request',
			'summary' => $subject,
		]);
		$result = true;
	}
	return $result;
}

/**
 * Pull together variables for the edit form
 *
 * @param ElggObject $event
 * @return array
 */
function event_calendar_prepare_edit_form_vars($event = null, $page_type = '', $start_date = '') {

	// input names => defaults
	$now = time();
	$iso_date = date('Y-m-d',$now);
	$now_midnight = strtotime($iso_date);
	if ($start_date) {
		$start_date = strtotime($start_date);
	} else {
		$start_date = $now+60*60;
	}
	$start_time = floor(($now-$now_midnight)/60) + 60;
	$start_time = floor($start_time/5)*5;
	$values = [
		'title' => null,
		'description' => null,
		'venue' => null,
		'start_date' => $start_date,
		'end_date' => $start_date+60*60,
		'start_time' => $start_time,
		'start_time_hour' => null,
		'start_time_minute' => null,
		'start_time_meridian' => null,
		'start_date_for_all_day' => null,
		'end_time' => $start_time + 60,
		'end_time_hour' => null,
		'end_time_minute' => null,
		'end_time_meridian' => null,
		'spots' => null,
		'region' => '-',
		'event_type' => '-',
		'fees' => null,
		'contact' => null,
		'organiser' => null,
		'tags' => null,
		'send_reminder' => null,
		'reminder_number' => 1,
		'reminder_interval' => 60,
		'repeats' => null,
		'repeat_interval' => 1,
		'event-calendar-repeating-monday-value' => 0,
		'event-calendar-repeating-tuesday-value' => 0,
		'event-calendar-repeating-wednesday-value' => 0,
		'event-calendar-repeating-thursday-value' => 0,
		'event-calendar-repeating-friday-value' => 0,
		'event-calendar-repeating-saturday-value' => 0,
		'event-calendar-repeating-sunday-value' => 0,
		'personal_manage' => 'open',
		'web_conference' => null,
		'schedule_type' => null,
		'long_description' => null,
		'access_id' => ACCESS_DEFAULT,
		'group_guid' => null,
	];

	if ($page_type == 'schedule') {
		$values['schedule_type'] = 'poll';
	} else {
		$values['schedule_type'] = 'fixed';
	}

	if ($event) {
		foreach (array_keys($values) as $field) {
			if (isset($event->$field)) {
				$values[$field] = $event->$field;
			}
		}
	}

	if (elgg_is_sticky_form('event_calendar')) {
		$sticky_values = elgg_get_sticky_values('event_calendar');
		foreach ($sticky_values as $key => $value) {
			$values[$key] = $value;
		}
	}

	elgg_clear_sticky_form('event_calendar');

	return $values;
}

function event_calendar_generate_listing_params($page_type, $container_guid, $original_start_date, $display_mode, $filter, $region='-') {
	$event_calendar_listing_format = elgg_get_plugin_setting('listing_format', 'event_calendar');
	$event_calendar_spots_display = trim(elgg_get_plugin_setting('spots_display', 'event_calendar'));
	$event_calendar_first_date = trim(elgg_get_plugin_setting('first_date', 'event_calendar'));
	$event_calendar_last_date = trim(elgg_get_plugin_setting('last_date', 'event_calendar'));

	if (!$original_start_date) {
		$original_start_date = date('Y-m-d');
	}
	if ( $event_calendar_first_date && ($original_start_date < $event_calendar_first_date) ) {
		$original_start_date = $event_calendar_first_date;
	}
	if ( $event_calendar_last_date && ($original_start_date > $event_calendar_last_date) ) {
		$original_start_date = $event_calendar_first_date;
	}

	if ($event_calendar_listing_format == 'paged') {
		$start_ts = strtotime($original_start_date);
		$start_date = $original_start_date;
		if ($event_calendar_last_date) {
			$end_ts = strtotime($event_calendar_last_date);
		} else {
			// set to a large number
			$end_ts = 2000000000;
		}
		$mode = 'paged';
	} else {

		// the default interval is one month
		$day = 60*60*24;
		$week = 7*$day;
		$month = 31*$day;

		$mode = trim($display_mode);
		if (!$mode) {
			$mode = 'month';
		}

		if ($mode == "day") {
			$start_date = $original_start_date;
			$end_date = $start_date;
			$start_ts = strtotime($start_date);
			$end_ts = strtotime($end_date)+$day-1;
		} else if ($mode == "week") {
			// need to adjust start_date to be the beginning of the week
			$start_ts = strtotime($original_start_date);
			$start_ts -= date("w", $start_ts)*$day;
			// First day Sunday or Monday (depending on user/site language)
			$user_language = get_language();
			$user_language = ($user_language == false) ? 'en' : $user_language;
			$first_day_of_week = event_calendar_get_first_day_of_week($user_language);
			if ($first_day_of_week == 1) {
				$start_ts += $day;
			}
			$end_ts = $start_ts + 6*$day;

			$start_date = date('Y-m-d', $start_ts);
			$end_date = date('Y-m-d', $end_ts);
		} else {
			$start_ts = strtotime($original_start_date);
			$month = date('m', $start_ts);
			$year = date('Y', $start_ts);
			$start_date = $year.'-'.$month.'-1';
			$end_date = $year.'-'.$month.'-'.getLastDayOfMonth($month,$year);
		}

		if ($event_calendar_first_date && ($start_date < $event_calendar_first_date)) {
			$start_date = $event_calendar_first_date;
		}

		if ($event_calendar_last_date && ($end_date > $event_calendar_last_date)) {
			$end_date = $event_calendar_last_date;
		}

		$start_ts = strtotime($start_date);
		if ($mode == "day") {
			$end_ts = strtotime($end_date)+$day-1;
			$subtitle = elgg_echo('event_calendar:day_label_with_separator', [date('j', $start_ts), elgg_echo("event_calendar:month:".date('m', $start_ts)), date('Y', $start_ts)]);
		} else if ($mode == "week") {
			// KJ - fix for end date bug
			//$end_ts = $start_ts + 6*$day;
			$end_ts = $start_ts + 7*$day;
			$subtitle = elgg_echo('event_calendar:week_label_with_separator', [date('j',$start_ts), elgg_echo("event_calendar:month:".date('m', $start_ts)), date('j',$end_ts), elgg_echo("event_calendar:month:".date('m', $end_ts)), date('Y',$end_ts)]);
		} else {
			// KJ - fix for end date bug
			//$end_ts = strtotime($end_date);
			$end_ts = strtotime($end_date)+24*60*60-1;
			$subtitle = elgg_echo("event_calendar:month_label_with_separator", [elgg_echo("event_calendar:month:".date('m', $start_ts)), date('Y', $start_ts)]);
		}
	}

	$current_user_guid = elgg_get_logged_in_user_guid();

	$access_status = elgg_get_ignore_access();

	$container = get_entity($container_guid);
	if ($page_type == 'owner') {
		if (elgg_instanceof($container, 'user')) {
			$auth_token = get_input('auth_token');
			if ($auth_token) {
				$secret_key = event_calendar_get_secret_key();
				if ($secret_key && ($auth_token === sha1($container->username . $secret_key))) {
					elgg_set_ignore_access(true);
				}
			}
			if ($current_user_guid && ($current_user_guid == $container_guid)) {
				$filter = 'mine';
			} else {
				$filter = 'owner';
			}
			$user_guid = $container_guid;
			$group_guid = 0;
		} else {
			register_error(elgg_echo('event_calendar:owner:permissions_error'));
			forward();
			exit;
		}
	} else {
		$user_guid = $current_user_guid;
		$group_guid = $container_guid;
	}

	$offset = get_input('offset');
	$limit = get_input('limit',15);

	if (!$filter) {
			$filter = 'all';
	}

	if (($filter == 'all') || ($filter == 'owner')) {
		$count = event_calendar_get_events_between($start_ts, $end_ts, true, $limit, $offset, $container_guid, $region);
		$events = event_calendar_get_events_between($start_ts, $end_ts, false, $limit, $offset, $container_guid, $region);
	} else if ($filter == 'open') {
		$count = event_calendar_get_open_events_between($start_ts, $end_ts, true, $limit, $offset, $container_guid, $region);
		$events = event_calendar_get_open_events_between($start_ts, $end_ts, false, $limit, $offset, $container_guid, $region);
	} else if ($filter == 'friends') {
		$count = event_calendar_get_events_for_friends_between($start_ts, $end_ts, true, $limit, $offset, $user_guid, $container_guid, $region);
		$events = event_calendar_get_events_for_friends_between($start_ts, $end_ts, false, $limit, $offset, $user_guid, $container_guid, $region);
	} else if ($filter == 'mine') {
		$container = elgg_get_logged_in_user_entity();
		$count = event_calendar_get_events_for_user_between($start_ts, $end_ts, true, $limit, $offset, $user_guid, $container_guid, $region);
		$events = event_calendar_get_events_for_user_between($start_ts, $end_ts, false, $limit, $offset, $user_guid, $container_guid, $region);
	}

	$vars = [
		'original_start_date' => $original_start_date,
		'start_date' => $start_date,
		'end_date' => $end_date,
		'first_date' => $event_calendar_first_date,
		'last_date' => $event_calendar_last_date,
		'mode' => $mode,
		'events' => $events,
		'count' => $count,
		'offset' => $offset,
		'limit' => $limit,
		'group_guid' => $group_guid,
		'filter' => $filter,
		'region' => $region,
		'listing_format' => $event_calendar_listing_format,
	];

	$content = elgg_view('event_calendar/show_events', $vars);

	if ($page_type == 'group') {
		$filter_override = '';
		$sidebar = 'group';
	} else {
		$filter_override = elgg_view('event_calendar/filter_menu', $vars);
		$sidebar = $filter;
	}

	switch ($event_calendar_listing_format) {
		case 'paged':
			switch ($page_type) {
				case 'group':
				case 'owner':
					$title = elgg_echo('event_calendar:upcoming_events_listing_title:user', [$container->name]);
					break;
				default:
					switch ($filter) {
						case 'mine':
							$title = elgg_echo('event_calendar:upcoming_events_listing_title:user', [$container->name]);
							break;
						default:
							$title = elgg_echo('event_calendar:upcoming_events_listing_title:'.$filter);
					}
			}
			break;
		case 'full':
			switch ($page_type) {
				case 'group':
				case 'owner':
					$title = elgg_echo('event_calendar:listing_title:user', [$container->name]);
					break;
				default:
					switch ($filter) {
						case 'mine':
							$title = elgg_echo('event_calendar:listing_title:user', [$container->name]);
							break;
						default:
							$title = elgg_echo('event_calendar:listing_title:'.$filter);
					}
			}
			break;
		default:
			switch ($page_type) {
				case 'group':
				case 'owner':
					$title = elgg_echo('event_calendar:listing_title:user', [$container->name]). ' ('.$subtitle.')';
					break;
				default:
					switch ($filter) {
						case 'mine':
							$title = elgg_echo('event_calendar:listing_title:user', [$container->name]). ' ('.$subtitle.')';
							break;
						default:
							$title = elgg_echo('event_calendar:listing_title:'.$filter). ' ('.$subtitle.')';
					}
			}
	}

	$params = [
		'title' => $title,
		'content' => $content,
		'filter_override' => $filter_override,
		'sidebar' => elgg_view('event_calendar/sidebar', ['page' => $sidebar]),
	];

	elgg_set_ignore_access($access_status);
	return $params;
}

function event_calendar_get_ical_events($page_type, $container_guid, $original_start_date, $display_mode, $filter, $region='-') {
	$event_calendar_listing_format = elgg_get_plugin_setting('listing_format', 'event_calendar');
	$event_calendar_first_date = trim(elgg_get_plugin_setting('first_date', 'event_calendar'));
	$event_calendar_last_date = trim(elgg_get_plugin_setting('last_date', 'event_calendar'));

	if (!$original_start_date) {
		$original_start_date = date('Y-m-d');
	}
	if ( $event_calendar_first_date && ($original_start_date < $event_calendar_first_date) ) {
		$original_start_date = $event_calendar_first_date;
	}
	if ( $event_calendar_last_date && ($original_start_date > $event_calendar_last_date) ) {
		$original_start_date = $event_calendar_first_date;
	}

	if ($event_calendar_listing_format == 'paged') {
		$start_ts = strtotime($original_start_date);
		$start_date = $original_start_date;
		if ($event_calendar_last_date) {
			$end_ts = strtotime($event_calendar_last_date);
		} else {
			// set to a large number
			$end_ts = 2000000000;
		}
		$mode = 'paged';
	} else {

		// the default interval is one month
		$day = 60*60*24;
		$week = 7*$day;
		$month = 31*$day;

		$mode = trim($display_mode);
		if (!$mode) {
			$mode = 'month';
		}

		if ($mode == "day") {
			$start_date = $original_start_date;
			$end_date = $start_date;
			$start_ts = strtotime($start_date);
			$end_ts = strtotime($end_date)+$day-1;
		} else if ($mode == "week") {
			// need to adjust start_date to be the beginning of the week
			$start_ts = strtotime($original_start_date);
			$start_ts -= date("w", $start_ts)*$day;
			$end_ts = $start_ts + 6*$day;

			$start_date = date('Y-m-d', $start_ts);
			$end_date = date('Y-m-d', $end_ts);
		} else {
			$start_ts = strtotime($original_start_date);
			$month = date('m', $start_ts);
			$year = date('Y', $start_ts);
			$start_date = $year.'-'.$month.'-1';
			$end_date = $year.'-'.$month.'-'.getLastDayOfMonth($month,$year);
		}

		if ($event_calendar_first_date && ($start_date < $event_calendar_first_date)) {
			$start_date = $event_calendar_first_date;
		}

		if ($event_calendar_last_date && ($end_date > $event_calendar_last_date)) {
			$end_date = $event_calendar_last_date;
		}

		$start_ts = strtotime($start_date);
		if ($mode == "day") {
			$end_ts = strtotime($end_date)+$day-1;
		} else if ($mode == "week") {
			// KJ - fix for end date bug
			//$end_ts = $start_ts + 6*$day;
			$end_ts = $start_ts + 7*$day;
		} else {
			// KJ - fix for end date bug
			//$end_ts = strtotime($end_date);
			$end_ts = strtotime($end_date)+24*60*60-1;
		}
	}

	$current_user_guid = elgg_get_logged_in_user_guid();

	$access_status = elgg_get_ignore_access();

	$container = get_entity($container_guid);
	if ($page_type == 'owner') {
		if (elgg_instanceof($container, 'user')) {
			$auth_token = get_input('auth_token');
			if ($auth_token) {
				$secret_key = event_calendar_get_secret_key();
				if ($secret_key && ($auth_token === sha1($container->username . $secret_key))) {
					elgg_set_ignore_access(true);
				}
			}
			if ($current_user_guid && ($current_user_guid == $container_guid)) {
				$filter = 'mine';
			} else {
				$filter = 'owner';
			}
			$user_guid = $container_guid;
			$group_guid = 0;
		} else {
			return false;
		}
	} else {
		$user_guid = $current_user_guid;
		$group_guid = $container_guid;
	}

	if (!$filter) {
			$filter = 'all';
	}

	if (($filter == 'all') || ($filter == 'owner')) {
		$events = event_calendar_get_events_between($start_ts, $end_ts, false, false, 0, $container_guid, $region);
	} else if ($filter == 'open') {
		$events = event_calendar_get_open_events_between($start_ts, $end_ts, false, false, 0, $container_guid, $region);
	} else if ($filter == 'friends') {
		$events = event_calendar_get_events_for_friends_between($start_ts, $end_ts, false, false, 0, $user_guid, $container_guid, $region);
	} else if ($filter == 'mine') {
		$events = event_calendar_get_events_for_user_between($start_ts, $end_ts, false, false, 0, $user_guid, $container_guid, $region);
	}

	elgg_set_ignore_access($access_status);
	return $events;
}

function event_calendar_handle_menu($event_guid) {
	$event = get_entity($event_guid);
	$event_calendar_personal_manage = elgg_get_plugin_setting('personal_manage', 'event_calendar');
	if ((($event_calendar_personal_manage == 'by_event') && ($event->personal_manage == 'closed'))
		|| (($event_calendar_personal_manage == 'closed') || ($event_calendar_personal_manage == 'no'))) {
		$url =  "event_calendar/review_requests/$event_guid";
		$item = new ElggMenuItem('event-calendar-0review_requests', elgg_echo('event_calendar:review_requests_menu_title'), $url);
		$item->setSection('event_calendar');
		elgg_register_menu_item('page', $item);
	}
	$event_calendar_add_users = elgg_get_plugin_setting('add_users', 'event_calendar');
	if ($event_calendar_add_users == 'yes') {
		$url =  "event_calendar/manage_users/$event_guid";
		$item = new ElggMenuItem('event-calendar-1manage_users', elgg_echo('event_calendar:manage_users:breadcrumb'), $url);
		$item->setSection('event_calendar');
		elgg_register_menu_item('page', $item);
	}
}
function event_calendar_get_secret_key() {
	$key_file_name = elgg_get_plugin_setting('ical_auth_file_name', 'event_calendar');
	if ($key_file_name && file_exists($key_file_name)) {
		$key = (require($key_file_name));

		return $key['tokenSecretKey'];
	} else {
		return false;
	}
}

function getLastDayOfMonth($month, $year) {
	return idate('d', mktime(0, 0, 0, ($month + 1), 0, $year));
}

function event_calendar_modify_full_calendar($event_guid, $day_delta, $minute_delta, $start_time, $resend, $minutes, $iso_date) {
	$event = get_entity($event_guid);
	if (elgg_instanceof($event, 'object', 'event_calendar') && $event->canEdit()) {
		if ($event->is_event_poll) {
			if (elgg_is_active_plugin('event_poll')) {
				elgg_load_library('elgg:event_poll');
				return event_poll_change($event_guid, $day_delta, $minute_delta, $start_time, $resend, $minutes, $iso_date);
			} else {
				return false;
			}
		} else {
			$event->start_date = strtotime("$day_delta days", $event->start_date)+60*$minute_delta;
			if ($event->end_date) {
				$event->end_date = strtotime("$day_delta days", $event->end_date);
			}
			$times = elgg_get_plugin_setting('times', 'event_calendar');
			//$inc = 24*60*60*$day_delta+60*$minute_delta;

			//$event->real_end_time += $inc;
			$event->real_end_time = strtotime("$day_delta days", $event->real_end_time)+60*$minute_delta;
			if ($times != 'no') {
				$event->start_time += $minute_delta;
				if ($event->end_time) {
					$event->end_time += $minute_delta;
				}
			}
			return true;
		}
	}
	return false;
}

// right now this does not return repeated events in sorted order, so repeated events only really work properly for the full calendar
// TODO: find another solution for displaying repeated events

function event_calendar_flatten_event_structure($events) {
	$flattened = [];
	$guids = [];
	foreach($events as $e) {
		$this_event = $e['event'];
		$guid = $this_event->guid;
		if (!in_array($guid,$guids)) {
			$guids[] = $guid;
			$flattened[] = $this_event;
		}
	}
	return $flattened;
}

/**
 * Notify users before an event if the message_queue plugin is installed
 *
 * Game plan - get all events up to 60 days ahead with no reminder sent compute
 * reminder period if <= current time, set reminder_queued flag and queue the
 * notification message using the message_queue plugin.
 */
function event_calendar_queue_reminders() {
	if (!elgg_is_active_plugin('message_queue')) {
		return;
	}

	$now = time();

	$ia = elgg_set_ignore_access(true);

	$event_list = event_calendar_get_events_between($now, $now + 60*24*60*60, false, 0);

	foreach($event_list as $es) {
		$e = $es['event'];
		if ($e->send_reminder) {
			$reminder_period = 60 * $e->reminder_interval * $e->reminder_number;

			if ($e->repeats) {
				// repeated events require more complex handing
				foreach($es['data'] as $d) {
					// if event falls in the reminder period
					if ($d->start_time - $reminder_period >= $now) {
						// and the reminder has not already been queued
						if (!event_calendar_repeat_reminder_logged($e, $d->start_time)) {
							// set the reminder queued flag
							event_calendar_repeat_reminder_log($e, $d->start_time);
							// queue the reminder for sending
							event_calendar_queue_reminder($e);
						}
						break;
					}
				}
			} else {
				// if this is just a normal non-repeated event, then we just need to set a flag and queue the reminder
				if (($e->reminder_queued != 'yes') && ($e->start_date - $now <= $reminder_period)) {
					$e->reminder_queued = 'yes';
					event_calendar_queue_reminder($e);
				}
			}
		}
	}

	elgg_set_ignore_access($ia);
}

function event_calendar_repeat_reminder_log($e, $start) {
	// this simple log just uses annotations on the event
	// TODO - remove log entries for past events
	create_annotation($e->guid, 'repeat_reminder_log_item', $start, '', 0, ACCESS_PRIVATE);
}

function event_calendar_repeat_reminder_logged($e, $start) {
	$options = [
		'guid' => $e->guid,
		'annotation_name' => 'repeat_reminder_log_item',
		'annotation_value' => $start,
		'limit' => 1,
	];

	if (elgg_get_annotations($options)) {
		return true;
	} else {
		return false;
	}
}

function event_calendar_queue_reminder($e) {
	elgg_load_library('elgg:message_queue');
	$subject = elgg_echo('event_calendar:reminder:subject', [$e->title]);
	$time_string = event_calendar_get_formatted_time($e);
	$body = elgg_echo('event_calendar:reminder:body', [$e->title, $time_string, $e->getURL()]);
	$m = message_queue_create_message($subject, $body);
	if ($m) {
		$users = event_calendar_get_users_for_event($e->guid,0);
		foreach($users as $u) {
			message_queue_add($m->guid, $u->guid);
		}
		message_queue_set_for_sending($m->guid);
	}
}

// utility function for BBB api calls
function event_calendar_bbb_api($api_function, $params=null) {

	$bbb_security_salt = elgg_get_plugin_setting('bbb_security_salt', 'event_calendar');
	$bbb_server_url = rtrim(elgg_get_plugin_setting('bbb_server_url', 'event_calendar'), '/') . '/';
	if ($bbb_security_salt) {
		if (isset($params) && is_array($params) && count($params) > 0) {
			$query = [];
			foreach($params as $k => $v) {
				$query[] = $k.'='.rawurlencode($v);
			}
			$qs = implode('&',$query);
		} else {
			$qs = '';
		}
		$checksum = sha1($api_function.$qs.$bbb_security_salt);
		if ($qs) {
			$qs .= "&checksum=$checksum";
		}

		// create curl resource
	    $ch = curl_init();

	    // set url
	    curl_setopt($ch, CURLOPT_URL, $bbb_server_url.'api/'.$api_function.'?'.$qs);

	    //return the transfer as a string
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	    // $output contains the output string
	    $output = curl_exec($ch);

	    // close curl resource to free up system resources
	    curl_close($ch);

		return $output;
	} else {
		return false;
	}
}

function event_calendar_create_bbb_conf($event) {
	$day_in_minutes = 60*24;
	$now = time();
	// fix duration bug
	# $duration = (int)(($event->real_end_time-$event->start_date)/60)+$day_in_minutes;
	$duration = (int)(($event->real_end_time-$now)/60)+$day_in_minutes;
	if ($duration > 0) {
		$title = urlencode($event->title);
		$output = event_calendar_bbb_api('create', ['meetingID' => $event->guid, 'name' => $title, 'duration' => $duration]);
		if ($output) {
			$xml = new SimpleXMLElement($output);
			if ($xml->returncode == 'SUCCESS') {
				$event->bbb_attendee_password = (string) $xml->attendeePW;
				$event->bbb_moderator_password = (string) $xml->moderatorPW;
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	} else {
		return false;
	}
}

// checks to see if a BBB conference is actually running
function event_calendar_is_conference_running($event) {
	$output = event_calendar_bbb_api('isMeetingRunning', ['meetingID' => $event->guid]);
	if (!$output) {
		return false;
	} else {
		$xml = new SimpleXMLElement($output);
		if ($xml->returncode == 'SUCCESS' && $xml->running == 'true') {
			return true;
		} else {
			return false;
		}
	}
}

// checks to see if a BBB conference exists
function event_calendar_conference_exists($event) {
	$output = event_calendar_bbb_api('getMeetingInfo', ['meetingID' => $event->guid, 'password' => $event->bbb_moderator_password]);
	if (!$output) {
		return false;
	} else {
		$xml = new SimpleXMLElement($output);
		if ($xml->returncode == 'SUCCESS' && $xml->meetingID == $event->guid) {
			return true;
		} else {
			return false;
		}
	}
}

// forwards to the join link
// this function assumes that the conference is running
function event_calendar_join_conference($event) {
	forward(event_calendar_get_join_bbb_url($event));
}

function event_calendar_get_join_bbb_url($event) {
	$bbb_security_salt = elgg_get_plugin_setting('bbb_security_salt', 'event_calendar');
	$bbb_server_url = rtrim(elgg_get_plugin_setting('bbb_server_url', 'event_calendar'), '/') . '/';
	$user = elgg_get_logged_in_user_entity();
	$full_name = urlencode($user->name);
	if ($event->canEdit()) {
		$password = urlencode($event->bbb_moderator_password);
	} else {
		$password = urlencode($event->bbb_attendee_password);
	}
	$params = "fullName=$full_name&meetingID={$event->guid}&userID={$user->username}&password=$password";
	$checksum = sha1('join'.$params.$bbb_security_salt);
	$params .= "&checksum=$checksum";
	$url = $bbb_server_url.'api/join?'.$params;
	return $url;
}

// returns true if the given user can add an event to the given calendar
// if group_guid is 0, this is assumed to be the site calendar
function event_calendar_can_add($group_guid=0, $user_guid=0) {
	if (!$user_guid) {
		if (elgg_is_logged_in()) {
			$user_guid = elgg_get_logged_in_user_guid();
		} else {
			return false;
		}
	}
	if ($group_guid) {
		$group = get_entity($group_guid);
		if (!event_calendar_activated_for_group($group)) {
			return false;
		}
		if (elgg_instanceof($group, 'group')) {
			$group_calendar = elgg_get_plugin_setting('group_calendar', 'event_calendar');
			if (!$group_calendar || $group_calendar == 'members') {
				return $group->canWriteToContainer($user_guid);
			} else if ($group_calendar == 'admin') {
				if ($group->canEdit($user_guid)) {
					return true;
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	} else {
		$site_calendar = elgg_get_plugin_setting('site_calendar', 'event_calendar');
		if (!$site_calendar || $site_calendar == 'admin') {
			// only admins can post directly to the site-wide calendar
			return elgg_is_admin_user($user_guid);
		} else if ($site_calendar == 'loggedin') {
			// any logged-in user can post to the site calendar
			return true;
		}
	}

	return false;
}

/**
 * Return a color associated with given event type
 *
 * @param  string         $type_name Type of the event (e.g. "meeting")
 * @return string|boolean $color     Color value (e.g. "#FF0000" or "red") or false
 */
function event_calendar_map_type_to_color($type_name) {
	$type_list = trim(elgg_get_plugin_setting('type_list', 'event_calendar'));

	// Make sure that we are using Unix line endings
	$type_list = str_replace("\r\n", "\n", $type_list);
	$type_list = str_replace("\r", "\n", $type_list);
	$types = explode("\n", $type_list);

	foreach ($types as $type) {
		$type = explode("|", $type);

		if (isset($type[1])) {
			$name = $type[0];
			$color = $type[1];

			if ($name == $type_name) {
				return $color;
			}
		}
	}

	return false;
}

/**
 * Are there upgrade scripts to be run?
 *
 * @return bool
 */
function event_calendar_is_upgrade_available() {
	// sets $version based on code
	require_once elgg_get_plugins_path() . "event_calendar/version.php";

	$local_version = elgg_get_plugin_setting('version', 'event_calendar');
	if ($local_version === null) {
		// set initial version for new install
		elgg_set_plugin_setting('version', $version, 'event_calendar');
		$local_version = $version;
	}

	if ($local_version == $version) {
		return false;
	} else {
		return true;
	}
}


/**
 * Return locale first day of week depending on user/site language
 * 0: Sunday, 1: Monday, etc.
 *
 * Values are taken from jquery.ui/datepicker
 *
 * @return int  value of first day of week
 */
function event_calendar_get_first_day_of_week($language) {
	// the following languages have Sunday(0) as first day of the week
	$zero = [
		'ar',
		'en',
		'eo',
// 		'fr-CA', // currently not a valid language code of Elgg
		'he',
		'id',
		'is',
		'ja',
		'ko',
		'ms',
		'pt_br', // jquery datepicker uses pt-BR
		'pt',
		'th',
		'vi',
// 		'zh-HK', // currently not a valid language code of Elgg
	];

	// the following languages have Monday(1) as first day of the week
// 	$one = [
// 		'af',
// 		'az',
// 		'be',
// 		'bg',
// // 		'bs', // currently not a valid language code of Elgg
// 		'ca',
// 		'cs',
// // 		'cy-GB', // currently not a valid language code of Elgg
// 		'da',
// 		'de',
// 		'el',
// // 		'en-AU', // currently not a valid language code of Elgg
// // 		'en-GB', // currently not a valid language code of Elgg
// // 		'en-NZ', // currently not a valid language code of Elgg
// 		'es',
// 		'et',
// 		'eu',
// 		'fi',
// 		'fo',
// // 		'fr-CH', // currently not a valid language code of Elgg
// 		'fr',
// 		'gl',
// 		'hi',
// 		'hr',
// 		'hu',
// 		'hy',
// // 		'it-CH', // currently not a valid language code of Elgg
// 		'it',
// 		'ka',
// 		'kk',
// 		'km',
// 		'ky',
// // 		'lb', // currently not a valid language code of Elgg
// 		'lt',
// 		'lv',
// 		'mk',
// 		'ml',
// // 		'nb', // currently not a valid language code of Elgg
// // 		'nl-BE', // currently not a valid language code of Elgg
// 		'nl',
// 		'nn',
// 		'no',
// 		'pl',
// 		'rm',
// 		'ro',
// 		'ru',
// 		'sk',
// 		'sl',
// 		'sq',
// 		'sr',
// // 		'sr-SR', // currently not a valid language code of Elgg
// 		'sv',
// 		'ta',
// // 		'tj', // currently not a valid language code of Elgg
// 		'tr',
// 		'uk',
// // 		'zh-CN', // currently not a valid language code of Elgg
// // 		'zh-TW', // currently not a valid language code of Elgg
// 	];

	// the following languages have Friday(6) as first day of the week
	// listed here just for completeness; not supported at the moment
	// no language files of the following languages in Elgg at the moment anyway
// 	$six = [
// // 	'ar-DZ', // currently not a valid language code of Elgg
// 		'fa',
// 	];

	// for completeness the language code supported by Elgg without support by jquery.ui datepicker
// 	$currently_unknown = [
// 		"aa" => "Afar",
// 		"ab" => "Abkhazian",
// 		"am" => "Amharic",
// 		"as" => "Assamese",
// 		"ay" => "Aymara",
// 		"ba" => "Bashkir",
// 		"bh" => "Bihari",
// 		"bi" => "Bislama",
// 		"bn" => "Bengali; Bangla",
// 		"bo" => "Tibetan",
// 		"br" => "Breton",
// 		"cmn" => "Mandarin Chinese", // ISO 639-3
// 		"co" => "Corsican",
// 		"cy" => "Welsh",
// 		"dz" => "Bhutani",
// 		"eu_es" => "Basque (Spain)",
// 		"fj" => "Fiji",
// 		"fy" => "Frisian",
// 		"ga" => "Irish",
// 		"gd" => "Scots / Gaelic",
// 		"gn" => "Guarani",
// 		"gu" => "Gujarati",
// 		"ha" => "Hausa",
// 		"ia" => "Interlingua",
// 		"ie" => "Interlingue",
// 		"ik" => "Inupiak",
// 		"iu" => "Inuktitut",
// 		"iw" => "Hebrew (obsolete)",
// 		"ji" => "Yiddish (obsolete)",
// 		"jw" => "Javanese",
// 		"kl" => "Greenlandic",
// 		"kn" => "Kannada",
// 		"ks" => "Kashmiri",
// 		"ku" => "Kurdish",
// 		"la" => "Latin",
// 		"ln" => "Lingala",
// 		"lo" => "Laothian",
// 		"mg" => "Malagasy",
// 		"mi" => "Maori",
// 		"mn" => "Mongolian",
// 		"mo" => "Moldavian",
// 		"mr" => "Marathi",
// 		"mt" => "Maltese",
// 		"my" => "Burmese",
// 		"na" => "Nauru",
// 		"ne" => "Nepali",
// 		"oc" => "Occitan",
// 		"om" => "(Afan) Oromo",
// 		"or" => "Oriya",
// 		"pa" => "Punjabi",
// 		"ps" => "Pashto / Pushto",
// 		"qu" => "Quechua",
// 		"rn" => "Kirundi",
// 		"ro_ro" => "Romanian (Romania)",
// 		"rw" => "Kinyarwanda",
// 		"sa" => "Sanskrit",
// 		"sd" => "Sindhi",
// 		"sg" => "Sangro",
// 		"sh" => "Serbo-Croatian",
// 		"si" => "Singhalese",
// 		"sm" => "Samoan",
// 		"sn" => "Shona",
// 		"so" => "Somali",
// 		"sr_latin" => "Serbian (Latin)",
// 		"ss" => "Siswati",
// 		"st" => "Sesotho",
// 		"su" => "Sundanese",
// 		"sw" => "Swahili",
// 		"te" => "Tegulu",
// 		"tg" => "Tajik",
// 		"ti" => "Tigrinya",
// 		"tk" => "Turkmen",
// 		"tl" => "Tagalog",
// 		"tn" => "Setswana",
// 		"to" => "Tonga",
// 		"ts" => "Tsonga",
// 		"tt" => "Tatar",
// 		"tw" => "Twi",
// 		"ug" => "Uigur",
// 		"ur" => "Urdu",
// 		"uz" => "Uzbek",
// 		"vo" => "Volapuk",
// 		"wo" => "Wolof",
// 		"xh" => "Xhosa",
// 		"yi" => "Yiddish",
// 		"yo" => "Yoruba",
// 		"za" => "Zuang",
// 		"zh" => "Chinese",
// 		"zh_hans" => "Chinese Simplified",
// 		"zu" => "Zulu",
// 	];
	
	if (in_array($language, $zero)) {
		return 0;
	}
	
	return 1;
}