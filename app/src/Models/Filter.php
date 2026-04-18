<?php
namespace App\Models;

use App\Models\FilterSet;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;

/**
 * Class \App\Models\Filter
 *
 * @property ?string $Title
 * @property ?string $CSSStyles
 * @property int $ParentID
 * @property int $ImageID
 * @method \App\Models\FilterSet Parent()
 * @method \SilverStripe\Assets\Image Image()
 * @mixin \SilverStripe\Assets\Shortcodes\FileLinkTracking
 * @mixin \SilverStripe\Assets\AssetControlExtension
 * @mixin \SilverStripe\Versioned\RecursivePublishable
 * @mixin \SilverStripe\Versioned\VersionedStateExtension
 */
class Filter extends DataObject
{
    private static $db = [
        'Title' => 'Varchar(255)',
        'CSSStyles' => 'Text',
    ];

    private static $has_one = [
        "Parent" => FilterSet::class,
        "Image" => Image::class,
    ];

    private static $owns = [
        'Image',
    ];

    private static $cascade_deletes = [
        'Image',
    ];

    private static $summary_fields = [

    ];

    private static $default_sort = 'Title ASC';
    private static $table_name = 'Filter';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('ParentID');
        return $fields;
    }

    public function canView($member = null)
    {
        return true;
    }

    public function canCreate($member = null, $context = [])
    {
        return Permission::check('CMS_ACCESS_App\Admin\EntryAdmin', 'any', $member);
    }

    public function canEdit($member = null)
    {
        return Permission::check('CMS_ACCESS_App\Admin\EntryAdmin', 'any', $member);
    }

    public function canDelete($member = null)
    {
        return Permission::check('CMS_ACCESS_App\Admin\EntryAdmin', 'any', $member);
    }
}
