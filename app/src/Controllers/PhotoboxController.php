<?php
namespace App\Controllers;

use SilverStripe\Control\HTTPRequest;

/**
 * Class \App\Controllers\PhotoboxController
 *
 * Handles all photobox routes and serves the Vue SPA
 *
 */
class PhotoboxController extends BaseController
{
    private static $url_segment = 'photobox';

    private static $allowed_actions = [
        'index',
    ];

    private static $url_handlers = [
        '$Action' => 'index',
        '' => 'index',
    ];

    /**
     * Handle all requests and return the Vue app
     * Vue Router will handle client-side routing
     */
    public function index(HTTPRequest $request)
    {
        return [
        ];
    }
}
