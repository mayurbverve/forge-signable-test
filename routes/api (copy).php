<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UsersController;
use App\Http\Controllers\api\CommonController;
use App\Http\Controllers\api\InterPreterController;
use App\Http\Controllers\api\CallController;
use App\Http\Controllers\api\PushNotificationController;
use App\Http\Controllers\api\PurposeController;
use App\Http\Controllers\api\DispositionController;
use App\Http\Controllers\api\LanguageController;
use App\Http\Controllers\api\ReasonController;
use App\Http\Controllers\api\InterpreterBreakController;
use App\Http\Controllers\api\TicketController;
use App\Http\Controllers\api\ReportController;
use App\Http\Controllers\api\DashboardController;

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

Route::group(['namespace' => 'api'], function() {
    Route::POST('login', [UsersController::class, 'login']);
    Route::POST('authenticate', [UsersController::class, 'authenticate']);
    Route::GET('city_list', [CommonController::class, 'city_list']);
    Route::GET('purposes', [CommonController::class, 'purposes_list']);
    Route::POST('dispositions', [CommonController::class, 'dispositions_list']);
    Route::GET('reasons', [CommonController::class, 'reasons_list']);
    Route::GET('regions', [CommonController::class, 'region_list']);
    Route::GET('miles', [CommonController::class, 'miles_list']);
    Route::POST('forget_password', [UsersController::class, 'forget_password']);
    Route::GET('link_expiry', [UsersController::class, 'link_expiry']);
    Route::POST('call/end_call_signable', [CallController::class,'end_call_signable']);
    
    Route::POST('user/info', [UsersController::class, 'getUserData']);
    Route::GET('common_group_create', [CommonController::class, 'common_group_create']);
    Route::GET('pending_call_teminate_cron', [CommonController::class, 'pending_call_teminate_cron']);
});

Route::group(['middleware' => ['jwt.auth'], 'namespace' => 'api'], function() {
    Route::POST('logout', [UsersController::class, 'logout']);
    Route::GET('logged_user', [UsersController::class, 'logged_user']);
    Route::POST('change_password', [UsersController::class, 'change_password']);
    Route::POST('update_profile', [UsersController::class, 'update_profile']);
    Route::POST('interpreter_list', [InterPreterController::class, 'interpreters_list']);
    Route::POST('language_wise_interpretes_list', [InterPreterController::class, 'language_wise_interpretes_list']);


    /* call requests */
    Route::POST('call/request', [CallController::class, 'call_request']);
    Route::POST('call/search_interpreter', [CallController::class, 'searching_interpreter']);



    Route::POST('call/history', [CallController::class, 'call_history']);

    Route::POST('add_user_device', [PushNotificationController::class, 'add_user_device']);
    Route::POST('call/end_call', [CallController::class, 'end_call']);
    Route::POST('call/action', [CallController::class, 'call_action']);
    Route::POST('call/pending_call', [CallController::class, 'pending_calls']);
    Route::POST('call/answer_call', [CallController::class, 'answer_call']);
    Route::POST('call/reject_call', [CallController::class, 'reject_call']);
    Route::POST('call/connect_call', [CallController::class, 'connect_call']);
    Route::GET('call/get_call_details_data/{call_id}', [CallController::class, 'get_call_details_data']);

    // FEEDBACK
    Route::POST('call/call_quality_feedback_post', [CallController::class, 'call_quality_feedback_post']);
    Route::POST('call/call_feedback_users_post', [CallController::class, 'call_feedback_users_post']);

    
    //Route::GET('report/call_report_history', [CallController::class,'call_report_history_api']);
    //Call Report History Export
    //Route::POST('report/call_report_history_export', [CallController::class,'call_report_history_export']);

    

    // Report Data  
    /*Route::POST('report/call_report_history', [CallController::class,'call_report_history']);
    Route::POST('report/active_user', [CallController::class,'active_user']);
    Route::POST('report/active_call', [CallController::class,'active_call']);
    Route::POST('report/frequent_user', [CallController::class,'frequent_user']);*/
    
    // new
    Route::POST('report/active_call_report', [ReportController::class,'active_call_report']);
    Route::POST('report/call_report_history', [ReportController::class,'call_report_history']);
    Route::POST('report/active_user_report', [ReportController::class,'active_user_report']);
    Route::POST('report/frequent_user_report', [ReportController::class,'frequent_user_report']);
    Route::POST('report/interpreter_report', [ReportController::class,'interpreter_report']);
    Route::POST('report/supervisor_user_report', [ReportController::class,'supervisor_user_report']);
    Route::POST('report/interpreter_user_report', [ReportController::class,'interpreter_user_report']);

    // get tempalte url
    Route::GET('report/active_call_report_history', [ReportController::class,'active_call_report_history_api']);
    Route::GET('report/call_report_history', [ReportController::class,'call_report_history_api']);
    Route::GET('report/active_user_report_history', [ReportController::class,'active_user_report_history_api']);
    Route::GET('report/frequent_user_report_history', [ReportController::class,'frequent_user_report_history_api']);

    
    
    Route::POST('report/call_report_history_new', [CallController::class,'call_report_history_new']);
    

    //Report History Export
    Route::POST('report/call_report_history_export', [CallController::class,'call_report_history_export']);
    Route::POST('report/active_user_report_history_export', [CallController::class,'active_user_report_history_export']);
    Route::POST('report/active_call_report_history_export', [CallController::class,'active_call_report_history_export']);
    Route::POST('report/frequent_user_report_history_export', [CallController::class,'frequent_user_report_history_export']);
    

    Route::POST('interpreter_message', [CallController::class,'interpreter_message']);

    Route::POST('interpreterbreak/list', [InterpreterBreakController::class, 'index']);
    Route::POST('interpreterbreak/add_request', [InterpreterBreakController::class, 'add_request']);
    Route::POST('interpreterbreak/approval_request/{id}', [InterpreterBreakController::class, 'approval_request']);

    //temp interpreter break
    Route::POST('interpreterbreak/list_break_request', [InterpreterBreakController::class, 'list_break_request']);
    Route::POST('interpreterbreak/start_break_request', [InterpreterBreakController::class, 'start_break_request']);
    Route::POST('interpreterbreak/end_break_request/{id}', [InterpreterBreakController::class, 'end_break_request']);


    // add user  
    Route::GET('user/list', [UsersController::class, 'index']);
    Route::POST('user/store', [UsersController::class, 'store']);
    Route::POST('user/update/{id}', [UsersController::class, 'update']);
    Route::GET('user/destroy/{id}', [UsersController::class, 'destroy']);
    Route::GET('user/details/{id}', [UsersController::class, 'details']);
    
  //tickets 
    Route::POST('ticket/list', [TicketController::class, 'index']);
    Route::POST('ticket/store', [TicketController::class, 'store']);
    Route::POST('ticket/update/{id}', [TicketController::class, 'update']);
    Route::GET('ticket/destroy/{id}', [TicketController::class, 'destroy']);
    Route::POST('ticket/details/{id}', [TicketController::class, 'details']);
    Route::POST('ticket/ticket_on_action', [TicketController::class, 'ticket_on_action']);
    Route::POST('ticket/ticket_on_action_update/{id}', [TicketController::class, 'ticket_on_action_update']);

    // Dashboard count data 
    Route::POST('dashboard/getDashboardcount', [DashboardController::class, 'getDashboardcount']);
    Route::POST('dashboard/getTrendscount', [DashboardController::class, 'getTrendscount']);
    Route::POST('dashboard/getLanguageTrendscount', [DashboardController::class, 'getLanguageTrendscount']);
    Route::POST('dashboard/getLanguageTrendscount_data', [DashboardController::class, 'getLanguageTrendscount_data']);

    // Contact us 
    Route::POST('contact_us', [CommonController::class, 'contact_us']);
});


Route::group(['middleware' => ['jwt.auth'], ['role:super_admin'], 'namespace' => 'api'], function() {
    //purpose 
    Route::GET('purpose/list', [PurposeController::class, 'index']);
    Route::POST('purpose/store', [PurposeController::class, 'store']);
    Route::POST('purpose/update/{id}', [PurposeController::class, 'update']);
    Route::GET('purpose/destroy/{id}', [PurposeController::class, 'destroy']);

    //disposition
    Route::POST('disposition/list', [DispositionController::class, 'index']);
    Route::POST('disposition/store', [DispositionController::class, 'store']);
    Route::POST('disposition/update/{id}', [DispositionController::class, 'update']);
    Route::GET('disposition/destroy/{id}', [DispositionController::class, 'destroy']);

    //language
    Route::GET('language/list', [LanguageController::class, 'index']);
    Route::POST('language/store', [LanguageController::class, 'store']);
    Route::POST('language/update/{id}', [LanguageController::class, 'update']);
    Route::GET('language/destroy/{id}', [LanguageController::class, 'destroy']);

    //reasons
    Route::GET('reason/list', [ReasonController::class, 'index']);
    Route::POST('reason/store', [ReasonController::class, 'store']);
    Route::POST('reason/update/{id}', [ReasonController::class, 'update']);
    Route::GET('reason/destroy/{id}', [ReasonController::class, 'destroy']);
    
});