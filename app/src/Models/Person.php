<?php
namespace App\Models;

use App\Models\Event;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;

/**
 * Class \App\Models\Person
 *
 * @property ?string $FirstName
 * @property ?string $LastName
 * @property ?string $Hash
 * @property int $ParentID
 * @property int $ImageID
 * @method \App\Models\Event Parent()
 * @method \SilverStripe\Assets\Image Image()
 * @method \SilverStripe\ORM\ManyManyList|\App\Models\Photo[] Photos()
 * @mixin \SilverStripe\Assets\Shortcodes\FileLinkTracking
 * @mixin \SilverStripe\Assets\AssetControlExtension
 * @mixin \SilverStripe\Versioned\RecursivePublishable
 * @mixin \SilverStripe\Versioned\VersionedStateExtension
 */
class Person extends DataObject
{
    private static $db = [
        "FirstName" => "Varchar(255)",
        "LastName" => "Varchar(255)",
        "Hash" => "Varchar(255)",
    ];

    private static $has_one = [
        "Parent" => Event::class,
        "Image" => Image::class,
    ];

    private static $belongs_many_many = [
        "Photos" => Photo::class,
    ];

    private static $owns = [
        'Image',
    ];

    private static $cascade_deletes = [
    ];

    private static $summary_fields = [
        "Image.CMSThumbnail" => "Image",
        "Title" => "Name",
    ];

    private static $default_sort = 'FirstName ASC, LastName ASC';
    private static $table_name = 'Person';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('ParentID');
        return $fields;
    }

    public function getTitle()
    {
        return trim($this->FirstName . " " . $this->LastName);
    }

    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (!$this->Hash) {
            $this->Hash = md5(uniqid($this->Title, true));
        }
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
