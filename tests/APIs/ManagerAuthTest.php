<?php

namespace APIs;

use App\Events\EmployeeCreatedEvent;
use App\Jobs\SendResetLinkToManagerJob;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use TestCase;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

class ManagerAuthTest extends TestCase
{


    use
        DatabaseTransactions;
    /**
     * @test
     */

    public function test_active_manager_login()
    {
        $manager = Employee::factory()->create([
            "status" => "ACTIVE",
            "position" => "MANAGER",
        ]);

        $credentials = $manager->only(["email"]);
        $credentials["password"] = "password";

        $this->json("POST", route("manager.login"), $credentials)
            ->seeJson([
                "status" => 200,
            ]);
    }

    public function test_suspended_manager_login()
    {
        $manager = Employee::factory()->create([
            "status" => "INACTIVE",
            "position" => "MANAGER",
        ]);

        $credentials = $manager->only(["email"]);
        $credentials["password"] = "password";

        $this->json("POST", route("manager.login"), $credentials)
            ->seeJson([
                "status" => 403,
            ]);
    }


    public function test_unknown_manager_login()
    {
        $manager = Employee::factory()->make([
            "status" => "INACTIVE",
            "position" => "MANAGER",
        ]);

        $credentials = $manager->only(["email"]);
        $credentials["password"] = "password";

        $this->json("POST", route("manager.login"), $credentials)
            ->seeJson([
                "status" => 401,
            ]);
    }


    /**
     * @test
     */
    public function test_signup_manager()
    {


        $this->expectsEvents(EmployeeCreatedEvent::class);

        $employee = Employee::factory()->make([
            "dob" => Carbon::now()
                ->subYears(mt_rand(18, 30))
                ->format("Y-m-d")
        ])->toArray();

        $this->json(
            'POST',
            route("manager.signup"),
            $employee
        )->seeJson([
            "status" => 200,
        ]);
    }


    /**
     * @test
     */
    public function test_signup_under_age_manager()
    {
        $employee = Employee::factory()->make([
            "dob" => Carbon::now()->subYears(15)->format("Y-m-d")
        ])->toArray();



        $this->json(
            'POST',
            route("manager.signup"),
            $employee
        )->seeJson([
            "error" => "Employee Date of birth should be over 18",
            "status" => 422,
        ]);
    }

    /**
     * @test
     */
    public function test_manager_logged_in_manager()
    {
        /** @var Authenticatable*/
        $employee = Employee::factory([
            "status" => "ACTIVE"
        ])->create();

        $this->actingAs($employee)->json(
            'GET',
            route("manager.me")
        )->seeJson([
            "status" => 200,
        ]);
    }


    public function test_manager_request_reset_link()
    {

        $this->expectsJobs(SendResetLinkToManagerJob::class);

        $manager = Employee::factory()->create([
            "position" => "MANAGER",
            "status" => "ACTIVE",
        ]);

        $this->json(
            "POST",
            route("manager.requestResetLink"),
            $manager->only("email")
        )->seeJson([
            "status" => 200
        ]);
    }

    public function test_manager_reset_password()
    {


        $manager = Employee::factory()->create([
            "position" => "MANAGER",
            "status" => "ACTIVE",
        ]);

        $this->json(
            "POST",
            route("manager.requestResetLink"),
            $manager->only("email")
        )->seeJson([
            "status" => 200
        ]);

        $manager->refresh();

        $data = Collection::make($manager->toArray())
            ->merge(["password" => "password"])
            ->only("reset_code", "password")
            ->toArray();

        $this->json(
            "POST",
            route("manager.reset_password", [
                "reset_code" => $data["reset_code"]
            ]),
            $data
        )->seeJson([
            "status" => 200
        ]);
    }
}
