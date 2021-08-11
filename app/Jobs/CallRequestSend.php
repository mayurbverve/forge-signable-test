<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\CallDetail;
use App\Models\CallStatus;
use App\Models\CallInitMessage;
use App\Models\InterPreterChat;
use App\Models\CallLog;
use App\Models\Call;
use App\Models\User;
use App\Models\Purpose;
use App\Models\Language;
use App\Models\UserProfile;
use App\Helper\Helper;
use Log;
use DB;

class CallRequestSend implements ShouldQueue {

    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    protected $call_request_id;
    protected $is_interpreter_message = 0;
    protected $helper;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($call_request_id, $is_interpreter_message) {

        $this->call_request_id = $call_request_id;
        $this->is_interpreter_message = $is_interpreter_message;

        $this->helper = new Helper();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        try {

            Log::info("call request in queue function : In function", ["call_request_id" => $this->call_request_id]);

            $this->callRequest();
        } catch (\Exception $ex) {
            DB::rollBack();
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param \App\User $user
     * @param \App\Http\Helpers\Helpers $helpers
     */
    private function callRequest() {
        try {
            DB::beginTransaction();
            $current_user = $this->helper->getLoginUser();

            $get_call_details = Call::getCallMessageData()->where('id', $this->call_request_id)->first();

            $call_detail_exists = CallDetail::where(['call_id' => $get_call_details->id, 'status' => 2])->first();
            $user_profiles_data = UserProfile::getUserProfileData()->where('id', $get_call_details->from_user_profile_id)->first();


            if (isset($this->is_interpreter_message) && !empty($this->is_interpreter_message)) {
                if (empty($call_detail_exists)) {



                    $interpreter_lists = User::getActiveInterpreter()->where('user_languages.language_id', $get_call_details->language_id)->get();

                    if (isset($interpreter_lists[0]->user_profile->id) && !empty($interpreter_lists[0]->user_profile->id)) {


                        $title = "Call comming";

                        if (!empty($user_profiles_data->first_name) && !empty($user_profiles_data->last_name)) {
                            $message = $user_profiles_data->first_name . " " . $user_profiles_data->lastname . " Calling You";
                        } else {
                            $message = "Supervisor Calling You";
                        }

                        $purpose = Purpose::getPurposeData()->where('id', $get_call_details->purpose_id)->first();

                        $language = Language::getLanguageData()->where('id', $get_call_details->language_id)->first();


                        $user_data = array(
                            'language' => $language->name,
                            'purpose' => $purpose->name,
                            'company' => $user_profiles_data->company->company_name,
                            'city' => $user_profiles_data->locations->city->name,
                            'miles' => $user_profiles_data->locations->mile->value,
                            'region' => $user_profiles_data->locations->regions->value,
                            'site' => $user_profiles_data->locations->site,
                        );



                        $user_quickblock_session = $this->helper->createUserSession(env('ADMIN_LOGIN'), env('ADMIN_PASSWORD'));
                        $quickblock_token = $user_quickblock_session['session']['token'];

                        $custom_message = array(
                            'purpose_id' => $get_call_details->purpose_id,
                            'language_id' => $get_call_details->language_id,
                            'call_request_id' => $get_call_details->id,
                            'from_user_profile_id' => $get_call_details->from_user_profile_id,
                            'from_user_role_id' => $get_call_details->from_user_role_id,
                            'call_details' => $get_call_details
                        );

                        $last_call_log = CallLog::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();

                        $call_log_data = array(
                            'call_id' => $get_call_details->id,
                            'from_status' => $last_call_log->to_status,
                            'to_status' => $this->helper->getCallStatusId('messages_created')
                        );
                        $call_log_data = CallLog::create($call_log_data);



                        $send_message = $this->helper->createMessage($quickblock_token, $message, $custom_message, $this->is_interpreter_message);



                        if ($send_message) {
                            Log::info('Send interpreter message log: ' . json_encode($send_message));

                            $last_call_log = CallLog::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();

                            $call_log_data = array(
                                'call_id' => $get_call_details->id,
                                'from_status' => $last_call_log->to_status,
                                'to_status' => $this->helper->getCallStatusId('message_sent_to_GIQ')
                            );
                            $call_log_data = CallLog::create($call_log_data);



                            $call_init_message_data = array(
                                'call_id' => $get_call_details->id,
                                'supervisor_message_id' => null,
                                'interpreter_message_id' => $send_message['_id']
                            );
                            $call_init_message = CallInitMessage::create($call_init_message_data);
                            DB::commit();
                            $message = trans("translate.CALL_REQUEST_ADDED_CONNECT");
                            $response_array = $this->helper->custom_response(true, $send_message, $message);
                            return response()->json($response_array, Response::HTTP_OK);
                        }
                    } else {


                        $last_call_log = CallLog::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();

                            $call_log_data = array(
                                'call_id' => $get_call_details->id,
                                'from_status' => $last_call_log->to_status,
                                'to_status' => $this->helper->getCallStatusId('no_interpreter_found')
                            );
                            $call_log_data = CallLog::create($call_log_data);

                        $call_record = Call::find($get_call_details->id);
                        $call_record->action = 3;
                        $call_record->status = $this->helper->getCallStatusId('end');
                        $call_record->save();
                        
                        $last_call_log = CallLog::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();

                            $call_log_data = array(
                                'call_id' => $call->id,
                                'from_status' => $last_call_log->to_status,
                                'to_status' => $this->helper->getCallStatusId('terminated_by_system')
                            );
                            $call_log_data = CallLog::create($call_log_data);

                        
                        
                        DB::commit();
                        $message = trans("translate.INTERPRETER_CALL_NOT_CONNECTED");
                        $count = count($interpreter_lists);
                        $response_array = $this->helper->custom_response(true, $interpreter_lists, $message, false, $count);
                        return response()->json($response_array, Response::HTTP_OK);
                    }
                } else {
                    $call_detail_exists = json_decode($call_detail_exists['call_detail']);
                    DB::rollBack();
                    $message = trans("translate.CALL_ALREADY_INITIATED");
                    $count = count($interpreter_lists);
                    $response_array = $this->helper->custom_response(true, $call_detail_exists, $message, false, $count);
                    return response()->json($response_array, Response::HTTP_OK);
                }
            } else {
                $call_init_message_exist = CallInitMessage::where('call_id', $get_call_details->id)->first();
                if (!isset($call_init_message_exist->supervisor_message_id) || empty($call_init_message_exist->supervisor_message_id)) {
                    $title = "Call Accepted";

                    $message = "Interrpreter Call Reciving ";

                    $purpose = Purpose::getPurposeData()->where('id', $get_call_details->purpose_id)->first();

                    $language = Language::getLanguageData()->where('id', $get_call_details->language_id)->first();
                     $call_record = Call::find($get_call_details->id);
                        $call_record->status = $this->helper->getCallStatusId('connect');
                        $call_record->save();

                    $custom_message = array(
                        'purpose_id' => $get_call_details->purpose_id,
                        'language_id' => $get_call_details->language_id,
                        'call_request_id' => $get_call_details->id,
                        'from_user_profile_id' => $get_call_details->from_user_profile_id,
                        'from_user_role_id' => $get_call_details->from_user_role_id,
                        'call_details' => $get_call_details
                    );
                    $interpreter_chat_room = InterPreterChat::where('user_profile_id', $get_call_details->call_details[0]->user_profile->id)->first();
                    $get_call_details->chat_room_details = json_decode($interpreter_chat_room->chat_room_details, true);
                    $user_quickblock_session = $this->helper->createUserSession(env('ADMIN_LOGIN'), env('ADMIN_PASSWORD'));
                    $quickblock_token = $user_quickblock_session['session']['token'];

                    
                    $last_call_log = CallLog::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();

                            $call_log_data = array(
                                'call_id' => $get_call_details->id,
                                'from_status' => $last_call_log->to_status,
                                'to_status' => $this->helper->getCallStatusId('supervisor_message_created')
                            );
                            $call_log_data = CallLog::create($call_log_data);
                    
                    
                    $send_message = $this->helper->createMessage($quickblock_token, $message, $custom_message, $this->is_interpreter_message);

                    if(isset($send_message) && !empty($send_message)){
                        Log::info('Send supervisor message log: ' . json_encode($send_message));
                       $last_call_log = CallLog::where('call_id', $get_call_details->id)->orderBy('id', 'DESC')->first();

                            $call_log_data = array(
                                'call_id' => $get_call_details->id,
                                'from_status' => $last_call_log->to_status,
                                'to_status' => $this->helper->getCallStatusId('message_sent_to_supervisor')
                            );
                            $call_log_data = CallLog::create($call_log_data);

                    $call_init_message_exist->supervisor_message_id = $send_message['_id'];
                    $call_init_message_exist->save();
                    }
                    DB::commit();
                } else {
                    $call_detail_exists = json_decode($call_detail_exists['call_detail']);
                    DB::rollBack();
                    $message = trans("translate.CALL_ALREADY_INITIATED");
                    $count = count($interpreter_lists);
                    $response_array = $this->helper->custom_response(true, $call_detail_exists, $message, false, $count);
                    return response()->json($response_array, Response::HTTP_OK);
                }
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

}
