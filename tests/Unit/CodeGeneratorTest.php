<?php

namespace Unit;

use App\Models\Employee;
use App\Services\CodeGenerator;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;
use TestCase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;


class CodeGeneratorTest extends TestCase
{


    use
        WithoutMiddleware,
        DatabaseTransactions;

    /**
     * @test
     */
    public function test_employee_code_generated()
    {
        $code = CodeGenerator::EMPLOYEE();

        $this->assertNotNull($code);
        $this->assertStringContainsString("EMP", $code);

        assertEquals(strlen($code), 7);
    }


    public function test_employee_id_generated()
    {
        $id = CodeGenerator::NATIONAL_ID();

        $this->assertNotNull($id);

        $validator = Validator::make([
            "national_id" => $id
        ], [
            "national_id" => "national_id|required",
        ]);

        assertFalse($validator->fails(), "Invalid national id generated");
    }

    public function test_employee_rwanda_phone_generated()
    {
        $id = CodeGenerator::PHONE_NUMBER();

        $this->assertNotNull($id);

        $validator = Validator::make([
            "phone" => $id
        ], [
            "phone" => "phone|required",
        ]);

        assertFalse($validator->fails(), "Invalid Rwandan phone generated");
    }
}
