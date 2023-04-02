<?php
/**
 * Event Calendar upgrade action
 */

$plugins_path = elgg_get_plugins_path();

require_once "{$plugins_path}event_calendar/version.php";

$local_version = elgg_get_plugin_setting('version', 'event_calendar');

if ($version <= $local_version) {
	return elgg_error_response(elgg_echo('event_calendar:upgrade:no_updates'), REFERER);
}

set_time_limit(0);

$base_dir = "{$plugins_path}event_calendar/upgrades";

// taken from engine/lib/version.php
if ($handle = opendir($base_dir)) {
	$upgrades = [];

	while ($updatefile = readdir($handle)) {
		// Look for upgrades and add to upgrades list
		if (!is_dir("$base_dir/$updatefile")) {
			if (preg_match('/^([0-9]{10})\.(php)$/', $updatefile, $matches)) {
				$plugin_version = (int) $matches[1];
				if ($plugin_version > $local_version) {
					$upgrades[] = "$base_dir/$updatefile";
				}
			}
		}
	}

	// Sort and execute
	asort($upgrades);

	if (sizeof($upgrades) > 0) {
		foreach ($upgrades as $upgrade) {
			include($upgrade);
		}
	}
}

elgg_set_plugin_setting('version', $version, 'event_calendar');

return elgg_ok_response('', elgg_echo('event_calendar:upgrade:success'), REFERER);
