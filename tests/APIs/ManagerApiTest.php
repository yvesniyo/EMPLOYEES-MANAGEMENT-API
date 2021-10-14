<?php

namespace APIs;

use ApiTestTrait;
use App\Models\Employee;
use Carbon\Carbon;
use TestCase;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;


class ManagerApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;


    /**
     * @test
     */
    public function test_signup_manager()
    {
        $employee = Employee::factory()->make()->toArray();

        $employee["dob"] = Carbon::now()
            ->subYears(mt_rand(18, 30))
            ->format("Y-m-d");

        $this->resp = $this->json(
            'POST',
            '/api/v1/manager/auth/signup',
            $employee
        )->seeJson([
            "message" => $employee["name"] . " successfuly created",
            "status" => 200,
        ]);
    }



    /**
     * @test
     */
    public function test_signup_under_age_manager()
    {
        $employee = Employee::factory()->make()->toArray();

        $employee["dob"] = Carbon::now()->subYears(15)->format("Y-m-d");

        $this->resp = $this->json(
            'POST',
            '/api/v1/manager/auth/signup',
            $employee
        )->seeJson([
            "error" => "Employee Date of birth should be over 18",
            "status" => 422,
        ]);
    }


    /**
     * @test
     */
    public function test_create_employee()
    {
        $employee = Employee::factory()->make()->toArray();

        $employee["dob"] = Carbon::now()
            ->subYears(mt_rand(18, 30))
            ->format("Y-m-d");

        $manager = Employee::manager()->active()->first();

        $this->actingAs($manager)
            ->json(
                'POST',
                '/api/v1/employee/store',
                $employee
            )->seeJson([
                "status" => 200,
            ]);
    }


    /**
     * @test
     */
    public function test_update_employee()
    {
        $employee_data = Employee::factory()->make()->toArray();

        $employee_data["dob"] = Carbon::now()
            ->subYears(mt_rand(18, 30))
            ->format("Y-m-d");

        $employee_update_data = collect($employee_data)->only(["name"])->toArray();

        $employee_to_update = Employee::all()->random();

        $manager = Employee::manager()
            ->active()
            ->first();


        $this->actingAs($manager)
            ->json(
                'PATCH',
                "/api/v1/employee/{$employee_to_update->code}/update",
                $employee_update_data
            )->seeJson([
                "status" => 200,
            ]);
    }
}
