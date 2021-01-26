<?php

/**
 * Elgg show events RSS view
 *
 * @package event_calendar
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Kevin Jardine <kevin@radagast.biz>
 * @copyright Radagast Solutions 2009
 * @link http://radagast.biz/
 *
 */

require_once(elgg_get_plugins_path() . 'event_calendar/models/model.php');

if ($vars['events']) {
	$options = [
		'count' => $vars['count'],
		'offset' => 0,
		'limit' => 15,
		'list_type_toggle' => false,
		'pagination' => false,
	];
	echo elgg_view_entity_list(event_calendar_flatten_event_structure($vars['events']), $options);
}
