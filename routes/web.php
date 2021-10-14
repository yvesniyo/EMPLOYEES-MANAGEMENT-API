<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Events\EmployeeCreatedEvent;
use App\Mail\ManagerResetCodeMail;
use App\Mail\WelcomeEmployeeMail;
use App\Models\Employee;
use App\Services\CodeGenerator;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return config("app.name") . " - API";
});


$router->group(["prefix" => "/api/v1"], function () use ($router) {

    $router->get('/', function () use ($router) {
        return config("app.name") . " - API V1.0";
    });



    $router->group(["prefix" => "manager"], function () use ($router) {

        $router->group(["prefix" => "auth"], function () use ($router) {
            $router->post("login", ["uses" => "AuthController@login"]);
            $router->post("signup", ["uses" => "AuthController@signup"]);
            $router->get("logout", ["uses" => "AuthController@logout"]);
            $router->get("me", ["uses" => "AuthController@me"]);

            $router->post("requestResetLink", ["uses" => "AuthController@sendResetLink"]);
            $router->post("resetPassword/{reset_code}", ["as" => "manager.reset_password", "uses" => "AuthController@resetPassword"]);
        });

        $router->get("activities", ["uses" => "ActivityController@myActivities"]);
        $router->patch("profile/update", ["uses" => "ManagerProfileController@update"]);

        $router->get("/reset-link/{reset_code}", [
            "as" => "manager.reset_link",
            "uses" => "AuthController@viewResetPage",
        ]);
    });


    $router->group(["prefix" => "employee", "middleware" => "auth:api"], function () use ($router) {

        $router->post("/store", ["uses" => "EmployeeController@store"]);
        $router->post("/import", ["uses" => "EmployeeController@import"]);
        $router->patch("{employee_code}/update", ["uses" => "EmployeeController@update"]);
        $router->patch("{employee_code}/suspend", ["uses" => "EmployeeController@suspend"]);
        $router->patch("{employee_code}/activate", ["uses" => "EmployeeController@activate"]);
        $router->delete("{employee_code}/delete", ["uses" => "EmployeeController@delete"]);

        $router->get("{employee_code}/activities", ["uses" => "ActivityController@index"]);

        $router->get("/search", ["uses" => "EmployeeController@search"]);
    });
});
