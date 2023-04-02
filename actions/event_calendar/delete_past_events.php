<?php
/**
 * Deletion of past event calendar entities
 *
 * Called through ajax, but registered as an Elgg action.
 *
 */

set_time_limit(0);

$delete_upper_limit = get_input('delete_upper_limit');

if (!$delete_upper_limit) {
	$response = ['success' => false, 'message' => elgg_echo('event_calendar:administer:error_no_interval')];
	echo json_encode($response);
	exit;
}

$delete_repeating_events = get_input('delete_repeating_events');
$delete_repeating_events = ($delete_repeating_events === 'true');

$response = [];
elgg_call(ELGG_IGNORE_ACCESS | ELGG_SHOW_DISABLED_ENTITIES, function () use($delete_upper_limit, $delete_repeating_events, $response) {

	$now = time();
	$upper_limit = false;
	switch ($delete_upper_limit) {
		case 'one_year':
			$upper_limit = $now - 60*60*24*367;
			break;
		case 'half_year':
			$upper_limit = $now - 60*60*24*181;
			break;
		case 'three_months':
			$upper_limit = $now - 60*60*24*91;
			break;
		case 'four_weeks':
			$upper_limit = $now - 60*60*24*29;
			break;
		case 'two_weeks':
			$upper_limit = $now - 60*60*24*15;
			break;
	}

	if ($upper_limit && ($upper_limit > 0)) {

		// Fetching events with start_date < $upper_limit here because repeating events have no meaningful real_end_time metadata value
		// Non-scheduled event poll events are not retrieved here because they don't have any fixed start_date metadata value yet
		$past_events = elgg_get_entities([
			'type' => 'object',
			'subtype' => EventCalendar::SUBTYPE,
			'limit' => false,
			'metadata_name_value_pairs' => [
				['name' => 'start_date', 'value' => $upper_limit, 'operand' => '<']
			],
			'batch' => true,
			'batch_inc_offset' => false,
		]);

		$success_count = 0;
		$error_count = 0;
		foreach($past_events as $past_event) {
			// do we delete also repeating events?
			if ($past_event->repeats == 'yes') {
				if ($delete_repeating_events) {
					if ($past_event->delete()) {
						$success_count++;
					} else {
						$error_count++;
					}
				}
			} else if ($past_event->repeats != 'yes') {
				// Non-repeating events have a real_end_time metadata value, so let's check if real_end_time < $upper_limit, too, before deletion
				if ($past_event->real_end_time < $upper_limit) {
					if ($past_event->delete()) {
						$success_count++;
					} else {
						$error_count++;
					}
				}
			}
		}

		$response = ['success' => true, 'message' => elgg_echo('event_calendar:administer:delete_past_events_result', [$success_count, $error_count])];
	} else {
		$response = ['success' => false, 'message' => elgg_echo('event_calendar:administer:error_invalid_interval')];
	}
});

echo json_encode($response);
exit();
