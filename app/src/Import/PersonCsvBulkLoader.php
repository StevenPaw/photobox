<?php
namespace App\Import;

use App\Models\Person;
use SilverStripe\Dev\CsvBulkLoader;

/**
 * Imports Persons from a CSV file (columns: FirstName, LastName) and scopes
 * them to a specific Event, since the plain CsvBulkLoader has no notion of
 * "the event this import belongs to".
 */
class PersonCsvBulkLoader extends CsvBulkLoader
{
    /**
     * @var int
     */
    public $eventID;

    public $duplicateChecks = [
        'FirstName' => ['callback' => 'findDuplicatePerson'],
    ];

    public function __construct($objectClass = Person::class)
    {
        parent::__construct($objectClass);
    }

    /**
     * Match against existing Persons of the same event (by first + last
     * name) so re-importing the same CSV updates rather than duplicates.
     */
    public function findDuplicatePerson($value, $record)
    {
        if (!$this->eventID) {
            return false;
        }

        $firstName = trim($record['FirstName'] ?? '');
        $lastName = trim($record['LastName'] ?? '');

        if ($firstName === '' && $lastName === '') {
            return false;
        }

        return Person::get()->filter([
            'ParentID' => $this->eventID,
            'FirstName' => $firstName,
            'LastName' => $lastName,
        ])->first();
    }

    protected function processRecord($record, $columnMap, &$results, $preview = false)
    {
        $record['ParentID'] = $this->eventID;
        return parent::processRecord($record, $columnMap, $results, $preview);
    }
}
