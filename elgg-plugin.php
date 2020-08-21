<?php

//Seri 6/19/2020 - added this to set the value of ical_calendar_title_menu
if (elgg_get_plugin_setting('ical_import_export', 'event_calendar') == "yes") {
	set_input('ical_calendar_title_menu', true);
}

return [
    'routes' => [
        /**
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
         */
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
    'views' => [
        'default' => [
            'event_calendar/' => __DIR__ . '/graphics',
        ],
    ]
];
