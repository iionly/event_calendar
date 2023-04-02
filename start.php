<?php

/**
 *  Event calendar plugin
 *
 * @package event_calendar
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Kevin Jardine <kevin@radagast.biz>
 * @copyright Radagast Solutions 2008
 * @link http://radagast.biz/
 */

elgg_register_event_handler('init','system','event_calendar_init');
elgg_register_plugin_hook_handler("setting", "plugin", "event_calendar_invalidate_cache");

function event_calendar_init() {

	elgg_register_library('elgg:event_calendar', elgg_get_plugins_path() . 'event_calendar/models/model.php');
	elgg_register_library('event_calendar:ical', elgg_get_plugins_path() . 'event_calendar/vendors/iCalcreator/iCalcreator.php');

	// extend the account settings form
	elgg_extend_view('forms/account/settings', 'core/settings/account/event_calendar', 110);

	elgg_register_plugin_hook_handler('cron', 'fiveminute', 'event_calendar_handle_reminders_cron', 400);
	elgg_register_plugin_hook_handler('entity:url', 'object', 'event_calendar_url');
	elgg_register_plugin_hook_handler('prepare', 'notification:create:object:event_calendar', 'event_calendar_prepare_notification');
	elgg_register_plugin_hook_handler('usersettings:save', 'user', 'event_calendar_set_user_listing_format');

	// Register a page handler, so we can have nice URLs
	elgg_register_page_handler('event_calendar', 'event_calendar_page_handler');

	// Register granular notification
	elgg_register_notification_event('object', 'event_calendar', ['create']);

	// Set up site menu
	$site_calendar = elgg_get_plugin_setting('site_calendar', 'event_calendar');
	if (!$site_calendar || $site_calendar != 'no') {
		// add a site navigation item
		$item = new ElggMenuItem('event_calendar', elgg_echo('item:object:event_calendar'), 'event_calendar/list/');
		elgg_register_menu_item('site', $item);
	}

	// make event calendar title and description searchable
	elgg_register_entity_type('object', 'event_calendar');

	// make legacy tags searchable
	elgg_register_tag_metadata_name('event_tags');

	// ajax event summary popup
	elgg_register_ajax_view('event_calendar/popup');

	// add to group profile page
	$group_calendar = elgg_get_plugin_setting('group_calendar', 'event_calendar');
	if (!$group_calendar || $group_calendar != 'no') {
		elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'event_calendar_owner_block_menu');
		elgg_extend_view('groups/tool_latest', 'event_calendar/group_module');

		// add the event calendar group tool option
		$event_calendar_group_default = elgg_get_plugin_setting('group_default', 'event_calendar');
		if (!$event_calendar_group_default || ($event_calendar_group_default == 'yes')) {
			add_group_tool_option('event_calendar', elgg_echo('event_calendar:enable_event_calendar'), true);
		} else {
			add_group_tool_option('event_calendar', elgg_echo('event_calendar:enable_event_calendar'), false);
		}
	}

	// add to the css
	elgg_extend_view('css/elgg', 'event_calendar/css');
	elgg_extend_view('css/elgg', 'fullcalendar/css');

	// add a widget
	elgg_register_widget_type('event_calendar', elgg_echo("event_calendar:widget_title"), elgg_echo('event_calendar:widget:description'));

	// Index page and group profile page widgets and widget title urls if Widget Manager plugin is available
	if (elgg_is_active_plugin('widget_manager')) {
		// add index widget for Widget Manager plugin
		elgg_register_widget_type('index_event_calendar', elgg_echo("event_calendar:widget_title"), elgg_echo('event_calendar:widget:description'), ["index"]);
		if (!$group_calendar || $group_calendar != 'no') {
			elgg_register_widget_type('groups_event_calendar', elgg_echo("event_calendar:widget_title"), elgg_echo('event_calendar:widget:description'), ["groups"]);
		}
		// register title urls for widgets
		elgg_register_plugin_hook_handler("entity:url", "object", "event_calendar_widget_urls");
		// handle the availability of the Event Calendar group widget
		elgg_register_plugin_hook_handler("group_tool_widgets", "widget_manager", "event_calendar_tool_widgets_handler");
	}

	// if autogroup is set, listen and respond to join/leave events
	if (elgg_get_plugin_setting('autogroup', 'event_calendar') == 'yes') {
		elgg_register_event_handler('join', 'group', 'event_calendar_handle_join');
		elgg_register_event_handler('leave', 'group', 'event_calendar_handle_leave');
	}

	// entity menu
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'event_calendar_entity_menu_setup');
	elgg_register_plugin_hook_handler('prepare', 'menu:entity', 'event_calendar_entity_menu_prepare');

	// Add page in Administer - Utilities section (this page offers cleanup of past event entries)
	elgg_register_admin_menu_item('administer', 'event_calendar', 'administer_utilities');

	// allow for liking of events
	elgg_register_plugin_hook_handler('likes:is_likable', 'object:event_calendar', 'Elgg\Values::getTrue');

	// register actions
	$action_path = elgg_get_plugins_path() . 'event_calendar/actions/event_calendar';

	elgg_register_action("event_calendar/edit","$action_path/edit.php");
	elgg_register_action("event_calendar/delete","$action_path/delete.php");
	elgg_register_action("event_calendar/add_personal","$action_path/add_personal.php");
	elgg_register_action("event_calendar/remove_personal","$action_path/remove_personal.php");
	elgg_register_action("event_calendar/request_personal_calendar","$action_path/request_personal_calendar.php");
	elgg_register_action("event_calendar/toggle_personal_calendar","$action_path/toggle_personal_calendar.php");
	elgg_register_action("event_calendar/killrequest","$action_path/killrequest.php");
	elgg_register_action("event_calendar/addtocalendar","$action_path/addtocalendar.php");
	elgg_register_action("event_calendar/add_to_group","$action_path/add_to_group.php");
	elgg_register_action("event_calendar/remove_from_group","$action_path/remove_from_group.php");
	elgg_register_action("event_calendar/add_to_group_members","$action_path/add_to_group_members.php");
	elgg_register_action("event_calendar/remove_from_group_members","$action_path/remove_from_group_members.php");
	elgg_register_action("event_calendar/manage_subscribers","$action_path/manage_subscribers.php");
	elgg_register_action("event_calendar/modify_full_calendar","$action_path/modify_full_calendar.php");
	elgg_register_action("event_calendar/join_conference","$action_path/join_conference.php");
	elgg_register_action("event_calendar/upgrade", "$action_path/upgrade.php", 'admin');
	elgg_register_action("event_calendar/delete_past_events", "$action_path/delete_past_events.php", 'admin');
	elgg_register_action("event_calendar/import", "$action_path/import.php");
	elgg_register_action('event_calendar/export', "$action_path/export.php");

	// check for pending event_calendar upgrades when a site upgrade is made
	elgg_register_event_handler('upgrade', 'system', 'event_calendar_check_pending_upgrades');

	if (elgg_get_plugin_setting('ical_import_export', 'event_calendar') == "yes") {
		elgg_register_plugin_hook_handler('register', 'menu:title', 'ec_ical_titlemenu');
	}
}

/**
 * Add a menu item to an ownerblock
 */
function event_calendar_owner_block_menu($hook, $type, $return, $params) {
	elgg_load_library('elgg:event_calendar');
	if (elgg_instanceof($params['entity'], 'group')) {
		if (event_calendar_activated_for_group($params['entity'])) {
			$url = "event_calendar/group/{$params['entity']->guid}";
			$item = new ElggMenuItem('event_calendar', elgg_echo('event_calendar:group'), $url);
			$return[] = $item;
		}
	} else if (elgg_instanceof($params['entity'], 'user')) {
		$url = "event_calendar/owner/{$params['entity']->username}";
		$item = new ElggMenuItem('event_calendar', elgg_echo('event_calendar:widget_title'), $url);
		$return[] = $item;
	}

	return $return;
}

/**
 * Set url for event_calendar objects
 *
 * @param string $hook   entity:url
 * @param string $type   object
 * @param string $url    Current URL
 * @param array  $params Hook parameters
 * @return string The URL
 */
function event_calendar_url($hook, $type, $url, $params) {
	$entity = $params['entity'];

	if (!elgg_instanceof($entity, 'object', 'event_calendar')) {
		return $url;
	}

	$friendly_title = elgg_get_friendly_title($entity->title);
	return "event_calendar/view/{$entity->guid}/$friendly_title";
}

/**
 * Dispatches event calendar pages.
 *
 * URLs take the form of
 *  Site event calendar:			event_calendar/list/<start_date>/<display_mode>/<filter_context>/<region>
 *  Single event:       			event_calendar/view/<event_guid>/<title>
 *  New event:        				event_calendar/add
 *  Edit event:       				event_calendar/edit/<event_guid>
 *  Group event calendar:  			event_calendar/group/<group_guid>/<start_date>/<display_mode>/<filter_context>/<region>
 *  Add group event:   				event_calendar/add/<group_guid>
 *  Review requests:				event_calendar/review_requests/<event_guid>
 *  Display event subscribers:		event_calendar/display_users/<event_guid>
 *  Events for a user's calendar:	event_calendar/owner/<username>/<start_date>/<display_mode>/<filter_context>/<region>
 *
 * Title is ignored
 *
 * @param array $page
 * @return null
 */
function event_calendar_page_handler($page) {

	if (elgg_get_plugin_setting('ical_import_export', 'event_calendar') == "yes") {
		set_input('ical_calendar_title_menu', true);
	}
	elgg_load_library('elgg:event_calendar');
	$page_type = $page[0];
	$resource_vars = [];
	switch ($page_type) {
		case 'list':
			if (isset($page[1])) {
				$resource_vars['start_date'] = $page[1];
				set_input('ical_date', $page[1]);
				if (isset($page[2])) {
					$resource_vars['display_mode'] = $page[2];
					set_input('ical_interval', $page[2]);
					if (isset($page[3])) {
						$resource_vars['filter_mode'] = $page[3];
						switch($page[3]) {
							case 'mine':
								set_input('ical_calendar_filter', 'mine');
								break;
							case 'friends':
								set_input('ical_calendar_filter', 'friends');
								break;
							default:
								set_input('ical_calendar_filter', 'all');
								break;
						}
						if (isset($page[4])) {
							$resource_vars['region'] = $page[4];
							set_input('ical_region', $page[4]);
						} else {
							$resource_vars['region'] = '-';
							set_input('ical_region', '');
						}
					} else {
						$resource_vars['filter_mode'] = '';
						set_input('ical_calendar_filter', 'all');
					}
				} else {
					$resource_vars['display_mode'] = '';
					set_input('ical_interval', '');
				}
			} else {
				$resource_vars['start_date'] = 0;
				set_input('ical_date', 0);
			}
			$format = get_input('format', false);
			if ($format && in_array($format, ['paged', 'agenda', 'month', 'full'])) {
				$resource_vars['format'] = $format;
			}
			echo elgg_view_resource('event_calendar/list', $resource_vars);
			break;
		case 'view':
			elgg_entity_gatekeeper($page[1], 'object', 'event_calendar');
			elgg_group_gatekeeper();
			$resource_vars['event_guid'] = $page[1];
			echo elgg_view_resource('event_calendar/view', $resource_vars);
			break;
		case 'display_users':
			elgg_entity_gatekeeper($page[1], 'object', 'event_calendar');
			elgg_group_gatekeeper();
			$resource_vars['event_guid'] = $page[1];
			echo elgg_view_resource('event_calendar/display_users', $resource_vars);
			break;
		case 'manage_users':
			elgg_entity_gatekeeper($page[1], 'object', 'event_calendar');
			elgg_group_gatekeeper();
			$resource_vars['event_guid'] = $page[1];
			echo elgg_view_resource('event_calendar/manage_users', $resource_vars);
			break;
		case 'schedule':
		case 'add':
			$resource_vars['page_type'] = $page_type;
			if (isset($page[1])) {
				elgg_group_gatekeeper();
				$resource_vars['guid'] =  $page[1];
				$resource_vars['start_date'] = elgg_extract(2, $page, '');
			} else {
				elgg_gatekeeper();
				$resource_vars['guid'] = 0;
			}
			echo elgg_view_resource('event_calendar/edit', $resource_vars);
			break;
		case 'edit':
			elgg_gatekeeper();
			$resource_vars['page_type'] = $page_type;
			$resource_vars['guid'] = $page[1];
			echo elgg_view_resource('event_calendar/edit', $resource_vars);
			break;
		case 'group':
			elgg_group_gatekeeper();
			if (isset($page[1])) {
				$resource_vars['container_guid'] = $page[1];
				set_input('ical_group_guid', $page[1]);
				if (isset($page[2])) {
					$resource_vars['start_date'] = $page[2];
					set_input('ical_date', $page[2]);
					if (isset($page[3])) {
						$resource_vars['display_mode'] = $page[3];
						set_input('ical_interval', $page[3]);
						if (isset($page[4])) {
							$resource_vars['filter_mode'] = $page[4];
							switch($page[4]) {
								case 'mine':
									set_input('ical_calendar_filter', 'mine');
									break;
								case 'friends':
									set_input('ical_calendar_filter', 'friends');
									break;
								default:
									set_input('ical_calendar_filter', 'all');
									break;
							}
							if (isset($page[5])) {
								$resource_vars['region'] = $page[5];
								set_input('ical_region', $page[5]);
							} else {
								$resource_vars['region'] = '-';
								set_input('ical_region', '');
							}
						} else {
							$resource_vars['filter_mode'] = '';
							set_input('ical_calendar_filter', 'all');
						}
					} else {
						$resource_vars['display_mode'] = '';
						set_input('ical_interval', '');
					}
				} else {
					$resource_vars['start_date'] = '';
					set_input('ical_date', 0);
				}
			} else {
				$resource_vars['container_guid'] = elgg_get_page_owner_guid();
				set_input('ical_group_guid', $page[1], $resource_vars['container_guid']);
			}
			$format = get_input('format', false);
			if ($format && in_array($format, ['paged', 'agenda', 'month', 'full'])) {
				$resource_vars['format'] = $format;
			}
			echo elgg_view_resource('event_calendar/group', $resource_vars);
			break;
		case 'owner':
			if (isset($page[1])) {
				$username = $page[1];
				$user = get_user_by_username($username);
				$resource_vars['container_guid'] = $user->guid;
				if (isset($page[2])) {
					$resource_vars['start_date'] = $page[2];
					if (isset($page[3])) {
					$resource_vars['display_mode'] = $page[3];
					if (isset($page[4])) {
						$resource_vars['filter_mode'] = $page[4];
						if (isset($page[5])) {
							$resource_vars['region'] = $page[5];
						} else {
							$resource_vars['region'] = '-';
						}
					} else {
						$resource_vars['filter_mode'] = '';
					}
					} else {
						$resource_vars['display_mode'] = '';
					}
				} else {
					$resource_vars['start_date'] = '';
				}
			} else {
				elgg_gatekeeper();
				$resource_vars['container_guid'] = elgg_get_logged_in_user_guid();
			}
			$format = get_input('format', false);
			if ($format && in_array($format, ['paged', 'agenda', 'month', 'full'])) {
				$resource_vars['format'] = $format;
			}
			echo elgg_view_resource('event_calendar/owner', $resource_vars);
			break;
		case 'review_requests':
			elgg_gatekeeper();
			$resource_vars['event_guid'] = $page[1];
			echo elgg_view_resource('event_calendar/review_requests', $resource_vars);
			break;
		case 'get_fullcalendar_events':
			$resource_vars['start_date'] = $page[1];
			$resource_vars['end_date'] = $page[2];
			$resource_vars['filter'] = $page[3];
			$resource_vars['container_guid'] = (int)$page[4];
			$resource_vars['region'] = $page[5];
			echo elgg_view_resource('event_calendar/fullcalendar_events', $resource_vars);
			break;
		case 'ical':
			$resource_vars['action_type'] = $page[1];
			echo elgg_view_resource('event_calendar/export', $resource_vars);
			break;
		default:
			return false;
	}
	return true;
}

// If iCal import/export is enabled add corresponding action buttons in title area
function ec_ical_titlemenu($hook, $type, $return, $params) {
	if(!elgg_in_context("event_calendar")) {
		return $return;
	}

	$use_titlemenu = get_input('ical_calendar_title_menu', false);
	if ($use_titlemenu && elgg_is_logged_in()) {
		$filter = get_input('ical_calendar_filter', false);
		$date = get_input('ical_date', false);
		$interval = get_input('ical_interval', false);
		$group_guid = get_input('ical_group_guid', false);

		$export_url = elgg_get_site_url() . 'event_calendar/ical/export?method=ical';
		$import_url = elgg_get_site_url() . 'event_calendar/ical/import?method=ical';
		$urlsuffix = '';

		if ($filter) {
			$urlsuffix .= "&filter={$filter}";
		}

		if ($date) {
			$urlsuffix .= "&date={$date}";
		}

		if ($interval) {
			$urlsuffix .= "&interval={$interval}";
		}

		if ($group_guid !== false) {
			$urlsuffix .= "&group_guid={$group_guid}";
		}

		$export = new ElggMenuItem('ical_export', elgg_echo('event_calendar:export'), $export_url . $urlsuffix);
		$export->setLinkClass('elgg-button elgg-button-action');
		$return[] = $export;

		$import = new ElggMenuItem('ical_import', elgg_echo('event_calendar:import'), $import_url . $urlsuffix);
		$import->setLinkClass('elgg-button elgg-button-action');
		$return[] = $import;

		return $return;
	}
}

/**
 * Add particular event calendar links/info to entity menu
 */
function event_calendar_entity_menu_setup($hook, $type, $return, $params) {
	if (elgg_in_context('widgets')) {
		return $return;
	}

	$entity = $params['entity'];
	$handler = elgg_extract('handler', $params, false);
	if ($handler != 'event_calendar') {
		return $return;
	}
	if (elgg_is_active_plugin('event_poll') && $entity->canEdit() && $entity->schedule_type == 'poll') {
		$options = [
			'name' => 'schedule',
			'text' => elgg_echo('event_poll:schedule_button'),
			'title' => elgg_echo('event_poll:schedule_button'),
			'href' => 'event_poll/vote/'.$entity->guid,
			'priority' => 150,
		];
		$return[] = ElggMenuItem::factory($options);
	}
	$user_guid = elgg_get_logged_in_user_guid();
	if ($user_guid) {
		$calendar_status = event_calendar_personal_can_manage($entity, $user_guid);
		if ($calendar_status == 'open') {
			if (event_calendar_has_personal_event($entity->guid, $user_guid)) {
				$options = [
					'name' => 'personal_calendar',
					'text' => elgg_echo('event_calendar:remove_from_the_calendar_menu_text'),
					'title' => elgg_echo('event_calendar:remove_from_my_calendar'),
					'href' => elgg_add_action_tokens_to_url("action/event_calendar/remove_personal?guid={$entity->guid}"),
					'priority' => 150,
				];
				$return[] = ElggMenuItem::factory($options);
			} else {
				if (!event_calendar_is_full($entity->guid) && !event_calendar_has_collision($entity->guid, $user_guid)) {
					$options = [
						'name' => 'personal_calendar',
						'text' => elgg_echo('event_calendar:add_to_the_calendar_menu_text'),
						'title' => elgg_echo('event_calendar:add_to_my_calendar'),
						'href' => elgg_add_action_tokens_to_url("action/event_calendar/add_personal?guid={$entity->guid}"),
						'priority' => 150,
					];
					$return[] = ElggMenuItem::factory($options);
				}
			}
		} else if ($calendar_status == 'closed') {
			if (!event_calendar_has_personal_event($entity->guid, $user_guid) && !check_entity_relationship($user_guid, 'event_calendar_request', $entity->guid)) {
				$options = [
					'name' => 'personal_calendar',
					'text' => elgg_echo('event_calendar:make_request_title'),
					'title' => elgg_echo('event_calendar:make_request_title'),
					'href' => elgg_add_action_tokens_to_url("action/event_calendar/request_personal_calendar?guid={$entity->guid}"),
					'priority' => 150,
				];
				$return[] = ElggMenuItem::factory($options);
			}
		}

		$count = event_calendar_get_users_for_event($entity->guid, 0, 0, true);
		if ($count == 1) {
			$calendar_text = elgg_echo('event_calendar:personal_event_calendars_link_one');
		} else {
			$calendar_text = elgg_echo('event_calendar:personal_event_calendars_link', [$count]);
		}

		$options = [
			'name' => 'calendar_listing',
			'text' => $calendar_text,
			'title' => elgg_echo('event_calendar:users_for_event_menu_title'),
			'href' => "event_calendar/display_users/{$entity->guid}",
			'priority' => 150,
		];
		$return[] = ElggMenuItem::factory($options);

		if (elgg_get_plugin_setting('ical_import_export', 'event_calendar') == "yes") {
			$url = elgg_get_site_url() . 'action/event_calendar/export?filter=' . $entity->guid;
			$options = [
				'name' => 'ical_export',
				'text' => elgg_view('output/img', ['src' => elgg_get_simplecache_url('event_calendar/ics.png')]),
				'href' => elgg_add_action_tokens_to_url($url),
				'priority' => 1000,
			];
			$return[] = ElggMenuItem::factory($options);
		}
	}

	return $return;
}

function event_calendar_entity_menu_prepare($hook, $type, $return, $params) {
	// remove access level from listings
	if (elgg_in_context('event_calendar') && !elgg_in_context('event_calendar:view')) {
		$new_return = [];
		if (isset($return['default']) && is_array($return['default'])) {
			foreach($return['default'] as $item) {
				if ($item->getName() != 'access') {
					$new_return[] = $item;
				}
			}
		}
		$return['default'] = $new_return;
	}

	return $return;
}

function event_calendar_widget_urls($hook_name, $entity_type, $return_value, $params){
	$result = $return_value;
	$widget = $params["entity"];

	if(empty($result) && ($widget instanceof ElggWidget)) {
		$owner = $widget->getOwnerEntity();

		switch($widget->handler) {
			case "event_calendar":
				$result = "/event_calendar/owner/" . $owner->username;
				break;
			case "index_event_calendar":
				$result = "/event_calendar/list";
				break;
			case "groups_event_calendar":
				if($owner instanceof ElggGroup){
					$result = "/event_calendar/group/" . $owner->guid;
				} else {
					$result = "/event_calendar/list";
				}
				break;
		}
	}
	return $result;
}

function event_calendar_handle_join($event, $object_type, $object) {
	elgg_load_library('elgg:event_calendar');
	$group = $object['group'];
	$user = $object['user'];
	$user_guid = $user->getGUID();

	$options = [
		'type' => 'object',
		'subtype' => 'event_calendar',
		'container_guid' => $group->getGUID(),
		'limit' => false,
	];
	$events = new ElggBatch('elgg_get_entities', $options);

	foreach ($events as $event) {
		$event_id = $event->getGUID();
		event_calendar_add_personal_event($event_id, $user_guid);
	}
}

function event_calendar_handle_leave($event, $object_type, $object) {
	elgg_load_library('elgg:event_calendar');
	$group = $object['group'];
	$user = $object['user'];
	$user_guid = $user->getGUID();

	$options = [
		'type' => 'object',
		'subtype' => 'event_calendar',
		'container_guid' => $group->getGUID(),
		'limit' => false,
	];
	$events = new ElggBatch('elgg_get_entities', $options);

	foreach ($events as $event) {
		$event_id = $event->getGUID();
		event_calendar_remove_personal_event($event_id, $user_guid);
	}
}

function event_calendar_handle_reminders_cron() {
	elgg_load_library('elgg:event_calendar');
	event_calendar_queue_reminders();
}

function event_calendar_invalidate_cache($hook, $type, $return, $params){
  if(isset($params["plugin"]) && ($params["plugin"]->getID() == "event_calendar")){
    elgg_invalidate_simplecache();
  }
}

/**
 * Prepare a notification message about a new event
 *
 * @param string $hook         Hook name
 * @param string $type         Hook type
 * @param object $notification The notification to prepare
 * @param array  $params       Hook parameters
 * @return object Modified notification
 */
function event_calendar_prepare_notification($hook, $type, $notification, $params) {
	$entity = $params['event']->getObject();
	$owner = $params['event']->getActor();
	$language = $params['language'];

	// Title for the notification
	$notification->subject = elgg_echo('event_calendar:notify:subject', [$entity->title], $language);

	// Message body for the notification
	$notification->body = elgg_echo('event_calendar:notify:body', [$owner->name, $entity->title, $entity->description, $entity->getURL()], $language);

	// The summary text is used e.g. by the site_notifications plugin
	$notification->summary = elgg_echo('event_calendar:notify:summary', [$entity->title], $language);

	return $notification;
}

function event_calendar_check_pending_upgrades() {
	elgg_load_library('elgg:event_calendar');
	elgg_delete_admin_notice('event_calendar_admin_notice_pending_upgrades');
	if (event_calendar_is_upgrade_available()) {
		$message = elgg_echo('event_calendar:admin_notice_pending_upgrades', [elgg_normalize_url('admin/plugin_settings/event_calendar')]);
		elgg_add_admin_notice('event_calendar_admin_notice_pending_upgrades', $message);
	}
}

// Add or remove a group's event calendar widget based on the corresponding group tools option
function event_calendar_tool_widgets_handler($hook, $type, $return_value, $params) {
	if (!empty($params) && is_array($params)) {
		$entity = elgg_extract("entity", $params);

		if (!empty($entity) && elgg_instanceof($entity, "group")) {
			if (!is_array($return_value)) {
				$return_value = [];
			}

			if (!isset($return_value["enable"])) {
				$return_value["enable"] = [];
			}
			if (!isset($return_value["disable"])) {
				$return_value["disable"] = [];
			}

			if ($entity->event_calendar_enable == "yes") {
				$return_value["enable"][] = "groups_event_calendar";
			} else {
				$return_value["disable"][] = "groups_event_calendar";
			}
		}
	}

	return $return_value;
}


// Save user setting of listing format event calendar should use
function event_calendar_set_user_listing_format($hook, $type, $return_value, $params) {
	$event_calendar_format = get_input('event_calendar_format');
	$user_guid = get_input('guid');

	if (!isset($event_calendar_format)) {
		return;
	}
	
	if ($user_guid) {
		$user = get_user($user_guid);
	} else {
		$user = elgg_get_logged_in_user_entity();
	}

	$listing_options = ['paged', 'agenda', 'month', 'full'];

	if ($user && $event_calendar_format) {
		if (in_array($event_calendar_format, $listing_options)) {
			if ($user->event_calendar_format === $event_calendar_format) {
				return true;
			}
			$user->event_calendar_format = $event_calendar_format;
			if ($user->save()) {
				return true;
			} else {
				register_error(elgg_echo('event_calendar:event_calendar_format:fail'));
			}
		} else {
			// no change
			return;
		}
	} else {
		register_error(elgg_echo('event_calendar:event_calendar_format:fail'));
	}
	return false;
}
