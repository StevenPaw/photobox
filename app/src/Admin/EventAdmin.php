<?php
namespace App\Admin;

use App\Models\Event;
use SilverStripe\Admin\ModelAdmin;

/**
 * Class \App\Admin\EventAdmin
 *
 */
class EventAdmin extends ModelAdmin
{
    private static $managed_models = [
        Event::class,
    ];

    private static $url_segment = 'events';

    private static $menu_title = 'Events';
}
