<?php

namespace Tests\Feature;

use App\Models\SolarProject;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Contact;

class ContactsTest extends TestCase
{

    /*
    rollback database after each test to get to original state (make test cases work exactly the same on eah iteration)
    note that both below trait and the lower setup function which calls seed() is required for this.
    */
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        // seed the database
        //$this->artisan('db:seed');
        // alternatively you can call
        $this->seed();
    }

    /**
     * Test contact Last Name is being updated when full update(PUT) is requested on a contact record.
     *
     * @return void
     */
    public function testCanUpdateContactLastName()
    {

        $firstContact=Contact::first();
        $id = $firstContact->id;
        $uuid = $firstContact->uuid;
        $firstName = $firstContact->first_name;
        $lastName = $firstContact->last_name;
        $email = $firstContact->email;

        $modifiedLastName="Smith";
        $this->assertNotEquals($lastName, $modifiedLastName, "please choose another lastname than $lastName for this test.");

        $response = $this->json('PUT', "/api/contacts/$uuid", [
            'id' => $id,
            'uuid' => $uuid,
            'first_name' => $firstName,
            'last_name' => $modifiedLastName,
            'email' => $email
        ]);

        $firstContact=Contact::first();

        $response->assertStatus(200);
//todo: this didn't work maybe because of updated_at, find exact reason and substitude with below workaround
//        $this->assertDatabaseHas('contacts', [
//            'id' => $id,
//            'uuid'=>$uuid,
//            'first_name'=>$firstName,
//            'last_name'=>$lastName,
//            'email' => $email
//        ]);

        $updatedFirstContact=Contact::first();
        $this->assertEquals($updatedFirstContact->last_name, $modifiedLastName, "last_name field is not updated with $modifiedLastName as instructed!");


    }
}
