<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;

class ManagerProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("api:auth");
    }



    public function update(Request $request)
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
        $employee = Auth::guard("api")->user();


        $employee->update($employeeDetails);


        if ($employee) {

            log_activity(auth("api")->user(), "Updated Profile");

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
}
