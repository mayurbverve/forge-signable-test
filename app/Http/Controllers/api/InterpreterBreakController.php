<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helper\Helper;
use App\Models\InterpreterBreak;
use App\Models\InterpreterBreakLog;
use App\Models\InterpreterBreaksTemp;
use App\Models\ActiveInterpreter;
use JWTAuth;
use JWTFactory;
use Config;
use Log;
use DB;

class InterpreterBreakController extends Controller {

    protected $helper;

    /**
     * UserController constructor.
     */
    public function __construct() {
        $this->helper = new Helper();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $currentUser = $this->helper->getLoginUser();
            $user_profile_id = '';

            $status = (isset($request->status) && !empty($request->status)) ? $request->status : 1;
            $user_id = (isset($request->user_id) && !empty($request->user_id)) ? $request->user_id : '';
            $date = (isset($request->date) && !empty($request->date)) ? $request->date : date('Y-m-d');
            $break_data = InterpreterBreak::getBreakReasonData()->whereDate('created_at', '=', $date);
            if($currentUser->role == 'supplier_employee'){
                $user_profile_id = $currentUser->user_profile->id;
                $break_data = $break_data->where('user_profile_id',$user_profile_id);
                if($status != 1){
                    $break_data = $break_data->where('status',$status);
                }
            }

            if($currentUser->role == 'supplier_supervisor'){
                $user_profile_id = $currentUser->user_profile->id;
                $break_data = $break_data->where('assign_to',$user_profile_id)->where('status',$status);
                if($user_id != ''){
                    $break_data = $break_data->where('user_profile_id',$user_id);
                }
            }
            $break_data = $break_data->orderBy('created_at','DESC')->get();
            if (!$break_data->isEmpty()) {
                $message = trans("translate.BREAK_LIST_RECORD");
                $response_array = $this->helper->custom_response(true, $break_data, $message);
                return response()->json($response_array, Response::HTTP_OK);
            } else {
                $response_array = $this->helper->custom_response(true, array(), trans("translate.EMPTY_LIST"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    } 

    public function add_request(Request $request){
        try {
            Log::info('Create InterpreterBreak Request: Params: ' . json_encode($request->all()));
            $validator = Validator::make($request->all(),[
                'duration' => 'required',
                'break_reason_id' => 'required'
            ]);

            if($validator->fails()){
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }
            $currentUser = $this->helper->getLoginUser();

            $current_date = date('Y-m-d H:i:s');
            $current_dayofweek = date('l');
            $break_data = [
                'duration' => (isset($request->duration) && !empty($request->duration)) ? $request->duration : '',
                'break_reason_id' => (isset($request->break_reason_id) && !empty($request->break_reason_id)) ? $request->break_reason_id : '',
                'day' => $current_dayofweek,
                'user_profile_id' => $currentUser->user_profile->id,
                'assign_to' => $currentUser->created_by,
                'status' => 1,
                
            ];   
            Log::info('Create interpreterBreak Request: Params: ' . json_encode($break_data));
            $break_data = InterpreterBreak::create($break_data);

            $break_data_log = [
                'break_id' => $break_data->id,
                'to_status' => 1,
                'created_by' => $currentUser->id,
                
            ];   
            Log::info('Create interpreterBreak Request Log: Params: ' . json_encode($break_data_log));
            $break_data_log = InterpreterBreakLog::create($break_data_log);

            if (isset($break_data) && !empty($break_data)) {
                $message = trans("translate.BREAK_REQUEST_ADDED_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $break_data, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(false, array(), trans("translate.BREAK_REQUEST_ADDED_FAILED"));
                return response()->json($response_array, Response::HTTP_OK);
            }
            
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    } 

    public function approval_request(Request $request,$id){
        try {
            $currentUser = $this->helper->getLoginUser();
            if($currentUser->role == 'supplier_supervisor'){
                Log::info('Approval Break Request: Params: ' . json_encode($request->all()));
                $currentUser = $this->helper->getLoginUser();
                
                $break_start_time = date('Y-m-d H:i:s');

                $break_data = InterpreterBreak::find($id);
                $date = $break_data->duration;
                $date = strtotime($date);
                $hours= date('H', $date);
                $min= date('i', $date);
                $sec= date('s', $date);
                
                $break_end_time = date('Y-m-d H:i:s',strtotime('+'.$hours.' hour +'.$min.' minutes  +'.$sec.' sec',strtotime($break_start_time)));
                $break_data->break_start_time = $break_start_time;
                $break_data->break_end_time = $break_end_time;
                $break_data->approved_at = $break_start_time;
                $break_data->status = 2;
                $break_data->save();
                Log::info('Approval Break Request: Params: ' . json_encode($break_data));

                $break_data_log = [
                    'break_id' => $break_data->id,
                    'from_status' => 1,
                    'to_status' => 2,
                    'updated_by' => $currentUser->id,
                    
                ];   
                Log::info('update interpreterBreak Request Log: Params: ' . json_encode($break_data_log));
                $break_data_log = InterpreterBreakLog::create($break_data_log);
                if (isset($break_data) && !empty($break_data)) {
                    $message = trans("translate.BREAK_REQUEST_APPROVAL_SUCCESSFULLY");
                    $response_array = $this->helper->custom_response(true, $break_data, $message);
                    return response()->json($response_array, Response::HTTP_OK);
                }else{
                    $response_array = $this->helper->custom_response(true, array(), trans("translate.BREAK_REQUEST_APPROVAL_FAILED"));
                    return response()->json($response_array, Response::HTTP_OK);
                }
            }else{
                $response_array = $this->helper->custom_response(true, array(), trans("translate.ONLY_SUPPLIER_SUPERVISOR_APPROVAL"));
                    return response()->json($response_array, Response::HTTP_OK);
            }
            
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    // Temp break reson 

    public function start_break_request(Request $request){
        try {
            Log::info('Create InterpreterBreak Request: Params: ' . json_encode($request->all()));
            $validator = Validator::make($request->all(),[
                'break_reason' => 'required'
            ]);

            if($validator->fails()){
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }
            $currentUser = $this->helper->getLoginUser();

            $current_date = date('Y-m-d H:i:s');
            $current_dayofweek = date('l');
            $break_start_time = date('Y-m-d H:i:s');
            $break_data = [
                'user_profile_id' => $currentUser->user_profile->id,
                'break_reason' => (isset($request->break_reason) && !empty($request->break_reason)) ? $request->break_reason : '',
                'day' => $current_dayofweek,
                'break_start_time' => $break_start_time
                
            ];   
            Log::info('Create interpreterBreak Request: Params: ' . json_encode($break_data));
            $break_data = InterpreterBreaksTemp::create($break_data);  

            //interpreter status changes active to break
            $interpreter_data = ActiveInterpreter::where('user_profile_id',$currentUser->user_profile->id)->update(['status' => 3]);

            if (isset($break_data) && !empty($break_data)) {
                $message = trans("translate.START
                    _BREAK_REQUEST_ADDED_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $break_data, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(false, array(), trans("translate.START_BREAK_REQUEST_ADDED_FAILED"));
                return response()->json($response_array, Response::HTTP_OK);
            }
            
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function end_break_request(Request $request,$id){
        try {
            $currentUser = $this->helper->getLoginUser();
            Log::info('End Break Request: Params: ' . json_encode($request->all()));
            
            $break_end_time = date('Y-m-d H:i:s');
            $break_data = InterpreterBreaksTemp::find($id);
            $break_data->break_end_time = date('Y-m-d H:i:s');
            $break_data->save();
            //interpreter status changes break to active   
            $interpreter_data = ActiveInterpreter::where('user_profile_id',$currentUser->user_profile->id)->update(['status' => 1]);
            Log::info('End Break Request: Params: ' . json_encode($break_data));
            if (isset($break_data) && !empty($break_data)) {
                $message = trans("translate.START_BREAK_REQUEST_ADDED_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $break_data, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(true, array(), trans("translate.END_BREAK_REQUEST_ADDED_FAILED"));
                return response()->json($response_array, Response::HTTP_OK);
            }
            
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function list_break_request(Request $request)
    {
        try {
            $currentUser = $this->helper->getLoginUser();

            $date = (isset($request->date) && !empty($request->date)) ? $request->date : date('Y-m-d');
            $break_data = InterpreterBreaksTemp::where('user_profile_id',$currentUser->user_profile->id)->whereDate('created_at', '=', $date)->orderBy('created_at','DESC')->get();
            if (!$break_data->isEmpty()) {
                $message = trans("translate.BREAK_LIST_RECORD");
                $response_array = $this->helper->custom_response(true, $break_data, $message);
                return response()->json($response_array, Response::HTTP_OK);
            } else {
                $response_array = $this->helper->custom_response(true, array(), trans("translate.EMPTY_LIST"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }
}
