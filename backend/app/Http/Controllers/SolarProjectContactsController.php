<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Contact;
use App\Models\SolarProject;
use App\Http\Resources\ContactCollection;

class SolarProjectContactsController extends Controller
{
    /** Show all the Contacts which are attached to a SolarProject. */
    public function index(SolarProject $solar_project)
    {
        return new ContactCollection($solar_project->contacts()->paginate());
    }

    /**
     * Update the contacts that are related to this SolarProject.
     * This is intended to be similar to JSON:API's relationship update operation.
     * Note that we only have a PUT here, not a PATCH, as you must update the entire
     * relationship by passing all Contact IDs that should be related.
     * See tests for examples of this.
     */
    public function update(Request $request, SolarProject $solar_project)
    {
        $newContactIDs = $this->parseContactIDsFromObjectArray($request->input('data'));

        // See https://laravel.com/docs/6.x/eloquent-relationships#updating-many-to-many-relationships
        $solar_project->contacts()->sync($newContactIDs);

        return new ContactCollection($solar_project->contacts()->paginate());
    }

    /**
     * The input is expected to be e.g. [['type' => 'contacts', 'id' => 'blah']]
     * That case will return collect([1]), assuming the contact with id 1 has uuid 'blah'.
     */
    protected function parseContactIDsFromObjectArray($objects)
    {
        $newContacts = collect([]);
        foreach ($objects as $obj) {
            if ($obj['type'] !== 'contacts') {
                throw new \Exception('Related items must be of type "contacts"');
            }
            $contactId = Contact::where('uuid', $obj['id'])->firstOrFail()->id;
            $newContacts->push($contactId);
        }
        return $newContacts;
    }
}
