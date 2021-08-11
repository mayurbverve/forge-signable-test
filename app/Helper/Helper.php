<?php

namespace App\Helper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Models\UserRole;
use App\Models\User;
use App\Models\EmailTemplate;
use App\Models\CallStatus;
use App\Jobs\SendEmailJob;
use VideoThumbnail;
use JWTFactory;
use JWTAuth;
use Hash;
use App;
use Log;
use File;
use Image;
use Mail;

class Helper {
    /* Header Get */

    public static function getHeaders() {
        return \Request::header();
    }

    /* Get Login User */

    public static function getLoginUser() {
        $helper = new Helper();
        $user = "";
        $headers = \Request::header();
        if (isset($headers['authorization'])) {
            $token_data = explode(" ", $headers['authorization'][0]);
            $user = Auth::user();
            $user_role = $user->getRoleByRoleUserID();

            $user->role = '';
            if ($user_role) {
                $user->role = $user_role->role_name;
            }
        }

        return $user;
    }

    /*
     * Response to send to user in all requests
     *
     * @param $success boolean response status
     * @param $data array response data
     * @param $message response message
     * @return $response_array final response that send on frontend
     */

    public function custom_response($status = false, $data = array(), $message = "", $count_flag = false, $total_counts = "") {

        $headers = \Request::header();

        if (empty($data)) {
            $data = ((object) []);
        }


        if (isset($total_counts) && $total_counts !== "") {
            $response_array = array(
                'status' => $status,
                'data' => $data,
                'count' => $total_counts,
                'message' => $message
            );
        } else {
            $response_array = array(
                'status' => $status,
                'data' => $data,
                'message' => $message
            );
        }

        if (isset($headers["is-mobile"][0]) && ($headers["is-mobile"][0] == 1) && empty($data)) {
            unset($response_array["data"]);
        }

        if ($count_flag) {
            $response_array["totalNumberOfRecords"] = $total_counts;
        }

        return $response_array;
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $code) {
        $response = [
            'status' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    /*
     * Create a random string
     *
     * @param $length of the string to create
     * @return $str the string
     */

    public function random_string($length = 6) {
        $str = "";
        $characters = array_merge(range('a', 'z'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

    /*
     * function to convert UTC to Swiss time
     */

    public function convert_timezone($datetime = "", $to = "Europe/Zurich") {
        if ($datetime) {
            $datetime = new DateTime($datetime);
            $datetime->format('Y-m-d H:i:s');
            $la_time = new DateTimeZone('Europe/Zurich');
            $datetime->setTimezone($la_time);
            $datetime->format('Y-m-d H:i:s');
        }
        return $datetime;
    }

    /**
     * Send email to language
     *
     * @param array $to
     * @param array $data
     * @param array $template
     * @return array $email_template
     */
    public function send_email($to, $data, $template, $attachments = array(), $cc = array(), $bcc = array()) {

        Log::info("call send_email : Before", ["to" => $to, "data" => $data]);
        try {
            if ($template && !empty($template)) {
                // add email in queue
                Log::info('Call helper->sendEmailToUser Job', ["receiver_email" => $to]);
                //$job = (new SendEmailJob($template, $to, $data, $attachments, $cc));
                //  dispatch($job);


                $mail = Mail::send([], [], function($message) use ($template, $to, $data, $attachments, $cc, $bcc) {

                            Log::info("call mail send function : In function", ["data" => $data, "to" => $to, 'subject' => $template->email_subject]);

                            $email_body = $template->email_body;
                            if (!empty($data)) {
                                foreach ($data as $key => $value) {
                                    $temp_key = "{{" . $key . "}}";
                                    $email_body = str_replace($temp_key, $value, $email_body);
                                }
                            }

                            $message->to($to)
                                    ->subject($template->email_subject)
                                    ->setBody($email_body, 'text/html');

                            //Add cc recipients
                            if ($cc && !empty($cc)) {
                                $message->cc($cc);
                            }

                            //Add bcc recipients
                            if ($bcc && !empty($bcc)) {
                                $message->bcc($bcc);
                            }

                            // Add attachments if available
                            if ($attachments && !empty($attachments[0])) {
                                foreach ($attachments as $attachment) {
                                    $message->attach($attachment);
                                }
                            }
                        });


                Log::info("call mail send function : After", ["to" => $to]);
            }
        } catch (\Exception $e) {
            Log::error("call mail send with error ", ['message' => $e->getMessage(), 'line_num' => $e->getLine(), 'code' => $e->getCode(), "file_name" => $e->getFile()]);
            return false;
        }
        return true;
    }

    /*
     * Upload Attachment and generate image thumbnail
     */

    public static function upload_attachment($attachment, $section) {
        $helper = new Helper();
        // define image width and height
        $width = $height = 300;

        $file_name = $attachment->getClientOriginalName();
        $extension = $attachment->getClientOriginalExtension();

        $random_string = $helper->random_string(4);
        $attachment_name = $random_string . "_" . time() . '.' . $extension;

        $upload_path = public_path() . DS . 'uploads' . DS . $section;
        File::isDirectory($upload_path) or File::makeDirectory($upload_path, 0777, true, true);
        File::isDirectory($upload_path . DS . 'thumb') or File::makeDirectory($upload_path . DS . 'thumb', 0777, true, true);



        $attachment->move($upload_path, $attachment_name);

        return array(
            'attachment_name' => $file_name,
            'attachment_path' => 'uploads' . DS . $section . DS . $attachment_name,
            'attachment_extension' => $attachment->getClientOriginalExtension(),
            'attachment_thumb' => isset($attachment_name) ? 'uploads' . DS . $section . DS . 'thumb' . DS . $attachment_name : NULL,
        );
    }

    public static function copy_attachment($attachment, $section) {
        $helper = new Helper();
        $extension = File::extension($attachment->attachment_path);
        $original_path = public_path() . DS . $attachment->attachment_path;

        $random_string = $helper->random_string(4);
        $attachment_name = $random_string . "_" . time() . '.' . $extension;


        $upload_path = public_path() . DS . 'uploads' . DS . $section;
        File::isDirectory($upload_path) or File::makeDirectory($upload_path, 0777, true, true);
        File::isDirectory($upload_path . DS . 'thumb') or File::makeDirectory($upload_path . DS . 'thumb', 0777, true, true);

        $new_name_attachment_path = $upload_path . DS . $attachment_name;


        $copy_file = File::copy($original_path, $new_name_attachment_path);

        if ($attachment->attachment_type != 2 && $copy_file) {
            $original_thumbnail_path = public_path() . DS . $attachment->attachment_thumb;
            $thumbnail_name = $random_string . "_" . time() . '.' . "png";
            $new_thumnail_path = $upload_path . DS . 'thumb' . DS . $thumbnail_name;
            $copy_thumb_file = File::copy($original_thumbnail_path, $new_thumnail_path);
        }

        return array(
            'attachment_type' => $attachment->attachment_type,
            'attachment_name' => $attachment_name,
            'attachment_path' => 'uploads' . DS . $section . DS . $attachment_name,
            'attachment_extension' => $attachment->attachment_extension,
            'attachment_thumb' => isset($thumbnail_name) ? 'uploads' . DS . $section . DS . 'thumb' . DS . $thumbnail_name : NULL,
        );
    }

    /**
     * Helper to uploading image
     *
     * @param  image_name,
     * @param  image,
     * @param  image_path,
     * @param  \Illuminate\Http\Request  $request,
     * @return $incident_list
     * @throws Exception If try block get any errors.
     */
    public static function image_uploading($image_name, $image, $image_path) {
        $helper = new Helper();



        list($type, $image) = explode(';', $image);
        list(, $image_type) = explode(':', $type);
        list(, $extension) = explode('/', $type);
        list(, $image) = explode(',', $image);
        $image = base64_decode($image);


        // define image height width
        // $width = $height = 100;
        // define image custom name
        $image_custom_name = $image_name . "_" . $helper->random_string(4) . "_" . date("YmdHis") . "." . $extension;

        // Image origional name


        $image_name = public_path() . DS . "documents" . DS . $image_path . DS . $image_custom_name;



        $image_upload = File::put($image_name, $image);



        return array(
            'image' => $image_custom_name,
            'extension' => $image_type,
            'path' => $image_path
        );
    }

    public function extracttext($obj, $nested = 0) {
        $txt = "";
        if (method_exists($obj, 'getSections')) {
            foreach ($obj->getSections() as $section) {
                $txt .= " " . $this->extracttext($section, $nested + 1);
            }
        } else if (method_exists($obj, 'getElements')) {
            foreach ($obj->getElements() as $element) {
                $txt .= " " . $this->extracttext($element, $nested + 1);
            }
        } else if (method_exists($obj, 'getText')) {
            $txt .= $obj->getText();
        } else if (method_exists($obj, 'getRows')) {
            foreach ($obj->getRows() as $row) {
                $txt .= " " . $this->extracttext($row, $nested + 1);
            }
        } else if (method_exists($obj, 'getCells')) {
            foreach ($obj->getCells() as $cell) {
                $txt .= " " . $this->extracttext($cell, $nested + 1);
            }
        } else if (get_class($obj) != "PhpOffice\PhpWord\Element\TextBreak") {
            $txt .= "(" . get_class($obj) . ")"; # unknown object
        }

        return $txt;
    }

    /**
     * get user role data
     *
     * @param array $user_id
     * @return array $user_id
     */
    function getUserRole($user_id = "") {
        if ($user_id) {
            // Check filter data
            $role_id = UserRole::where("user_id", $user_id)->pluck("role_id")->first();

            return $role_id;
        }

        return '';
    }

    public function getUserById($id) {
        $user = User::find($id);
        return $user;
    }

    public function getUserByEmail($email) {
        $user = User::where('email', $email)->first();
        return $user;
    }

    public function getEmailTemplate($template_title) {
        $template_details = EmailTemplate::where('email_templates.template_key', $template_title)
                ->leftjoin("email_template_contents", "email_template_contents.email_template_id", "=", "email_templates.id")
                ->select('email_templates.id', 'email_templates.template_title', 'email_template_contents.email_body', 'email_template_contents.email_subject')
                ->first();
        return $template_details;
    }

    public function getCallStatus($call_status_name) {
        $call_status_details = CallStatus::select('id', 'value')->where('name', $call_status_name)->first();
        return $call_status_details;
    }

    public function getCallStatusId($call_status_name) {
        $call_status_details = $this->getCallStatus($call_status_name);
        return $call_status_details->id;
    }

    public function createSession() {


        $timestamp = strtotime(date("Y-m-d H:i:s"));
        $nonce = rand(100000000, 999999999);
        $response_token = $this->createSignature([], [], $timestamp, $nonce);

        $ch = curl_init();
        $post_data = [
            "application_id" => env('QB_APP_ID'),
            "auth_key" => env('QB_APP_KEY'),
            "timestamp" => $timestamp,
            "nonce" => $nonce,
            "signature" => $response_token,
        ];

        curl_setopt($ch, CURLOPT_URL, env('QB_APP_BASEURL') . '/session.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $session = json_decode($result, true);

        return $session;
    }

    public function getUser($email, $token) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, env('QB_APP_BASEURL') . '/users/by_email.json?email=' . $email);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Qb-Token: ' . $token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $user_exists = json_decode($result, true);
        return $user_exists;
    }

    public function createUser($quickblock_user_string, $email, $token) {

        $timestamp = strtotime(date("Y-m-d H:i:s"));
        $nonce = rand(100000000, 999999999);
        $response_token = $this->createSignature([], [], $timestamp, $nonce);

        $ch = curl_init();
        $user_data = array(
            "user" => array(
                'login' => $quickblock_user_string,
                'password' => $quickblock_user_string,
                'email' => $email,
                'full_name' => $quickblock_user_string
            )
        );

        curl_setopt($ch, CURLOPT_URL, env('QB_APP_BASEURL') . '/users.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($user_data));
        curl_setopt($ch, CURLOPT_POST, 1);


        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Qb-Token:' . $token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $user_create = json_decode($result, true);

        return $user_create;
    }

    public function createUserSession($login, $password) {

        $timestamp = strtotime(date("Y-m-d H:i:s"));
        $nonce = rand(100000000, 999999999);
        $response_token = $this->createSignature($login, $password, $timestamp, $nonce);

        $ch = curl_init();
        $post_data = [
            "application_id" => env('QB_APP_ID'),
            "auth_key" => env('QB_APP_KEY'),
            "timestamp" => $timestamp,
            "nonce" => $nonce,
            "signature" => $response_token,
            "user" => [
                "login" => $login,
                "password" => $password
            ]
        ];

        curl_setopt($ch, CURLOPT_URL, env('QB_APP_BASEURL') . '/session.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $session = json_decode($result, true);

        return $session;
    }

    private function createSignature($login = [], $password = [], $timestamp, $nonce) {

        if (!empty($login) && !empty($password)) {
            $signatureContent = "application_id=" . env('QB_APP_ID') .
                    "&auth_key=" . env('QB_APP_KEY') .
                    "&nonce=" . $nonce .
                    "&timestamp=" . $timestamp .
                    "&user[login]=" . $login .
                    "&user[password]=" . $password;
        } else {
            $signatureContent = "application_id=" . env('QB_APP_ID') .
                    "&auth_key=" . env('QB_APP_KEY') .
                    "&nonce=" . $nonce .
                    "&timestamp=" . $timestamp;
        }
        return hash_hmac('sha1', urldecode($signatureContent), env('QB_APP_SECRET'));
    }

    public function getUserAuthenticate($login, $password, $token) {

        $login_data = [
            "login" => $login,
            "password" => $password,
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, env('QB_APP_BASEURL') . '/login.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($login_data));
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Qb-Token:' . $token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);


        $login_response = json_decode($result, true);

        return $login_response;
    }

    public function getUserChatList($token) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, env('QB_APP_BASEURL') . '/chat/Dialog.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "type[in]=1,2&sort_desc=last_message_date_sent&limit=3");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Qb-Token:' . $token;
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $chat_list = json_decode($result, true);

        return $chat_list;
    }

    public function createChatRoom($token, $occupant_ids, $type, $group_name) {
        $ch = curl_init();

        $post_data = [
            'name' => $group_name,
            'type' => $type,
            'occupants_ids' => $occupant_ids
        ];
        curl_setopt($ch, CURLOPT_URL, env('QB_APP_BASEURL') . '/chat/Dialog.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($ch, CURLOPT_POST, 1);


        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Qb-Token:' . $token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $chat_create = json_decode($result, true);
        return $chat_create;
    }

    public function send_push_notification($token, $chat_room_id, $user_data) {
        $ch = curl_init();


        curl_setopt($ch, CURLOPT_URL, env('QB_APP_BASEURL') . "/chat/Dialog/$chat_room_id/notifications.json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{  \n   \"enabled\":1\n}");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');


        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Qb-Token:' . $token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $send_push_notification = json_decode($result, true);


        $ch = curl_init();

        $post_data = array(
        );

        curl_setopt($ch, CURLOPT_URL, env('QB_APP_BASEURL') . 'https://api.quickblox.com/events.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Qb-Token:' . $token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $send_push_notification = json_decode($result, true);


        return $send_push_notification;
    }

    public function createMessage($token, $message, $data, $intepreter_chat_room) {
        $ch = curl_init();

        if (isset($intepreter_chat_room) && !empty($intepreter_chat_room)) {
            $chat_dialog_id = env('COMMON_INTERPRETER_ROOMID');
        } else {
            $chat_dialog_id = env('COMMON_SUPERVISOR_ROOMID');
        }

        $post_data = [
            'chat_dialog_id' => $chat_dialog_id,
            'message' => $message,
            'custom_field_N' => json_encode($data),
            'send_to_chat' => 1,
            'is_pinned' => false
        ];

        curl_setopt($ch, CURLOPT_URL, env('QB_APP_BASEURL') . '/chat/Message.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Qb-Token:' . $token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $chat_create = json_decode($result, true);

        return $chat_create;
    }

    public function deleteMessage($token, $message_ids) {
        $ch = curl_init();
        
        $post_data =array(
          "force" => 1  
        );
        
        
        curl_setopt($ch, CURLOPT_URL, env('QB_APP_BASEURL') . '/chat/Message/' . $message_ids . '.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');


        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Qb-Token:'.$token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
    }

}
