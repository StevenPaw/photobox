<?php
namespace App\Controllers;

use App\Models\Photo;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;

/**
 * Class \App\Controllers\PhotoDownloadController
 *
 * Handles photo download pages
 *
 */
class PhotoDownloadController extends BaseController
{
    private static $url_segment = 'download';

    private static $allowed_actions = [
        'index',
    ];

    private static $url_handlers = [
        '$Hash' => 'index',
        '' => 'index',
    ];

    /**
     * Show photo download page
     */
    public function index(HTTPRequest $request)
    {
        $hash = $request->param('Hash');

        if (!$hash) {
            return $this->httpError(404, 'Foto nicht gefunden');
        }

        $photo = Photo::get()->filter('Hash', $hash)->first();

        if (!$photo) {
            return $this->httpError(404, 'Foto nicht gefunden');
        }

        return [
            'Photo' => $photo,
            'ImageURL' => $photo->Image()->exists() ? $photo->Image()->AbsoluteURL : null,
            'DownloadURL' => $photo->Image()->exists() ? $photo->Image()->AbsoluteURL : null,
            'EventTitle' => $photo->Parent()->exists() ? $photo->Parent()->Title : 'Event',
            'PhotoDate' => $photo->dbObject('Date')->Format('dd.MM.yyyy HH:mm'),
        ];
    }
}
