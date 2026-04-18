<?php
namespace App\Models;

use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Security\Permission;

/**
 * Class \App\Models\Entry
 *
 * @property string $Name
 * @property int $ImageID
 * @method Image Image()
 */
class Entry extends DataObject
{
    private static $db = [
        'Name' => 'Varchar(255)',
    ];

    private static $has_one = [
        'Image' => Image::class,
    ];

    private static $owns = [
        'Image',
    ];

    private static $cascade_deletes = [
        'Image',
    ];

    private static $summary_fields = [
        'FormattedDate' => 'Datum',
        'Name' => 'Name',
        'Thumbnail' => 'Bild',
    ];

    private static $default_sort = 'Created DESC';
    private static $table_name = 'Entry';

    public function FormattedDate()
    {
        return $this->dbObject('Created')->Format('dd.MM.yyyy');
    }

    public function Thumbnail()
    {
        if($image = $this->Image()) {
            $thumb = $image->CMSThumbnail()->forTemplate();
            return DBField::create_field('HTMLText',"<a onClick=\"event.stopPropagation()\" href=\"$image->URL\" target=\"_blank\">$thumb</a><br/><a onClick=\"event.stopPropagation()\" href=\"$image->URL\" target=\"_blank\" download>Download</a>");
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
