<?php
namespace App\Models;

use App\Forms\GridFieldPersonImportButton;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
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

        // Add CSV export/import for this event's Persons, so a guest list
        // can be prepared/edited outside the CMS and (re-)imported here.
        $personsField = $fields->dataFieldByName('Persons');
        if ($personsField && $personsField->getConfig()) {
            $personsConfig = $personsField->getConfig();
            $personsConfig->addComponent(new GridFieldExportButton('buttons-before-left', [
                'FirstName' => 'FirstName',
                'LastName' => 'LastName',
            ]));

            // Importing needs a saved Event to attach the Persons to
            if ($this->exists()) {
                $personsConfig->addComponent(new GridFieldPersonImportButton($this->ID, 'buttons-before-left'));
            }
        }

        return $fields;
    }

    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (!$this->Hash) {
            $this->Hash = md5(uniqid($this->Title, true));
        }
    }

    protected function onAfterWrite()
    {
        parent::onAfterWrite();
        $this->removeOrphanedPhotoPersonRelations();
    }

    /**
     * Removes stale Photo<->Person many_many rows left behind when a Person
     * was removed but a Photo still references it (e.g. via CSV re-import
     * replacing the guest list, or manual deletion in the CMS).
     */
    private function removeOrphanedPhotoPersonRelations()
    {
        $photoIDs = $this->Photos()->column('ID');
        if (empty($photoIDs)) {
            return;
        }

        $schema = DataObject::getSchema();
        $relation = $schema->manyManyComponent(Photo::class, 'Persons');
        if (!$relation) {
            return;
        }

        DB::query(sprintf(
            'DELETE FROM "%s" WHERE "%s" IN (%s) AND "%s" NOT IN (SELECT "ID" FROM "%s")',
            $relation['join'],
            $relation['parentField'],
            implode(',', array_map('intval', $photoIDs)),
            $relation['childField'],
            $schema->tableName(Person::class)
        ));
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
