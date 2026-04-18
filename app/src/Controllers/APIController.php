<?php
namespace App\Controllers;

use App\Models\Event;
use App\Models\FilterSet;
use App\Models\Photo;
use App\Models\Person;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;

/**
 * Class \App\Controllers\APIController
 *
 */
class APIController extends BaseController
{
    private static $url_segment = 'api';

    private static $allowed_actions = [
        'events',
        'filtersets',
        'photos',
    ];

    private static $url_handlers = [
        'events/$ID/filtersets/$FilterSetID' => 'events',
        'events/$ID/filtersets' => 'events',
        'events/$ID/persons' => 'events',
        'events/$ID' => 'events',
        'events' => 'events',
        'filtersets/$ID/filters' => 'filtersets',
        'filtersets/$ID' => 'filtersets',
        'filtersets' => 'filtersets',
        'photos' => 'photos',
    ];

    /**
     * Handle events endpoints
     */
    public function events(HTTPRequest $request)
    {
        $id = $request->param('ID');
        $filterSetId = $request->param('FilterSetID');
        $urlSegments = $request->allParams();

        // DELETE events/{id}/filtersets/{filterSetId}
        if ($filterSetId && $request->httpMethod() === 'DELETE') {
            return $this->removeFilterSetFromEvent($request, $id, $filterSetId);
        }

        // POST events/{id}/filtersets
        if ($id && strpos($request->getURL(), '/filtersets') !== false && $request->httpMethod() === 'POST') {
            return $this->addFilterSetToEvent($request, $id);
        }

        // GET events/{id}/filtersets
        if ($id && strpos($request->getURL(), '/filtersets') !== false && $request->httpMethod() === 'GET') {
            return $this->getEventFilterSets($request, $id);
        }

        // GET events/{id}/persons
        if ($id && strpos($request->getURL(), '/persons') !== false) {
            return $this->getEventPersons($request, $id);
        }

        // GET events/{id}
        if ($id) {
            return $this->getEvent($request, $id);
        }

        // GET events
        return $this->getEvents($request);
    }

    /**
     * Handle filtersets endpoints
     */
    public function filtersets(HTTPRequest $request)
    {
        $id = $request->param('ID');

        // GET filtersets/{id}/filters
        if ($id && strpos($request->getURL(), '/filters') !== false) {
            return $this->getFilters($request, $id);
        }

        // GET filtersets/{id}
        if ($id) {
            return $this->getFilterSet($request, $id);
        }

        // GET filtersets
        return $this->getFilterSets($request);
    }

    /**
     * Handle photos endpoints
     */
    public function photos(HTTPRequest $request)
    {
        // POST photos
        if ($request->httpMethod() === 'POST') {
            return $this->createPhoto($request);
        }

        return $this->jsonResponse(['error' => 'Method not allowed'], 405);
    }

    /**
     * Get all events
     */
    private function getEvents(HTTPRequest $request)
    {
        $events = Event::get();
        $data = [];

        foreach ($events as $event) {
            $data[] = [
                'ID' => $event->ID,
                'Hash' => $event->Hash,
                'EventDate' => $event->EventDate,
                'FormattedDate' => $event->FormattedDate(),
                'UsePersonRecognition' => (bool)$event->UsePersonRecognition,
            ];
        }

        return $this->jsonResponse($data);
    }

    /**
     * Get single event
     */
    private function getEvent(HTTPRequest $request, $id)
    {
        $event = Event::get()->byID($id);

        if (!$event) {
            return $this->jsonResponse(['error' => 'Event not found'], 404);
        }

        return $this->jsonResponse([
            'ID' => $event->ID,
            'Hash' => $event->Hash,
            'EventDate' => $event->EventDate,
            'FormattedDate' => $event->FormattedDate(),
        ]);
    }

    /**
     * Get all filter sets
     */
    private function getFilterSets(HTTPRequest $request)
    {
        $filterSets = FilterSet::get();
        $data = [];

        foreach ($filterSets as $filterSet) {
            $data[] = [
                'ID' => $filterSet->ID,
                'Title' => $filterSet->Title,
                'Hash' => $filterSet->Hash,
            ];
        }

        return $this->jsonResponse($data);
    }

    /**
     * Get single filter set
     */
    private function getFilterSet(HTTPRequest $request, $id)
    {
        $filterSet = FilterSet::get()->byID($id);

        if (!$filterSet) {
            return $this->jsonResponse(['error' => 'FilterSet not found'], 404);
        }

        return $this->jsonResponse([
            'ID' => $filterSet->ID,
            'Title' => $filterSet->Title,
            'Hash' => $filterSet->Hash,
        ]);
    }

    /**
     * Get filters of a filter set
     */
    private function getFilters(HTTPRequest $request, $id)
    {
        $filterSet = FilterSet::get()->byID($id);

        if (!$filterSet) {
            return $this->jsonResponse(['error' => 'FilterSet not found'], 404);
        }

        $filters = $filterSet->Filters();
        $data = [];

        foreach ($filters as $filter) {
            $imageUrl = null;
            if ($filter->Image()->exists()) {
                $imageUrl = $filter->Image()->AbsoluteURL;
            }

            $data[] = [
                'ID' => $filter->ID,
                'Title' => $filter->Title,
                'CSSStyles' => $filter->CSSStyles,
                'ImageURL' => $imageUrl,
            ];
        }

        return $this->jsonResponse($data);
    }

    /**
     * Get filter sets of an event
     */
    private function getEventFilterSets(HTTPRequest $request, $id)
    {
        $event = Event::get()->byID($id);

        if (!$event) {
            return $this->jsonResponse(['error' => 'Event not found'], 404);
        }

        $filterSets = $event->UsedFilterSet();
        $data = [];

        foreach ($filterSets as $filterSet) {
            $data[] = [
                'ID' => $filterSet->ID,
                'Title' => $filterSet->Title,
                'Hash' => $filterSet->Hash,
            ];
        }

        return $this->jsonResponse($data);
    }

    /**
     * Add filter set to event
     */
    private function addFilterSetToEvent(HTTPRequest $request, $eventId)
    {
        $event = Event::get()->byID($eventId);

        if (!$event) {
            return $this->jsonResponse(['error' => 'Event not found'], 404);
        }

        $body = json_decode($request->getBody(), true);
        $filterSetId = $body['filterSetId'] ?? null;

        if (!$filterSetId) {
            return $this->jsonResponse(['error' => 'FilterSet ID required'], 400);
        }

        $filterSet = FilterSet::get()->byID($filterSetId);
        if (!$filterSet) {
            return $this->jsonResponse(['error' => 'FilterSet not found'], 404);
        }

        $event->UsedFilterSet()->add($filterSet);

        return $this->jsonResponse(['success' => true]);
    }

    /**
     * Remove filter set from event
     */
    private function removeFilterSetFromEvent(HTTPRequest $request, $eventId, $filterSetId)
    {
        $event = Event::get()->byID($eventId);
        if (!$event) {
            return $this->jsonResponse(['error' => 'Event not found'], 404);
        }

        $filterSet = FilterSet::get()->byID($filterSetId);
        if (!$filterSet) {
            return $this->jsonResponse(['error' => 'FilterSet not found'], 404);
        }

        $event->UsedFilterSet()->remove($filterSet);

        return $this->jsonResponse(['success' => true]);
    }

    /**
     * Get persons of an event
     */
    private function getEventPersons(HTTPRequest $request, $id)
    {
        $event = Event::get()->byID($id);

        if (!$event) {
            return $this->jsonResponse(['error' => 'Event not found'], 404);
        }

        $persons = $event->Persons();
        $data = [];

        foreach ($persons as $person) {
            $imageUrl = null;
            if ($person->Image()->exists()) {
                $imageUrl = $person->Image()->AbsoluteURL;
            }

            $data[] = [
                'ID' => $person->ID,
                'FirstName' => $person->FirstName,
                'LastName' => $person->LastName,
                'Title' => $person->getTitle(),
                'ImageURL' => $imageUrl,
            ];
        }

        return $this->jsonResponse($data);
    }

    /**
     * Create a new photo
     */
    private function createPhoto(HTTPRequest $request)
    {
        $body = json_decode($request->getBody(), true);

        $eventId = $body['eventId'] ?? null;
        $imageData = $body['imageData'] ?? null;
        $personIds = $body['personIds'] ?? [];

        if (!$eventId || !$imageData) {
            return $this->jsonResponse(['error' => 'Event ID and image data required'], 400);
        }

        $event = Event::get()->byID($eventId);
        if (!$event) {
            return $this->jsonResponse(['error' => 'Event not found'], 404);
        }

        // Create photo
        $photo = Photo::create();
        $photo->ParentID = $eventId;
        $photo->Hash = md5(uniqid(rand(), true));
        $photo->Date = date('Y-m-d H:i:s');

        // Decode base64 image data
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $type = strtolower($type[1]);

            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                return $this->jsonResponse(['error' => 'Base64 decode failed'], 400);
            }

            // Create image
            $image = \SilverStripe\Assets\Image::create();
            $fileName = 'photo_' . $photo->Hash . '.' . $type;

            // Set image from binary string
            $image->setFromString($imageData, $fileName);
            $image->write();

            // Link image to photo
            $photo->ImageID = $image->ID;
        }

        $photo->write();

        // Add persons to photo
        if (!empty($personIds)) {
            foreach ($personIds as $personId) {
                $person = Person::get()->byID($personId);
                if ($person && $person->ParentID == $eventId) {
                    $photo->Persons()->add($person);
                }
            }
        }

        return $this->jsonResponse([
            'success' => true,
            'photoId' => $photo->ID,
            'hash' => $photo->Hash,
        ]);
    }

    /**
     * Helper method to return JSON response
     */
    private function jsonResponse($data, $statusCode = 200)
    {
        $response = HTTPResponse::create();
        $response->addHeader('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
        $response->setBody(json_encode($data));
        return $response;
    }
}
