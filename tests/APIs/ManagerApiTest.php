<?php

namespace APIs;

use ApiTestTrait;
use App\Events\EmployeeCreatedEvent;
use App\Models\Employee;
use Carbon\Carbon;
use TestCase;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;


class ManagerApiTest extends TestCase
{
    use  WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_employee()
    {
        $this->expectsEvents(EmployeeCreatedEvent::class);

        $employee = Employee::factory()->make([
            "dob" => Carbon::now()
                ->subYears(mt_rand(18, 30))
                ->format("Y-m-d")
        ])->toArray();


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
