<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'calendar' => [
        'path' => './assets/calendar.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    'bootstrap' => [
        'version' => '5.3.8',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.8',
        'type' => 'css',
    ],
    '@fortawesome/fontawesome-free/css/all.css' => [
        'version' => '7.0.1',
        'type' => 'css',
    ],
    'fullcalendar' => [
        'version' => '5.11.5',
    ],
    '@fullcalendar/core' => [
        'version' => '5.11.5',
    ],
    '@fullcalendar/daygrid' => [
        'version' => '5.11.5',
    ],
    '@fullcalendar/bootstrap' => [
        'version' => '5.11.5',
    ],
    '@fullcalendar/common' => [
        'version' => '5.11.5',
    ],
    '@fullcalendar/interaction' => [
        'version' => '5.11.5',
    ],
    'tslib' => [
        'version' => '2.5.0',
    ],
    'preact' => [
        'version' => '10.12.1',
    ],
    'preact/compat' => [
        'version' => '10.12.1',
    ],
    'preact/hooks' => [
        'version' => '10.12.1',
    ],
    'animate.css' => [
        'version' => '4.1.1',
    ],
    'animate.css/animate.min.css' => [
        'version' => '4.1.1',
        'type' => 'css',
    ],
];
