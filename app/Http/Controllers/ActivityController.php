<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ActivityController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("auth:api");
    }


    public function index(Request $request, String $employee_code)
    {


        /** @var \App\Models\Employee */
        $employee = Employee::manager()
            ->whereCode($employee_code)
            ->first();

        if (!$employee) {
            abort(404, "Manager not found");
        }

        return $employee->actions()
            ->orderByDesc("id")
            ->paginate($request->per_page ?? 10);
    }



    public function myActivities(Request $request)
    {
        /** @var \App\Models\Employee */
        $manager = Auth::guard("api")->user();

        return $manager->actions()
            ->orderByDesc("id")
            ->paginate($request->per_page ?? 10);
    }
}
