<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helper\Helper;
use App\Models\City;
use App\Models\CallInitMessage;
use App\Models\Purpose;
use App\Models\Disposition;
use App\Models\Reason;
use App\Models\Region;
use App\Models\Mile;
use App\Models\Call;
use App\Models\CallDetail;
use App\Models\ContactUs;
use Carbon\Carbon;
use JWTAuth;
use JWTFactory;
use Config;
use Log;
use DB;

class CommonController extends Controller {

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
    public function city_list() {
        try {

            $cities = City::getCityData()->get();
            if (!$cities->isEmpty()) {
                $message = trans("translate.CITY_LIST_RECORD");
                $response_array = $this->helper->custom_response(true, $cities, $message);
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

    public function purposes_list() {
        try {

            $purposes = Purpose::getPurposeData()->get();
            if (!$purposes->isEmpty()) {
                $message = trans("translate.PURPOSE_LIST_RECORD");
                $response_array = $this->helper->custom_response(true, $purposes, $message);
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

    public function dispositions_list(Request $request) {
        try {
            if (!empty($request)) {
                $dispositions = Disposition::getDispositionData()->where('type', $request->type)->get();
            }
            if (!$dispositions->isEmpty()) {
                $message = trans("translate.DISPOSITIONS_LIST_RECORD");
                $response_array = $this->helper->custom_response(true, $dispositions, $message);
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

    public function reasons_list(Request $request) {
        try {
            if (!empty($request)) {
                $reasons = Reason::getReasonsData()->get();
            }
            if (!$reasons->isEmpty()) {
                $message = trans("translate.RESONS_LIST_RECORD");
                $response_array = $this->helper->custom_response(true, $reasons, $message);
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function region_list() {
        try {

            $regions = Region::getRegionData()->get();
            if (!$regions->isEmpty()) {
                $message = trans("translate.REGION_LIST_RECORD");
                $response_array = $this->helper->custom_response(true, $regions, $message);
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function miles_list() {
        try {

            $miles = Mile::getMileData()->get();
            if (!$miles->isEmpty()) {
                $message = trans("translate.REGION_LIST_RECORD");
                $response_array = $this->helper->custom_response(true, $miles, $message);
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

    public function pending_call_teminate_cron() {
        try {
            $now = Carbon::now()->subMinute(5);
            $deleted_cron_jmessages = $now->format('Y-m-d H:i:s');
            $status = $this->helper->getCallStatusId('end');
            $pending_calls = CallInitMessage::getCallInitMessageData()->where('updated_at', "<=", $deleted_cron_jmessages)
                    ->get();
            
            foreach ($pending_calls AS $call) {
            $message_ids = [];
                $message_ids[]= $call->supervisor_message_id;
                $message_ids[]= $call->interpreter_message_id;
                $message_ids = implode(",", $message_ids);
                $user_quickblock_session = $this->helper->createUserSession(env('ADMIN_LOGIN'), env('ADMIN_PASSWORD'));
                $quickblock_token = $user_quickblock_session['session']['token'];
                $delete_message = $this->helper->deleteMessage($quickblock_token, $message_ids);
                
                $call_detail = Call::where('id', $call->calls->id)->first();
                
                $call_detail->status = $this->helper->getCallStatusId('terminated_by_system');
                $call_detail->save();
                
                $last_call_details = CallDetail::where('call_id', $call->calls->id)->orderBy('id','DESC')->first();
                 $call_data = array(
                        'call_id' => $last_call_details->call_id,
                        'user_profile_id' => $last_call_details->user_profile_id,
                        'user_role_id' => $last_call_details->user_role_id,
                        'status' => $this->helper->getCallStatusId('terminated_by_system'),
                        'is_called_failed' => 0,
                        'start_time' => $last_call_details->start_time,
                        'end_time' => $last_call_details->end_time,
                        'duration' => $last_call_details->duration,
                        'call_detail' => $last_call_details->call_detail,
                    );
                $add_call_details = CallDetail::create($call_data);
                
                
            }
                echo "done";die;
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function contact_us(Request $request) {
        try {
            Log::info('Create ContactUs: Params: ' . json_encode($request->all()));
            $validator = Validator::make($request->all(),[
                'name' => 'required',
                'email' => 'required',
                'comments' => 'required'
            ]);

            if($validator->fails()){
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }

            $contact_us_data = [
                'name' => (isset($request->name) && !empty($request->name)) ? $request->name : '',
                'email' => (isset($request->email) && !empty($request->email)) ? $request->email : '',
                'comments' => (isset($request->comments) && !empty($request->comments)) ? $request->comments : ''
            ];   
            Log::info('Create ContactUs: Params: ' . json_encode($contact_us_data));
            $contact_us = ContactUs::create($contact_us_data);

            if (isset($contact_us) && !empty($contact_us)) {

                //Send email to admin to contact us 
                $template_replace_data = array(
                    'admin_name' => Config::get('settings.ADMIN_NAME'),
                    'user_name' => $request->name,
                    'user_email' => $request->email,
                    'user_comments' => $request->comments,
                    'LOGO' => Config::get('settings.APP_LOGO'),
                    'project_name' => Config::get('settings.APP_NAME'),
                    'app_store_logo' => Config::get('settings.APPLE_STORE_LOGO'),
                    'play_store_logo' => Config::get('settings.PLAY_STORE_LOGO'),
                    'app_store_link' => Config::get('settings.APPLE_STORE_LINK'),
                    'play_store_link' => Config::get('settings.PLAY_STORE_LINK'),
                );

                $template_details = $this->helper->getEmailTemplate('contact_us');

                $send_data = $this->helper->send_email(Config::get('settings.ADMIN_EMAIL'), $template_replace_data, $template_details);    



                $message = trans("translate.CONTACT_US_ADDED_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $contact_us, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(false, array(), trans("translate.CONTACT_US_ADDED_FAILED"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function common_group_create() {
        try {
            $user_quickblock_session = $this->helper->createUserSession(env('ADMIN_LOGIN'), env('ADMIN_PASSWORD'));
            $quickblock_token = $user_quickblock_session['session']['token'];
//            $path = base_path('.env');
//
//            if (file_exists($path)) {
//                $env_data = file_get_contents($path);
//            }

//            print_r($quickblock_token);die;
            $group_name = "common_local_interpreter";
            $chat_interpreter_room_details = $this->helper->createChatRoom($quickblock_token, [], 1, $group_name);
            
            $group_name = "common_local_supervisor";
            $chat_supervisor_room_details = $this->helper->createChatRoom($quickblock_token, [], 1, $group_name);
//            
//            $env_data = str_replace('COMMON_INTERPRETER_ROOMID=' .env('COMMON_INTERPRETER_ROOMID'),'COMMON_INTERPRETER_ROOMID='.$chat_interpreter_room_details['_id'], $env_data);
//            $env_data = str_replace('COMMON_SUPERVISOR_ROOMID=' .env('COMMON_SUPERVISOR_ROOMID'),'COMMON_SUPERVISOR_ROOMID='.$chat_supervisor_room_details['_id'], $env_data);

//            file_get_contents($path);
            $message = trans("just list que calls");
            $response_array = $this->helper->custom_response(true, array(), $message);
            return response()->json($response_array, Response::HTTP_OK);
        } catch (\Exception $ex) {

            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }
}
