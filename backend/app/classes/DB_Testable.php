<?php


namespace App\classes;


use Illuminate\Support\Facades\DB;

class DB_Testable extends DB
{
    public static function transaction(\Closure $callback)
    {
        //$result = "false";
        $result = ["data" => ["success" => false, "message" => "an error occurred when applying database transaction!"]];
        self::beginTransaction();

        // We'll simply execute the given callback within a try / catch block
        // and if we catch any exception we can rollback the transaction
        // so that none of the changes are persisted to the database.
        try {
            $result = $callback();

            self::commit();
        }

            // If we catch an exception, we will roll back so nothing gets messed
            // up in the database. Then we'll re-throw the exception so it can
            // be handled how the developer sees fit for their applications.
        catch (\Exception $e) {
            self::rollBack();

            $result["data"]["message"] = $e->getMessage();
            //avoid cluttering phpunit output by exception details
            //throw $e;

            //todo: find a way to send 422 error
            //return $controller->response()->json($result, 422);
        }

        return $result;
    }

}
