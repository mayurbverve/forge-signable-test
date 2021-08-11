<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helper\Helper;
use App\Models\Call;
use App\Models\Language;
use App\Models\Purpose;
use App\Models\CallLog;
use App\Models\CallDetail;
use App\Models\InterPreterChat;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\ActiveInterpreter;
use App\Models\UserPushDevice;
use App\Http\Controllers\api\PushNotificationController;
use App\Models\Company;
use App\Models\CallStatus;
use App\Models\CallInitMessage;
use App\Models\CallQualityFeedback;
use App\Models\CallFeedbackUser;
use App\Models\Disposition;
use App\Jobs\CallRequestSend;
use Carbon\Carbon;
use JWTAuth;
use JWTFactory;
use Config;
use Log;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use View,
    Redirect;

class CallController extends Controller {

    protected $helper;
    protected $supplier_roles;
    protected $consumer_roles;
    protected $current_user;

    /**
     * UserController constructor.
     */
    public function __construct() {
        $this->helper = new Helper();
        $this->supplier_roles = array(2);
        $this->consumer_roles = array(3);
        $this->current_user = auth()->user();
    }

    public function call_request(Request $request) {
        try {
            DB::beginTransaction();
            $current_user = $this->helper->getLoginUser();
            $roles = $current_user->user_profile->user_roles;
            $role_id = $roles[0]->role_id;

            if (in_array($role_id, $this->consumer_roles)) {

                Log::info('Call request call: Params: ' . json_encode($request->all()));
                $validator = Validator::make($request->all(), [
                            'purpose_id' => 'required',
                            'language_id' => 'required',
                            'action' => 'required'
                ]);

                if ($validator->fails()) {
                    $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                    return response()->json($response_array, Response::HTTP_BAD_REQUEST);
                }

                $exist_call_request = Call::getCallData()->where(['from_user_profile_id' => $current_user->user_profile->id, 'from_user_role_id' => $role_id, 'language_id' => $request->language_id, 'is_recall' => $request->is_recall, 'action' => $request->action, 'status' => $this->helper->getCallStatusId('request')])->first();

                if (empty($exist_call_request)) {
                    if ($request->action == 1) {
                        $call_data = array(
                            'from_user_profile_id' => isset($current_user->user_profile->id) && !empty($current_user->user_profile->id) ? $current_user->user_profile->id : "",
                            'from_user_role_id' => isset($role_id) && !empty($role_id) ? $role_id : "",
                            'purpose_id' => isset($request->purpose_id) && !empty($request->purpose_id) ? $request->purpose_id : "",
                            'language_id' => isset($request->language_id) && !empty($request->language_id) ? $request->language_id : "",
                            'is_recall' => isset($request->is_recall) ? $request->is_recall : 0,
                            'previous_call_id' => isset($request->previous_call_id) && !empty($request->previous_call_id) ? $request->previous_call_id : 0,
                            'action' => 1,
                            'status' => $this->helper->getCallStatusId('request'),
                        );
                        $call = Call::create($call_data);

                        $call_log_data = array(
                            'call_id' => $call->id,
                            'from_status' => null,
                            'to_status' => $this->helper->getCallStatusId('request')
                        );
                        $call_log_data = CallLog::create($call_log_data);

                        $interpreter_lists = User::getActiveInterpreter()->where('user_languages.language_id', $call->language_id)->get();

                        if (isset($interpreter_lists) && !$interpreter_lists->isEmpty()) {
                            $call_log_data = array(
                                'call_id' => $call->id,
                                'from_status' => $this->helper->getCallStatusId('request'),
                                'to_status' => $this->helper->getCallStatusId('interpreter_found')
                            );
                            $call_log_data = CallLog::create($call_log_data);
                        } else {
                            $call_log_data = array(
                                'call_id' => $call->id,
                                'from_status' => $this->helper->getCallStatusId('request'),
                                'to_status' => $this->helper->getCallStatusId('no_active_interpreter_found')
                            );
                            $call_log_data = CallLog::create($call_log_data);
                        }
                    }

                    $added_call = Call::getCallData()->where('id', $call->id)->first();
                } else {
                    DB::rollBack();
                    $response_array = $this->helper->custom_response(true, $exist_call_request, trans("translate.CALL_REQUEST_ALREADY_ADDED"));
                    return response()->json($response_array, Response::HTTP_OK);
                }

                if (isset($added_call) && !empty($added_call)) {
                    DB::commit();
                    $message = trans("translate.CALL_REQUEST_ADDED");
                    $response_array = $this->helper->custom_response(true, $added_call, $message);
                    return response()->json($response_array, Response::HTTP_OK);
                } else {
                    DB::rollBack();
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.CALL_REQUEST_NOT_ADDED"));
                    return response()->json($response_array, Response::HTTP_OK);
                }
            } else {
                $response_array = $this->helper->custom_response(false, array(), trans("translate.USER_NOT_AUTHORIZE_TO_ACCESS"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function searching_interpreter(Request $request) {
        try {
            DB::beginTransaction();
            $current_user = $this->helper->getLoginUser();
            $roles = $current_user->user_profile->user_roles;
            $role_id = $roles[0]->role_id;

            if (in_array($role_id, $this->consumer_roles)) {


                $current_user = $this->helper->getLoginUser();

                Log::info('Call request seaching interpreter: Params: ' . json_encode($request->all()));
                $validator = Validator::make($request->all(), [
                            'call_request_id' => 'required',
                ]);
                if ($validator->fails()) {

                    $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                    return response()->json($response_array, Response::HTTP_BAD_REQUEST);
                }
                $get_call_details = Call::getCallMessageData()->where('id', $request->call_request_id)->first();
                $interpreter_lists = User::getActiveInterpreter()->where('user_languages.language_id', $get_call_details->language_id)->get();

                if (isset($interpreter_lists) && !$interpreter_lists->isEmpty()) {

                    $call = Call::find($get_call_details->id);
                    $call = $this->helper->getCallStatus('search');
                    $call->save();
                    $last_call_log = CallLog::where('call_id', $request->call_request_id)->orderBy('id', 'DESC')->first();

                    $call_log_data = array(
                        'call_id' => $get_call_details->id,
                        'from_status' => $last_call_log->to_status,
                        'to_status' => $this->helper->getCallStatusId('search')
                    );
                    $call_log_data = CallLog::create($call_log_data);

                    $call_init_message_exist = CallInitMessage::where('call_id', $request->call_request_id)->first();
                    if (!isset($call_init_message_exist) || empty($call_init_message_exist)) {
                        $is_interpreter_message = 1;
                        $sendcall_request = (new CallRequestSend($request->call_request_id, $is_interpreter_message))->delay(Carbon::now()->addMinutes(1));
                        dispatch($sendcall_request);
                        if ($sendcall_request) {
                            DB::commit();
                            $message = trans("translate.CALL_REQUEST_ADDED_CONNECT");
                            $response_array = $this->helper->custom_response(true, array(), $message);
                            return response()->json($response_array, Response::HTTP_OK);
                        } else {
                            $message = trans("translate.CALL_REQUEST_NOT_ADDED");
                            $response_array = $this->helper->custom_response(true, array(), $message);
                            return response()->json($response_array, Response::HTTP_OK);
                        }
                    } else {
                        $message = trans("translate.CALL_ALREADY_INITIATED");
                        $response_array = $this->helper->custom_response(true, array(), $message);
                        return response()->json($response_array, Response::HTTP_OK);
                    }
                } else {

                    $call = Call::find($get_call_details->id);
                    $call = $this->helper->getCallStatus('search');
                    $call->save();

                    $last_call_log = CallLog::where('call_id', $request->call_request_id)->orderBy('id', 'DESC')->first();

                    $call_log_data = array(
                        'call_id' => $call->id,
                        'from_status' => $last_call_log->to_status,
                        'to_status' => $this->helper->getCallStatusId('no_interpreter_found')
                    );
                    $call_log_data = CallLog::create($call_log_data);
                    DB::commit();
                    $message = trans("translate.INTERPRETER_NOT_FOUND");
                    $response_array = $this->helper->custom_response(false, array(), $message);
                    return response()->json($response_array, Response::HTTP_OK);
                }
            } else {
                $response_array = $this->helper->custom_response(false, array(), trans("translate.USER_NOT_AUTHORIZE_TO_ACCESS"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function action_call(Request $request) {
        try {
            DB::beginTransaction();
            $current_user = $this->helper->getLoginUser();
            $roles = $current_user->user_profile->user_roles;
            $role_id = $roles[0]->role_id;
            $get_call_details = Call::getCallData()->where('id', $request->call_request_id)->first();
            if (in_array($role_id, $this->consumer_roles)) {


                Log::info('Call request seaching interpreter: Params: ' . json_encode($request->all()));
                $validator = Validator::make($request->all(), [
                            'call_request_id' => 'required',
                            'action' => 'required',
                ]);
                if ($validator->fails()) {

                    $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                    return response()->json($response_array, Response::HTTP_BAD_REQUEST);
                }

                if ($request->action == 1) {
                    $last_call_log = CallLog::where('call_id', $request->call_request_id)->orderBy('id', 'DESC')->first();

                    $call_log_data = array(
                        'call_id' => $get_call_details->id,
                        'from_status' => $last_call_log->to_status,
                        'to_status' => $this->helper->getCallStatusId('search')
                    );
                    $call_log_data = CallLog::create($call_log_data);

                    $call_init_message_exist = CallInitMessage::where('call_id', $request->call_request_id)->first();
                    if (!isset($call_init_message_exist) || empty($call_init_message_exist)) {
                        $is_interpreter_message = 1;
                        $sendcall_request = (new CallRequestSend($request->call_request_id, $is_interpreter_message))->delay(Carbon::now()->addMinutes(1));
                        dispatch($sendcall_request);
                        if ($sendcall_request) {
                            DB::commit();
                            $message = trans("translate.CALL_REQUEST_ADDED_CONNECT");
                            $response_array = $this->helper->custom_response(true, array(), $message);
                            return response()->json($response_array, Response::HTTP_OK);
                        } else {
                            $message = trans("translate.CALL_REQUEST_NOT_ADDED");
                            $response_array = $this->helper->custom_response(true, array(), $message);
                            return response()->json($response_array, Response::HTTP_OK);
                        }
                    } else {
                        $message = trans("translate.CALL_ALREADY_INITIATED");
                        $response_array = $this->helper->custom_response(true, array(), $message);
                        return response()->json($response_array, Response::HTTP_OK);
                    }
                } elseif ($request->action == 2) {
                    $call = Call::find($get_call_details->id);
                    $call = $this->helper->getCallStatus('end');
                    $call->save();

                    $last_call_log = CallLog::where('call_id', $request->call_request_id)->orderBy('id', 'DESC')->first();

                    $call_log_data = array(
                        'call_id' => $get_call_details->id,
                        'from_status' => $last_call_log->to_status,
                        'to_status' => $this->helper->getCallStatusId('terminated_by_system')
                    );
                    $call_log_data = CallLog::create($call_log_data);

                    $call_init_message_exist = CallInitMessage::where('call_id', $request->call_request_id)->first();

                    if (isset($call_init_message_exist) && !empty($call_init_message_exist)) {
                        $message_ids = $call_init_message_exist->interpreter_message_id;
                        $user_quickblock_session = $this->helper->createUserSession(env('ADMIN_LOGIN'), env('ADMIN_PASSWORD'));
                        $quickblock_token = $user_quickblock_session['session']['token'];
                        $delete_message = $this->helper->deleteMessage($quickblock_token, $message_ids);
                    }
                    DB::commit();
                    $message = trans("translate.CALL_TERMINATED_SUCCESSFULLY");
                    $response_array = $this->helper->custom_response(true, array(), $message);
                    return response()->json($response_array, Response::HTTP_OK);
                }
            } else {
                $response_array = $this->helper->custom_response(false, array(), trans("translate.USER_NOT_AUTHORIZE_TO_ACCESS"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function answer_call(Request $request) {
        try {
            DB::beginTransaction();
            $current_user = $this->helper->getLoginUser();
            $roles = $current_user->user_profile->user_roles;
            $role_id = $roles[0]->role_id;

            if (in_array($role_id, $this->supplier_roles)) {
                Log::info('Call request seaching interpreter: Params: ' . json_encode($request->all()));
                $validator = Validator::make($request->all(), [
                            'call_request_id' => 'required',
                ]);

                if ($validator->fails()) {
                    $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                    return response()->json($response_array, Response::HTTP_BAD_REQUEST);
                }

                $call_init_message_exist = CallInitMessage::where('call_id', $request->call_request_id)->first();

                $message_ids = $call_init_message_exist->interpreter_message_id;
                $user_quickblock_session = $this->helper->createUserSession(env('ADMIN_LOGIN'), env('ADMIN_PASSWORD'));
                $quickblock_token = $user_quickblock_session['session']['token'];
                $delete_message = $this->helper->deleteMessage($quickblock_token, $message_ids);
                $get_call_details = Call::getCallData()->where('id', $request->call_request_id)->first();
                $roles = $current_user->user_profile->user_roles;
                $role_id = $roles[0]->role_id;

                $create_chat_room_details = InterPreterChat::select('chat_room_details')->where('user_profile_id', $current_user->user_profile->id)->orderBy('id', 'ASC')->first();

                $create_chat_room_details = json_decode($create_chat_room_details['chat_room_details']);

                $call_details_data = array(
                    'call_id' => isset($request->call_request_id) && !empty($request->call_request_id) ? $request->call_request_id : null,
                    'user_profile_id' => $current_user->user_profile->id,
                    'user_role_id' => isset($role_id) && !empty($role_id) ? $role_id : null,
                    'status' => $this->helper->getCallStatusId('interpreter_accepted'),
                    'is_called_failed' => 0,
                    'start_time' => date("Y-m-d H:i:s"),
                    'call_detail' => json_encode($create_chat_room_details)
                );

                $call_details = CallDetail::create($call_details_data);

                $last_call_log = CallLog::where('call_id', $request->call_request_id)->orderBy('id', 'DESC')->first();

                $call_log_data = array(
                    'call_id' => $get_call_details->id,
                    'from_status' => $last_call_log->to_status,
                    'to_status' => $this->helper->getCallStatusId('interpreter_accepted')
                );
                $call_log_data = CallLog::create($call_log_data);

                $call_record = Call::find($get_call_details->id);
                $call_record->status = $this->helper->getCallStatusId('connect');
                $call_record->save();
                $active_interpreters = ActiveInterpreter::getActiveInterpreterData()->where('user_profile_id', $current_user->user_profile->id)->get();
                foreach ($active_interpreters AS $active_interpreter) {
                    $active_interpreter->is_active = 1;
                    $active_interpreter->status = 4;
                    $active_interpreter->save();
                }

                //**************** supervisor message related coding **************// 

                $call_init_message_exist = CallInitMessage::where('call_id', $request->call_request_id)->first();
                if ((!isset($call_init_message_exist) || empty($call_init_message_exist) || (empty($call_init_message_exist->supervisor_message_id))) && ($last_call_log->to_status >= $this->helper->getCallStatusId('search') && $last_call_log->to_status <= $this->helper->getCallStatusId('refused_by_interpreter'))) {

                    $is_interpreter_message = 0;
                    $sendcall_request = (new CallRequestSend($request->call_request_id, $is_interpreter_message))->delay(Carbon::now()->addMinutes(1));
                    dispatch($sendcall_request);
                    //**************** supervisor message related coding **************// 
                    DB::commit();
                    $message = trans("translate.CALL_REQUEST_CONNECTED");
                    $response_array = $this->helper->custom_response(true, array(), $message);
                    return response()->json($response_array, Response::HTTP_OK);
                } else {
                    if ($last_call_log->to_status >= $this->helper->getCallStatusId('connect')) {
                        $call_init_message_exist = CallInitMessage::where('call_id', $request->call_request_id)->first();
                        $message_ids = $call_init_message_exist->interpreter_message_id;
                        $user_quickblock_session = $this->helper->createUserSession(env('ADMIN_LOGIN'), env('ADMIN_PASSWORD'));
                        $quickblock_token = $user_quickblock_session['session']['token'];
                        $delete_message = $this->helper->deleteMessage($quickblock_token, $message_ids);
                        $response_array = $this->helper->custom_response(false, array(), trans("translate.CALL_ALREADY_DISCONNECTED"));
                        return response()->json($response_array, Response::HTTP_OK);
                    } else {
                        $response_array = $this->helper->custom_response(false, array(), trans("translate.CALL_REQUEST_ALREADY_CONNECTED"));
                        return response()->json($response_array, Response::HTTP_OK);
                    }
                }
            } else {
                $response_array = $this->helper->custom_response(false, array(), trans("translate.USER_NOT_AUTHORIZE_TO_ACCESS"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function connect_call(Request $request) {
        try {
            DB::beginTransaction();
            $current_user = $this->helper->getLoginUser();
            $roles = $current_user->user_profile->user_roles;
            $role_id = $roles[0]->role_id;

            if (in_array($role_id, $this->consumer_roles)) {
                Log::info('Call request end calls: Params: ' . json_encode($request->all()));
                $validator = Validator::make($request->all(), [
                            'call_request_id' => 'required'
                ]);
                if ($validator->fails()) {
                    $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                    return response()->json($response_array, Response::HTTP_BAD_REQUEST);
                }
                $call_init_message_exist = CallInitMessage::where('call_id', $request->call_request_id)->first();

                $message_ids = $call_init_message_exist->supervisor_message_id;
                $user_quickblock_session = $this->helper->createUserSession(env('ADMIN_LOGIN'), env('ADMIN_PASSWORD'));
                $quickblock_token = $user_quickblock_session['session']['token'];
                $delete_message = $this->helper->deleteMessage($quickblock_token, $message_ids);

                $last_call_log = CallLog::where('call_id', $request->call_request_id)->orderBy('id', 'DESC')->first();

                $call_log_data = array(
                    'call_id' => $request->call_request_id,
                    'from_status' => $last_call_log->to_status,
                    'to_status' => $this->helper->getCallStatusId('supervisor_accepted'),
                    'created_by' => $current_user->user_profile->id,
                    'updated_by' => $current_user->user_profile->id
                );
                $call_log_data = CallLog::create($call_log_data);

                $call_record = Call::find($request->call_request_id);
                $call_record->status = $this->helper->getCallStatusId('call');
                $call_record->save();

                $last_call_log = CallLog::where('call_id', $request->call_request_id)->orderBy('id', 'DESC')->first();

                $call_log_data = array(
                    'call_id' => $request->call_request_id,
                    'from_status' => $last_call_log->to_status,
                    'to_status' => $this->helper->getCallStatusId('handshake_happened'),
                    'created_by' => $current_user->user_profile->id,
                    'updated_by' => $current_user->user_profile->id
                );
                $call_log_data = CallLog::create($call_log_data);

                $last_call_details = CallDetail::where('call_id', $request->call_request_id)->orderBy('id', 'DESC')->first();

                $call_data = array(
                    'call_id' => $request->call_request_id,
                    'user_profile_id' => $last_call_details->user_profile_id,
                    'user_role_id' => $last_call_details->user_role_id,
                    'status' => $this->helper->getCallStatusId('handshake_happened'),
                    'is_called_failed' => 0,
                    'start_time' => $last_call_details->start_time,
                    'end_time' => NULL,
                    'call_detail' => $last_call_details->call_detail,
                    'feedback' => isset($reques->feedback) && !empty($reques->feedback) ? $reques->feedback : "",
                );
                $add_call_details = CallDetail::create($call_data);

                if (isset($add_call_details) && isset($call_log_data)) {
                    DB::commit();
                    $message = trans("translate.CALL_ACCEPT_SUCCESSFULLY");
                    $response_array = $this->helper->custom_response(true, $add_call_details, $message);
                    return response()->json($response_array, Response::HTTP_OK);
                } else {
                    DB::rollback();
                    $message = trans("translate.CALL_ACCEPT_NOT_SUCCESSFULLY");
                    $response_array = $this->helper->custom_response(false, $add_call_details, $message);
                    return response()->json($response_array, Response::HTTP_OK);
                }
            } else {
                $response_array = $this->helper->custom_response(false, array(), trans("translate.USER_NOT_AUTHORIZE_TO_ACCESS"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function reject_call(Request $request) {
        try {
            DB::beginTransaction();
            $current_user = $this->helper->getLoginUser();
            $roles = $current_user->user_profile->user_roles;
            $role_id = $roles[0]->role_id;
            if (in_array($role_id, $this->supplier_roles)) {
                Log::info('Call request end calls: Params: ' . json_encode($request->all()));
                $validator = Validator::make($request->all(), [
                            'call_request_id' => 'required',
                            'reason_id' => 'required'
                ]);
                if ($validator->fails()) {
                    $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                    return response()->json($response_array, Response::HTTP_BAD_REQUEST);
                }



                $last_call_log = CallLog::where('call_id', $request->call_request_id)->orderBy('id', 'DESC')->first();

                $call_log_data = array(
                    'call_id' => $request->call_request_id,
                    'from_status' => $last_call_log->to_status,
                    'to_status' => $this->helper->getCallStatusId('refused_by_interpreter'),
                    'created_by' => $current_user->user_profile->id,
                    'updated_by' => $current_user->user_profile->id
                );
                $call_log_data = CallLog::create($call_log_data);

                $last_call_details = CallDetail::where('call_id', $request->call_request_id)->orderBy('id', 'DESC')->first();

                $call_data = array(
                    'call_id' => $request->call_request_id,
                    'user_profile_id' => $current_user->user_profile->id,
                    'user_role_id' => $role_id,
                    'status' => $this->helper->getCallStatusId('refused_by_interpreter'),
                    'is_called_failed' => 0,
                    'start_time' => null,
                    'end_time' => null,
                    'call_detail' => null,
                    'reason_id' => isset($request->reason_id) && !empty($request->reason_id) ? $request->reason_id : "",
                    'feedback' => isset($request->feedback) && !empty($request->feedback) ? $request->feedback : "",
                );
                $add_call_details = CallDetail::create($call_data);

                if (isset($add_call_details) && isset($call_log_data)) {
                    DB::commit();
                    $message = trans("translate.CALL_REQUEST_REJECTED");
                    $response_array = $this->helper->custom_response(true, $add_call_details, $message);
                    return response()->json($response_array, Response::HTTP_OK);
                }
            } elseif (in_array($role_id, $this->consumer_roles)) {
                $call_init_message_exist = CallInitMessage::where('call_id', $request->call_request_id)->first();

                $message_ids = $call_init_message_exist->supervisor_message_id;
                $user_quickblock_session = $this->helper->createUserSession(env('ADMIN_LOGIN'), env('ADMIN_PASSWORD'));
                $quickblock_token = $user_quickblock_session['session']['token'];
                $delete_message = $this->helper->deleteMessage($quickblock_token, $message_ids);

                $last_call_log = CallLog::where('call_id', $request->call_request_id)->orderBy('id', 'DESC')->first();

                $call_log_data = array(
                    'call_id' => $request->call_request_id,
                    'from_status' => $last_call_log->to_status,
                    'to_status' => $this->helper->getCallStatusId('supervisor_canceled'),
                    'created_by' => $current_user->user_profile->id,
                    'updated_by' => $current_user->user_profile->id
                );
                $call_log_data = CallLog::create($call_log_data);

                $last_call_details = CallDetail::where('call_id', $request->call_request_id)->orderBy('id', 'DESC')->first();

                $call_data = array(
                    'call_id' => $request->call_request_id,
                    'user_profile_id' => $last_call_details->user_profile_id,
                    'user_role_id' => $last_call_details->user_role_id,
                    'status' => $this->helper->getCallStatusId('supervisor_canceled'),
                    'is_called_failed' => 0,
                    'start_time' => null,
                    'end_time' => null,
                    'call_detail' => null,
                    'reason_id' => isset($request->reason_id) && !empty($request->reason_id) ? $request->reason_id : "",
                    'feedback' => isset($request->feedback) && !empty($request->feedback) ? $request->feedback : "",
                );

                $add_call_details = CallDetail::create($call_data);

                $call = Call::find($request->call_request_id);
                $call->status = $this->helper->getCallStatusId('end');
                $call->save();

                $last_call_log = CallLog::where('call_id', $request->call_request_id)->orderBy('id', 'DESC')->first();
                $call_log_data = array(
                    'call_id' => $request->call_request_id,
                    'from_status' => $last_call_log->to_status,
                    'to_status' => $this->helper->getCallStatusId('terminated_by_system'),
                    'created_by' => $current_user->user_profile->id,
                    'updated_by' => $current_user->user_profile->id
                );
                $call_log_data = CallLog::create($call_log_data);

                $last_call_details = CallDetail::where('call_id', $request->call_request_id)->orderBy('id', 'DESC')->first();

                $call_data = array(
                    'call_id' => $request->call_request_id,
                    'user_profile_id' => $last_call_details->user_profile_id,
                    'user_role_id' => $last_call_details->user_role_id,
                    'status' => $this->helper->getCallStatusId('terminated_by_system'),
                    'is_called_failed' => 0,
                    'start_time' => null,
                    'end_time' => null,
                    'call_detail' => null,
                    'reason_id' => isset($request->reason_id) && !empty($request->reason_id) ? $request->reason_id : "",
                    'feedback' => isset($request->feedback) && !empty($request->feedback) ? $request->feedback : ""
                );
                $add_call_details = CallDetail::create($call_data);
                if (isset($add_call_details) && isset($call_log_data)) {
                    DB::commit();
                    $message = trans("translate.CALL_REQUEST_REJECTED");
                    $response_array = $this->helper->custom_response(true, $add_call_details, $message);
                    return response()->json($response_array, Response::HTTP_OK);
                }
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function end_call(Request $request) {
        try {
            DB::beginTransaction();
            $current_user = $this->helper->getLoginUser();
            $roles = $current_user->user_profile->user_roles;
            $role_id = $roles[0]->role_id;

            $get_call_details = Call::getCallData()->where('id', $request->call_request_id)->first();
            if (in_array($role_id, $this->supplier_roles)) {

                if (isset($get_call_details) && !empty($get_call_details)) {

                    $call = Call::find($get_call_details->id);
                    $call->status = $this->helper->getCallStatusId('end');
                    $call->save();

                    $last_call_log = CallLog::where(['call_id' => $get_call_details->id ])->orderBy('id', 'DESC')->first();

                    $call_log_data = array(
                        'call_id' => $get_call_details->id,
                        'from_status' => $last_call_log->to_status,
                        'to_status' => $this->helper->getCallStatusId('interpreter_disconnected'),
                        'created_by' => $current_user->user_profile->id,
                        'updated_by' => $current_user->user_profile->id
                    );
                    $call_log_data = CallLog::create($call_log_data);

                    $last_call_log = CallLog::where(['call_id' => $get_call_details->id, ['to_status', '!=', $this->helper->getCallStatusId('supervisor_disconnected')]])->orderBy('id', 'DESC')->first();

                    if (empty($last_call_log)) {
                        $last_call_log = CallLog::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();
                        $call_log_data = array(
                            'call_id' => $get_call_details->id,
                            'from_status' => $last_call_log->to_status,
                            'to_status' => $this->helper->getCallStatusId('supervisor_did_not_disconnect'),
                            'created_by' => $current_user->user_profile->id,
                            'updated_by' => $current_user->user_profile->id
                        );
                        $call_log_data = CallLog::create($call_log_data);
                    } else {
                        $call_log_data = array(
                            'call_id' => $get_call_details->id,
                            'from_status' => $last_call_log->to_status,
                            'to_status' => $this->helper->getCallStatusId('complete_from_both'),
                            'created_by' => $current_user->user_profile->id,
                            'updated_by' => $current_user->user_profile->id
                        );
                        $call_log_data = CallLog::create($call_log_data);
                    }

                    $last_call_details = CallDetail::where(['call_id' => $get_call_details->id])->orderBy('id', 'DESC')->first();

                    if (isset($last_call_details) && !empty($last_call_details)) {
                        $diff = strtotime(date("Y-m-d H:i:s")) - strtotime($last_call_details->start_time);

                        $hours = floor($diff / 3600);
                        $mins = floor(($diff - $hours * 3600) / 60);
                        $s = $diff - ($hours * 3600 + $mins * 60);
                        $duration = $hours . ":" . $mins . ":" . $s;

                        $call_data = array(
                            'call_id' => $get_call_details->id,
                            'user_profile_id' => $current_user->user_profile->id,
                            'user_role_id' => $role_id,
                            'status' => $this->helper->getCallStatusId('interpreter_disconnected'),
                            'is_called_failed' => 0,
                            'start_time' => $last_call_details->start_time,
                            'end_time' => date("Y-m-d H:i:s"),
                            'duration' => $duration,
                            'call_detail' => $last_call_details->call_detail,
                            'feedback' => isset($reques->feedback) && !empty($reques->feedback) ? $reques->feedback : "",
                        );
                        $add_call_details = CallDetail::create($call_data);
                    }

                    $call_init_message_exist = CallInitMessage::where('call_id', $request->call_request_id)->first();
                    if (isset($call_init_message_exist) && !empty($call_init_message_exist)) {
                        $message_ids = [];
                        $message_ids[] = $call_init_message_exist->interpreter_message_id;
                        $message_ids[] = $call_init_message_exist->supervisor_message_id;
                        $message_ids = implode(",", $message_ids);
                        $user_quickblock_session = $this->helper->createUserSession(env('ADMIN_LOGIN'), env('ADMIN_PASSWORD'));
                        $quickblock_token = $user_quickblock_session['session']['token'];
                        $delete_message = $this->helper->deleteMessage($quickblock_token, $message_ids);
                    }
                    $active_interpreter = ActiveInterpreter::where('user_profile_id', $current_user->user_profile->id)->update(['status' => 1]);

                    if (isset($call_log_data) && isset($call) && isset($active_interpreter)) {
                        DB::commit();
                        $call_data = Call::getCallData()->where('id', $get_call_details->id)->first();
                        $last_call_log = CallLog:: getCallLogData()->where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();
                        $data = [];
                        $data['call_status'] = $call->status_detail->id;
                        $data['call_status_name'] = $call->status_detail->name;
                        $data['call_log_status'] = $last_call_log->to_status_detail->id;
                        $data['call_log_status_name'] = $last_call_log->to_status_detail->name;

                        $message = trans("translate.CALL_ENDED_SUCESSFULLY");
                        $response_array = $this->helper->custom_response(true, $call_data, $message);
                        return response()->json($response_array, Response::HTTP_OK);
                    } else {
                        DB::rollback();
                        $message = trans("translate.CALL_NOT_ENDED_SUCESSFULLY");
                        $response_array = $this->helper->custom_response(false, $call, $message);
                        return response()->json($response_array, Response::HTTP_OK);
                    }
                } else {

                    DB::rollback();
                    $message = trans("translate.CALL_NOT_ENDED_SUCESSFULLY");
                    $response_array = $this->helper->custom_response(false, $call, $message);
                    return response()->json($response_array, Response::HTTP_OK);
                }
            } elseif (in_array($role_id, $this->consumer_roles)) {

                if (isset($get_call_details) && !empty($get_call_details)) {

                    $call = Call::find($get_call_details->id);
                    $call->status = $this->helper->getCallStatusId('end');
                    $call->save();

                    $last_call_log = CallLog::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();

                    $call_log_data = array(
                        'call_id' => $get_call_details->id,
                        'from_status' => $last_call_log->to_status,
                        'to_status' => $this->helper->getCallStatusId('supervisor_disconnected'),
                        'created_by' => $current_user->user_profile->id,
                        'updated_by' => $current_user->user_profile->id
                    );
                    $call_log_data = CallLog::create($call_log_data);

                    $last_call_interpreter_picked_log = CallLog::where(['call_id' => $get_call_details->id, ['to_status', $this->helper->getCallStatusId('interpreter_accepted')]])->orderBy('id', 'DESC')->first();
                    $last_call_interpreter_disconnect_log = CallLog::where(['call_id' => $get_call_details->id, ['to_status', $this->helper->getCallStatusId('interpreter_disconnected')]])->orderBy('id', 'DESC')->first();
                    if (empty($last_call_interpreter_disconnect_log) && !empty($last_call_interpreter_picked_log)) {
                        $last_call_log = CallLog::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();
                        $call_log_data = array(
                            'call_id' => $get_call_details->id,
                            'from_status' => $last_call_log->to_status,
                            'to_status' => $this->helper->getCallStatusId('interpreter_did_not_disconnect'),
                            'created_by' => $current_user->user_profile->id,
                            'updated_by' => $current_user->user_profile->id
                        );
                        $call_log_data = CallLog::create($call_log_data);
                    } elseif (empty($last_call_interpreter_disconnect_log) && !empty($last_call_interpreter_picked_log)) {
                        $call_log_data = array(
                            'call_id' => $get_call_details->id,
                            'from_status' => $last_call_log->to_status,
                            'to_status' => $this->helper->getCallStatusId('complete_from_both'),
                            'created_by' => $current_user->user_profile->id,
                            'updated_by' => $current_user->user_profile->id
                        );
                        $call_log_data = CallLog::create($call_log_data);
                    }


                    $last_call_details = CallDetail::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();

                    if (isset($last_call_details) && !empty($last_call_details)) {
                        $diff = strtotime(date("Y-m-d H:i:s")) - strtotime($last_call_details->start_time);

                        $hours = floor($diff / 3600);
                        $mins = floor(($diff - $hours * 3600) / 60);
                        $s = $diff - ($hours * 3600 + $mins * 60);
                        $duration = $hours . ":" . $mins . ":" . $s;

                        $call_data = array(
                            'call_id' => $get_call_details->id,
                            'user_profile_id' => $current_user->user_profile->id,
                            'user_role_id' => $role_id,
                            'status' => $this->helper->getCallStatusId('supervisor_disconnected'),
                            'is_called_failed' => 0,
                            'start_time' => $last_call_details->start_time,
                            'end_time' => date("Y-m-d H:i:s"),
                            'duration' => $duration,
                            'call_detail' => $last_call_details->call_detail,
                            'feedback' => isset($reques->feedback) && !empty($reques->feedback) ? $reques->feedback : "",
                        );
                        $add_call_details = CallDetail::create($call_data);
                    }
                    $call_init_message_exist = CallInitMessage::where('call_id', $request->call_request_id)->first();
                    if (isset($call_init_message_exist) && !empty($call_init_message_exist)) {
                        $message_ids = [];
                        $message_ids[] = $call_init_message_exist->interpreter_message_id;
                        $message_ids[] = $call_init_message_exist->supervisor_message_id;
                        $message_ids = implode(",", $message_ids);
                        $user_quickblock_session = $this->helper->createUserSession(env('ADMIN_LOGIN'), env('ADMIN_PASSWORD'));
                        $quickblock_token = $user_quickblock_session['session']['token'];
                        $delete_message = $this->helper->deleteMessage($quickblock_token, $message_ids);
                    }

                    if (isset($call)) {

                        DB::commit();
                        $call_data = Call::getCallData()->where('id', $get_call_details->id)->first();
                        $last_call_log = CallLog:: getCallLogData()->where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();
                        $data = [];
                        $data['call_status'] = $call->status_detail->id;
                        $data['call_status_name'] = $call->status_detail->name;
                        $data['call_log_status'] = $last_call_log->to_status_detail->id;
                        $data['call_log_status_name'] = $last_call_log->to_status_detail->name;

                        $call_data['status_data'] = $data;
                        $message = trans("translate.CALL_ENDED_SUCESSFULLY");
                        $response_array = $this->helper->custom_response(true, $call_data, $message);
                        return response()->json($response_array, Response::HTTP_OK);
                    } else {
                        DB::rollback();
                        $message = trans("translate.CALL_NOT_ENDED_SUCESSFULLY");
                        $response_array = $this->helper->custom_response(false, $call, $message);
                        return response()->json($response_array, Response::HTTP_OK);
                    }
                } else {
                    DB::rollback();
                    $message = trans("translate.CALL_NOT_ENDED_SUCESSFULLY");
                    $response_array = $this->helper->custom_response(false, $call, $message);
                    return response()->json($response_array, Response::HTTP_OK);
                }
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

//    public function end_call_signable(Request $request) {
//        try {
//            DB::beginTransaction();
//            $current_user = $this->helper->getLoginUser();
//
//            Log::info('Call request end calls: Params: ' . json_encode($request->all()));
//
//            
//
//            if (isset($current_user) && !empty($current_user)) {
//                $role_id = $current_user->user_profile->user_roles[0]->role_id;
//
//                if (in_array($role_id, $this->supplier_roles)) {
//                    $get_call_details = Call::getCallData()->where('calls.status', 13)->whereHas('call_details', function($query) use($current_user) {
//                                $query->where(['user_profile_id' => $current_user->user_profile->id]);
//                            })->orderBy('id', 'DESC')->first();
//                }
//            } else {
//
//                $get_call_details = Call::getCallData()->where('id', $request->call_request_id)->first();
//
//                $current_user = User::getUserData()->where('id', $request->user_id)->first();
//            }
//
//            if (isset($get_call_details) && !empty($get_call_details)) {
//
//                $call = Call::find($get_call_details->id);
//                $call->status = $this->helper->getCallStatusId('end');
//                $call->save();
//
//                $last_call_log = CallLog::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();
//
//
//                $call_log_data = array(
//                    'call_id' => $get_call_details->id,
//                    'from_status' => $last_call_log->to_status,
//                    'to_status' => $this->helper->getCallStatusId('supervisor_disconnected'),
//                    'created_by' => $current_user->user_profile->id,
//                    'updated_by' => $current_user->user_profile->id
//                );
//                $call_log_data = CallLog::create($call_log_data);
//
//                $last_call_log = CallLog::where(['call_id' => $get_call_details->id, 'to_status !=' => $this->helper->getCallStatusId('interpreter_disconnected')])->orderBy('id', 'DESC')->first();
//
//                if (empty($last_call_log)) {
//                    $last_call_log = CallLog::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();
//                    $call_log_data = array(
//                        'call_id' => $get_call_details->id,
//                        'from_status' => $last_call_log->to_status,
//                        'to_status' => $this->helper->getCallStatusId('interpreter_did_not_disconnect'),
//                        'created_by' => $current_user->user_profile->id,
//                        'updated_by' => $current_user->user_profile->id
//                    );
//                    $call_log_data = CallLog::create($call_log_data);
//                } else {
//                    $call_log_data = array(
//                        'call_id' => $get_call_details->id,
//                        'from_status' => $last_call_log->to_status,
//                        'to_status' => $this->helper->getCallStatusId('complete_from_both'),
//                        'created_by' => $current_user->user_profile->id,
//                        'updated_by' => $current_user->user_profile->id
//                    );
//                    $call_log_data = CallLog::create($call_log_data);
//                }
//
//
//                $last_call_details = CallDetail::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();
//
//
//                $call_data = array(
//                    'call_id' => $get_call_details->id,
//                    'user_profile_id' => $last_call_details->user_profile_id,
//                    'user_role_id' => $last_call_details->user_role_id,
//                    'status' => $this->helper->getCallStatusId('supervisor_disconnected'),
//                    'is_called_failed' => 0,
//                    'start_time' => $last_call_details->start_time,
//                    'end_time' => date("Y-m-d H:i:s"),
//                    'call_detail' => $last_call_details->call_detail,
//                    'feedback' => isset($reques->feedback) && !empty($reques->feedback) ? $reques->feedback : "",
//                );
//                $add_call_details = CallDetail::create($call_data);
//
//                $active_interpreter = ActiveInterpreter::where('user_profile_id', $last_call_details->user_profile->id)->update(['status' => 1]);
//
//
//                if (isset($add_call_details) && isset($call_log_data) && isset($call) && isset($active_interpreter)) {
//                    DB::commit();
//                    $message = trans("translate.CALL_ENDED_SUCESSFULLY");
//                    $response_array = $this->helper->custom_response(true, $add_call_details, $message);
//                    return response()->json($response_array, Response::HTTP_OK);
//                } else {
//                    DB::rollback();
//                    $message = trans("translate.CALL_NOT_ENDED_SUCESSFULLY");
//                    $response_array = $this->helper->custom_response(false, $add_call_details, $message);
//                    return response()->json($response_array, Response::HTTP_OK);
//                }
//            } else {
//                DB::rollback();
//                $message = trans("translate.CALL_NOT_ENDED_SUCESSFULLY");
//                $response_array = $this->helper->custom_response(false, $add_call_details, $message);
//                return response()->json($response_array, Response::HTTP_OK);
//            }
//        } catch (\Exception $ex) {
//            $response_array = $this->helper->sendError($ex->getMessage(), 500);
//            Log::info('Error captured: ' . json_encode($response_array));
//            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
//        }
//    }

    public function average_call_history_data($conditions) {
        try {
            // Total Calls 
            $total_calls = Call::getCallData()->count();

            // Total Average Times
            $average_times = CallDetail::select(DB::raw("AVG(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) AS average_times"))->whereNotNull(['start_time', 'end_time'])->first();
            $hours = floor($average_times['average_times'] / 3600);
            $mins = floor(($average_times['average_times'] - $hours * 3600) / 60);
            $s = $average_times['average_times'] - ($hours * 3600 + $mins * 60);
            $average_times = $hours . ":" . $mins . ":" . floor($s);

            // Total Average Language
            $average_language = Call::select(DB::raw("ROUND(AVG(language_id)) AS average_lanuage_id"))->first();
            $average_language = Language::select('id', 'name', 'is_active')->where('id', $average_language['average_lanuage_id'])->first();

            // Total Average Purpose
            $average_purpose = Call::select(DB::raw("ROUND(AVG(purpose_id)) AS average_purpose_id"))->first();
            $average_purpose = Purpose::select('id', 'name', 'description')->where('id', $average_purpose['average_purpose_id'])->first();

            $average_detail = [
                "total_calls" => $total_calls,
                "average_times" => $average_times,
                "average_language" => $average_language,
                "average_purpose" => $average_purpose
            ];

            if (isset($average_detail) && !empty($average_detail)) {
                $message = trans("translate.CALL_HISTORY_DATA_FOUND");
                $response_array = $this->helper->custom_response(true, $average_detail, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function call_history(Request $request) {
        try {
            $conditions = [];

            $average_call_data = $this->average_call_history_data($conditions);
            $average_call_data = json_decode(json_encode($average_call_data), true);

            $limit = isset($request->limit) && !empty($request->limit) ? $request->limit : 20;

            $call_data = Call::getCallData();
            if (isset($request->user_id) && !empty($request->user_id)) {
                $call_data->whereHas('call_details', function ($query) use ($request) {
                    $query->whereHas('user_profile', function ($query) use ($request) {
                        $query->where('id', $request->user_id);
                    });
                });
            }

            if (isset($request->call_id) && !empty($request->call_id)) {
                $call_data->where('id', $request->call_id);
            }
            $call_data = $call_data->paginate($limit);

            $call_details['average_call_details'] = $average_call_data['original']['data'];

            $call_details['call_details'] = $call_data;

            if (isset($call_details) && !empty($call_details)) {
                $message = trans("translate.CALL_HISTORY_DATA_FOUND");
                $response_array = $this->helper->custom_response(true, $call_details, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function pending_calls(Request $request) {
        try {
            $current_user = $this->helper->getLoginUser();
            Log::info('Call request pending calls: Params: ' . json_encode($request->all()));

            $call_data = Call::getCallReportData()->where('status', $this->helper->getCallStatusId('operated_at_accepted'))->whereHas('call_details', function ($query) use ($current_user) {
                        $query->where(['user_profile_id' => $current_user->user_profile->id]);
                    })->orderBy('id', 'DESC')->first();

            if (isset($call_data) && !empty($call_data)) {
                $call_data->call_details[0]->call_detail = json_decode($call_data->call_details[0]->call_detail, true);
                $message = trans("translate.PENDING_CALL_LISTS");
                $response_array = $this->helper->custom_response(true, $call_data, $message);
                return response()->json($response_array, Response::HTTP_OK);
            } else {
                $message = trans("translate.EMPTY_LIST");
                $response_array = $this->helper->custom_response(false, $call_data, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function call_action(Request $request) {
        try {
            DB::beginTransaction();
            Log::info('Call request end calls: Params: ' . json_encode($request->all()));
            $validator = Validator::make($request->all(), [
                        'action' => 'required'
            ]);

            if ($validator->fails()) {
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }

            $current_user = $this->helper->getLoginUser();
            $role_id = $current_user->user_profile->user_roles[0]->role_id;

            if (isset($request->action) && !empty($request->action) && $request->action == 1) {


                if (in_array($role_id, $this->supplier_roles)) {
                    $get_call_details = Call::getCallData()->whereHas('call_details', function ($query) use ($current_user) {
                                $query->where('user_profile_id', $current_user->user_profile->id);
                            })->orderBy('id', 'DESC')->first();

                    $call = Call::find($get_call_details->id);
                    $call->status = $this->helper->getCallStatusId('in_progress');
                    $call->save();

                    $last_call_log = CallLog::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();

                    $call_log_data = array(
                        'call_id' => $get_call_details->id,
                        'from_status' => $last_call_log->to_status,
                        'to_status' => $this->helper->getCallStatusId('in_progress'),
                        'created_by' => $current_user->user_profile->id,
                        'updated_by' => $current_user->user_profile->id
                    );
                    $call_log_data = CallLog::create($call_log_data);

                    $last_call_details = CallDetail::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();

                    $call_data = array(
                        'call_id' => $get_call_details->id,
                        'user_profile_id' => $last_call_details->user_profile_id,
                        'user_role_id' => $last_call_details->user_role_id,
                        'status' => $this->helper->getCallStatusId('in_progress'),
                        'is_called_failed' => 0,
                        'start_time' => $last_call_details->start_time,
                        'end_time' => date("Y-m-d H:i:s"),
                        'call_detail' => $last_call_details->call_detail,
                        'feedback' => isset($reques->feedback) && !empty($reques->feedback) ? $reques->feedback : "",
                    );
                    $add_call_details = CallDetail::create($call_data);

                    if (isset($add_call_details) && isset($call_log_data) && isset($call)) {
                        DB::commit();
                        $message = trans("translate.CALL_ACCEPT_SUCCESSFULLY");
                        $response_array = $this->helper->custom_response(true, $add_call_details, $message);
                        return response()->json($response_array, Response::HTTP_OK);
                    } else {
                        DB::rollback();
                        $message = trans("translate.CALL_ACCEPT_NOT_SUCCESSFULLY");
                        $response_array = $this->helper->custom_response(false, $add_call_details, $message);
                        return response()->json($response_array, Response::HTTP_OK);
                    }
                }
            } else if (isset($request->action) && !empty($request->action) && $request->action == 2) {
                if (in_array($role_id, $this->supplier_roles)) {
                    $validator = Validator::make($request->all(), [
                                'reason' => 'required'
                    ]);

                    if ($validator->fails()) {
                        $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                        return response()->json($response_array, Response::HTTP_BAD_REQUEST);
                    }

                    $get_call_details = Call::getCallData()->whereHas('call_details', function ($query) use ($current_user) {
                                $query->where('user_profile_id', $current_user->user_profile->id);
                            })->orderBy('id', 'DESC')->first();

                    $call = Call::find($get_call_details->id);
                    $call->status = $this->helper->getCallStatusId('interpreter_rejected');
                    $call->reason_id = isset($request->reason) && !empty($request->reason) ? $request->reason : null;
                    $call->save();

                    $last_call_log = CallLog::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();

                    $call_log_data = array(
                        'call_id' => $get_call_details->id,
                        'from_status' => $last_call_log->to_status,
                        'to_status' => $this->helper->getCallStatusId('interpreter_rejected'),
                        'created_by' => $current_user->user_profile->id,
                        'updated_by' => $current_user->user_profile->id
                    );
                    $call_log_data = CallLog::create($call_log_data);

                    $last_call_details = CallDetail::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();

                    $call_data = array(
                        'call_id' => $get_call_details->id,
                        'user_profile_id' => $last_call_details->user_profile_id,
                        'user_role_id' => $last_call_details->user_role_id,
                        'status' => $this->helper->getCallStatusId('interpreter_rejected'),
                        'is_called_failed' => 0,
                        'start_time' => $last_call_details->start_time,
                        'end_time' => date("Y-m-d H:i:s"),
                        'call_detail' => $last_call_details->call_detail,
                        'feedback' => isset($reques->feedback) && !empty($reques->feedback) ? $reques->feedback : "",
                    );
                    $add_call_details = CallDetail::create($call_data);

                    if (isset($add_call_details) && isset($call_log_data) && isset($call)) {
                        DB::commit();
                        $message = trans("translate.CALL_DECLINE_SUCCESSFULLY");
                        $response_array = $this->helper->custom_response(true, $add_call_details, $message);
                        return response()->json($response_array, Response::HTTP_OK);
                    } else {
                        DB::rollback();
                        $message = trans("translate.CALL_DECLINE_NOT_SUCCESSFULLY");
                        $response_array = $this->helper->custom_response(false, $add_call_details, $message);
                        return response()->json($response_array, Response::HTTP_OK);
                    }
                }
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function que_calls(Request $request) {
        try {
            $current_user = $this->helper->getLoginUser();
            Log::info('Call request que calls: Params: ' . json_encode($request->all()));

            $message = trans("just list que calls");
            $response_array = $this->helper->custom_response(true, array(), $message);
            return response()->json($response_array, Response::HTTP_OK);
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function call_report_history_api(Request $request) {
        try {
            $Filepath = url('/') . "/call_report_template/";
            $message = trans("translate.CALL_REPORT_DATA");
            $response_array = $this->helper->custom_response(true, $Filepath, $message);
            return response()->json($response_array, Response::HTTP_OK);
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function active_user_report_history_api(Request $request) {
        try {
            $Filepath = url('/') . "/active_user_report_template/";
            $message = trans("translate.ACTIVE_USER_REPORT_DATA");
            $response_array = $this->helper->custom_response(true, $Filepath, $message);
            return response()->json($response_array, Response::HTTP_OK);
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function active_call_report_history_api(Request $request) {
        try {
            $Filepath = url('/') . "/active_call_report_template/";
            $message = trans("translate.ACTIVE_CALL_REPORT_DATA");
            $response_array = $this->helper->custom_response(true, $Filepath, $message);
            return response()->json($response_array, Response::HTTP_OK);
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function frequent_user_report_history_api(Request $request) {
        try {
            $Filepath = url('/') . "/frequent_user_report_template/";
            $message = trans("translate.FREQUENT_USER_REPORT_DATA");
            $response_array = $this->helper->custom_response(true, $Filepath, $message);
            return response()->json($response_array, Response::HTTP_OK);
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function call_report_history_template(Request $request) {
        try {

            $limit = isset($request->limit) && !empty($request->limit) ? $request->limit : 20;
            $call_report_data = Call::getCallReportData()->get();
            if (!empty($call_report_data)) {
                $conditions = [];
                $message = trans("translate.CALL_REPORT_DATA");
                $average_call_data = $this->average_call_history_data($conditions);
                $average_call_data = json_decode(json_encode($average_call_data), true);
                $call_details['average_call_details'] = $average_call_data['original']['data'];
            } else {
                $call_report_data = [];
                $message = trans("translate.CALL_REPORT_DATA_NOT_FOUND");
            }

            return response()->view('template.call_report_template', compact(['call_report_data', 'call_details']));
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function active_user_report_history_template(Request $request) {
        try {
            $current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;
            if ($roles == '') {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                if (in_array($role_id, $this->supplier_roles)) {
                    $status = (isset($request->status) && !empty($request->status)) ? $request->status : 4;
                    $user_profiles_data = User::getUserData()
                            ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                            ->join('role_users', 'user_profies.id', '=', 'role_users.user_profile_id')
                            ->join('roles', 'roles.id', "=", 'role_users.role_id')
                            ->join('user_languages', 'user_profies.id', "=", 'user_languages.user_profile_id')
                            ->join('active_interpreters', 'user_profies.id', "=", 'active_interpreters.user_profile_id')
                            ->where('active_interpreters.status', $status)
                            ->whereIn('roles.id', $this->supplier_roles)
                            ->GroupBy('user_profies.id');

                    $user_profiles_data = $user_profiles_data->get()->toArray();
                }

                if (in_array($role_id, $this->consumer_roles)) {
                    $status = (isset($request->status) && !empty($request->status)) ? $request->status : 2;
                    $user_profiles_data = User::getUserData()
                            ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                            ->join('role_users', 'user_profies.id', '=', 'role_users.user_profile_id')
                            ->join('roles', 'roles.id', "=", 'role_users.role_id')
                            ->join('user_languages', 'user_profies.id', "=", 'user_languages.user_profile_id')
                            ->join('calls', 'user_profies.id', "=", 'calls.from_user_profile_id')
                            ->where('calls.status', $status)
                            ->whereIn('roles.id', $this->consumer_roles)
                            ->GroupBy('user_profies.id');

                    $user_profiles_data = $user_profiles_data->get()->toArray();
                }
                if (isset($user_profiles_data) && !empty($user_profiles_data)) {
                    return response()->view('template.active_user_report_template', compact(['user_profiles_data']));
                } else {
                    return response()->view('template.active_user_report_template', compact(['user_profiles_data']));
                }
            } else {
                $message = trans("translate.USER_ROLE_NOT_FOUND");
                $response_array = $this->helper->custom_response(true, array(), $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function active_call_report_history_template_old(Request $request) {
        try {

            $current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                $status = (isset($request->status) && !empty($request->status)) ? $request->status : 2;
                $user_profiles_data = array();
                if (in_array($role_id, $this->supplier_roles)) {
                    $user_profiles_data = CallDetail::getInterpreterCallDetailData()->where('status', $status)->whereIn('user_role_id', $this->supplier_roles);
                    $user_profiles_data = $user_profiles_data->get()->toArray();
                }

                if (in_array($role_id, $this->consumer_roles)) {
                    $user_profiles_data = Call::getSignableCallData()->where('status', $status)->whereIn('from_user_role_id', $this->consumer_roles);

                    $user_profiles_data = $user_profiles_data->get()->toArray();
                }

                return response()->view('template.active_call_report_template', compact(['user_profiles_data']));
            } else {
                $message = trans("translate.USER_ROLE_NOT_FOUND");
                $response_array = $this->helper->custom_response(true, array(), $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    //new

    public function active_call_report_history_template(Request $request) {
        try {
            // Total Calls 
            $total_active_calls = Call::getCallReportData()->where('status', 2)->count();
            $total_available_interpreter = ActiveInterpreter::where('status', 1)->count();
            // Total Average Times
            $average_times = CallDetail::select(DB::raw("AVG(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) AS average_times"))->whereNotNull(['start_time', 'end_time'])->first();
            $hours = floor($average_times['average_times'] / 3600);
            $mins = floor(($average_times['average_times'] - $hours * 3600) / 60);
            $s = $average_times['average_times'] - ($hours * 3600 + $mins * 60);
            $average_times = $hours . ":" . $mins . ":" . floor($s);

            $average_detail = [
                "total_active_calls" => $total_active_calls,
                "total_available_interpreter" => $total_available_interpreter,
                "average_times" => $average_times
            ];

            $limit = isset($request->limit) && !empty($request->limit) ? $request->limit : 20;
            $user_profiles_data = Call::getCallReportData()->where('status', 2)->get();
            if (!empty($user_profiles_data)) {
                $message = trans("translate.ACTIVE_CALL_REPORT_DATA");
            } else {
                $user_profiles_data = [];
                $message = trans("translate.ACTIVE_CALL_REPORT_DATA_NOT_FOUND");
            }

            return response()->view('template.active_call_report_template', compact(['user_profiles_data', 'average_detail']));
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function call_report_history_template_new(Request $request) {
        try {
            $current_user = $this->helper->getLoginUser();
            // Total Calls 
            //$current_user_profile_id = $current_user->user_profile->id;
            $current_user_profile_id = 2;
            $total_calls = Call::getCallReportData()->where('from_user_profile_id', $current_user_profile_id)->GroupBy('from_user_profile_id')->count();

            // Total Average Times
            $average_times = CallDetail::select(DB::raw("AVG(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) AS average_times"))->whereNotNull(['start_time', 'end_time'])->join('calls', 'calls.id', "=", 'call_details.call_id')->where('calls.from_user_profile_id', $current_user_profile_id)->first();
            $hours = floor($average_times['average_times'] / 3600);
            $mins = floor(($average_times['average_times'] - $hours * 3600) / 60);
            $s = $average_times['average_times'] - ($hours * 3600 + $mins * 60);
            $average_times = $hours . ":" . $mins . ":" . floor($s);
            // Total Average Language
            $average_language = Call::select(DB::raw("ROUND(AVG(language_id)) AS average_lanuage_id"))->where('from_user_profile_id', $current_user_profile_id)->first();
            $average_language = Language::select('id', 'name', 'is_active')->where('id', $average_language['average_lanuage_id'])->first();

            // Total Average Purpose
            $average_purpose = Call::select(DB::raw("ROUND(AVG(purpose_id)) AS average_purpose_id"))->where('from_user_profile_id', $current_user_profile_id)->first();
            $average_purpose = Purpose::select('id', 'name', 'description')->where('id', $average_purpose['average_purpose_id'])->first();

            $average_detail = [
                "total_calls" => $total_calls,
                "average_times" => $average_times,
                "average_language" => $average_language,
                "average_purpose" => $average_purpose
            ];

            $limit = isset($request->limit) && !empty($request->limit) ? $request->limit : 20;

            $call_data = Call::getCallReportData()->where('from_user_profile_id', $current_user_profile_id);

            if (isset($request->call_id) && !empty($request->call_id)) {
                $call_data->where('id', $request->call_id);
            }
            //$call_data = $call_data->paginate($limit);
            $call_data = $call_data->get();

            $call_details['average_call_details'] = $average_detail;

            $call_report_data = $call_data;

            if (isset($call_details) && !empty($call_details)) {
                return response()->view('template.call_report_template', compact(['call_report_data', 'call_details']));
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function frequent_user_report_history_template(Request $request) {
        try {
            $user_profiles_data = User::getUserData()
                    ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                    ->join('calls', 'user_profies.id', "=", 'calls.from_user_profile_id')
                    ->selectRaw('count(calls.from_user_profile_id) as total_call')
                    ->orderBy('total_call', 'DESC')
                    ->GroupBy('calls.from_user_profile_id');

            $user_profiles_data = $user_profiles_data->get()->toArray();

            return response()->view('template.frequent_user_report_template', compact('user_profiles_data'));
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function call_report_history(Request $request) {
        try {

            $limit = isset($request->limit) && !empty($request->limit) ? $request->limit : 20;
            $call_report_data = Call::getCallReportData()->paginate($limit); // pagination query
            if (!empty($call_report_data)) {
                $message = trans("translate.CALL_REPORT_DATA");
            } else {
                $call_report_data = [];
                $message = trans("translate.CALL_REPORT_DATA_NOT_FOUND");
            }

            $response_array = $this->helper->custom_response(true, $call_report_data, $message);
            return response()->json($response_array, Response::HTTP_OK);
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function active_user(Request $request) {
        try {
            $current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                if (in_array($role_id, $this->supplier_roles)) {
                    $status = (isset($request->status) && !empty($request->status)) ? $request->status : 4;
                    $user_profiles_data = User::getUserData()
                            ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                            ->join('role_users', 'user_profies.id', '=', 'role_users.user_profile_id')
                            ->join('roles', 'roles.id', "=", 'role_users.role_id')
                            ->join('user_languages', 'user_profies.id', "=", 'user_languages.user_profile_id')
                            ->join('active_interpreters', 'user_profies.id', "=", 'active_interpreters.user_profile_id')
                            ->where('active_interpreters.status', $status)
                            ->whereIn('roles.id', $this->supplier_roles)
                            ->GroupBy('user_profies.id');

                    $user_profiles_data = $user_profiles_data->get()->toArray();
                }

                if (in_array($role_id, $this->consumer_roles)) {
                    $status = (isset($request->status) && !empty($request->status)) ? $request->status : 2;
                    $user_profiles_data = User::getUserData()
                            ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                            ->join('role_users', 'user_profies.id', '=', 'role_users.user_profile_id')
                            ->join('roles', 'roles.id', "=", 'role_users.role_id')
                            ->join('user_languages', 'user_profies.id', "=", 'user_languages.user_profile_id')
                            ->join('calls', 'user_profies.id', "=", 'calls.from_user_profile_id')
                            ->where('calls.status', $status)
                            ->whereIn('roles.id', $this->consumer_roles)
                            ->GroupBy('user_profies.id');

                    $user_profiles_data = $user_profiles_data->get()->toArray();
                }
                $user_profiles_data_count = count($user_profiles_data);
                if (isset($user_profiles_data) && !empty($user_profiles_data)) {
                    $response_array = $this->helper->custom_response(true, $user_profiles_data, trans("translate.ACTIVE_USER_DATA"), true, $user_profiles_data_count);
                    return response()->json($response_array, Response::HTTP_OK);
                } else {
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.ACTIVE_USER_DATA_NOT_FOUND"));
                    return response()->json($response_array, Response::HTTP_OK);
                }
            } else {
                $message = trans("translate.USER_ROLE_NOT_FOUND");
                $response_array = $this->helper->custom_response(true, array(), $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function active_call(Request $request) {
        try {

            $current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                $status = (isset($request->status) && !empty($request->status)) ? $request->status : 2;

                if (in_array($role_id, $this->supplier_roles)) {
                    $user_profiles_data = CallDetail::getInterpreterCallDetailData()->where('status', $status)->whereIn('user_role_id', $this->supplier_roles);
                    $user_profiles_data = $user_profiles_data->get()->toArray();
                    //->join('call_details', 'calls.id', "=", 'call_details.call_id');
                }

                if (in_array($role_id, $this->consumer_roles)) {
                    $user_profiles_data = Call::getSignableCallData()->where('status', $status)->whereIn('from_user_role_id', $this->consumer_roles);

                    $user_profiles_data = $user_profiles_data->get()->toArray();
                }
                $user_profiles_data_count = count($user_profiles_data);
                if (isset($user_profiles_data) && !empty($user_profiles_data)) {
                    $response_array = $this->helper->custom_response(true, $user_profiles_data, trans("translate.ACTIVE_CALL_DATA"), true, $user_profiles_data_count);
                    return response()->json($response_array, Response::HTTP_OK);
                } else {
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.ACTIVE_CALL_DATA_NOT_FOUND"));
                    return response()->json($response_array, Response::HTTP_OK);
                }
            } else {
                $message = trans("translate.USER_ROLE_NOT_FOUND");
                $response_array = $this->helper->custom_response(true, array(), $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function frequent_user(Request $request) {
        try {
            /* $call_report_data = Call::getFrequentCallReportData()->selectRaw('count(from_user_profile_id) as total_call')->orderBy('total_call','DESC')->GroupBy('from_user_profile_id')->get(); */
            $user_profiles_data = User::getUserData()
                    ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                    ->join('calls', 'user_profies.id', "=", 'calls.from_user_profile_id')
                    ->selectRaw('count(calls.from_user_profile_id) as total_call')
                    ->orderBy('total_call', 'DESC')
                    ->GroupBy('calls.from_user_profile_id');

            $user_profiles_data = $user_profiles_data->get()->toArray();

            $user_profiles_data_count = count($user_profiles_data);
            if (!empty($user_profiles_data)) {
                $message = trans("translate.FREQUENT_CALL_REPORT_DATA");
            } else {
                $user_profiles_data = [];
                $message = trans("translate.FREQUENT_CALL_REPORT_DATA_NOT_FOUND");
            }

            $response_array = $this->helper->custom_response(true, $user_profiles_data, $message, true, $user_profiles_data_count);
            return response()->json($response_array, Response::HTTP_OK);
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function call_report_history_export(Request $request) {
        $current_user = auth()->user();
        try {
            $extension = $request->export_type;
            if (!empty($extension)) {
                $extension = $extension;
            } else {
                $extension = 'csv';
            }
            $data = array();
            $output = array();
            $data['title'] = 'Call Report Excel Sheet';

            //echo $user_id;exit();
            $call_report_data = Call::getCallReportData();
            if (isset($request->call_id) && !empty($request->call_id)) {
                $call_report_data = $call_report_data->where('id', $request->call_id);
            }
            if (isset($request->status) && !empty($request->status)) {
                $call_report_data = $call_report_data->where('status', $request->status);
            }

            if (isset($request->user_id) && !empty($request->user_id)) {
                $user_id = $request->user_id;
                $call_report_data = $call_report_data->WhereHas('call_details', function ($query) use ($user_id) {
                    $query->where('user_profile_id', $user_id);
                });
            }

            $call_report_data = $call_report_data->get()->toArray();
            $Filepath = [];
            if (!empty($call_report_data)) {
                $fileName = 'Call_list_' . time();
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                $sheet->setCellValue('A1', '#');
                $sheet->setCellValue('B1', 'Call Id');
                $sheet->setCellValue('C1', 'from_user_first_name');
                $sheet->setCellValue('D1', 'from_user_last_name');
                $sheet->setCellValue('E1', 'from_user_gender');
                $sheet->setCellValue('F1', 'from_user_date_of_join');
                $sheet->setCellValue('G1', 'from_user_date_of_birth');
                $sheet->setCellValue('H1', 'from_user_user_id');
                $sheet->setCellValue('I1', 'from_user_company_name');
                $sheet->setCellValue('J1', 'from_user_role_name');
                $sheet->setCellValue('K1', 'from_user_role_display_name');
                $sheet->setCellValue('L1', 'purpose_name');
                $sheet->setCellValue('M1', 'purpose_description');
                $sheet->setCellValue('N1', 'language');
                $sheet->setCellValue('O1', 'reason');
                $sheet->setCellValue('P1', 'start_time');
                $sheet->setCellValue('Q1', 'end_time');
                $sheet->setCellValue('R1', 'band_width');
                $sheet->setCellValue('S1', 'resolution');
                $sheet->setCellValue('T1', 'is_called_failed');
                $sheet->setCellValue('U1', 'feedback');
                $sheet->setCellValue('V1', 'to_user_first_name');
                $sheet->setCellValue('W1', 'to_user_last_name');
                $sheet->setCellValue('X1', 'to_user_gender');
                $sheet->setCellValue('Y1', 'to_user_date_of_join');
                $sheet->setCellValue('Z1', 'to_user_date_of_birth');
                $sheet->setCellValue('AA1', 'to_user_user_id');
                $sheet->setCellValue('AB1', 'to_user_company_name');
                $sheet->setCellValue('AC1', 'to_user_role_name');
                $sheet->setCellValue('AD1', 'to_user_role_display_name');
                $sheet->setCellValue('AE1', 'status');

                $rowCount = 2;
                $cnt = 1;
                //echo "<pre>"; print_r($call_report_data);exit();

                foreach ($call_report_data as $key => $call_data) {
                    if (isset($call_data['call_details'][0])) {
                        $reason = 'sd';
                        $start_time = $call_data['call_details'][0]['start_time'];
                        $end_time = $call_data['call_details'][0]['end_time'];
                        $band_width = $call_data['call_details'][0]['band_width'];
                        $resolution = $call_data['call_details'][0]['resolution'];
                        $is_called_failed = $call_data['call_details'][0]['is_called_failed'];
                        $feedback = $call_data['call_details'][0]['feedback'];
                        $first_name = $call_data['call_details'][0]['user_profile']['first_name'];
                        $last_name = $call_data['call_details'][0]['user_profile']['last_name'];
                        $gender = $call_data['call_details'][0]['user_profile']['gender'];
                        $date_of_join = $call_data['call_details'][0]['user_profile']['date_of_join'];
                        $date_of_birth = $call_data['call_details'][0]['user_profile']['date_of_birth'];
                        $user_id = $call_data['call_details'][0]['user_profile']['user_id'];
                        $company = $call_data['call_details'][0]['user_profile']['company']['company_name'];
                        $name = $call_data['call_details'][0]['user_role']['name'];
                        $display_name = $call_data['call_details'][0]['user_role']['display_name'];

                        $from_date_of_join = date('d/m/Y', strtotime($call_data['from_user_profile']['date_of_join']));
                        $from_date_of_birth = date('d/m/Y', strtotime($call_data['from_user_profile']['date_of_birth']));

                        $to_date_of_join = date('d/m/Y', strtotime($call_data['call_details'][0]['user_profile']['date_of_join']));
                        $to_date_of_birth = date('d/m/Y', strtotime($call_data['call_details'][0]['user_profile']['date_of_birth']));
                        
                        $start_time = date('d/m/Y h:i:sa', strtotime($call_data['call_details'][0]['start_time']));
                        $end_time = date('d/m/Y h:i:sa', strtotime($call_data['call_details'][0]['end_time']));

                        if($from_date_of_join == '01/01/1970'){
                            $$from_date_of_join = '';                            
                        }
                        if($from_date_of_birth == '01/01/1970'){
                            $$from_date_of_birth = '';                            
                        }
                        if($to_date_of_join == '01/01/1970'){
                            $$to_date_of_join = '';                            
                        }
                        if($to_date_of_birth == '01/01/1970'){
                            $$to_date_of_birth = '';                            
                        }
                        if(date('d/m/Y', strtotime($call_data['call_details'][0]['start_time'])) == '01/01/1970'){
                            $start_time = '';                            
                        }
                        if(date('d/m/Y', strtotime($call_data['call_details'][0]['end_time'])) == '01/01/1970'){
                            $end_time = '';                            
                        }

                        if (!empty($call_data['call_details'][0]['status'])) {
                            $status = CallStatus::where('id', $call_data['call_details'][0]['status'])->pluck('value')->first();
                        }
                    } else {
                        $reason = $start_time = $end_time = $band_width = $resolution = $is_called_failed = $feedback = $first_name = $last_name = $gender = $to_date_of_join = $to_date_of_birth = $user_id = $company = $name = $display_name = $to_status = $status = $from_date_of_join =  $from_date_of_birth = '';
                    }


                    $sheet->setCellValue('A' . $rowCount, $cnt);
                    $sheet->setCellValue('B' . $rowCount, $call_data['id']);
                    $sheet->setCellValue('C' . $rowCount, $call_data['from_user_profile']['first_name']);
                    $sheet->setCellValue('D' . $rowCount, $call_data['from_user_profile']['last_name']);
                    $sheet->setCellValue('E' . $rowCount, $call_data['from_user_profile']['gender']);
                    $sheet->setCellValue('F' . $rowCount, $from_date_of_join);
                    $sheet->setCellValue('G' . $rowCount, $from_date_of_birth);
                    $sheet->setCellValue('H' . $rowCount, $call_data['from_user_profile']['user_id']);
                    $sheet->setCellValue('I' . $rowCount, $call_data['from_user_profile']['company']['company_name']);
                    $sheet->setCellValue('J' . $rowCount, $call_data['from_user_role']['name']);
                    $sheet->setCellValue('K' . $rowCount, $call_data['from_user_role']['display_name']);
                    $sheet->setCellValue('L' . $rowCount, $call_data['purpose']['name']);
                    $sheet->setCellValue('M' . $rowCount, $call_data['purpose']['description']);
                    $sheet->setCellValue('N' . $rowCount, $call_data['language']['name']);
                    $sheet->setCellValue('O' . $rowCount, $reason);

                    $sheet->setCellValue('P' . $rowCount, $start_time);
                    $sheet->setCellValue('Q' . $rowCount, $end_time);
                    $sheet->setCellValue('R' . $rowCount, $band_width);
                    $sheet->setCellValue('S' . $rowCount, $resolution);
                    $sheet->setCellValue('T' . $rowCount, $is_called_failed);
                    $sheet->setCellValue('U' . $rowCount, $feedback);
                    $sheet->setCellValue('V' . $rowCount, $first_name);
                    $sheet->setCellValue('W' . $rowCount, $last_name);
                    $sheet->setCellValue('X' . $rowCount, $gender);
                    $sheet->setCellValue('Y' . $rowCount, $to_date_of_join);
                    $sheet->setCellValue('Z' . $rowCount, $to_date_of_birth);
                    $sheet->setCellValue('AA' . $rowCount, $user_id);
                    $sheet->setCellValue('AB' . $rowCount, $company);
                    $sheet->setCellValue('AC' . $rowCount, $name);
                    $sheet->setCellValue('AD' . $rowCount, $display_name);
                    $sheet->setCellValue('AE' . $rowCount, $status);

                    $rowCount++;
                    $cnt++;
                }
                if ($extension == 'csv') {
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
                    $writer->setPreCalculateFormulas(false);
                    $fileName = $fileName . '.csv';
                } elseif ($extension == 'xlsx') {
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                    $fileName = $fileName . '.xlsx';
                } else {
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
                    $fileName = $fileName . '.xls';
                }



                $folderPath = public_path('call_report_export');
                if (!is_dir($folderPath)) {
                    mkdir($folderPath, 0777, true);
                }
                $writer->save($folderPath . "/" . $fileName);

                header("Content-Type: application/vnd.ms-excel");

                $Filepath = url('/') . "/call_report_export/" . $fileName;


                CallController::send_download_report_mail($current_user->email,$current_user->user_profile->first_name,$current_user->user_profile->last_name,$Filepath);
                $response_array = $this->helper->custom_response(true, $Filepath, trans("translate.CALL_REPORT_DATA"));
                return response()->json($response_array, Response::HTTP_OK);
            } else {
                $response_array = $this->helper->custom_response(false, array(), trans("translate.CALL_REPORT_DATA_NOT_FOUND"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function frequent_user_report_history_export(Request $request) {
        $current_user = auth()->user();
        try {
            $extension = $request->export_type;
            if (!empty($extension)) {
                $extension = $extension;
            } else {
                $extension = 'csv';
            }
            $data = array();
            $output = array();
            $data['title'] = 'Frequent User Excel Sheet';

            $user_profiles_data = User::getUserData()
                    ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                    ->join('calls', 'user_profies.id', "=", 'calls.from_user_profile_id')
                    ->selectRaw('count(calls.from_user_profile_id) as total_call')
                    ->orderBy('total_call', 'DESC')
                    ->GroupBy('calls.from_user_profile_id');

            if (isset($request->user_id) && !empty($request->user_id)) {
                $user_id = $request->user_id;
                $user_profiles_data = $user_profiles_data->where('user_profies.id', $user_id);
            }
            $user_profiles_data = $user_profiles_data->get()->toArray();

            $Filepath = [];
            if (!empty($user_profiles_data)) {
                $fileName = 'Frequent_user_list_' . time();
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                $sheet->setCellValue('A1', '#');
                $sheet->setCellValue('B1', 'total_call');
                $sheet->setCellValue('C1', 'name');
                $sheet->setCellValue('D1', 'email');
                $sheet->setCellValue('E1', 'phone');
                $sheet->setCellValue('F1', 'gender');
                $sheet->setCellValue('G1', 'date_of_join');
                $sheet->setCellValue('H1', 'date_of_birth');
                $sheet->setCellValue('I1', 'user_id');
                $sheet->setCellValue('J1', 'company_name');
                $sheet->setCellValue('K1', 'role_name');
                $sheet->setCellValue('L1', 'display_name');

                $rowCount = 2;
                $cnt = 1;
                //echo "<pre>"; print_r($call_report_data);exit();
                $gender = '';
                foreach ($user_profiles_data as $key => $frequent_data) {
                    if ($frequent_data['user_profile']['gender'] == 1) {
                        $gender = 'male';
                    }
                    if ($frequent_data['user_profile']['gender'] == 2) {
                        $gender = 'female';
                    }
                    $sheet->setCellValue('A' . $rowCount, $cnt);
                    $sheet->setCellValue('B' . $rowCount, $frequent_data['total_call']);
                    $sheet->setCellValue('C' . $rowCount, $frequent_data['user_profile']['first_name'] . ' ' . $frequent_data['user_profile']['last_name']);
                    $sheet->setCellValue('D' . $rowCount, $frequent_data['email']);
                    $sheet->setCellValue('E' . $rowCount, $frequent_data['phone']);
                    $sheet->setCellValue('F' . $rowCount, $gender);
                    $sheet->setCellValue('G' . $rowCount, date('d/m/Y', strtotime($frequent_data['user_profile']['date_of_join'])));
                    $sheet->setCellValue('H' . $rowCount, date('d/m/Y', strtotime($frequent_data['user_profile']['date_of_birth'])));
                    $sheet->setCellValue('I' . $rowCount, $frequent_data['user_profile']['user_id']);
                    $sheet->setCellValue('J' . $rowCount, $frequent_data['user_profile']['company']['company_name']);
                    $sheet->setCellValue('K' . $rowCount, $frequent_data['user_profile']['user_roles'][0]['role_name']);
                    $sheet->setCellValue('L' . $rowCount, $frequent_data['user_profile']['user_roles'][0]['role_display_name']);

                    $rowCount++;
                    $cnt++;
                }
                if ($extension == 'csv') {
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
                    $fileName = $fileName . '.csv';
                } elseif ($extension == 'xlsx') {
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                    $fileName = $fileName . '.xlsx';
                } else {
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
                    $fileName = $fileName . '.xls';
                }



                $folderPath = public_path('frequent_user_report_export');
                if (!is_dir($folderPath)) {
                    mkdir($folderPath, 0777, true);
                }
                $writer->save($folderPath . "/" . $fileName);

                header("Content-Type: application/vnd.ms-excel");

                $Filepath = url('/') . "/frequent_user_report_export/" . $fileName;
                //Redirect::away($folderPath . "/" . $fileName);exit();

                // send email to the user for download the file
                CallController::send_download_report_mail($current_user->email,$current_user->user_profile->first_name,$current_user->user_profile->last_name,$Filepath);


                $response_array = $this->helper->custom_response(true, $Filepath, trans("translate.FREQUENT_USER_REPORT_DATA"));
                return response()->json($response_array, Response::HTTP_OK);
            } else {
                $response_array = $this->helper->custom_response(false, array(), trans("translate.FREQUENT_USER_REPORT_DATA_NOT_FOUND"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }
    // Email send to the user for download report file
    public function send_download_report_mail($to_email,$user_first_name,$user_last_name,$file_link){
        $template_replace_data = array(
            'fullname' => $user_first_name .' '. $user_last_name,
            'download_report_link' => '<a style="color: white;background-color: #173a67;padding: 10px 20px;text-decoration: none;" href="'.$file_link.'"> DOWNLOAD THE REPORT</a>',
            'LOGO' => Config::get('settings.APP_LOGO'),
            'project_name' => Config::get('settings.APP_NAME'),
            'app_store_logo' => Config::get('settings.APPLE_STORE_LOGO'),
            'play_store_logo' => Config::get('settings.PLAY_STORE_LOGO'),
            'app_store_link' => Config::get('settings.APPLE_STORE_LINK'),
            'play_store_link' => Config::get('settings.PLAY_STORE_LINK'),
        );

        $template_details = $this->helper->getEmailTemplate('download_report_link');

        $send_data = $this->helper->send_email('mayursinh@mailinator.com', $template_replace_data, $template_details);
    }
    public function active_call_report_history_export(Request $request) {
        try {
            $extension = $request->export_type;
            if (!empty($extension)) {
                $extension = $extension;
            } else {
                $extension = 'xlsx';
            }
            $data = array();
            $output = array();
            $data['title'] = 'Active Call Excel Sheet';
            $current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                $status = (isset($request->status) && !empty($request->status)) ? $request->status : 2;

                if (in_array($role_id, $this->supplier_roles)) {
                    $user_profiles_data = CallDetail::getInterpreterCallDetailData()->where('status', $status)->whereIn('user_role_id', $this->supplier_roles);
                    $user_profiles_data = $user_profiles_data->get()->toArray();
                    //->join('call_details', 'calls.id', "=", 'call_details.call_id');
                }

                if (in_array($role_id, $this->consumer_roles)) {
                    $user_profiles_data = Call::getSignableCallData()->where('status', $status)->whereIn('from_user_role_id', $this->consumer_roles);

                    $user_profiles_data = $user_profiles_data->get()->toArray();
                }
                $Filepath = [];
                if (!empty($user_profiles_data)) {
                    $fileName = 'Frequent_user_list_' . time();
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    $sheet->setCellValue('A1', '#');
                    $sheet->setCellValue('B1', 'call_id');
                    $sheet->setCellValue('C1', 'name');
                    $sheet->setCellValue('D1', 'gender');
                    $sheet->setCellValue('E1', 'date_of_join');
                    $sheet->setCellValue('F1', 'date_of_birth');
                    $sheet->setCellValue('G1', 'user_id');
                    $sheet->setCellValue('H1', 'company_name');
                    $sheet->setCellValue('I1', 'role_name');
                    $sheet->setCellValue('J1', 'display_name');
                    $sheet->setCellValue('K1', 'start_time');
                    $sheet->setCellValue('L1', 'end_time');
                    $sheet->setCellValue('M1', 'band_width');
                    $sheet->setCellValue('N1', 'resolution');
                    $sheet->setCellValue('O1', 'reason');
                    $sheet->setCellValue('P1', 'status');

                    $rowCount = 2;
                    $cnt = 1;
                    //echo "<pre>"; print_r($call_report_data);exit();
                    $gender = '';
                    foreach ($user_profiles_data as $key => $call_data) {
                        if ($call_data['user_profile']['gender'] == 1) {
                            $gender = 'male';
                        }
                        if ($call_data['user_profile']['gender'] == 2) {
                            $gender = 'female';
                        }
                        $sheet->setCellValue('A' . $rowCount, $cnt);
                        $sheet->setCellValue('B' . $rowCount, $call_data['call_id']);
                        $sheet->setCellValue('C' . $rowCount, $call_data['user_profile']['first_name'] . ' ' . $call_data['user_profile']['last_name']);
                        $sheet->setCellValue('D' . $rowCount, $gender);
                        $sheet->setCellValue('E' . $rowCount, date('d/m/Y', strtotime($call_data['user_profile']['date_of_join'])));
                        $sheet->setCellValue('F' . $rowCount, date('d/m/Y', strtotime($call_data['user_profile']['date_of_birth'])));
                        $sheet->setCellValue('G' . $rowCount, $call_data['user_profile']['user_id']);
                        $sheet->setCellValue('H' . $rowCount, $call_data['user_profile']['company']['company_name']);
                        $sheet->setCellValue('I' . $rowCount, $call_data['user_role']['name']);
                        $sheet->setCellValue('J' . $rowCount, $call_data['user_role']['display_name']);
                        $sheet->setCellValue('K' . $rowCount, date('d/m/Y', strtotime($call_data['start_time'])));
                        $sheet->setCellValue('L' . $rowCount, date('d/m/Y', strtotime($call_data['end_time'])));
                        $sheet->setCellValue('M' . $rowCount, $call_data['band_width']);
                        $sheet->setCellValue('N' . $rowCount, $call_data['resolution']);
                        $sheet->setCellValue('O' . $rowCount, $call_data['reason']);
                        $sheet->setCellValue('P' . $rowCount, $call_data['status']['value']);

                        $rowCount++;
                        $cnt++;
                    }
                    if ($extension == 'csv') {
                        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
                        $fileName = $fileName . '.csv';
                    } elseif ($extension == 'xlsx') {
                        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                        $fileName = $fileName . '.xlsx';
                    } else {
                        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
                        $fileName = $fileName . '.xls';
                    }



                    $folderPath = public_path('active_call_report_export');
                    if (!is_dir($folderPath)) {
                        mkdir($folderPath, 0777, true);
                    }
                    $writer->save($folderPath . "/" . $fileName);

                    header("Content-Type: application/vnd.ms-excel");

                    $Filepath = url('/') . "/active_call_report_export/" . $fileName;

                    $response_array = $this->helper->custom_response(true, $Filepath, trans("translate.ACTIVE_CALL_REPORT_DATA"));
                    return response()->json($response_array, Response::HTTP_OK);
                } else {
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.ACTIVE_CALL_REPORT_DATA_NOT_FOUND"));
                    return response()->json($response_array, Response::HTTP_OK);
                }
            } else {
                $message = trans("translate.USER_ROLE_NOT_FOUND");
                $response_array = $this->helper->custom_response(true, array(), $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function active_user_report_history_export(Request $request) {
        try {
            $extension = $request->export_type;
            if (!empty($extension)) {
                $extension = $extension;
            } else {
                $extension = 'xlsx';
            }
            $data = array();
            $output = array();
            $data['title'] = 'Active User Excel Sheet';
            $current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                if (in_array($role_id, $this->supplier_roles)) {
                    $status = (isset($request->status) && !empty($request->status)) ? $request->status : 4;
                    $user_profiles_data = User::getUserData()
                            ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                            ->join('role_users', 'user_profies.id', '=', 'role_users.user_profile_id')
                            ->join('roles', 'roles.id', "=", 'role_users.role_id')
                            ->join('user_languages', 'user_profies.id', "=", 'user_languages.user_profile_id')
                            ->join('active_interpreters', 'user_profies.id', "=", 'active_interpreters.user_profile_id')
                            ->where('active_interpreters.status', $status)
                            ->whereIn('roles.id', $this->supplier_roles)
                            ->GroupBy('user_profies.id');

                    $user_profiles_data = $user_profiles_data->get()->toArray();
                }

                if (in_array($role_id, $this->consumer_roles)) {
                    $status = (isset($request->status) && !empty($request->status)) ? $request->status : 2;
                    $user_profiles_data = User::getUserData()
                            ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                            ->join('role_users', 'user_profies.id', '=', 'role_users.user_profile_id')
                            ->join('roles', 'roles.id', "=", 'role_users.role_id')
                            ->join('user_languages', 'user_profies.id', "=", 'user_languages.user_profile_id')
                            ->join('calls', 'user_profies.id', "=", 'calls.from_user_profile_id')
                            ->where('calls.status', $status)
                            ->whereIn('roles.id', $this->consumer_roles)
                            ->GroupBy('user_profies.id');

                    $user_profiles_data = $user_profiles_data->get()->toArray();
                }
                $Filepath = [];
                if (!empty($user_profiles_data)) {
                    $fileName = 'Frequent_user_list_' . time();
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    $sheet->setCellValue('A1', '#');
                    $sheet->setCellValue('B1', 'name');
                    $sheet->setCellValue('C1', 'email');
                    $sheet->setCellValue('D1', 'phone');
                    $sheet->setCellValue('E1', 'gender');
                    $sheet->setCellValue('F1', 'date_of_join');
                    $sheet->setCellValue('G1', 'date_of_birth');
                    $sheet->setCellValue('H1', 'user_id');
                    $sheet->setCellValue('I1', 'company_name');
                    $sheet->setCellValue('J1', 'role_name');
                    $sheet->setCellValue('K1', 'display_name');

                    $rowCount = 2;
                    $cnt = 1;

                    $gender = '';
                    foreach ($user_profiles_data as $key => $user_data) {
                        if ($user_data['user_profile']['gender'] == 1) {
                            $gender = 'male';
                        }
                        if ($user_data['user_profile']['gender'] == 2) {
                            $gender = 'female';
                        }
                        $sheet->setCellValue('A' . $rowCount, $cnt);
                        $sheet->setCellValue('B' . $rowCount, $user_data['user_profile']['first_name'] . ' ' . $user_data['user_profile']['last_name']);
                        $sheet->setCellValue('C' . $rowCount, $user_data['email']);
                        $sheet->setCellValue('D' . $rowCount, $user_data['phone']);
                        $sheet->setCellValue('E' . $rowCount, $gender);
                        $sheet->setCellValue('F' . $rowCount, date('d/m/Y', strtotime($user_data['user_profile']['date_of_join'])));
                        $sheet->setCellValue('G' . $rowCount, date('d/m/Y', strtotime($user_data['user_profile']['date_of_birth'])));
                        $sheet->setCellValue('H' . $rowCount, $user_data['user_profile']['user_id']);
                        $sheet->setCellValue('I' . $rowCount, $user_data['user_profile']['company']['company_name']);
                        $sheet->setCellValue('J' . $rowCount, $user_data['user_profile']['user_roles'][0]['role_name']);
                        $sheet->setCellValue('K' . $rowCount, $user_data
                                ['user_profile']['user_roles'][0]['role_display_name']);

                        $rowCount++;
                        $cnt++;
                    }
                    if ($extension == 'csv') {
                        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
                        $fileName = $fileName . '.csv';
                    } elseif ($extension == 'xlsx') {
                        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                        $fileName = $fileName . '.xlsx';
                    } else {
                        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
                        $fileName = $fileName . '.xls';
                    }



                    $folderPath = public_path('active_user_report_export');
                    if (!is_dir($folderPath)) {
                        mkdir($folderPath, 0777, true);
                    }
                    $writer->save($folderPath . "/" . $fileName);

                    header("Content-Type: application/vnd.ms-excel");

                    $Filepath = url('/') . "/active_user_report_export/" . $fileName;

                    $response_array = $this->helper->custom_response(true, $Filepath, trans("translate.ACTIVE_USER_REPORT_DATA"));
                    return response()->json($response_array, Response::HTTP_OK);
                } else {
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.ACTIVE_USER_REPORT_DATA_NOT_FOUND"));
                    return response()->json($response_array, Response::HTTP_OK);
                }
            } else {
                $message = trans("translate.USER_ROLE_NOT_FOUND");
                $response_array = $this->helper->custom_response(true, array(), $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function interpreter_report(Request $request) {
        try {
            $current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                if (in_array($role_id, $this->supplier_roles)) {
                    //$status = (isset($request->status) && !empty($request->status)) ? $request->status : 4;
                    $user_profiles_data = User::getUserData()
                            ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                            ->join('role_users', 'user_profies.id', '=', 'role_users.user_profile_id')
                            ->join('roles', 'roles.id', "=", 'role_users.role_id')
                            ->join('user_languages', 'user_profies.id', "=", 'user_languages.user_profile_id')
                            ->join('active_interpreters', 'user_profies.id', "=", 'active_interpreters.user_profile_id')
                            ->whereIn('roles.id', $this->supplier_roles)
                            ->GroupBy('user_profies.id');

                    $user_profiles_data = $user_profiles_data->get()->toArray();

                    $user_profiles_data = User::getUserData()
                            ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                            ->join('role_users', 'user_profies.id', '=', 'role_users.user_profile_id')
                            ->join('roles', 'roles.id', "=", 'role_users.role_id')
                            ->join('user_languages', 'user_profies.id', "=", 'user_languages.user_profile_id')
                            ->join('active_interpreters', 'user_profies.id', "=", 'active_interpreters.user_profile_id')
                            ->where('active_interpreters.status')
                            ->whereIn('roles.id', $this->supplier_roles)
                            ->GroupBy('user_profies.id');

                    $user_profiles_data = $user_profiles_data->get()->toArray();
                }
                $user_profiles_data_count = count($user_profiles_data);
                if (isset($user_profiles_data) && !empty($user_profiles_data)) {
                    $response_array = $this->helper->custom_response(true, $user_profiles_data, trans("translate.INTERPRETER_USER_DATA"), true, $user_profiles_data_count);
                    return response()->json($response_array, Response::HTTP_OK);
                } else {
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.INTERPRETER_USER_DATA_NOT_FOUND"));
                    return response()->json($response_array, Response::HTTP_OK);
                }
            } else {
                $message = trans("translate.USER_ROLE_NOT_FOUND");
                $response_array = $this->helper->custom_response(true, array(), $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    //new
    public function active_call_report(Request $request) {
        try {
            $current_user = auth()->user();
            $current_user_profile_id = $current_user->user_profile->id;
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                $status = (isset($request->status) && !empty($request->status)) ? $request->status : 2;
                $calls_datas = Call::getCallReportData();

                // Supplier Admin (Signable Interpreters) if Supplier Admin then show All Supplier Roles active call recocrds 
                $supplier_roles = $this->supplier_roles;
                $consumer_roles = $this->consumer_roles;
                if ($role_id == 2) {
                    $calls_datas = $calls_datas->where('status', $status);
                    $calls_datas->whereHas('call_details', function ($query) use ($supplier_roles) {
                        $query->whereIn('user_role_id', $supplier_roles);
                    });
                }

                // Supplier Supervisor (Signable Interpreters)  => if Supplier Supervisor then show own active call recocrds 
                if ($role_id == 3) {
                    $calls_datas = $calls_datas->where('status', $status);
                    $calls_datas->whereHas('call_details', function ($query) use ($current_user_profile_id, $supplier_roles) {
                        $query->where('user_profile_id', $current_user_profile_id)->whereIn('user_role_id', $supplier_roles);
                    });
                }

                // Company Admin (Amazon)  => if Company Admin then show All Company Roles active call recocrds 
                if ($role_id == 5) {
                    $calls_datas = $calls_datas->where('status', $status)->whereIn('from_user_role_id', $this->consumer_roles);
                }

                // Company Supervisor (Amazon)  => if Company Supervisor then show own active call recocrds 
                if ($role_id == 6) {
                    $calls_datas = $calls_datas->where('status', $status)->where('from_user_profile_id', $current_user_profile_id)->whereIn('from_user_role_id', $this->consumer_roles);
                }

                $calls_datas = $calls_datas->get();
                $calls_datas_count = count($calls_datas);
                $avg_call_wait_time = '';
                $most_active_location = '';
                // available interpreter
                $total_available_interpreter = ActiveInterpreter::where('status', 1)->count();
                $average_detail = [
                    "total_calls" => $calls_datas_count,
                    "total_available_interpreter" => $total_available_interpreter,
                    "avg_call_wait_time" => $avg_call_wait_time,
                    "most_active_location" => $most_active_location
                ];
                $data = array();
                if (isset($calls_datas) && !empty($calls_datas)) {
                    foreach ($calls_datas AS $calls_data) {

                        $calls_data->call_detail = $calls_data->call_details[0];
                        unset($calls_data->call_details);
                    }
                    $data['call_datas'] = $calls_datas;
                    $data['average_detail'] = $average_detail;
                    $response_array = $this->helper->custom_response(true, $data, trans("translate.ACTIVE_CALL_DATA"), true, $calls_datas_count);
                    return response()->json($response_array, Response::HTTP_OK);
                } else {
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.ACTIVE_CALL_DATA_NOT_FOUND"));
                    return response()->json($response_array, Response::HTTP_OK);
                }
            } else {
                $message = trans("translate.USER_ROLE_NOT_FOUND");
                $response_array = $this->helper->custom_response(true, array(), $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function call_report_history_new_one(Request $request) {
        try {
            $current_user = auth()->user();
            $current_user_profile_id = $current_user->user_profile->id;
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                $calls_datas = Call::getCallReportData();

                // Supplier Admin (Signable) if Supplier Admin then show All Supplier Roles active call recocrds 
                $supplier_roles = $this->supplier_roles;
                $consumer_roles = $this->consumer_roles;
                if ($role_id == 2) {
                    $calls_datas->whereHas('call_details', function ($query) use ($supplier_roles) {
                        $query->whereIn('user_role_id', $supplier_roles);
                    });
                }

                // Supplier Supervisor (Signable)  => if Supplier Supervisor then show own active call recocrds 
                if ($role_id == 3) {
                    $calls_datas->whereHas('call_details', function ($query) use ($current_user_profile_id, $supplier_roles) {
                        $query->where('user_profile_id', $current_user_profile_id)->whereIn('user_role_id', $supplier_roles);
                    });
                }

                // Company Admin (Amazon) => if Company Admin then show All Company Roles active call recocrds 
                if ($role_id == 5) {
                    $calls_datas = $calls_datas->whereIn('from_user_role_id', $this->consumer_roles);
                }

                // Company Supervisor (Amazon)  => if Company Supervisor then show own active call recocrds 
                if ($role_id == 6) {
                    $calls_datas = $calls_datas->where('from_user_profile_id', $current_user_profile_id)->whereIn('from_user_role_id', $this->consumer_roles);
                }

                $calls_datas = $calls_datas->get();
                $calls_datas_count = count($calls_datas);
                $avg_call_wait_time = '';
                $most_active_location = '';
                // available interpreter
                $total_available_interpreter = ActiveInterpreter::where('status', 1)->count();
                $average_detail = [
                    "total_calls" => $calls_datas_count,
                    "total_available_interpreter" => $total_available_interpreter,
                    "avg_call_wait_time" => $avg_call_wait_time,
                    "most_active_location" => $most_active_location
                ];
                $data = array();
                if (isset($calls_datas) && !empty($calls_datas)) {
                    foreach ($calls_datas AS $calls_data) {
                        $calls_data->call_detail = '';
                        if (isset($calls_data->call_details[0]) && !empty($calls_data->call_details[0])) {
                            $calls_data->call_detail = $calls_data->call_details[0];
                        }
                        unset($calls_data->call_details);
                    }
                    $data['call_datas'] = $calls_datas;
                    $data['average_detail'] = $average_detail;
                    $response_array = $this->helper->custom_response(true, $data, trans("translate.CALL_HISTORY_DATA"), true, $calls_datas_count);
                    return response()->json($response_array, Response::HTTP_OK);
                } else {
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.CALL_HISTORY_DATA_NOT_FOUND"));
                    return response()->json($response_array, Response::HTTP_OK);
                }
            } else {
                $message = trans("translate.USER_ROLE_NOT_FOUND");
                $response_array = $this->helper->custom_response(true, array(), $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    // new template 

    public function active_call_report_history_template_new_one(Request $request) {
        try {
            $current_user = auth()->user();
            $current_user_profile_id = $current_user->user_profile->id;
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                $status = (isset($request->status) && !empty($request->status)) ? $request->status : 2;
                $calls_datas = Call::getCallReportData();

                $supplier_roles = $this->supplier_roles;
                $consumer_roles = $this->consumer_roles;
                // Supplier Admin (Signable Interpreters) if Supplier Admin then show All Supplier Roles active call recocrds 
                if ($role_id == 2) {
                    $calls_datas = $calls_datas->where('status', $status);
                    $calls_datas->whereHas('call_details', function ($query) use ($supplier_roles) {
                        $query->whereIn('user_role_id', $supplier_roles);
                    });
                }

                // Supplier Supervisor (Signable Interpreters)  => if Supplier Supervisor then show own active call recocrds 
                if ($role_id == 3) {
                    $calls_datas = $calls_datas->where('status', $status);
                    $calls_datas->whereHas('call_details', function ($query) use ($current_user_profile_id, $supplier_roles) {
                        $query->where('user_profile_id', $current_user_profile_id)->whereIn('user_role_id', $supplier_roles);
                    });
                }

                // Company Admin (Amazon)  => if Company Admin then show All Company Roles active call recocrds 
                if ($role_id == 5) {
                    $calls_datas = $calls_datas->where('status', $status)->whereIn('from_user_role_id', $this->consumer_roles);
                }

                // Company Supervisor (Amazon)  => if Company Supervisor then show own active call recocrds 
                if ($role_id == 6) {
                    $calls_datas = $calls_datas->where('status', $status)->where('from_user_profile_id', $current_user_profile_id)->whereIn('from_user_role_id', $this->consumer_roles);
                }

                $calls_datas = $calls_datas->get();
                $calls_datas_count = count($calls_datas);
                $avg_call_wait_time = '';
                $most_active_location = '';
                // available interpreter
                $total_available_interpreter = ActiveInterpreter::where('status', 1)->count();
                $average_detail = [
                    "total_calls" => $calls_datas_count,
                    "total_available_interpreter" => $total_available_interpreter,
                    "avg_call_wait_time" => $avg_call_wait_time,
                    "most_active_location" => $most_active_location
                ];
                $data = array();
                if (isset($calls_datas) && !empty($calls_datas)) {
                    foreach ($calls_datas AS $calls_data) {

                        $calls_data->call_detail = $calls_data->call_details[0];
                        unset($calls_data->call_details);
                    }
                    $data['call_datas'] = $calls_datas;
                    $data['average_detail'] = $average_detail;
                    return response()->view('template.active_call_report_template', compact(['data']));
                }
            } else {
                $message = trans("translate.USER_ROLE_NOT_FOUND");
                $response_array = $this->helper->custom_response(true, array(), $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function call_history_report_template_new_one(Request $request) {
        try {
            /* $current_user = auth()->user();
              $current_user_profile_id = $current_user->user_profile->id;
              $roles = $current_user->user_profile->user_roles; */
            $roles = 'tset';
            $current_user_profile_id = 5;
            //if ($roles->isEmpty()) {
            if ($roles) {
                /* $current_user->role = $roles[0];
                  $role_user_id = $current_user->role->id;
                  $role_id = $current_user->role->role_id; */
                $role_id = 2;

                $status = (isset($request->status) && !empty($request->status)) ? $request->status : 2;
                $calls_datas = Call::getCallReportData();

                $supplier_roles = $this->supplier_roles;
                $consumer_roles = $this->consumer_roles;

                if (in_array($role_id, $this->supplier_roles)) {
                    $calls_datas->whereHas('call_details', function ($query) use ($current_user_profile_id, $supplier_roles) {
                        $query->where('user_profile_id', $current_user_profile_id)->whereIn('user_role_id', $supplier_roles);
                    });
                }
                if (in_array($role_id, $this->consumer_roles)) {
                    $calls_datas = $calls_datas->where('from_user_profile_id', $current_user_profile_id)->whereIn('from_user_role_id', $this->consumer_roles);
                }


                // Supplier Admin (Signable Interpreters) if Supplier Admin then show All Supplier Roles active call recocrds 
                // if ($role_id == 2) {
                //     $calls_datas->whereHas('call_details', function($query) use($this->supplier_roles) {
                //         $query->whereIn('user_role_id', $this->supplier_roles);
                //     });
                // }
                // Supplier Supervisor (Signable Interpreters)  => if Supplier Supervisor then show own active call recocrds 
                /* if ($role_id == 3) {
                  $calls_datas->whereHas('call_details', function($query) use($current_user_profile_id, $this->supplier_roles) {
                  $query->where('user_profile_id', $current_user_profile_id)->whereIn('user_role_id', $this->supplier_roles);
                  });
                  } */

                // Company Admin (Amazon)  => if Company Admin then show All Company Roles active call recocrds 
                /* if ($role_id == 5) {
                  $calls_datas = $calls_datas->whereIn('from_user_role_id', $this->consumer_roles);
                  } */

                // Company Supervisor (Amazon)  => if Company Supervisor then show own active call recocrds 
                // if ($role_id == 6) {
                //     $calls_datas = $calls_datas->where('from_user_profile_id', $current_user_profile_id)->whereIn('from_user_role_id', $this->consumer_roles);
                // }

                $calls_datas = $calls_datas->get();
                $calls_datas_count = count($calls_datas);
                $avg_call_wait_time = '';
                $most_active_location = '';
                // available interpreter
                $total_available_interpreter = ActiveInterpreter::where('status', 1)->count();
                $average_detail = [
                    "total_calls" => $calls_datas_count,
                    "total_available_interpreter" => $total_available_interpreter,
                    "avg_call_wait_time" => $avg_call_wait_time,
                    "most_active_location" => $most_active_location
                ];
                $data = array();
                if (isset($calls_datas) && !empty($calls_datas)) {
                    foreach ($calls_datas AS $calls_data) {

                        $calls_data->call_detail = $calls_data->call_details[0];
                        unset($calls_data->call_details);
                    }
                    $data['call_datas'] = $calls_datas;
                    $data['average_detail'] = $average_detail;
                    return response()->view('template.active_call_report_template', compact(['data']));
                }
            } else {
                $message = trans("translate.USER_ROLE_NOT_FOUND");
                $response_array = $this->helper->custom_response(true, array(), $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function call_report_history_new(Request $request) {
        try {
            $current_user = $this->helper->getLoginUser();
            // Total Calls 
            //$current_user_profile_id = $current_user->user_profile->id;
            $current_user_profile_id = 2;
            $total_calls = Call::getCallReportData()->where('from_user_profile_id', $current_user_profile_id)->count();

            // Total Average Times
            $average_times = CallDetail::select(DB::raw("AVG(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) AS average_times"))->whereNotNull(['start_time', 'end_time'])->join('calls', 'calls.id', "=", 'call_details.call_id')->where('calls.from_user_profile_id', $current_user_profile_id)->first();
            $hours = floor($average_times['average_times'] / 3600);
            $mins = floor(($average_times['average_times'] - $hours * 3600) / 60);
            $s = $average_times['average_times'] - ($hours * 3600 + $mins * 60);
            $average_times = $hours . ":" . $mins . ":" . floor($s);

            // Total Average Language
            $average_language = Call::select(DB::raw("ROUND(AVG(language_id)) AS average_lanuage_id"))->where('from_user_profile_id', $current_user_profile_id)->first();
            $average_language = Language::select('id', 'name', 'is_active')->where('id', $average_language['average_lanuage_id'])->first();

            // Total Average Purpose
            $average_purpose = Call::select(DB::raw("ROUND(AVG(purpose_id)) AS average_purpose_id"))->where('from_user_profile_id', $current_user_profile_id)->first();
            $average_purpose = Purpose::select('id', 'name', 'description')->where('id', $average_purpose['average_purpose_id'])->first();

            $average_detail = [
                "total_calls" => $total_calls,
                "average_times" => $average_times,
                "average_language" => $average_language,
                "average_purpose" => $average_purpose
            ];

            $limit = isset($request->limit) && !empty($request->limit) ? $request->limit : 20;

            $call_data = Call::getCallReportData()->where('from_user_profile_id', $current_user_profile_id);

            if (isset($request->call_id) && !empty($request->call_id)) {
                $call_data->where('id', $request->call_id);
            }
            //$call_data = $call_data->paginate($limit);
            $call_data = $call_data->get();

            $call_details['average_call_details'] = $average_detail;

            $call_details['call_details'] = $call_data;

            if (isset($call_details) && !empty($call_details)) {
                $message = trans("translate.CALL_HISTORY_DATA_FOUND");
                $response_array = $this->helper->custom_response(true, $call_details, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function interpreter_message(Request $request) {
        try {
            $current_user = $this->helper->getLoginUser();

            if (isset($current_user->qb_id) && !empty($current_user->qb_id) && isset($current_user->qb_password) && !empty($current_user->qb_password)) {

                $user_quickblock_session = $this->helper->createUserSession($current_user->qb_id, $current_user->qb_password);
                $quickblock_token = $user_quickblock_session['session']['token'];

                $quickblock_login_response = $this->helper->getUserAuthenticate($current_user->qb_id, $current_user->qb_password, $quickblock_token);
                $current_user->quickblock_user_data = $quickblock_login_response;
            }
            $message = $request->message;
            $send_message = $this->helper->createMessage($quickblock_token, $message);
            if ($send_message) {
                $message = trans("message Send!");
                $response_array = $this->helper->custom_response(true, array(), $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function get_call_details_data(Request $request, $call_id) {
        try {
            $status = (isset($request->status) && !empty($request->status)) ? $request->status : 2;
            $calls_datas = Call::getCallDetailsData()->where('id', $call_id)->first();

            if (isset($calls_datas) && !empty($calls_datas)) {
                $call_details = CallDetail::select('id', 'call_id', 'user_profile_id', 'user_role_id',  'start_time', 'end_time', 'duration', 'band_width', 'resolution', 'is_called_failed', 'call_detail', 'status', 'feedback')->with(['user_profile' => function ($query) {
                                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')->with('company', 'locations');
                            }, 'user_role' => function ($query) {
                                $query->select('id', 'name', 'display_name');
                            }, 'status' => function ($query) {
                                $query->select('id', 'name', 'value');
                            }
                        ])->where('status','>','40')->where('user_role_id','=','2')->where('call_id', $calls_datas->id)->orderBy('id', 'DESC')->first();

                $calls_datas->call_detail = $call_details;
                $calls_datas->user_feedback_data = '';
                $calls_datas->user_quality_feedback_data = '';
                if (isset($calls_datas) && !empty($calls_datas)) {
                    if(isset($calls_datas->call_detail->duration) && !empty($calls_datas->call_detail->duration)){
                        $calls_datas->call_detail->duration = date('H:i:s', strtotime($calls_datas->call_detail->duration));
                    }
                    $calls_datas->user_feedback_data = CallFeedbackUser::where('call_id', $calls_datas->id)->where('created_by', $calls_datas->from_user_profile_id)->first();

                    $calls_datas->user_quality_feedback_data = CallQualityFeedback::where('call_id', $calls_datas->id)->where('created_by', $calls_datas->from_user_profile_id)->first();
                }

                $call_status_log = CallLog::select('id', 'call_id', 'from_status', 'to_status', 'created_by', 'updated_by')->with('from_status_detail', 'to_status_detail')->where('call_id', $calls_datas->id)->orderBy('id', 'DESC')->first();
                $calls_datas->call_status_log = $call_status_log;
                $message = trans("translate.CALL_DETAILS_DATA_FOUND");

                $response_array = $this->helper->custom_response(true, $calls_datas, $message);
            } else {
                $message = trans("translate.CALL_DETAILS_DATA_NOT_FOUND");
                $response_array = $this->helper->custom_response(true, array(), $message);
            }
            return response()->json($response_array, Response::HTTP_OK);
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function call_quality_feedback_post(Request $request) {
        try {
            $current_user = $this->helper->getLoginUser();

            Log::info('Call quality feedback post feedback: Params: ' . json_encode($request->all()));
            $validator = Validator::make($request->all(), [
                        'call_id' => 'required',
                        'call_quality_rate' => 'required'
            ]);

            if ($validator->fails()) {
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }
            $result = array();
            if (isset($request->call_id) && $request->call_id != '' && isset($request->feedback_id) && $request->feedback_id != '') {

                $call_feedback_data = CallQualityFeedback::find($request->feedback_id);
                if (!empty($call_feedback_data)) {
                    $call_feedback_data->call_id = (isset($request->call_id) && !empty($request->call_id)) ? $request->call_id : '';
                    $call_feedback_data->is_group_call = (isset($request->is_group_call) && !empty($request->is_group_call)) ? $request->is_group_call : 0;
                    $call_feedback_data->call_quality_rate = (isset($request->call_quality_rate) && !empty($request->call_quality_rate)) ? $request->call_quality_rate : '';
                    $call_feedback_data->updated_by = $current_user->user_profile->id;
                    $call_feedback_data->save();

                    $result = $call_feedback_data;
                    $message = trans("translate.CALL_FEEDBACK_UPDATED");
                } else {
                    $message = trans("translate.ID_NOT_FOUND");
                }
            } else {
                $call_feedback_data = array(
                    'call_id' => isset($request->call_id) && !empty($request->call_id) ? $request->call_id : "",
                    'is_group_call' => isset($request->is_group_call) && !empty($request->is_group_call) ? $request->is_group_call : 0,
                    'call_quality_rate' => isset($request->call_quality_rate) && !empty($request->call_quality_rate) ? $request->call_quality_rate : "",
                    'created_by' => $current_user->user_profile->id,
                );
                $result = CallQualityFeedback::create($call_feedback_data);
                $message = trans("translate.CALL_FEEDBACK_ADDED");
            }

            if (isset($result) && !empty($result)) {
                $response_array = $this->helper->custom_response(true, $result, $message);
                return response()->json($response_array, Response::HTTP_OK);
            } else {
                $response_array = $this->helper->custom_response(false, array(), trans("translate.CALL_FEEDBACK_NOT_ADDED"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function call_feedback_users_post(Request $request) {
        try {
            $current_user = $this->helper->getLoginUser();
            if ($current_user->role == 'supervisor') {
                $feedback_type = 1;
            }
            if ($current_user->role == 'interpreter') {
                $feedback_type = 2;
            }
            if ($current_user->role == 'qa_manager') {
                $feedback_type = 3;
            }
            Log::info('Call feedback user post feedback: Params: ' . json_encode($request->all()));
            $validator = Validator::make($request->all(), [
                        'call_id' => 'required',
                        'to_user_profile_id' => 'required',
                        'to_user_role_id' => 'required',
                        'to_user_rating' => 'required',
                        'disposition_id' => 'required'
            ]);

            if ($validator->fails()) {
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }
            $result = array();
            if (isset($request->call_id) && $request->call_id != '' && isset($request->feedback_id) && $request->feedback_id != '') {

                $call_feedback_data = CallFeedbackUser::find($request->feedback_id);
                if (!empty($call_feedback_data)) {
                    $call_feedback_data->call_id = (isset($request->call_id) && !empty($request->call_id)) ? $request->call_id : '';
                    $call_feedback_data->to_user_profile_id = (isset($request->to_user_profile_id) && !empty($request->to_user_profile_id)) ? $request->to_user_profile_id : $call_feedback_data['to_user_profile_id'];
                    $call_feedback_data->to_user_role_id = (isset($request->to_user_role_id) && !empty($request->to_user_role_id)) ? $request->to_user_role_id : $call_feedback_data['to_user_role_id'];
                    $call_feedback_data->to_user_rating = (isset($request->to_user_rating) && !empty($request->to_user_rating)) ? $request->to_user_rating : $call_feedback_data['to_user_rating'];
                    $call_feedback_data->disposition_id = (isset($request->disposition_id) && !empty($request->disposition_id)) ? $request->disposition_id : $call_feedback_data['disposition_id'];
                    $call_feedback_data->comment = (isset($request->comment) && !empty($request->comment)) ? $request->comment : $call_feedback_data['comment'];
                    $call_feedback_data->feedback_type = $feedback_type;
                    $call_feedback_data->updated_by = $current_user->user_profile->id;
                    $call_feedback_data->save();

                    $result = $call_feedback_data;
                    $message = trans("translate.CALL_FEEDBACK_UPDATED");
                } else {
                    $message = trans("translate.ID_NOT_FOUND");
                }
            } else {
                $call_feedback_data = array(
                    'call_id' => isset($request->call_id) && !empty($request->call_id) ? $request->call_id : "",
                    'feedback_type' => $feedback_type,
                    'to_user_profile_id' => isset($request->to_user_profile_id) && !empty($request->to_user_profile_id) ? $request->to_user_profile_id : "",
                    'to_user_role_id' => isset($request->to_user_role_id) && !empty($request->to_user_role_id) ? $request->to_user_role_id : "",
                    'to_user_rating' => isset($request->to_user_rating) && !empty($request->to_user_rating) ? $request->to_user_rating : "",
                    'disposition_id' => isset($request->disposition_id) && !empty($request->disposition_id) ? $request->disposition_id : "",
                    'comment' => isset($request->comment) && !empty($request->comment) ? $request->comment : "",
                    'created_by' => $current_user->user_profile->id,
                );
                $result = CallFeedbackUser::create($call_feedback_data);
                $message = trans("translate.CALL_USER_FEEDBACK_ADDED");

                $sum_user_rating = CallFeedbackUser::where('to_user_profile_id', $result->to_user_profile_id)->sum('to_user_rating');
                $user_rating_count = CallFeedbackUser::where('to_user_profile_id', $result->to_user_profile_id)->count();

                $avg_user_rating = floor($sum_user_rating / $user_rating_count);

                $user_profile_data = UserProfile::find($result->to_user_profile_id);
                $user_profile_data->avg_user_rating = $avg_user_rating;
                $user_profile_data->save();
            }

            if (isset($result) && !empty($result)) {
                $response_array = $this->helper->custom_response(true, $result, $message);
                return response()->json($response_array, Response::HTTP_OK);
            } else {
                $response_array = $this->helper->custom_response(false, array(), trans("translate.CALL_USER_FEEDBACK_NOT_ADDED"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function qa_manger_call_feedback_list(Request $request) {
        try {
            $current_user = $this->helper->getLoginUser();
            $feedback_data = CallFeedbackUser::getfeedbackData();
            if ($current_user->role == 'supervisor') {
                $feedback_type = 1;
            }
            if ($current_user->role == 'interpreter') {
                $feedback_type = 2;
            }
            if ($current_user->role == 'qa_manager') {
                $feedback_type = 3;
                $feedback_data = $feedback_data->where('created_by',$current_user->user_profile->id);
            }
            
            $feedback_data = $feedback_data->orderBy('call_feedback_users.id','DESC')->get()->toArray();
            if (isset($feedback_data) && !empty($feedback_data)) {
                $all_data = array();
                $final_data = array();
                foreach ($feedback_data as $key => $value) {
                    $disposition_data =[];
                    $disposition_id = explode(',', $value['disposition_id']);
                    foreach ($disposition_id as $key1 => $value1) {
                        $disposition_data[] = Disposition::select('id','name', 'description','type')->where('id',$value1)->first();  
                    }
                    $value['disposition_data'] = $disposition_data;
                    $all_data[] = $value;
                    if($value['feedback_type'] == 3 && $value['to_user_role_id'] == 2){
                        $final_data['interpreter'][] = $value; 
                    }else{
                        $final_data['interpreter'] = NULL;
                    }
                    if($value['feedback_type'] == 3 && $value['to_user_role_id'] == 3){
                        $final_data['supervisor'][] = $value; 
                    }else{
                        $final_data['supervisor'] = NULL;
                    }
                }
                
                $response_array = $this->helper->custom_response(true, $final_data, trans("translate.CALL_USER_FEEDBACK_DATA"));
                return response()->json($response_array, Response::HTTP_OK);
            } else {
                $response_array = $this->helper->custom_response(false, array(), trans("translate.CALL_USER_FEEDBACK_NOT_FOUND"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

}
