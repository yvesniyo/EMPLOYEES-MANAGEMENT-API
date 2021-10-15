<?php

namespace App\Http\Controllers;

use App\Events\EmployeeCreatedEvent;
use App\Jobs\SendResetLinkToManagerJob;
use App\Models\Employee;
use App\Services\CodeGenerator;
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
        $this->middleware('auth:api', ['except' => ['login', 'sendResetLink', 'signup', 'resetPassword', 'viewResetPage']]);
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


        /** @var \App\Models\Employee */
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

        if (!$manager->isActive()) {
            return Response::json([
                "message" => "This account has been suspended!",
                "status" => 403,
            ], 403);
        }



        $token = $this->managerGuard()
            ->login($manager);

        if ($token) {
            log_activity($manager, "Logged In");
            return Response::json([
                "message" => "Login success",
                "data" => [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => $this->managerGuard()->factory()->getTTL() * 60
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
        $user = $this->managerGuard()->user();

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
                "email" => "email|required|unique:employees,email",
                "phone" => "phone|required|unique:employees,phone",
                "national_id" => "national_id|required|unique:employees,national_id",
                "dob" => "date|required",
                "password" => "string|min:6"
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
        $employeeDetails["password"] = Hash::make($request->password);

        $employeeDetails["code"] = CodeGenerator::EMPLOYEE();

        /** @var \App\Models\Employee */
        $employee = Employee::create($employeeDetails);

        if ($employee) {
            log_activity($employee, "Signed Up");
            event(new EmployeeCreatedEvent($employee));

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



    public function resetPassword(Request $request, string $reset_code)
    {
        $this->validate($request, [
            "password" => "string|min:6|required",
            "confirm_password" => "string|same:password"
        ]);




        $employee = Employee::manager()
            ->whereResetCode($reset_code)
            ->first();

        if (!$employee) {
            abort(404, "Invalid reset code or it is expired, try requesting new reset link");
        }

        $expires_in = Carbon::parse($employee->reset_code_expires_in);

        if ($expires_in->lt(Carbon::now())) {
            abort(404, "Invalid reset code or it is expired, try requesting new reset link");
        }


        $employee->password = Hash::make($request->password);
        $employee->reset_code = null;
        $employee->reset_code_expires_in = null;

        if (!$employee->save()) {
            abort(500, "Unexpected error, try again later");
        }

        return Response::json([
            "message" => "Password reset success",
            "status" => 200,
        ]);
    }


    public function sendResetLink(Request $request)
    {
        $this->validate($request, [
            "email" => "email|required",
        ]);

        /** @var \App\Models\Employee */
        $employee = Employee::manager()
            ->whereEmail($request->email)
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
        $user = $this->managerGuard()->user();

        $this->managerGuard()->logout();

        log_activity($user, "Logout");

        return Response::json([
            "message" => "Logout success",
            "status" => 200,
        ], 200);
    }



    public function viewResetPage(Request $request, string $reset_code)
    {
        $employee = Employee::manager()
            ->whereResetCode($reset_code)
            ->first();

        if (!$employee) {
            abort(404, "Invalid reset code or it is expired, try requesting new reset link");
        }

        $expires_in = Carbon::parse($employee->reset_code_expires_in);

        if ($expires_in->lt(Carbon::now())) {
            abort(404, "Invalid reset code or it is expired, try requesting new reset link");
        }

        return view("pages.reset_page")->with("reset_code", $reset_code);
    }

    /**
     * Get auth guard for manager
     *
     * @return Illuminate\Support\Facades\Auth
     */
    public function managerGuard()
    {
        return Auth::guard("api");
    }
}
