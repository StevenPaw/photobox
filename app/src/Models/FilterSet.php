<?php
namespace App\Models;

use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use App\Models\Filter;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;

/**
 * Class \App\Models\FilterSet
 *
 * @property ?string $Title
 * @property ?string $Hash
 * @method \SilverStripe\ORM\DataList|\App\Models\Filter[] Filters()
 * @method \SilverStripe\ORM\ManyManyList|\App\Models\Event[] UsedByEvents()
 * @mixin \SilverStripe\Assets\Shortcodes\FileLinkTracking
 * @mixin \SilverStripe\Assets\AssetControlExtension
 * @mixin \SilverStripe\Versioned\RecursivePublishable
 * @mixin \SilverStripe\Versioned\VersionedStateExtension
 */
class FilterSet extends DataObject
{
    private static $db = [
        'Title' => 'Varchar(255)',
        'Hash' => 'Varchar(255)',
    ];

    private static $has_many = [
        'Filters' => Filter::class,
    ];

    private static $belongs_many_many = [
        "UsedByEvents" => Event::class,
    ];

    private static $owns = [
        'Filters',
    ];

    private static $cascade_deletes = [
        'Filters',
    ];

    private static $summary_fields = [

    ];

    private static $default_sort = 'Title ASC';
    private static $table_name = 'FilterSet';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('Filters');
        $gridfieldConfig = GridFieldConfig_RelationEditor::create();
        $gridfield = GridField::create(
            'Filters',
            'Filter',
            $this->Filters(),
            $gridfieldConfig
        );
        $fields->addFieldToTab('Root.Main', $gridfield);
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
