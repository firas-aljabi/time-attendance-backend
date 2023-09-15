<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ConversationContoller;
use App\Http\Controllers\Api\DepositController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\ShiftController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['middleware' => 'api'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('verify_code', [AuthController::class, 'verify_code']);
});


Route::group(['middleware' => 'auth:api'], function () {

    // -- Admin --//
    Route::post('create_employee', [AdminController::class, 'store']);
    Route::post('create_hr', [AdminController::class, 'store_hr']);
    Route::post('create_admin', [AdminController::class, 'store_admin']);
    Route::post('update_employee', [AdminController::class, 'update_employee']);
    Route::post('admin_update_employee', [AdminController::class, 'admin_update_employee']);
    Route::delete('employee/{id}', [AdminController::class, 'destroyEmployee']);
    Route::post('update_working_hours', [AdminController::class, 'update_working_hours']);
    Route::post('reward_adversaries_salary', [AdminController::class, 'reward_adversaries_salary']);
    Route::get('get_dashboard_data', [AdminController::class, 'getDashboardData']);
    Route::get('get_employees_list', [AdminController::class, 'getEmployeesList']);
    Route::get('get_employees_dismissed_list', [AdminController::class, 'getEmployeesDismissedList']);
    Route::get('employees_salaries', [AdminController::class, 'employees_salaries']);
    Route::post('update_salary', [AdminController::class, 'update_salary']);
    Route::post('update_employee_shift', [ShiftController::class, 'update_employee_shift']);
    Route::get('employees_attendances', [AdminController::class, 'employees_attendances']);
    Route::get('get_contract_expiration', [AdminController::class, 'get_contract_expiration']);
    Route::get('get_employee/{id}', [AdminController::class, 'getEmployee']);
    Route::get('remining_vacation_hour_employee/{id}', [AdminController::class, 'remining_vacation_hour_employee']);
    Route::get('profile', [AdminController::class, 'profile']);
    Route::post('check_in_attendance', [AdminController::class, 'check_in_attendance']);
    Route::post('check_out_attendance', [AdminController::class, 'check_out_attendance']);
    Route::post('cancle_employees_contract', [AdminController::class, 'cancle_employees_contract']);
    Route::post('renewal_employment_contract', [AdminController::class, 'renewal_employment_contract']);
    Route::get('list_of_nationalities', [AdminController::class, 'list_of_nationalities']);
    Route::post('update_employee_permission_time', [AdminController::class, 'update_employee_permission_time']);
    Route::get('leave_calendar', [AdminController::class, 'leave_calendar']);
    Route::get('attendance_overview', [AdminController::class, 'attendance_overview']);
    Route::post('check_location', [AdminController::class, 'check_location']);

    // -- Posts -- //
    Route::post('create_post', [PostController::class, 'store']);
    Route::get('get_posts_list', [PostController::class, 'getPostsList']);
    Route::get('get_my_posts', [PostController::class, 'getMyPosts']);
    Route::post('add_comment', [PostController::class, 'addComment']);
    Route::post('add_like', [PostController::class, 'addLike']);
    Route::post('add_like_comment', [PostController::class, 'add_like_comment']);
    Route::post('share_post', [PostController::class, 'sharePost']);
    Route::delete('post/{id}', [PostController::class, 'destroyPost']);
    Route::delete('comment/{id}', [PostController::class, 'destroyComment']);

    // -- Requests -- //
    Route::post('add_vacation_request', [RequestController::class, 'add_vacation_request']);
    Route::post('add_justify_request', [RequestController::class, 'add_justify_request']);
    Route::post('add_retirement_request', [RequestController::class, 'add_retirement_request']);
    Route::post('add_resignation_request', [RequestController::class, 'add_resignation_request']);
    Route::get('request/{id}', [RequestController::class, 'show']);
    Route::put('approve_request/{id}', [RequestController::class, 'approve_request']);
    Route::post('reject_request', [RequestController::class, 'reject_request']);
    Route::get('my_requests', [RequestController::class, 'my_requests']);
    Route::get('my_approved_vacations_requests', [RequestController::class, 'my_approved_vacations_requests']);
    Route::get('vacation_requests', [RequestController::class, 'vacation_requests']);
    Route::get('justify_requests', [RequestController::class, 'justify_requests']);
    Route::get('retirement_requests', [RequestController::class, 'retirement_requests']);
    Route::get('resignation_requests', [RequestController::class, 'resignation_requests']);
    Route::get('my_monthly_shift', [RequestController::class, 'getMonthlyData']);
    Route::get('all_requests', [RequestController::class, 'all_requests']);

    // -- Alerts -- //
    Route::post('add_alert', [AlertController::class, 'store']);
    Route::get('get_my_alert', [AlertController::class, 'getMyAlert']);

    // -- Chat -- //
    Route::get('get_hrs_list', [ConversationContoller::class, 'getHrsList']);
    Route::get('conversations', [ConversationContoller::class, 'index']);
    Route::get('conversations/{id}/messages', [ConversationContoller::class, 'show_conversation_messages']);
    Route::post('messages', [ConversationContoller::class, 'store']);

    // -- Notifications  -- //
    Route::get('/notification', [NotificationController::class, 'index']);

    // -- Holiday -- //
    Route::post('create_weekly_holiday', [HolidayController::class, 'create_weekly_holiday']);
    Route::post('create_annual_holiday', [HolidayController::class, 'create_annual_holiday']);
    Route::post('update_annual_holiday', [HolidayController::class, 'update_annual_holiday']);
    Route::get('list_of_holidays', [HolidayController::class, 'list_of_holidays']);
    Route::delete('holiday/{id}', [HolidayController::class, 'destroy_holiday']);

    // -- Deposit -- //
    Route::post('craete_deposit', [DepositController::class, 'store']);
    Route::put('approve_deposit/{id}', [DepositController::class, 'approve_deposit']);
    Route::post('reject_deposit', [DepositController::class, 'reject_deposit']);
    Route::put('clearance_request/{id}', [DepositController::class, 'clearance_request']);
    Route::put('approve_clearance_request/{id}', [DepositController::class, 'approve_clearance_request']);
    Route::post('reject_clearance_request', [DepositController::class, 'reject_clearance_request']);
    Route::get('my_deposits', [DepositController::class, 'my_deposits']); // For Employee
    Route::get('list_of_deposits', [DepositController::class, 'list_of_deposits']); //For Admin
    Route::get('list_of_clearance_deposits', [DepositController::class, 'list_of_clearance_deposits']); //For Admin
    Route::get('my_approved_deposits', [DepositController::class, 'my_approved_deposits']);


    // -- Company  -- //
    Route::post('create_company', [CompanyController::class, 'store']);
    Route::post('add_commercial_record', [CompanyController::class, 'add_commercial_record']);
    Route::get('company/{id}', [CompanyController::class, 'show']);
    Route::post('update_comapny', [CompanyController::class, 'update_comapny']);
    Route::post('update_commercial_record', [CompanyController::class, 'update_commercial_record']);
    Route::get('show_percenatge_company', [CompanyController::class, 'show_percenatge_company']);
    Route::put('update_percentage', [CompanyController::class, 'update_percentage']);
});
