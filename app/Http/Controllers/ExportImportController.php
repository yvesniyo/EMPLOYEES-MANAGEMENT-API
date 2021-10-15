<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomExcelImportException;
use App\Exports\EmployeesExport;
use App\Imports\EmployeesImport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ExportImportController extends Controller
{


    public function importEmployees(Request $request)
    {

        $fails = $success = [];

        try {
            Excel::import(
                new EmployeesImport(),
                $_FILES["file"]["tmp_name"] //$request>file('file')->store('temp')
            );
        } catch (CustomExcelImportException $th) {
            $fails = $th->failures();
            $success = $th->successes();
        }


        if (count($fails) > 0) {
            return Response::json([
                "failed_rows" => $fails,
                "succeded_rows" => $success,
                "status" => 422,
            ], 422);
        }

        return Response::json([
            "message" => "successfuly imported " . count($success) . " Employeed",
            "succeded_rows" => $success,
            "status" => 200,
        ], 200);
    }


    public function exportEmployees(Request $request)
    {

        $this->validate($request, [
            "extenstion" => "in:csv,xlsx",
            "where" => "string",
            "columns" => "string",
        ]);

        $extenstion = $request->extenstion;

        if (!$extenstion) {
            $extenstion = ".csv";
        }
        if (!Str::contains($extenstion, ".")) {
            $extenstion = "." . $extenstion;
        }

        [$columns, $where] = employeesExportReqFilter($request);

        $export = new EmployeesExport(
            $columns,
            $where
        );

        $name = 'EmployesExportedAt_' . Carbon::now() . $extenstion;

        log_activity(Auth::guard("api")->user(), " Exported employees");


        return Excel::download($export, $name);
    }
}
