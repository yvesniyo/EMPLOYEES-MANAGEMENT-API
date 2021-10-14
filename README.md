# EMPLOYEES MANAGEMENT API

This is api is made in Laravel Lumen micro framework. it containes a Restful API and database for an employee management system.

## API Documentation

visit here for documentation [documentation link](https://documenter.getpostman.com/view/7166199/UV5UidcU)

## Install and Run

1. Clone this repository
2. Open folder in command and type `composer install`
3. Copy `.env.example` to `.env`
4. In `.env` change `APP_NAME` to company name
5. In `.env` set `APP_KEY` to any 32 characters secrete key
6. In `.env` set `DB_DATABASE`,`DB_USERNAME`,`DB_PASSWORD`
7. run `php artisan migrate` , if you want to seed datas also run `php artisan db:seed`
8. run `php artisan jwt:secret`
9. run `php -S localhost:8088 -t public`
10. run `php artisan queue:listen` in different terminal, if you want to send emails.
11. emails needs you to setup .env `MAIL_USERNAME` and `MAIL_PASSWORD`, you can use gmail account but make sure it is enabled for unsecure logins.

Note: Make sure you have database engine.

And while testing the Import feature you can use the file in `.\public\employee.xlsx`

## What was done

### Employees Management:

-   A manager should be able to create an employee bypassing the mentioned below details to the API except (Code and CreatedDate which are generated automatically). After creating the employee, the system should send a communication email to the uploaded employee informing him/her that they joined the company with the company name.

    -   A manager should be able to edit an employee record.
    -   A manager should be able to suspend an employee.
    -   A manager should be able to activate an employee.
    -   A manager should be able to delete an employee.

### Search Feature

-   A manager should be able to search for an employee based on his position, name, email, phone number or code.

### Authentication

-   A manager should be able to signup and confirm his email after registration.
    The Manager should signup by providing the same mentioned below details except (Code and CreatedDate which are generated automatically). The position property should be generated automatically as MANAGER upon signup.

-   The manager should also be able to log in and receive a token, preferably JWT(https://jwt.io) for authentication after successful login; Reset the password by providing an email address to which a password reset link will be sent, afterward, the user should be able to log in with the new password.

-   Your project should be on an online repository (Gitlab, Github,...)

### 👷🏽‍♀️Best Practices

-   Document your API Postman
-   Apply validation (phone number must be a Rwandan number, and email should be validated).
-   The national id should be 16 numbers.
-   Not allowing the registration of an employee who is below 18 years of age.
-   National Id, Email, Code and phone number should be unique.
-   The system should throw an exception if any error occurs.
-   Properly log your application.
-   Do not hard code any sensitive data (eg: env variables).

### ✨Bonus

-   Upload Excel Sheet

    -   A manager should be able to upload an excel sheet containing a list of his/her employees (employee name, national id number, phone number, email, date of birth, status and position). After uploading the employee list, the system should send a communication email to all the uploaded employees informing them they just joined a company with the company name.

-   System Logs
    -   The system should record all the manager’s activities (login, logout, password reset, profile update etc…) in order to comply with external audit requirements.
