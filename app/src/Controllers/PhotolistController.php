<?php
namespace App\Controllers;

use App\Models\Event;
use SilverStripe\Control\HTTPRequest;
use ZipArchive;

/**
 * Class \App\Controllers\PhotolistController
 *
 * Handles all photolist routes and serves the Vue SPA
 *
 */
class PhotolistController extends BaseController
{
    private static $url_segment = 'photolist';

    private static $allowed_actions = [
        'index',
        'downloadZip',
    ];

    private static $url_handlers = [
        '$Hash/zip' => 'downloadZip',
        '$Hash' => 'index',
        '' => 'index',
    ];

    /**
     * Handle all requests and return the Vue app
     * Vue Router will handle client-side routing
     */
    public function index(HTTPRequest $request)
    {
        $eventhash = $request->param('Hash');
        $event = null;

        if ($eventhash) {
            $event = Event::get()->filter('Hash', $eventhash)->first();
        }

        return $this->render([
            'Event' => $event,
            'ZipDownloadLink' => $event ? $this->Link($eventhash . '/zip') : null,
        ]);
    }

    public function downloadZip(HTTPRequest $request)
    {
        $eventhash = $request->param('Hash');

        if (!$eventhash) {
            return $this->httpError(404, 'Event nicht gefunden');
        }

        $event = Event::get()->filter('Hash', $eventhash)->first();

        if (!$event) {
            return $this->httpError(404, 'Event nicht gefunden');
        }

        $photos = $event->Photos();

        if ($photos->count() === 0) {
            return $this->httpError(404, 'Keine Fotos gefunden');
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'photobox_') . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return $this->httpError(500, 'ZIP konnte nicht erstellt werden');
        }

        $manifest = [];

        foreach ($photos as $photo) {
            $image = $photo->Image();
            if ($image->exists()) {
                $entryName = $photo->ID . '_' . $image->getFilename();
                $zip->addFromString($entryName, $image->getString());

                $manifest[] = [
                    'filename' => $entryName,
                    'persons' => array_map(fn($p) => $p->getTitle(), $photo->Persons()->toArray()),
                ];
            }
        }

        $zip->addFromString('persons.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $zip->close();

        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $event->Title) . '.zip';

        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);
        unlink($zipPath);
        exit();
    }
}
