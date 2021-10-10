<?php

namespace APIs;

use ApiTestTrait;
use App\Models\Employee;
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

        $this->resp = $this->json(
            'POST',
            '/api/v1/manager/auth/signup',
            $employee
        );

        $this->assertApiResponse($employee);
    }
}
