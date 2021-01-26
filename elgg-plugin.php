<?php

require_once(dirname(__FILE__) . '/models/model.php');
require_once(dirname(__FILE__) . '/lib/hooks.php');

return [
	'bootstrap' => \EventCalendarBootstrap::class,
	'entities' => [
		[
			'type' => 'object',
			'subtype' => 'event_calendar',
			'searchable' => true,
		],
	],
	'actions' => [
		"event_calendar/edit" => [],
		"event_calendar/delete" => [],
		"event_calendar/add_personal" => [],
		"event_calendar/remove_personal" => [],
		"event_calendar/request_personal_calendar" => [],
		"event_calendar/toggle_personal_calendar" => [],
		"event_calendar/killrequest" => [],
		"event_calendar/addtocalendar" => [],
		"event_calendar/add_to_group" => [],
		"event_calendar/remove_from_group" => [],
		"event_calendar/add_to_group_members" => [],
		"event_calendar/remove_from_group_members" => [],
		"event_calendar/manage_subscribers" => [],
		"event_calendar/modify_full_calendar" => [],
		"event_calendar/join_conference" => [],
		"event_calendar/upgrade" => ['admin'],
		"event_calendar/delete_past_events" => ['admin'],
		"event_calendar/import" => [],
		'event_calendar/export' => [],
	],
    'routes' => [
        'list:event_calendar' => [
            'path' => '/event_calendar/list/{start_date?}/{display_mode?}/{filter_mode?}/{region?}',
            'resource' => 'event_calendar/list',
            'defaults' => [
                'region' => '-',
                'filter_mode' => 'all',
                'display_mode' => '',
                'start_date' => '',
            ],
            'middleware' => [
                \Elgg\Router\Middleware\Gatekeeper::class
            ]
        ],
        'view:event_calendar' => [
            'path' => '/event_calendar/view/{guid}/{title?}',
            'defaults' => [
                'title' => ''
            ],
            'resource' => 'event_calendar/view',
            'middleware' => [
                \Elgg\Router\Middleware\Gatekeeper::class
            ]
        ],
        'display_users:event_calendar' => [
            'path' => '/event_calendar/display_users/{guid}',
            'resource' => 'event_calendar/display_users',
            'middleware' => [
                \Elgg\Router\Middleware\Gatekeeper::class
            ]
        ],
        'manage_users:event_calendar' => [
            'path' => '/event_calendar/manage_users/{guid}',
            'resource' => 'event_calendar/manage_users',
            'middleware' => [
                \Elgg\Router\Middleware\Gatekeeper::class
            ]
        ],
        'add:event_calendar' => [
            'path' => '/event_calendar/add/{guid?}/{start_date?}',
            'resource' => 'event_calendar/edit',
            'defaults' => [
                'page_type' => 'add'
            ],
            'middleware' => [
                \Elgg\Router\Middleware\Gatekeeper::class
            ]
        ],
        'edit:event_calendar' => [
            'path' => '/event_calendar/edit/{guid?}/{start_date?}',
            'resource' => 'event_calendar/edit',
            'defaults' => [
                'page_type' => 'edit'
            ],
            'middleware' => [
                \Elgg\Router\Middleware\Gatekeeper::class
            ]
        ],
        'group:event_calendar' => [
            'path' => '/event_calendar/group/{container_guid?}/{start_date?}/{display_mode?}/{filter_mode?}/{region?}',
            'resource' => 'event_calendar/group',
            'defaults' => [
                'region' => '-',
                'filter_mode' => 'all',
                'display_mode' => '',
                'start_date' => ''
            ],
            'middleware' => [
                \Elgg\Router\Middleware\Gatekeeper::class
            ]
        ],
        'review_requests:event_calendar' => [
            'path' => '/event_calendar/review_requests/{guid}',
            'resource' => 'event_calendar/review_requests',
            'middleware' => [
                \Elgg\Router\Middleware\Gatekeeper::class
            ]
        ],
        'get_fullcalendar_events:event_calendar' => [
            'path' => '/event_calendar/get_fullcalendar_events/{start_date}/{end_date}/{filter}/{container_guid}/{region}',
            'resource' => 'event_calendar/fullcalendar_events',
            'middleware' => [
                \Elgg\Router\Middleware\Gatekeeper::class
            ]
        ],
        'ical:event_calendar' => [
            'path' => '/event_calendar/ical/{action_type}',
            'resource' => 'event_calendar/export',
            'middleware' => [
                \Elgg\Router\Middleware\Gatekeeper::class
            ]
        ],
        'all:event_calendar' => [
            'path' => '/event_calendar/all',
            'resource' => 'event_calendar/list',
            'defaults' => [
                'region' => '-',
                'filter_mode' => 'all',
                'display_mode' => '',
                'start_date' => '',
            ],
            'middleware' => [
                \Elgg\Router\Middleware\Gatekeeper::class
            ]
        ],
        'owner:event_calendar' => [
            'path' => '/event_calendar/owner/{username?}/{start_date?}/{display_mode?}/{filter_mode?}/{region?}',
            'resource' => 'event_calendar/owner',
            'defaults' => [
                'region' => '-',
                'filter_mode' => 'owner',
                'display_mode' => '',
                'start_date' => ''
            ],
            'middleware' => [
                \Elgg\Router\Middleware\Gatekeeper::class
            ]
        ],
        'friends:event_calendar' => [
            'path' => '/event_calendar/friends/{username?}',
            'resource' => 'event_calendar/list',
            'defaults' => [
                'region' => '-',
                'filter_mode' => 'friends',
                'display_mode' => '',
                'start_date' => ''
            ],
            'middleware' => [
                \Elgg\Router\Middleware\Gatekeeper::class
            ]
        ]
    ],
	'widgets' => [
		'event_calendar' => [
			'context' => ['profile', 'index', 'dashboard'],
		],
	],
	'views' => [
		'default' => [
			'event_calendar/' => __DIR__ . '/graphics',
		],
	],
];
