<?php

use Elgg\DefaultPluginBootstrap;

class EventCalendarBootstrap extends DefaultPluginBootstrap {

	public function init() {

		elgg_extend_view('css/elgg', 'event_calendar/css');
		elgg_extend_view('css/elgg', 'fullcalendar/css');

		$hooks = $this->elgg()->hooks;
		$hooks->registerHandler('cron', 'fiveminute', 'event_calendar_handle_reminders_cron', 400);
		$hooks->registerHandler('entity:url', 'object', 'event_calendar_url');
		$hooks->registerHandler('prepare', 'notification:create:object:event_calendar', 'event_calendar_prepare_notification');
		// entity menu
		$hooks->registerHandler('register', 'menu:entity', 'event_calendar_entity_menu_setup');
		$hooks->registerHandler('prepare', 'menu:entity', 'event_calendar_entity_menu_prepare');

		// allow for liking of events
		$hooks->registerHandler('likes:is_likable', 'object:event_calendar', 'Elgg\Values::getTrue');

		$hooks->registerHandler('setting', 'plugin', 'event_calendar_invalidate_cache');

		// Register granular notification
		elgg_register_notification_event('object', 'event_calendar', ['create']);

		$plugin = $this->plugin();
		// Set up site menu
		$site_calendar = $plugin->getSetting('site_calendar');
		if (!$site_calendar || $site_calendar != 'no') {
			// add a site navigation item
			elgg_register_menu_item('site', array(
				'name' => 'event_calendar', 
				'text' => elgg_echo('item:object:event_calendar'), 
				'href' => 'event_calendar/list/'
			));
		}

		// make legacy tags searchable
		elgg_register_tag_metadata_name('event_tags');

		// ajax event summary popup
		elgg_register_ajax_view('event_calendar/popup');

		// add to group profile page
		$group_calendar = $plugin->getSetting('group_calendar');
		if (!$group_calendar || $group_calendar != 'no') {
			$hooks->registerHandler('register', 'menu:owner_block', 'event_calendar_owner_block_menu');
			elgg_extend_view('groups/tool_latest', 'event_calendar/group_module');

			// add the event calendar group tool option
			$event_calendar_group_default = $plugin->getSetting('group_default');
			if (!$event_calendar_group_default || ($event_calendar_group_default == 'yes')) {
				add_group_tool_option('event_calendar', elgg_echo('event_calendar:enable_event_calendar'), true);
			} else {
				add_group_tool_option('event_calendar', elgg_echo('event_calendar:enable_event_calendar'), false);
			}
		}

		// Index page and group profile page widgets and widget title urls if Widget Manager plugin is available
		if (elgg_is_active_plugin('widget_manager')) {
			// add index widget for Widget Manager plugin
			elgg_register_widget_type('index_event_calendar', elgg_echo('event_calendar:widget_title'), elgg_echo('event_calendar:widget:description'), ['index']);
			if (!$group_calendar || $group_calendar != 'no') {
				elgg_register_widget_type('groups_event_calendar', elgg_echo('event_calendar:widget_title'), elgg_echo('event_calendar:widget:description'), ['groups']);
			}
			// register title urls for widgets
			$hooks->registerHandler('entity:url', 'object', 'event_calendar_widget_urls');
			// handle the availability of the Event Calendar group widget
			$hooks->registerHandler('group_tool_widgets', 'widget_manager', 'event_calendar_tool_widgets_handler');
		}

		$events = $this->elgg()->events;
		// if autogroup is set, listen and respond to join/leave events
		if ($plugin->getSetting('autogroup') == 'yes') {
			$events->registerHandler('join', 'group', 'event_calendar_handle_join');
			$events->registerHandler('leave', 'group', 'event_calendar_handle_leave');
		}

		// Add page in Administer - Utilities section (this page offers cleanup of past event entries)
		elgg_register_menu_item('page', [
			'name' => 'administer_utilities:event_calendar',
			'text' => elgg_echo('event_calendar'),
			'href' => 'admin/administer_utilities/event_calendar',
			'section' => 'administer',
			'parent_name' => 'administer_utilities',
			'context' => 'admin',
		]);

		// check for pending event_calendar upgrades when a site upgrade is made
		$events->registerHandler('upgrade', 'system', 'event_calendar_check_pending_upgrades');

		$ical_import_export = $plugin->getSetting('ical_import_export');
		if ($ical_import_export === 'yes') {
			$hooks->registerHandler('register', 'menu:title', 'ec_ical_titlemenu');
		}

		//Seri 6/19/2020 - added this to set the value of ical_calendar_title_menu
		if ($plugin->getSetting('ical_import_export') === 'yes') {
			set_input('ical_calendar_title_menu', true);
		}
	}
}
