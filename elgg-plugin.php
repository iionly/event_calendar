<?php

require_once(dirname(__FILE__) . '/lib/functions.php');
require_once(dirname(__FILE__) . '/lib/hooks.php');
require_once(dirname(__FILE__) . '/lib/events.php');

return [
	'bootstrap' => \EventCalendarBootstrap::class,
	'entities' => [
		[
			'type' => 'object',
			'subtype' => 'event_calendar',
			'class' => 'EventCalendar',
			'searchable' => true,
		],
	],
	'actions' => [
		'event_calendar/edit' => [
			'access' => 'logged_in',
		],
		'event_calendar/delete' => [
			'access' => 'logged_in',
		],
		'event_calendar/add_personal' => [
			'access' => 'logged_in',
		],
		'event_calendar/remove_personal' => [
			'access' => 'logged_in',
		],
		'event_calendar/request_personal_calendar' => [
			'access' => 'logged_in',
		],
		'event_calendar/toggle_personal_calendar' => [
			'access' => 'logged_in',
		],
		'event_calendar/killrequest' => [
			'access' => 'logged_in',
		],
		'event_calendar/addtocalendar' => [
			'access' => 'logged_in',
		],
		'event_calendar/add_to_group' => [
			'access' => 'logged_in',
		],
		'event_calendar/remove_from_group' => [
			'access' => 'logged_in',
		],
		'event_calendar/add_to_group_members' => [
			'access' => 'logged_in',
		],
		'event_calendar/remove_from_group_members' => [
			'access' => 'logged_in',
		],
		'event_calendar/manage_subscribers' => [
			'access' => 'logged_in',
		],
		'event_calendar/modify_full_calendar' => [
			'access' => 'logged_in',
		],
		'event_calendar/join_conference' => [
			'access' => 'logged_in',
		],
		'event_calendar/upgrade' => [
			'access' => 'admin',
		],
		'event_calendar/delete_past_events' => [
			'access' => 'admin',
		],
		'event_calendar/import' => [
			'access' => 'logged_in',
		],
		'event_calendar/export' => [
			'access' => 'logged_in',
		],
	],
	'settings' => [
		'hide_access' => 'no',
		'default_access' => ACCESS_LOGGED_IN,
		'hide_end' => 'no',
		'listing_format' => 'month',
		'listing_format_allow_user_change' => 'yes',
		'repeated_events' => 'no',
		'reminders' => 'no',
		'times' => 'yes',
		'timeformat' => '24',
		'autopersonal' => 'yes',
		'autogroup' => 'no',
		'add_to_group_calendar' => 'no',
		'venue_view' => 'no',
		'fewer_fields' => 'no',
		'site_calendar' => 'admin',
		'group_calendar' => 'members',
		'group_default' => 'yes',
		'group_always_display' => 'no',
		'add_users' => 'no',
		'add_users_notify' => 'no',
		'personal_manage' => 'open',
		'spots_display' => 'no',
		'no_collisions' => 'no',
		'collision_length' => 3600,
		'region_display' => 'no',
		'region_list' => '',
		'region_list_handles' => 'no',
		'type_display' => 'no',
		'type_list' => '',
		'type_list_handles' => 'no',
		'first_date' => '',
		'last_date' => '',
		'more_required' => 'no',
		'ical_import_export' => 'no',
		'set_organizer' => 'yes',
	],
	'routes' => [
		'collection:object:event_calendar:list' => [
			'path' => '/event_calendar/list/{start_date?}/{display_mode?}/{filter_mode?}/{region?}',
			'resource' => 'event_calendar/list',
			'defaults' => [
				'start_date' => 0,
				'display_mode' => '',
				'filter_mode' => 'all',
				'region' => '-',
			],
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'collection:object:event_calendar:owner' => [
			'path' => '/event_calendar/owner/{username?}/{start_date?}/{display_mode?}/{filter_mode?}/{region?}',
			'resource' => 'event_calendar/owner',
			'defaults' => [
				'start_date' => 0,
				'display_mode' => '',
				'filter_mode' => 'owner',
				'region' => '-',
			],
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'collection:object:event_calendar:group' => [
			'path' => '/event_calendar/group/{container_guid?}/{start_date?}/{display_mode?}/{filter_mode?}/{region?}',
			'resource' => 'event_calendar/group',
			'defaults' => [
				'start_date' => 0,
				'display_mode' => '',
				'filter_mode' => 'all',
				'region' => '-',
			],
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
			'required_plugins' => [
				'groups',
			],
		],
		'view:object:event_calendar' => [
			'path' => '/event_calendar/view/{guid}/{title?}',
			'defaults' => [
				'title' => '',
			],
			'resource' => 'event_calendar/view',
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
        ],
		'add:object:event_calendar' => [
			'path' => '/event_calendar/add/{guid?}/{start_date?}',
			'resource' => 'event_calendar/edit',
			'defaults' => [
				'page_type' => 'add',
			],
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'schedule:object:event_calendar' => [
			'path' => '/event_calendar/schedule/{guid?}/{start_date?}',
			'resource' => 'event_calendar/edit',
			'defaults' => [
				'page_type' => 'schedule',
			],
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'edit:object:event_calendar' => [
			'path' => '/event_calendar/edit/{guid?}',
			'resource' => 'event_calendar/edit',
			'defaults' => [
				'page_type' => 'edit',
			],
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'display_users:object:event_calendar' => [
			'path' => '/event_calendar/display_users/{guid}',
			'resource' => 'event_calendar/display_users',
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'manage_users:object:event_calendar' => [
			'path' => '/event_calendar/manage_users/{guid}',
			'resource' => 'event_calendar/manage_users',
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'review_requests:object:event_calendar' => [
			'path' => '/event_calendar/review_requests/{guid}',
			'resource' => 'event_calendar/review_requests',
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'get_fullcalendar_events:object:event_calendar' => [
			'path' => '/event_calendar/get_fullcalendar_events/{start_date}/{end_date}/{filter}/{container_guid}/{region}',
			'resource' => 'event_calendar/fullcalendar_events',
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'ical:object:event_calendar' => [
			'path' => '/event_calendar/ical/{action_type}',
			'resource' => 'event_calendar/export',
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'default:object:event_calendar' => [
			'path' => '/event_calendar',
			'resource' => 'event_calendar/list',
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
	],
	'widgets' => [
		'event_calendar' => [
			'context' => ['profile', 'dashboard'],
		],
		'index_event_calendar' => [
			'context' => ['index'],
		],
	],
	'views' => [
		'default' => [
			'event_calendar/' => __DIR__ . '/graphics',
		],
	],
];
