<?php
namespace App\Forms;

use App\Import\PersonCsvBulkLoader;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\GridField\AbstractGridFieldComponent;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\Security\SecurityToken;

/**
 * Adds a "CSV importieren" button/page to a Persons GridField, scoped to a
 * specific Event (so imported rows are automatically attached to it).
 *
 * GridFieldImportButton (core) needs a standalone Form+Controller wired up
 * for its modal, which is awkward for a relation nested this deep in the
 * CMS. This component instead uses the GridField's own URL handling
 * (like GridFieldExportButton does) to serve a minimal upload page.
 */
class GridFieldPersonImportButton extends AbstractGridFieldComponent implements GridField_HTMLProvider, GridField_URLHandler
{
    protected $targetFragment;

    protected $eventID;

    public function __construct($eventID, $targetFragment = 'buttons-before-left')
    {
        $this->eventID = $eventID;
        $this->targetFragment = $targetFragment;
    }

    public function getHTMLFragments($gridField)
    {
        $link = Convert::raw2att($gridField->Link('personimport'));
        $html = '<a href="' . $link . '" class="btn btn-secondary font-icon-upload">Personen CSV importieren</a>';
        $html .= $this->renderFlashMessage();

        return [$this->targetFragment => $html];
    }

    protected function renderFlashMessage()
    {
        $session = Controller::curr()->getRequest()->getSession();
        $message = $session->get('PersonImportMessage');

        if (!$message) {
            return '';
        }

        $type = $session->get('PersonImportMessageType') === 'bad' ? 'danger' : 'success';
        $session->clear('PersonImportMessage');
        $session->clear('PersonImportMessageType');

        return '<div class="alert alert-' . $type . '" style="margin-top: 8px;">' . Convert::raw2xml($message) . '</div>';
    }

    public function getURLHandlers($gridField)
    {
        return ['personimport' => 'handlePersonImport'];
    }

    public function handlePersonImport(GridField $gridField, HTTPRequest $request)
    {
        $controller = Controller::curr();

        if ($request->isPOST()) {
            return $this->processImport($gridField, $request, $controller);
        }

        return $this->renderUploadForm($gridField, $controller);
    }

    protected function getEventEditLink(GridField $gridField, Controller $fallbackController)
    {
        $form = $gridField->getForm();
        $formController = $form ? $form->getController() : null;

        if ($formController && $formController->hasMethod('Link')) {
            return $formController->Link();
        }

        return $fallbackController->getRequest()->getURL();
    }

    protected function renderUploadForm(GridField $gridField, Controller $controller)
    {
        $formURL = Convert::raw2att($gridField->Link('personimport'));
        $backURL = Convert::raw2att($this->getEventEditLink($gridField, $controller));
        $tokenName = Convert::raw2att(SecurityToken::inst()->getName());
        $tokenValue = Convert::raw2att(SecurityToken::inst()->getValue());

        $body = <<<HTML
<div class="fill-height flexbox-area-grow p-4">
    <h2>Personen aus CSV importieren</h2>
    <p>Die CSV-Datei benötigt eine Kopfzeile mit den Spalten <code>FirstName</code> und <code>LastName</code>.
    Personen mit übereinstimmendem Vor- und Nachnamen in diesem Event werden aktualisiert statt dupliziert.</p>
    <form method="post" enctype="multipart/form-data" action="{$formURL}">
        <input type="hidden" name="{$tokenName}" value="{$tokenValue}" />
        <input type="hidden" name="BackURL" value="{$backURL}" />
        <div class="form-group">
            <input type="file" name="CsvFile" accept=".csv" required />
        </div>
        <button type="submit" class="btn btn-primary">Importieren</button>
        <a href="{$backURL}" class="btn btn-secondary">Abbrechen</a>
    </form>
</div>
HTML;

        return HTTPResponse::create($body);
    }

    protected function processImport(GridField $gridField, HTTPRequest $request, Controller $controller)
    {
        $backURL = $request->postVar('BackURL') ?: $this->getEventEditLink($gridField, $controller);
        $session = $request->getSession();

        if (!SecurityToken::inst()->checkRequest($request)) {
            $session->set('PersonImportMessage', 'Sicherheitsprüfung fehlgeschlagen, bitte erneut versuchen.');
            $session->set('PersonImportMessageType', 'bad');
            $session->save($request);
            return $controller->redirect($backURL);
        }

        if (empty($_FILES['CsvFile']['tmp_name']) || file_get_contents($_FILES['CsvFile']['tmp_name']) === '') {
            $session->set('PersonImportMessage', 'Bitte eine CSV-Datei auswählen.');
            $session->set('PersonImportMessageType', 'bad');
            $session->save($request);
            return $controller->redirect($backURL);
        }

        $loader = PersonCsvBulkLoader::create();
        $loader->eventID = $this->eventID;
        $results = $loader->load($_FILES['CsvFile']['tmp_name']);

        $session->set(
            'PersonImportMessage',
            sprintf(
                'Import abgeschlossen: %d neu, %d aktualisiert.',
                $results->CreatedCount(),
                $results->UpdatedCount()
            )
        );
        $session->set('PersonImportMessageType', 'good');

        $session->save($request);
        return $controller->redirect($backURL);
    }
}
