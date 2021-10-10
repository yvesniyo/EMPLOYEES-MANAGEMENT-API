<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomExcelImportException;
use App\Imports\EmployeesImport;
use App\Models\Employee;
use App\Services\CodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }



    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                "name" => "string|required",
                "email" => "email|required|unique:employee,email",
                "phone" => "phone|required|unique:employee,phone",
                "national_id" => "national_id|required|unique:employee,national_id",
                "position" => [Rule::in(["MANAGER", "DEVELOPER", "DESIGNER", "TESTER", "DEVOPS"]), "required"],
                "status" => [Rule::in(["ACTIVE", "INACTIVE"]), "required"],
                "dob" => "date|required",
            ]
        );

        $employeeDetails = $request->all();

        if (!isOver18($employeeDetails["dob"])) {
            return Response::json([
                "error" => "Employee Date of birth should be over 18",
                "status" => 422,
            ], 422);
        }


        $employeeDetails["code"] = CodeGenerator::EMPLOYEE();

        while (Employee::whereCode($employeeDetails["code"])->exists()) {
            $employeeDetails["code"] = CodeGenerator::EMPLOYEE();
        }


        /** @var \App\Models\Employee */
        $employee = Employee::create($employeeDetails);


        if ($employee) {
            log_activity(auth("api")->user(), "Created an employee", $employee);

            return Response::json([
                "message" => $employee->name . " successfuly created",
                "status" => 200,
            ]);
        }

        return Response::json([
            "error" => "Failed to create user due to ",
            "status" => 422,
        ], 422);
    }



    public function update(Request $request, string $employee_code)
    {
        $this->validate(
            $request,
            [
                "name" => "string",
                "email" => "email",
                "phone" => "phone",
                "national_id" => "national_id",
                "position" => Rule::in(["MANAGER", "DEVELOPER", "DESIGNER", "TESTER", "DEVOPS"]),
                "status" => Rule::in(["ACTIVE", "INACTIVE"]),
                "dob" => "date",
            ]
        );

        $employeeDetails = $request->all();

        if (!isOver18($employeeDetails["dob"])) {
            return Response::json([
                "error" => "Employee Date of birth should be over 18",
                "status" => 422,
            ], 422);
        }

        /** @var \App\Models\Employee */
        $employee = Employee::whereCode($employee_code)->first();

        if (!$employee) {
            return Response::json([
                "error" => "Couldn't find the employee",
                "status" => 404,
            ], 404);
        }

        $employee->update($employeeDetails);


        if ($employee) {

            log_activity(auth("api")->user(), "Updated an employee", $employee);

            return Response::json([
                "message" => $employee->name . " successfuly updated",
                "status" => 200,
            ]);
        }

        return Response::json([
            "error" => "Failed to update user due to ",
            "status" => 500,
        ], 500);
    }



    public function suspend(Request $request, string $employee_code)
    {
        /** @var \App\Models\Employee */
        $employee = Employee::whereCode($employee_code)->first();

        $employee->status = "INACTIVE";

        if (!$employee->save()) {
            return Response::json([
                "error" => "Failed to suspend user",
                "status" => 500,
            ], 500);
        }


        log_activity(auth("api")->user(), "Suspended an employee", $employee);

        return Response::json([
            "message" => $employee->name . " successfuly suspended",
            "status" => 200,
        ]);
    }


    public function activate(Request $request, string $employee_code)
    {
        /** @var \App\Models\Employee */
        $employee = Employee::whereCode($employee_code)->first();

        $employee->status = "ACTIVE";

        if (!$employee->save()) {
            return Response::json([
                "error" => "Failed to activate user",
                "status" => 500,
            ], 500);
        }


        log_activity(auth("api")->user(), "Activated an employee", $employee);
        return Response::json([
            "message" => $employee->name . " successfuly activated",
            "status" => 200,
        ]);
    }

    public function delete(Request $request, string $employee_code)
    {
        /** @var \App\Models\Employee */
        $employee = Employee::whereCode($employee_code)->first();

        if (!$employee->delete()) {
            return Response::json([
                "error" => "Failed to delete user",
                "status" => 500,
            ], 500);
        }

        log_activity(auth("api")->user(), "Deleted an employee", $employee);

        return Response::json([
            "message" => $employee->name . " successfuly deleted",
            "status" => 200,
        ]);
    }



    public function search(Request $request)
    {

        $employees = Employee::when($request->position, function ($q) {
            $q->wherePosition(request()->position);
        })->when($request->name, function ($q) {
            $q->where("name", "LIKE", request()->name . "%");
        })->when($request->email, function ($q) {
            $q->where("email", "LIKE", request()->email . "%");
        })->when($request->phone, function ($q) {
            $q->where("phone", request()->phone . "%");
        })->when($request->code, function ($q) {
            $q->whereCode(request()->code);
        })->paginate($request->per_page ?? 15);

        if ($employees) {
            return $employees;
        }

        return Response::json([
            "error" => "Failed to delete user",
            "status" => 500,
        ], 500);
    }



    public function import(Request $request)
    {

        $fails = $success = [];

        try {
            Excel::import(
                new EmployeesImport,
                $request
                    ->file('file')
                    ->store('temp')
            );
        } catch (CustomExcelImportException $th) {
            $fails = $th->failures();
            $success = $th->successes();
        }


        if (count($fails) > 0) {
            return Response::json([
                "errors" => $fails,
                "status" => 422,
            ], 422);
        }

        return Response::json([
            "message" => "successfuly imported " . count($success) . " Employeed",
            "status" => 200,
        ], 200);
    }
}
