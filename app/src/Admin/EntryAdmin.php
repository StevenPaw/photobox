<?php
namespace App\Admin;

use App\Models\Entry;
use SilverStripe\Admin\ModelAdmin;

/**
 * Class \App\Admin\EntryAdmin
 *
 */
class EntryAdmin extends ModelAdmin
{
    private static $managed_models = [
        Entry::class,
    ];

    private static $url_segment = 'entries';

    private static $menu_title = 'Einträge';
}
