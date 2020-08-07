<?php

namespace Tests\Feature;

use App\classes\BulkDeleteException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

use App\Models\SolarProject;

class SolarProjectsTest extends TestCase
{

    /*
     rollback database after each test to get to original state (make test cases work exactly the same on eah iteration)
     note that both below trait and the lower setup function which calls seed() is required for this.
     */
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        /* seed the database
         $this->artisan('db:seed');
         or use seed() as shortcut
        */
        $this->seed();
    }

    public function testIndex()
    {
        $response = $this->get('/api/solar_projects');

        $response->assertStatus(200)
            ->assertJson([
                'links' => [
                    'next' => url('/api/solar_projects') . '?page=2',
                    'prev' => null,
                ],
            ]);
    }

    public function testShow()
    {
        $id = SolarProject::first()->uuid;

        $response = $this->json('GET', "/api/solar_projects/$id");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'solar_projects',
                    'id' => $id,
                ],
            ]);
    }

    public function testIncompletePut()
    {
        $id = SolarProject::first()->uuid;

        $newTitle = 'wont work';
        $response = $this->json('PUT', "/api/solar_projects/$id", [
            'title' => $newTitle,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'title' => 'Validation Exception',
                'errors' => [
                    'system_size' => ['The system size field must be present.'],
                    'site_latitude' => ['The site latitude field is required.'],
                    'site_longitude' => ['The site longitude field is required.'],
                ],
            ]);
    }

    public function testPatch()
    {
        $id = SolarProject::first()->uuid;

        $newTitle = 'test title ' . now()->toAtomString();
        $response = $this->json('PATCH', "/api/solar_projects/$id", [
            'title' => $newTitle,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'attributes' => [
                        'title' => $newTitle,
                    ],
                ],
            ]);
    }


    /**
     *  test bulk delete projects (using transaction method)
     *  here we test if randomly grouping all existing project ids into groups and deleting them through our bulk delete api passes
     *
     * to run this single test case use:
     *  ./backend/vendor/bin/phpunit --filter testCanBulkDeleteUsingTransaction ./tests/Feature/SolarProjectsTest.php
     */
    public function testCanBulkDeleteUsingTransaction()
    {
        //choose method of bulk delete (should be either one of ["transaction", "findOrFail",  "whereIn"]
        $method = "transaction";

        // get all existing ids
        $allIds = SolarProject::get(["id"])->pluck("id")->toArray();

        // simulate non sequential sampling by shuffling ids
        shuffle($allIds);

        // form random chunks sampled from array of all ids
        $maxSize = count($allIds);
        $chunkSize = rand(1, $maxSize);
        echo "\n\nNow deleting all projects in random chunks of size $chunkSize\n";
        $idChunks = array_chunk($allIds, $chunkSize);

        // iterate over chunks and bulk delete them via api and see if any error occurs
        foreach ($idChunks as $idChunk) {
            $ids = implode(",", $idChunk);
            echo($ids . "\n");

            // please note that DELETE http verb is used as we are deleting. we used delete body parameter "ids" to pass ids. method is
            // passed in query string for more readability (ex. when inspecting browser)
            $response = $this->json('DELETE', "/api/solar_projects/bulk/$method", [ "ids" => $idChunk ])//when using temp GET use /api/solar_projects/bulk/$method/$ids
                              ->assertStatus(200)
                                ->assertJson([
                                    'data' => [
                                            'success' => true,
                                            'message' => ''
                                    ],
                                ]);

            //$this->assertStringContainsString("true", $response->getContent(), "failed to trigger exception when bulk delete list of ids contains non existing id in database");
            $this->assertIdsMissingInDb($idChunk);


        }
    }

    /**
     * used by test cases to see if deleted ids are now missing
     */
    private function assertIdsMissingInDb($ids)
    {
        // todo: this did not work, find the problem in proper time
        //        foreach ($ids as $id) {
        //            $this->assertDatabaseMissing('solar_projects', [
        //                'id' => $id
        //            ]);
        //        }

        $countFound = SolarProject::whereIn("id", $ids)->get(["id"])->count();
        $this->assertTrue($countFound == 0);
    }

    /**
     * used by test cases to see if deleted ids are now missing
     */
    private function assertSomeOfIdsAreInDb($ids)
    {
        $countFound = SolarProject::whereIn("id", $ids)->get(["id"])->count();
        $this->assertFalse($countFound == 0);
    }

    /**
     * Test if edge case deleting ids of non existing projects triggers a failure
     *
     *  * to run this single test case use:
     *  ./backend/vendor/bin/phpunit --filter testBulkDeleteUsingTransactionCanFail ./tests/Feature/SolarProjectsTest.php
     */
    public function testBulkDeleteUsingTransactionCanFail()
    {
        /*
         Below Exception handling didn't work so I was forced to override DB::transaction as in app\classes\DB_Testable
         $this->expectException(BulkDeleteException::class);
         $this->expectException(\Exception::class);
        */

        $method = "transaction";//"findOrFail", "whereIn" //todo: write tests also for other available bulk delete methods and do a time comparison to choose most efficient one

        // take a sample
        $idsArray = [26, 27, 28];
        $ids = implode(",", $idsArray);

        $response = $this->json('DELETE', "/api/solar_projects/bulk/$method", [ "ids"=>$idsArray ]);// when using temp GET (for debugging) use /api/solar_projects/bulk/$method/$ids
        $this->assertStringContainsString("true", $response->getContent(), "failed to succeed when bulk delete list of db existing ids");
        $this->assertIdsMissingInDb($idsArray);

        // try to delete a sample list that contains previously deleted sample id(s)
        //note: 27 and 28 should already be deleted, so below iteration of api should give an error
        $idsArray = [27, 28, 29, 30];
        $ids = implode(",", $idsArray);

        $response = $this->json('DELETE', "/api/solar_projects/bulk/$method", [ "ids"=>$idsArray  ])// when using temp GET (for debugging) use /api/solar_projects/bulk/$method/$ids
                            ->assertStatus(200) //todo: send 422 header in DB_Testable as we want to catch it from front end and show an error to user, then rewrite this as ->assertStatus(422)
                            ->assertJson([
                                'data' => [
                                    'success' => false,
                                    'message' =>BULK_DELETE_REJECTED_MSG
                                ],
                            ]);
        //$this->assertStringContainsString("false", $response->getContent(), "failed to error when bulk delete list contains non db existing id(s)");
        $this->assertSomeOfIdsAreInDb($idsArray);

        //keep for future debuggings
        //$content=$response->assertStatus(200)->getContent();
        //dd($content);
    }
}
