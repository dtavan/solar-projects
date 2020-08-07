<?php

namespace Tests\Unit;

// use laravel TestCase to facilitate loading of database (instead of PHPUnit version)
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;

use App\Models\Contact;

class ContactModelTest extends TestCase
{
    function setUp():void
    {
        // bootstrap application before running a test case
        parent::setUp();
    }


    public function testNewContactHasUuid()
    {
        $model = Contact::make();
        $this->assertTrue(strlen($model->uuid) === 37);
    }
}
