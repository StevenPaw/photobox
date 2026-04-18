<?php
namespace App\Models;

use App\Models\Event;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;

/**
 * Class \App\Models\Photo
 *
 * @property ?string $Hash
 * @property ?string $Date
 * @property int $ParentID
 * @property int $ImageID
 * @method \App\Models\Event Parent()
 * @method \SilverStripe\Assets\Image Image()
 * @method \SilverStripe\ORM\ManyManyList|\App\Models\Person[] Persons()
 * @mixin \SilverStripe\Assets\Shortcodes\FileLinkTracking
 * @mixin \SilverStripe\Assets\AssetControlExtension
 * @mixin \SilverStripe\Versioned\RecursivePublishable
 * @mixin \SilverStripe\Versioned\VersionedStateExtension
 */
class Photo extends DataObject
{
    private static $db = [
        'Hash' => 'Varchar(255)',
        'Date' => 'Datetime',
    ];

    private static $has_one = [
        'Parent' => Event::class,
        'Image' => Image::class,
    ];

    private static $many_many = [
        "Persons" => Person::class,
    ];

    private static $owns = [
        'Image',
    ];

    private static $cascade_deletes = [
        'Image',
    ];

    private static $summary_fields = [
        "ID" => "ID",
        "Image.CMSThumbnail" => "Thumbnail",
        "FormattedDate" => "Date",
        "FormattedPersons" => "Personen",
    ];

    private static $default_sort = 'Date DESC';
    private static $table_name = 'Photo';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('ParentID');
        return $fields;
    }

    public function FormattedDate()
    {
        return $this->dbObject('Date')->Format('dd.MM.yyyy | HH:mm');
    }

    public function FormattedPersons()
    {
        $persons = $this->Persons();
        if($persons->count() > 0) {
            return implode(", ", $persons->map("ID", "Title")->toArray());
        }
        return "No persons tagged";
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
