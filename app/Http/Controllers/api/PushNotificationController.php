<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\UserPushDevice;
use App\Models\UserNotification;
use Carbon\Carbon;
use Validator;

class PushNotificationController extends Controller {

    protected $helper;

    /**
     * PropertyManagerController constructor.
     */
    public function __construct() {
        $this->helper = new Helper();
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message) {
        $response = [
            'status' => true,
            'data' => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 200) {
        $response = [
            'status' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            //$response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    /**
     * Add/Update User Device data
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function add_user_device(Request $request) {
        $input = $request->all();
        $currentUser = $this->helper->getLoginUser();
        $current_user_role = $currentUser->getRoleByRoleUserID();

        $role_user_id = $current_user_role->id;
        $device_id = $input["device_id"];
        $fcm_token = $input["fcm_token"];
        $device_type = (isset($input["device_type"])) ? $input["device_type"] : "";
        $created_at = Carbon::now();
        $user_device_data = UserPushDevice::where(["role_user_id" => $role_user_id, 'device_id' => $device_id, 'device_type' => $device_type])->first();

        if (!empty($user_device_data)) {
            $user_push_device = UserPushDevice::where(["role_user_id" => $role_user_id, 'device_id' => $device_id, 'device_type' => $device_type])->first();
            $user_push_device = UserPushDevice::find($user_push_device['id']);
            $user_push_device->role_user_id = $role_user_id;
            $user_push_device->device_id = $device_id;
            $user_push_device->device_type = $device_type;
            $user_push_device->fcm_token = $fcm_token;
            $user_push_device->updated_at = $created_at;
            $user_push_device->save();
            return $this->sendResponse($user_device_data, 'user_device_exists');
        } else {
            $user_push_device = new UserPushDevice();
            $user_push_device->role_user_id = $role_user_id;
            $user_push_device->device_id = $device_id;
            $user_push_device->device_type = $device_type;
            $user_push_device->fcm_token = $fcm_token;
            $user_push_device->created_at = $created_at;
            $user_push_device->save();
            return $this->sendResponse($user_push_device, 'user_device_added');
        }
    }

    function firebase_push_notification_android_device(Request $request) {
        $input = $request->all();

        $validator = Validator::make($request->all(), [
                    'role_user_id' => 'required|exists:user_push_devices,user_id',
                    'title' => 'required',
                    'message' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), $validator->errors());
        }

        $role_user_id = isset($input['role_user_id']) ? $input['role_user_id'] : '';
        $title = isset($input['title']) ? $input['title'] : '';
        $message = isset($input['message']) ? $input['message'] : '';
        $result = $this->sendPushNotification($role_user_id, $title, $message, 3);
        return $this->sendResponse(json_decode($result), 'push_sent');
    }

    function sendPushNotification($role_user_id, $title, $message, $content_id, $type, $action_by = 0, $user_data) {
        $user_device_data = UserPushDevice::where("role_user_id", $role_user_id)->get();
        $result = '';
        if (!empty($user_device_data)) {
            $fcm_token = [];
            foreach ($user_device_data as $key => $value) {
                $fcm_token[] = $value['fcm_token'];
            }
            if (!empty($fcm_token)) {
                $this->sendPushToDevice($fcm_token, $title, $message, $action_by, $user_data);
                $result = $this->store_user_notification($role_user_id, $content_id, $type, $title, $message, $action_by, $user_data);
            }
        }
        return $result;
    }

    function sendPushToDevice($fcm_token, $title, $message, $action_by = 0, $user_data = []) {
        $push_notification_key = getenv("PUSH_KEY");
        $postdata = [
            "registration_ids" => $fcm_token,
            "notification" => [
                "title" => $title,
                "text" => $message,
                "body" => $message,
                "click_action" => ".SplashActivity",
                "language" => $user_data['language'],
                "purpose" => $user_data['purpose'],
                "company" => $user_data['company'],
                "city" => $user_data['city'],
                "miles" => $user_data['miles'],
                "region" => $user_data['region'],
                "site" => $user_data['site'],
                "channel_id" => "com.verve.signable.id",
                "android_channel_id" => "com.verve.signable.id",
                "sound" => "alert.mp3"
            ],
            "android" => [
                "notification" => [
                    "channel_id" => "com.verve.signable.id"
                ]
            ],
            "data" => [
                "title" => $title,
                "description" => $message,
                "text" => $message,
                "body" => $message,
                "is_read" => 0,
                "language" => $user_data['language'],
                "purpose" => $user_data['purpose'],
                "company" => $user_data['company'],
                "city" => $user_data['city'],
                "miles" => $user_data['miles'],
                "region" => $user_data['region'],
                "site" => $user_data['site'],
                "action_by" => $action_by,
                "channel_id" => "com.verve.signable.id"
            ]
        ];
        $postdata_json = json_encode($postdata);
//        print_r($postdata_json);die;
        $headers = array();
        $headers = array('Authorization: key=' . $push_notification_key, 'Content-Type: application/json');
        $url = "https://fcm.googleapis.com/fcm/send";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //Execute post
        $result = curl_exec($ch);

        if ($result === FALSE) {
            //die('Curl failed: ' . curl_error($ch));
            return false;
        }
        curl_close($ch);
        //Close connection
        return true;
    }

    function store_user_notification($role_user_id, $content_id, $type, $title, $message, $action_by = 0) {
        // if send push successfuly than insert record into the user_notification table
        $user_push_notification = new UserNotification();
        $user_push_notification->role_user_id = $role_user_id;
        $user_push_notification->notification_type = $type;
        $user_push_notification->notification_title = $title;
        $user_push_notification->notification_message = $message;
        $user_push_notification->content_id = $content_id;
        $user_push_notification->is_read = 0;
        $user_push_notification->read_date = NULL;
        $user_push_notification->action_by = $action_by;
        $user_push_notification->sent_date = Carbon::now();
        $user_push_notification->created_at = Carbon::now();
        $user_push_notification->save();
        return $user_push_notification;
    }

}
