<?php
namespace App\Models;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;

/**
 * Class \App\Models\Event
 *
 * @property ?string $Title
 * @property ?string $Hash
 * @property ?string $EventDate
 * @property bool $UsePersonRecognition
 * @method \SilverStripe\ORM\DataList|\App\Models\Photo[] Photos()
 * @method \SilverStripe\ORM\DataList|\App\Models\Person[] Persons()
 * @method \SilverStripe\ORM\ManyManyList|\App\Models\FilterSet[] UsedFilterSet()
 * @mixin \SilverStripe\Assets\Shortcodes\FileLinkTracking
 * @mixin \SilverStripe\Assets\AssetControlExtension
 * @mixin \SilverStripe\Versioned\RecursivePublishable
 * @mixin \SilverStripe\Versioned\VersionedStateExtension
 */
class Event extends DataObject
{
    private static $db = [
        'Title' => 'Varchar(255)',
        'Hash' => 'Varchar(255)',
        'EventDate' => 'Date',
        'UsePersonRecognition' => 'Boolean',
    ];

    private static $has_many = [
        'Photos' => Photo::class,
        "Persons" => Person::class,
    ];

    private static $many_many = [
        "UsedFilterSet" => FilterSet::class,
    ];

    private static $owns = [
        'Photos',
    ];

    private static $cascade_deletes = [
        'Photos',
        'Persons',
    ];

    private static $summary_fields = [

    ];

    private static $default_sort = 'EventDate DESC';
    private static $table_name = 'Event';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('Photos');
        $gridfieldConfig = GridFieldConfig_RelationEditor::create();
        $gridfield = GridField::create(
            'Photos',
            'Photos',
            $this->Photos(),
            $gridfieldConfig
        );
        $fields->addFieldToTab('Root.Main', $gridfield);
        return $fields;
    }

    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (!$this->Hash) {
            $this->Hash = md5(uniqid($this->Title, true));
        }
    }

    public function FormattedDate()
    {
        return $this->dbObject('EventDate')->Format('dd.MM.yyyy');
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
