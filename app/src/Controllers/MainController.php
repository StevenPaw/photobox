<?php
namespace App\Controllers;

use SilverStripe\Control\HTTPRequest;

/**
 * Class \App\Controllers\MainController
 *
 */
class MainController extends BaseController
{
    private static $allowed_actions = [
    ];

    public function index(HTTPRequest $request)
    {
        return [
            'Title' => 'Home',
            'Content' => 'Welcome to the SilverStripe Template'
        ];
    }
}
