<?php

namespace App\Http\Controllers;

use App\Jobs\SendResetLinkToManagerJob;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;


class AuthController extends Controller
{



    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }



    public function login(Request $request)
    {
        $this->validate(
            $request,
            [
                "password" => "string|required",
                "email" => "email|required",
            ]
        );


        $manager = Employee::manager()
            ->whereEmail($request->email)
            ->first();

        if (!$manager) {
            return Response::json([
                "message" => "Wrong Username/Password",
                "status" => 401,
            ], 401);
        }

        if (!Hash::check($request->password, $manager->password)) {
            return Response::json([
                "message" => "Wrong Username/Password",
                "status" => 401,
            ], 401);
        }

        $token = $this->EmployeeGuard()->login($manager);

        if ($token) {
            log_activity($manager, "Logged In");
            return Response::json([
                "message" => "Login success",
                "data" => [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => $this->EmployeeGuard()->factory()->getTTL() * 60
                ],
                "status" => 200,
            ], 200);
        }

        return Response::json([
            "error" => "Unexpected error~",
            "status" => 500,
        ], 500);
    }


    public function me()
    {
        $user = $this->EmployeeGuard()->user();

        return Response::json([
            "message" => "success",
            "datas" => $user,
            "status" => 200,
        ], 200);
    }



    public function signup(Request $request)
    {
        $this->validate(
            $request,
            [
                "name" => "string|required",
                "email" => "email|required|unique:employee,email",
                "phone" => "phone|required|unique:employee,phone",
                "national_id" => "national_id|required|unique:employee,national_id",
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

        $employeeDetails["status"] = "ACTIVE";
        $employeeDetails["position"] = "MANAGER";

        /** @var \App\Models\Employee */
        $employee = Employee::create($employeeDetails);

        if ($employee) {
            log_activity($employee, "Signed Up");

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



    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            "reset_code" => "string|required",
            "password" => "string|min:6",
        ]);

        $employee = Employee::manager()
            ->whereResetCode($request->reset_code)
            ->whereDateTime("reset_code_expires_in", "<", Carbon::now())
            ->first();

        if (!$employee) {
            abort(404, "Invalid reset code or it is expired, try requesting new reset link");
        }


        $employee->password = Hash::make($request->password);

        if (!$employee->save()) {
            abort(500, "Unexpected error, try again later");
        }

        return response("success");
    }


    public function sendResetLink(Request $request)
    {
        $this->validate($request, [
            "email" => "email|required",
        ]);

        /** @var \App\Models\Employee */
        $employee = Employee::manager()
            ->whereResetCode($request->reset_code)

            ->first();

        if (!$employee) {
            return Response::json([
                "error" => "There is no account registed with that email",
                "status" => 404,
            ], 404);
        }

        $uuid = Uuid::uuid4();

        $employee->reset_code = $uuid;
        $employee->reset_code_expires_in = Carbon::now()->addHours(2);

        if (!$employee->save()) {

            return Response::json([
                "error" => "Failed to reset password",
                "status" => 500,
            ], 500);
        }

        dispatch(new SendResetLinkToManagerJob($employee));


        log_activity($employee, "Requested Reset Password Link");

        return Response::json([
            "message" => "Reset link was sent to your " . $employee->email . " Account",
            "status" => 200,
        ], 200);
    }



    public function logout()
    {
        $user = $this->EmployeeGuard()->user();

        if (!$this->EmployeeGuard()->logout()) {
            return response("Unexpected error", 500);
        }

        log_activity($user, "Logout");

        return response("Logout success");
    }




    public function EmployeeGuard()
    {
        return Auth::guard("api");
    }
}
