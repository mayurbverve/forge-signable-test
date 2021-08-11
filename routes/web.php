<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\CallController;
use App\Http\Controllers\api\ReportController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
//Route::GET('call_report_template', [CallController::class,'call_report_history_template']);
Route::GET('report/frequent_user_report_history_export', [CallController::class,'frequent_user_report_history_export']);


//Route::GET('call_report_template', [CallController::class,'call_report_history_template_new']);


// new 
Route::GET('active_call_report_template', [ReportController::class,'active_call_report_history_template']);
Route::GET('call_report_template', [ReportController::class,'call_report_history_template']);
Route::GET('active_user_report_template', [ReportController::class,'active_user_report_history_template']);
Route::GET('frequent_user_report_template', [ReportController::class,'frequent_user_report_history_template']);