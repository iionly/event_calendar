<?php

function event_calendar_invalidate_cache(\Elgg\Hook $hook) {
	$params = $hook->getParams();

	if (isset($params['plugin']) && ($params['plugin']->getID() == 'event_calendar')){
		elgg_invalidate_simplecache();
	}
}

/**
 * Add a menu item to an ownerblock
 */
function event_calendar_owner_block_menu(\Elgg\Hook $hook) {
	$menu = $hook->getValue();
	$entity = $hook->getParam('entity');

	if ($entity instanceof ElggGroup) {
		if (event_calendar_activated_for_group($entity)) {
			$url = "event_calendar/group/{$entity->guid}";
			$item = new ElggMenuItem('event_calendar', elgg_echo('event_calendar:group'), $url);
			$menu[] = $item;
		}
	} else if ($entity instanceof ElggUser) {
		$url = "event_calendar/owner/{$entity->username}";
		$item = new ElggMenuItem('event_calendar', elgg_echo('event_calendar:widget_title'), $url);
		$menu[] = $item;
	}

	return $menu;
}

/**
 * Set url for event_calendar objects
 *
 * @param string $hook   entity:url
 * @return string The URL
 */
function event_calendar_url(\Elgg\Hook $hook) {
	$entity = $hook->getParam('entity');

	if ($entity->getSubtype() !==  'event_calendar') {
		return $url;
	}

	$friendly_title = elgg_get_friendly_title($entity->title);
	return "event_calendar/view/{$entity->guid}/$friendly_title";
}

/*
 *If iCal import/export is enabled add corresponding action buttons in title area
 */
function ec_ical_titlemenu(\Elgg\Hook $hook) {
	$menu = $hook->getValue();

	if(!elgg_in_context('event_calendar')) {
		return $menu;
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
		$menu[] = $export;

		$import = new ElggMenuItem('ical_import', elgg_echo('event_calendar:import'), $import_url . $urlsuffix);
		$import->setLinkClass('elgg-button elgg-button-action');
		$menu[] = $import;

		return $menu;
	}
}

/**
 * Add particular event calendar links/info to entity menu
 */
function event_calendar_entity_menu_setup(\Elgg\Hook $hook) {
	$menu = $hook->getValue();

	if (elgg_in_context('widgets')) {
		return $menu;
	}

	$params = $hook->getParams();
	$entity = $params['entity'];
	$handler = elgg_extract('handler', $params, false);
	if ($handler != 'event_calendar') {
		return $menu;
	}
	if (elgg_is_active_plugin('event_poll') && $entity->canEdit() && $entity->schedule_type == 'poll') {
		$options = [
			'name' => 'schedule',
			'text' => elgg_echo('event_poll:schedule_button'),
			'title' => elgg_echo('event_poll:schedule_button'),
			'href' => 'event_poll/vote/'.$entity->guid,
			'priority' => 150,
		];
		$menu[] = ElggMenuItem::factory($options);
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
				$menu[] = ElggMenuItem::factory($options);
			} else {
				if (!event_calendar_is_full($entity->guid) && !event_calendar_has_collision($entity->guid, $user_guid)) {
					$options = [
						'name' => 'personal_calendar',
						'text' => elgg_echo('event_calendar:add_to_the_calendar_menu_text'),
						'title' => elgg_echo('event_calendar:add_to_my_calendar'),
						'href' => elgg_add_action_tokens_to_url("action/event_calendar/add_personal?guid={$entity->guid}"),
						'priority' => 150,
					];
					$menu[] = ElggMenuItem::factory($options);
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
				$menu[] = ElggMenuItem::factory($options);
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
		$menu[] = ElggMenuItem::factory($options);

		if (elgg_get_plugin_setting('ical_import_export', 'event_calendar') == "yes") {
			$url = elgg_get_site_url() . 'action/event_calendar/export?filter=' . $entity->guid;
			$options = [
				'name' => 'ical_export',
				'text' => elgg_view('output/img', ['src' => elgg_get_simplecache_url('event_calendar/ics.png')]),
				'href' => elgg_add_action_tokens_to_url($url),
				'priority' => 1000,
			];
			$menu[] = ElggMenuItem::factory($options);
		}
	}

	return $menu;
}

function event_calendar_entity_menu_prepare(\Elgg\Hook $hook) {
	$menu = $hook->getValue();

	// remove access level from listings
	if (elgg_in_context('event_calendar') && !elgg_in_context('event_calendar:view')) {
		$new_return = [];
		if (isset($menu['default']) && is_array($menu['default'])) {
			foreach($menu['default'] as $item) {
				if ($item->getName() != 'access') {
					$new_return[] = $item;
				}
			}
			$menu['default'] = $new_return;
		}
	}

	return $menu;
}

function event_calendar_widget_urls(\Elgg\Hook $hook) {
	$return_value = $hook->getValue();

	$result = $return_value;
	$widget = $hook->getParam('entity');

	if(empty($result) && ($widget instanceof ElggWidget)) {
		$owner = $widget->getOwnerEntity();

		switch($widget->handler) {
			case 'event_calendar':
				$result = '/event_calendar/owner/' . $owner->username;
				break;
			case 'index_event_calendar':
				$result = '/event_calendar/list';
				break;
			case 'groups_event_calendar':
				if($owner instanceof ElggGroup){
					$result = '/event_calendar/group/' . $owner->guid;
				} else {
					$result = '/event_calendar/list';
				}
				break;
		}
	}
	return $result;
}

function event_calendar_handle_join(\Elgg\Event $event) {
	$params = $event->getObject();
	$group = elgg_extract('group', $params);
	$user = elgg_extract('user', $params);
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

function event_calendar_handle_leave(\Elgg\Event $event) {
	$params = $event->getObject();
	$group = elgg_extract('group', $params);
	$user = elgg_extract('user', $params);
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
	event_calendar_queue_reminders();
}

/**
 * Prepare a notification message about a new event
 *
 * @param string $hook         Hook name
 * @return object Modified notification
 */
function event_calendar_prepare_notification(\Elgg\Hook $hook) {
	$params = $hook->getParams();
	$entity = $params['event']->getObject();
	$owner = $params['event']->getActor();
	$language = $params['language'];

	$notification = $hook->getValue();
	// Title for the notification
	$notification->subject = elgg_echo('event_calendar:notify:subject', [$entity->title], $language);

	// Message body for the notification
	$notification->body = elgg_echo('event_calendar:notify:body', [$owner->name, $entity->title, $entity->description, $entity->getURL()], $language);

	// The summary text is used e.g. by the site_notifications plugin
	$notification->summary = elgg_echo('event_calendar:notify:summary', [$entity->title], $language);

	return $notification;
}

function event_calendar_check_pending_upgrades() {
	elgg_delete_admin_notice('event_calendar_admin_notice_pending_upgrades');
	if (event_calendar_is_upgrade_available()) {
		$message = elgg_echo('event_calendar:admin_notice_pending_upgrades', [elgg_normalize_url('admin/plugin_settings/event_calendar')]);
		elgg_add_admin_notice('event_calendar_admin_notice_pending_upgrades', $message);
	}
}

// Add or remove a group's event calendar widget based on the corresponding group tools option
function event_calendar_tool_widgets_handler(\Elgg\Hook $hook) {
	$params = $hook->getParams();
	$return_value = $hook->getValue();

	if (!empty($params) && is_array($params)) {
		$entity = elgg_extract('entity', $params);

		if (!empty($entity) && $entity instanceof ElggGroup) {
			if (!is_array($return_value)) {
				$return_value = [];
			}

			if (!isset($return_value['enable'])) {
				$return_value['enable'] = [];
			}
			if (!isset($return_value['disable'])) {
				$return_value['disable'] = [];
			}

			if ($entity->event_calendar_enable == 'yes') {
				$return_value['enable'][] = 'groups_event_calendar';
			} else {
				$return_value['disable'][] = 'groups_event_calendar';
			}
		}
	}

	return $return_value;
}
