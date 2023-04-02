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

use Elgg\DefaultPluginBootstrap;

class EventCalendarBootstrap extends DefaultPluginBootstrap {

	public function boot() {
		elgg_register_plugin_hook_handler("setting", "plugin", "event_calendar_invalidate_cache");
	}

	public function init() {
		// extend the account settings form
		elgg_extend_view('forms/usersettings/save', 'core/settings/account/event_calendar', 110);

		elgg_register_plugin_hook_handler('cron', 'fiveminute', 'event_calendar_handle_reminders_cron', 400);
		elgg_register_plugin_hook_handler('entity:url', 'object', 'event_calendar_url');
		elgg_register_plugin_hook_handler('prepare', 'notification:create:object:event_calendar', 'event_calendar_prepare_notification');
		elgg_register_plugin_hook_handler('usersettings:save', 'user', 'event_calendar_set_user_listing_format');

		// Register granular notification
		elgg_register_notification_event('object', 'event_calendar', ['create']);

		// Set up site menu
		$site_calendar = elgg_get_plugin_setting('site_calendar', 'event_calendar');
		if (!$site_calendar || $site_calendar != 'no') {
			// add a site navigation item
			$item = new ElggMenuItem('event_calendar', elgg_echo('item:object:event_calendar'), 'event_calendar/list/');
			elgg_register_menu_item('site', $item);
		}

		// make legacy tags searchable (attention: functionality removed in Elgg 4!)
		elgg_register_tag_metadata_name('event_tags');

		// ajax event summary popup
		elgg_register_ajax_view('event_calendar/popup');

		// add to group profile page
		$group_calendar = elgg_get_plugin_setting('group_calendar', 'event_calendar');
		if (!$group_calendar || $group_calendar != 'no') {
			elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'event_calendar_owner_block_menu');
			elgg_extend_view('groups/tool_latest', 'event_calendar/group_module');

			elgg_register_widget_type('groups_event_calendar', elgg_echo("widgets:groups_event_calendar:name"), elgg_echo('widgets:groups_event_calendar:description'), ["groups"]);

			// add the event calendar group tool option
			$event_calendar_group_default = elgg_get_plugin_setting('group_default', 'event_calendar');
			if (!$event_calendar_group_default || ($event_calendar_group_default == 'yes')) {
				elgg()->group_tools->register('event_calendar', [
					'default_on' => true,
					'label' => elgg_echo('event_calendar:enable_event_calendar'),
				]);
			} else {
				elgg()->group_tools->register('event_calendar', [
					'default_on' => false,
					'label' => elgg_echo('event_calendar:enable_event_calendar'),
				]);
			}
		}

		// add to the css
		elgg_extend_view('css/elgg', 'event_calendar/css');
		elgg_extend_view('css/elgg', 'fullcalendar/css');

		// register title urls for widgets
		elgg_register_plugin_hook_handler("entity:url", "object", "event_calendar_widget_urls");
		// handle the availability of the Event Calendar group widget
		elgg_register_plugin_hook_handler("group_tool_widgets", "widget_manager", "event_calendar_tool_widgets_handler");

		// if autogroup is set, listen and respond to join/leave events
		if (elgg_get_plugin_setting('autogroup', 'event_calendar') == 'yes') {
			elgg_register_event_handler('join', 'group', 'event_calendar_handle_join');
			elgg_register_event_handler('leave', 'group', 'event_calendar_handle_leave');
		}

		// entity menu
		elgg_register_plugin_hook_handler('register', 'menu:entity', 'event_calendar_entity_menu_setup');

		// Add page in Administer - Utilities section (this page offers cleanup of past event entries)
		elgg_register_menu_item('page', [
			'name' => 'administer_utilities:event_calendar',
			'href' => 'admin/administer_utilities/event_calendar',
			'text' => elgg_echo('admin:administer_utilities:event_calendar'),
			'context' => 'admin',
			'parent_name' => 'administer_utilities',
			'section' => 'administer'
		]);

		// allow for liking of events
		elgg_register_plugin_hook_handler('likes:is_likable', 'object:event_calendar', 'Elgg\Values::getTrue');

		// check for pending event_calendar upgrades when a site upgrade is made
		elgg_register_event_handler('upgrade', 'system', 'event_calendar_check_pending_upgrades');

		if (elgg_get_plugin_setting('ical_import_export', 'event_calendar') == "yes") {
			elgg_register_plugin_hook_handler('register', 'menu:title', 'ec_ical_titlemenu');
		}
	}

	public function activate() {
		// sets $version based on code
		require_once elgg_get_plugins_path() . "event_calendar/version.php";

		$local_version = elgg_get_plugin_setting('version', 'event_calendar');
		if ($local_version === null) {
			// set initial version for new install
			elgg_set_plugin_setting('version', $version, 'event_calendar');
		}
	}
}
