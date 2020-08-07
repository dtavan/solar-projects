<?php

namespace App\Http\Controllers;

use App\classes\BulkDeleteException;
use App\classes\DB_Testable;
use Illuminate\Http\Request;

use App\Http\Resources\SolarProjectResource;
use App\Http\Resources\SolarProjectCollection;
use App\Models\SolarProject;
use Illuminate\Support\Facades\DB;

// bulk delete message defaults
define("BULK_DELETE_REJECTED_MSG", "one of the ids deos not exist. the whole bulk delete is rejected!");
define("BULK_DELETE_INCOMPLETE_PARAMS_MSG", "Please provide bulk destroy method. It can be one of findOrFail, whereIn or transaction keywords.");

class SolarProjectsController extends Controller
{

    public function index()
    {
        return new SolarProjectCollection(SolarProject::paginate());
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'system_size' => 'numeric|present|nullable',
            'title' => 'string|required',
            'site_latitude' => 'numeric|required',
            'site_longitude' => 'numeric|required',
        ]);

        $solarProject = SolarProject::create($data);

        return new SolarProjectResource($solarProject);
    }

    public function show(SolarProject $solar_project)
    {
        return new SolarProjectResource($solar_project);
    }

    public function update(Request $request, SolarProject $solar_project)
    {
        if ($request->isMethod('patch')) {
            $data = $this->validate($request, [
                'system_size' => 'numeric|nullable',
                'title' => 'string',
                'system_details' => 'string',
                'site_latitude' => 'numeric',
                'site_longitude' => 'numeric',
            ]);
        } else {
            $data = $this->validate($request, [
                'system_size' => 'numeric|present|nullable',
                'title' => 'string|required',
                'system_details' => 'string',
                'site_latitude' => 'numeric|required',
                'site_longitude' => 'numeric|required',
            ]);
        }

        $solar_project->update($data);
        // file_put_contents($data)

        return new SolarProjectResource($solar_project);
    }

    public function destroy(SolarProject $solar_project)
    {
        $solar_project->delete();

        return response()->noContent();
    }


    /**
     * destroyMany (bulk delete projects)
     * @param $method string
     * @param $ids comma separated string
     * @return string
     *
     * Bulk deletes projects in one of three methods defined below this function
     * Note: this implementation is not using dependency injection as (I did not find a way in laravel to inject array of a class
     *  therefore it is not fully unit testable in isolation to its dependency (SolarProject class which is used in $destroyMethod() function)
     */
    public function destroyMany(Request $request, $method): array
    {
        //when using get for bypass DELETE  verb and use temporary GET (using commented out route in api.php) rewrite as destroyMany($method, $ids){
        $ids = $request->input("ids");

        //when using temp GET: $projectIds = explode(",", $ids);
        $projectIds = $ids;

        $destroyMethod = $method;

        $availableMethods = ["transaction", "findOrFail", "whereIn"];

        if (!in_array($destroyMethod, $availableMethods)) {

            throw(new \Exception(BULK_DELETE_INCOMPLETE_PARAMS_MSG));
        } else {
            $destroyMethod = "destroyManyUsing" . ucfirst($destroyMethod);
            return $this->$destroyMethod($projectIds);
        }

        return  ["data" => ["success" => true, "message" => ""]];
    }

    /**
     * destroyManyUsingTransaction   (bulk delete projects when method passed as "transaction")
     * @param array $projectIds
     * @return void
     *
     * It uses transaction to call one ORM database method per calling ajax request
     * Although more readable, it is not performance optimized because of two reasons:
     *  1- destroy internally calls delete many times each of which creates a separate sql query
     *  2- rolling back transaction might be costly from database engine
     *  The benefit of using destroy is that events are tiggered per each delete and can be hooked to
     *  some user defined handlers
     */
    private function destroyManyUsingTransaction($projectIds): array
    {
        return DB_Testable::transaction(function () use ($projectIds) {

            $countRequested = count($projectIds);
            $countDestroyed = SolarProject::destroy($projectIds);

            if ($countDestroyed < $countRequested) {
                //throw exception to rollback
                throw new BulkDeleteException(BULK_DELETE_REJECTED_MSG);
            }

            return ["data" => ["success" => true, "message" => ""]];//"true"
        });

    }

    /**
     * destroyManyUsingFindOrFail (bulk delete projects when method passed as "findOrFail")
     * @param array $projectIds
     * @return void
     *
     * It uses try catch mechanism with find and findOrFail to first check existance of all ids before proceeding to delete them
     * Since it uses destroy and internally, this function calls delete per each id, it is not db performance efficient.
     */
    private function destroyManyUsingFindOrFail($projectIds)
    {
        /*
          Note: the implementation is not using dependency injection as I did not find a way in laravel to inject array of a class
         */

        try {
            /*
             given n queries, this creates 2n database ORM statements but since destroy deletes models internally by using a loop
             this is not performance optimized (many delete sqls will be generated), please see the destroyManyVersion2 as more performant way to do bulk deletes
            */
            SolarProject::findOrFail($projectIds);
            SolarProject::destroy($projectIds);
        } catch (ModelNotFoundException $ex) {

            return BULK_DELETE_REJECTED_MSG;
        }

        return  ["data" => ["success" => true, "message" => ""]];
    }


    /**
     * destroyManyUsingFindOrFail  (bulk delete projects when method passed as "whereIn")
     * @param array $projectIds
     *
     * This method uses pre-emptive counting the project ids existing in database and uses eloquent query
     * object for bulk deleting them if their count in db matches their count in the request.
     * It benefits from using Eloquent wisdom to do this in one sql which is more performance optimized
     */
    private function destroyManyUsingWhereIn($projectIds)
    {

        $query = SolarProject::whereIn("id", $projectIds);
        $countRequested = count($projectIds);
        $countInDb = $query->count();

        if ($countInDb < $countRequested) {

            return BULK_DELETE_REJECTED_MSG;
        }
        // delete all $projectIds in one go (one sql will be generated)
        $query->delete();

        return ["data" => ["success" => true, "message" => ""]];
        // keep below for future debugging
        //DB::enableQueryLog();
        //dd(DB::getQueryLog());

    }


}
