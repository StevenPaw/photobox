<?php
namespace App\Admin;

use App\Models\FilterSet;
use SilverStripe\Admin\ModelAdmin;

/**
 * Class \App\Admin\FilterAdmin
 *
 */
class FilterAdmin extends ModelAdmin
{
    private static $managed_models = [
        FilterSet::class,
    ];

    private static $url_segment = 'filtersets';

    private static $menu_title = 'Filter Sets';
}
