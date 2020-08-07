<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Resources\ContactResource;
use App\Http\Resources\ContactCollection;
use App\Models\Contact;

class ContactsController extends Controller
{
    /** List all Contacts. */
    public function index(Request $request)
    {
        //extending index action to also return all contacts (is page in not supplied in query string)
        $page = $request->input("page");
        if ($page === null) {
            return new ContactCollection(Contact::all());
        } else {
            return new ContactCollection(Contact::paginate());
        }
    }

    /** Create a new Contact from the given parameters. */
    public function store(Request $request)
    {
        // $data will contain ONLY the validated fields, which means we don't
        // have to worry about extra data sneaking from the request body into the
        // call to create() below.
        $data = $this->validate($request, [
            'first_name' => 'string|present|max:255',
            'last_name' => 'string|present|max:255',
            'email' => 'string|present|max:255',
        ]);

        $contact = Contact::create($data);

        return new ContactResource($contact);
    }

    /** Return a single Contact. */
    public function show(Contact $contact)
    {
        return new ContactResource($contact);
    }

    /** Update a Contact, either wholly via PUT or partially via PATCH. */
    public function update(Request $request, Contact $contact)
    {
        if ($request->isMethod('patch')) {
            $data = $this->validate($request, [
                'first_name' => 'string|max:255',
                'last_name' => 'string|max:255',
                'email' => 'string|max:255',
            ]);
        } else {
            $data = $this->validate($request, [
                'first_name' => 'string|required|max:255',
                'last_name' => 'string|present|max:255',
                'email' => 'string|present|max:255',
            ]);
        }

        $contact->update($data);

        return new ContactResource($contact);
    }

    /** (Soft-)delete the Contact. */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->noContent();
    }
}
