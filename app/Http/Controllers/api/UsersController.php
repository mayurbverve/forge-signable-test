<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\PermissionRole;
use App\Models\LoginHistory;
use App\Helper\Helper;
use App\Models\Token;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\EmailTemplate;
use App\Models\ActiveInterpreter;
use App\Models\UserRole;
use App\Models\UserPushDevice;
use App\Models\UserLanguage;
use App\Models\InterPreterChat;
use App\Models\Location;
use Carbon\Carbon;
use App\Models\Timezones;
use JWTAuth;
use JWTFactory;
use Config;
use Log;
use DB;

class UsersController extends Controller {

    protected $helper;

    /**
     * UserController constructor.
     */
    public function __construct() {
        $this->helper = new Helper();
        $this->supplier_roles = array(2);
        $this->consumer_roles = array(3);
    }

    /**
     * User Register.
     * @param  \Illuminate\Http\Request  $request
     * @return $user
     * @throws Exception If try block get any errors.
     */
    public function register(Request $request) {
        try {
            DB:beginTransaction();

            Log::info('Call register user: Params: ' . json_encode($request->all()));

            $validator = Validator::make($request->all(), [
                        'first_name' => 'required',
                        'last_name' => 'required',
                        'email' => 'required',
                        'phone' => 'required',
                        'gender' => 'required',
                        'profile_photo' => 'required',
                        'date_of_join' => 'required',
                        'date_of_birth' => 'required'
            ]);

            if ($validator->fails()) {
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));

                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }

            $currentUser = $this->helper->getLoginUser();

            $register_data = array(
                'email' => isset($request->email) ? $request->email : "",
                'phone' => isset($request->phone) ? $request->phone : "",
                'authorization_key' => isset($request->authorization_key) && !empty($request->authorization_key) ? $request->authorization_key : '',
                'social_type' => isset($request->social_type) && !empty($request->social_type) ? $request->social_type : '',
                'qb_authorization' => isset($request->qb_authorization) && !empty($request->qb_authorization) ? $request->qb_authorization : '',
                'qb_id' => isset($request->qb_id) && !empty($request->qb_id) ? $request->qb_id : '',
                'qb_password' => isset($request->qb_password) && !empty($request->qb_password) ? $request->qb_password : '',
                'login_type' => isset($request->login_type) && !empty($request->login_type) ? $request->login_type : '',
                'is_active' => 0,
                'is_verified' => 0,
                'is_deleted' => 0
            );
            Log::info('Create User Regiater: Params: ' . json_encode($register_data));
            $user = User::create($register_data);

            $user_profile_data = array(
                'company_id' => isset($request->company_id) && !empty($request->company_id) ? $request->company_id : "",
                'first_name' => isset($request->first_name) && !empty($request->first_name) ? $request->first_name : "",
                'last_name' => isset($request->last_name) && !empty($request->last_name) ? $request->last_name : "",
                'profile_photo' => isset($request->profile_photo) && !empty($request->profile_photo) ? $request->profile_photo : "",
                'gender' => isset($request->gender) && !empty($request->gender) ? $request->gender : "",
                'date_of_join' => isset($request->date_of_join) && !empty($request->date_of_join) ? $request->date_of_join : "",
                'date_of_birth' => isset($request->date_of_birth) && !empty($request->date_of_birth) ? $request->date_of_birth : "",
            );

            $user_profile = UserProfile::create($user_profile_data);

            if (!empty($user) && !empty($user) && isset($user_profile) && !empty($user_profile)) {
                // Add SuperAdmin role
                $role_data = [
                    'user_id' => $user->id,
                    'role_id' => Config::get('constants.options.supplier_admin_id'),
                ];

                Log::info('Create property owner role: Params:' . json_encode($role_data));
                $user_role = UserRole::create($role_data);

                if (isset($user) && !empty($user) && isset($user_role) && !empty($user_role)) {
                    //Send verification email to user to create password
                    $random_string = $this->helper->random_string(15);
                    $token_data = array(
                        'email' => $user->email,
                        'token' => $random_string,
                        'user_id' => $user->id,
                        'expired_time' => strtotime('+1 Day'),
                        'is_used' => 0
                    );
                    Log::info('Create user create password token  : Params : ' . json_encode($token_data));
                    $add_token = Token::create($token_data);

                    //Send verification email to user to create password
                    $template_replace_data = array(
                        'fullname' => $user->name,
                        'forget_password_link' => '<a style="color: white;background-color: #173a67;padding: 10px 20px;text-decoration: none;" href="' . Config::get('settings.FORGET_PASSWORD_URL') . $random_string . '"> Set Password</a>',
                        'LOGO' => Config::get('settings.APP_LOGO'),
                        'project_name' => Config::get('settings.APP_NAME'),
                        'app_store_logo' => Config::get('settings.APPLE_STORE_LOGO'),
                        'play_store_logo' => Config::get('settings.PLAY_STORE_LOGO'),
                        'app_store_link' => Config::get('settings.APPLE_STORE_LINK'),
                        'play_store_link' => Config::get('settings.PLAY_STORE_LINK'),
                    );

                    $template_details = $this->helper->getEmailTemplate('forget_password');

                    $send_data = $this->helper->send_email($request->email, $template_replace_data, $template_details);
                }
                $message = trans("translate.USER_REGISTER_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $user, $message);
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
     * Used to update user password in database set by user
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $password,
     * @param  string $confirm_password,
     * @param  string $token,
     * @return boolean true/false,
     * @throws Exception If try block get any errors.
     */
    public function update_password(Request $request) {
        try {
            Log::info('call update_password : Params : ' . json_encode($request->all()));

            $validator = Validator::make($request->all(), [
                        'password' => 'required',
                        'confirm_password' => 'required',
                        'password_token' => 'required',
            ]);

            if ($validator->fails()) {
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }

            // Check if user exists or not
            $token_data = Token::where('token', '=', $request->password_token)->first();

            if (!empty($token_data)) {
                if ($token_data->is_used == 1) {
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.LINK_EXPIRED"));
                    return response()->json($response_array, Response::HTTP_BAD_REQUEST);
                }

                $current_timestap = strtotime("now");
                $token_expired_time = $token_data->expired_time;
                if ($token_expired_time < $current_timestap) {
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.PASSWORD_TOKEN_EXPIRED"));
                    return response()->json($response_array, Response::HTTP_BAD_REQUEST);
                }

                $user = User::find($token_data->user_id);
                $user->password = Hash::make($request->password);
                $user->is_active = 1;
                $user->is_verified = 1;
                $user->save();

                // make token used after update password
                $token_data->is_used = 1;
                $token_data->save();
                $data = array("email" => $user->email);
                $response_array = $this->helper->custom_response(true, $data, trans("translate.PASSWORD_UPDATED"));
                return response()->json($response_array, Response::HTTP_CREATED);
            }
            $response_array = $this->helper->custom_response(false, array(), trans("translate.PASSWORD_TOKEN_EXPIRED"));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * perform login in system and get loggin user data in  response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $email,
     * @param  string $password,
     * @return $user,
     * @throws Exception If try block get any errors.
     */
    public function login(Request $request) {

        $validator = Validator::make($request->all(), [
                    'email' => 'required',
                    'password' => 'required',
                    'source' => 'required'
        ]);

        if ($validator->fails()) {
            $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));

            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }

        $credentials = $request->only('email', 'password', 'authenitication_key');
        $credentials["is_active"] = 1;
        $credentials["is_deleted"] = 0;

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                $user_check_data = User::where("email", $request->email)->first();
                if (isset($user_check_data->is_verified) && !$user_check_data->is_verified) {
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.USER_NOT_VERIFIED"), "", "", false);
                    return response()->json($response_array, Response::HTTP_OK);
                } else {
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.INVALID_CREDENTIALS"), "", "", false);
                    return response()->json($response_array, Response::HTTP_OK);
                }
            }
        } catch (JWTException $e) {
            $response_array = $this->helper->custom_response(false, array(), trans("translate.ERROR_NOT_CREATED_TOKEN"), "", "", false);
            return response()->json($response_array, 500);
        }

        $current_user = auth()->user();

        $roles = $current_user->user_profile->user_roles;

        $user_profiles_data = UserProfile::getUserProfileData()->where('id', $current_user->user_profile->id)->first();

        if (!$roles->isEmpty()) {
            $current_user->role = $roles[0];
            $role_user_id = $current_user->role->id;
            $role_id = $current_user->role->role_id;

            if (isset($request->source) && !empty($request->source)) {
                if ($request->source == 2 && in_array($role_id, $this->supplier_roles)) {
                    $message = trans("translate.USER_ROLE_DEVICE_CONSUMER_NOT_MATCHED");
                    $response_array = $this->helper->custom_response(true, array(), $message);
                    return response()->json($response_array, Response::HTTP_OK);
                } elseif ($request->source == 1 && in_array($role_id, $this->consumer_roles)) {
                    $message = trans("translate.USER_ROLE_DEVICE_SUPPLIER_NOT_MATCHED");
                    $response_array = $this->helper->custom_response(true, array(), $message);
                    return response()->json($response_array, Response::HTTP_OK);
                }
            }

            // Added role_user_id and generated new JWT token
            $token = JWTAuth::claims(['role_user_id' => $role_user_id])->fromUser($current_user);

            JWTAuth::setToken($token);
            $token_data = JWTAuth::getPayload();

            $current_user->token = $token;
            $current_user->token_expirytime = $token_data['exp'];
            $current_user->user_profile->company = $user_profiles_data->company;
            $current_user->user_profile->locations = $user_profiles_data->locations;
            $current_user->user_profile->languages = $user_profiles_data->languages;

            $active_interpreter_data = ActiveInterpreter::where('user_profile_id', $current_user->user_profile->id)->delete();
            if (in_array($role_id, $this->supplier_roles)) {
                if (isset($current_user->user_profile->languages) && !empty($current_user->user_profile->languages)) {
                    foreach ($current_user->user_profile->languages AS $language) {
                        $active_interpreter_data = [
                            'user_profile_id' => $current_user->user_profile->id,
                            'language_id' => $language->language_id,
                            'ranking' => $language->ranking,
                            'is_active' => 1,
                            'status' => 1,
                        ];
                        $active_interpreter = ActiveInterpreter::create($active_interpreter_data);
                    }
                }
            }

            $user_permissions = PermissionRole::with("permission_details")->where("role_id", $role_id)->get();
            $current_user->user_permissions = $user_permissions;

            // Store data into login history
            $ip_address = $request->ip();
            $browser = $request->header('User-Agent');
            $login_history_data = [
                'user_profile_id' => $current_user->user_profile->id,
                'activity' => 'login',
                'ip_address' => $ip_address,
                'browser' => $browser
            ];
            if (in_array($role_id, $this->supplier_roles) && isset($current_user->qb_id) && !empty($current_user->qb_id) && isset($current_user->qb_password) && !empty($current_user->qb_password)) {
                $user_quickblock_session = $this->helper->createUserSession($current_user->qb_id, $current_user->qb_password);
                $quickblock_token = $user_quickblock_session['session']['token'];

                $quickblock_login_response = $this->helper->getUserAuthenticate($current_user->qb_id, $current_user->qb_password, $quickblock_token);
                $current_user->quickblock_user_data = $quickblock_login_response;

                $occupant_ids = $quickblock_login_response['user']['id'];
                $group_name = $quickblock_login_response['user']['full_name'];

                $chat_room_details = InterPreterChat::select('chat_room_details')->where('user_profile_id', $current_user->user_profile->id)->first();
                if (isset($chat_room_details) && !empty($chat_room_details)) {
                    $chat_room_details = json_decode($chat_room_details['chat_room_details'], true);
                } else {
                    $chat_room_details = $this->helper->createChatRoom($quickblock_token, $occupant_ids, 1, $group_name);
                    $interpreter_chat_detail = array(
                        'user_profile_id' => $current_user->user_profile->id,
                        'chat_room_details' => json_encode($chat_room_details)
                    );
                    $interpreter_chat_room = InterPreterChat::create($interpreter_chat_detail);
                }

                $current_user->quickblock_chat_room_data = $chat_room_details;
                $current_user->common_chat_interpreter_id = env('COMMON_INTERPRETER_ROOMID');
            }
            Log::info('Creauthenticateate Login History: Params: ' . json_encode($login_history_data));
            $login_history = LoginHistory::create($login_history_data);

            unset($current_user->password);
            unset($current_user->user_roles);
            $message = trans("translate.USER_LOGGEDIN_SUCCESSFULLY");
            $response_array = $this->helper->custom_response(true, $current_user, $message);
            return response()->json($response_array, Response::HTTP_OK);
        } else {
            $message = trans("translate.USER_ROLE_NOT_FOUND");
            $response_array = $this->helper->custom_response(true, array(), $message);
            return response()->json($response_array, Response::HTTP_OK);
        }
    }

    /**
     * perform login in system and get login and register for supervisor in  response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $email,
     * @param  string $password,
     * @return $user,
     * @throws Exception If try block get any errors.
     */
    public function authenticate(Request $request) {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                        'email' => 'required',
                        'miles' => 'required',
                        'region' => 'required',
                        'site' => 'required',
                        'source' => 'required'
            ]);

            if ($validator->fails()) {
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));

                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }

            if (strpos($request->email, "amazon.com") !== FALSE || strpos($request->email, "amazon.in") !== FALSE) {
                $user = User::getUserData()->where('email', $request->email)->first();

                if (isset($user) && !empty($user)) {
//                    print_r($user->user_profile->user_roles[0]->role_id);die;
                    if ($user->user_profile->user_roles[0]->role_id != Config::get('constants.options.supervisor_id')) {
                        $validator = Validator::make($request->all(), [
                                    'password' => 'required',
                        ]);

                        if ($validator->fails()) {
                            $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));

                            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
                        }
                    }

                    //**********************already register user*****************************//

                    $credentials = array(
                        'email' => $request->email,
                        'password' => 'login12*',
                        "is_active" => 1,
                        "is_deleted" => 0
                    );

                    try {
                        if (!$token = JWTAuth::attempt($credentials)) {
                            $user_check_data = User::where("email", $request->email)->first();
                            if (isset($user_check_data->is_verified) && !$user_check_data->is_verified) {
                                $response_array = $this->helper->custom_response(false, array(), trans("translate.USER_NOT_VERIFIED"), "", "", false);
                                return response()->json($response_array, Response::HTTP_OK);
                            } else {
                                $response_array = $this->helper->custom_response(false, array(), trans("translate.INVALID_CREDENTIALS"), "", "", false);
                                return response()->json($response_array, Response::HTTP_OK);
                            }
                        }
                    } catch (JWTException $e) {
                        $response_array = $this->helper->custom_response(false, array(), trans("translate.ERROR_NOT_CREATED_TOKEN"), "", "", false);
                        return response()->json($response_array, 500);
                    }

                    $current_user = auth()->user();

                    $roles = $current_user->user_profile->user_roles;

                    $user_profiles_data = UserProfile::getUserProfileData()->where('id', $current_user->user_profile->id)->first();

                    if (!$roles->isEmpty()) {
                        $current_user->role = $roles[0];
                        $role_user_id = $current_user->role->id;
                        $role_id = $current_user->role->role_id;

                        // Added role_user_id and generated new JWT token
                        $token = JWTAuth::claims(['role_user_id' => $role_user_id])->fromUser($current_user);

                        JWTAuth::setToken($token);
                        $token_data = JWTAuth::getPayload();

                        $current_user = User::getUserData()->where('email', $request->email)->first();
                        $current_user->token = $token;

                        $user_location = Location::find($current_user->user_profile->locations->id);
                        $user_location->miles = isset($request->miles) && !empty($request->miles) ? $request->miles : Null;
                        $user_location->region = isset($request->region) && !empty($request->region) ? $request->region : Null;
                        $user_location->site = isset($request->site) && !empty($request->site) ? $request->site : Null;
                        $user_location->save();

                        if (isset($user_location) && !empty($user_location)) {
                            DB::commit();

                            $current_user->token = $token;
                            $current_user->token_expirytime = $token_data['exp'];
                            $current_user->user_profile->company = $user_profiles_data->company;
                            $current_user->user_profile->locations = $user_profiles_data->locations;
                            $current_user->user_profile->languages = $user_profiles_data->languages;

                            // Store data into login history
                            $ip_address = $request->ip();
                            $browser = $request->header('User-Agent');
                            $login_history_data = [
                                'user_profile_id' => $current_user->user_profile->id,
                                'activity' => 'login',
                                'ip_address' => $ip_address,
                                'browser' => $browser
                            ];

                            Log::info('Creauthenticateate Login History: Params: ' . json_encode($login_history_data));
                            $login_history = LoginHistory::create($login_history_data);

                            if (isset($current_user) && !empty($current_user)) {
                                $current_user->common_chat_supervisor_id = env('COMMON_SUPERVISOR_ROOMID');
                                $current_user->common_test_audio_video_room_id = env('COMMON_TESTING_AUDIO_VIDEO_ROOMID');
                                $message = trans("translate.USER_LOGGEDIN_SUCCESSFULLY");
                                $response_array = $this->helper->custom_response(true, $current_user, $message);
                                return response()->json($response_array, Response::HTTP_OK);
                            }
                        }
                    } else {
                        $message = trans("translate.USER_ROLE_NOT_FOUND");
                        $response_array = $this->helper->custom_response(true, array(), $message);
                        return response()->json($response_array, Response::HTTP_OK);
                    }
                } elseif (!isset($user) || empty($user)) {
                    //**********************new  register user *****************************//
                    $user_data = array(
                        'email' => isset($request->email) ? $request->email : "",
                        'phone' => isset($request->phone) ? $request->phone : "",
                        'password' => Hash::make('login12*'),
                        'authorization_key' => isset($request->authorization_key) && !empty($request->authorization_key) ? $request->authorization_key : Null,
                        'social_type' => isset($request->social_type) && !empty($request->social_type) ? $request->social_type : Null,
                        'qb_authorization' => isset($request->qb_authorization) && !empty($request->qb_authorization) ? $request->qb_authorization : Null,
                        'qb_id' => isset($request->qb_id) && !empty($request->qb_id) ? $request->qb_id : Null,
                        'qb_password' => isset($request->qb_password) && !empty($request->qb_password) ? $request->qb_password : Null,
                        'login_type' => 1,
                        'is_active' => 1,
                        'is_verified' => 1,
                        'is_deleted' => 0,
                        'is_forgeted' => 0
                    );

                    $user_create = User::create($user_data);

                    $user_create->created_by = $user_create->id;
                    $user_create->updated_by = $user_create->id;
                    $user_create->save();

                    $first_name_data = explode("@", $request->email);
                    $first_name = $first_name_data[0];

                    $user_profile_data = array(
                        'user_id' => $user_create->id,
                        'company_id' => 2,
                        'first_name' => isset($first_name) && !empty($first_name) ? $first_name : "",
                        'last_name' => isset($request->last_name) && !empty($request->last_name) ? $request->last_name : Null,
                        'profile_photo' => isset($request->profile_photo) && !empty($request->profile_photo) ? $request->profile_photo : Null,
                        'gender' => isset($request->gender) && !empty($request->gender) ? $request->gender : Null,
                        'date_of_join' => isset($request->date_of_join) && !empty($request->date_of_join) ? $request->date_of_join : Null,
                        'date_of_birth' => isset($request->date_of_birth) && !empty($request->date_of_birth) ? $request->date_of_birth : Null,
                    );

                    $user_profile_create = UserProfile::create($user_profile_data);

                    $user_role_data = array(
                        'role_id' => Config::get('constants.options.supervisor_id'),
                        'user_profile_id' => $user_profile_create->id
                    );
                    $user_role_create = UserRole::create($user_role_data);

                    $user_location_data = array(
                        'user_profile_id' => $user_profile_create->id,
                        'city_id' => isset($request->city_id) && !empty($request->city_id) ? $request->city_id : 1,
                        'miles' => isset($request->miles) && !empty($request->miles) ? $request->miles : Null,
                        'region' => isset($request->region) && !empty($request->region) ? $request->region : Null,
                        'site' => isset($request->site) && !empty($request->site) ? $request->site : Null
                    );
                    $user_location_create = Location::create($user_location_data);

                    if (isset($user_create) && !empty($user_create) && isset($user_profile_create) && !empty($user_profile_create) && isset($user_role_create) && !empty($user_role_create) && isset($user_location_create) && !empty($user_location_create)) {

                        $user_quickblock_session = $this->helper->createSession();
                        $quickblock_token = $user_quickblock_session['session']['token'];

                        $quickblock_user_string = $first_name . "amazon";

                        $numbers = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0", " ");
                        $quickblock_user_string = ucfirst(str_replace($numbers, '', $quickblock_user_string)) . rand(000, 333);

                        $user_exist = $this->helper->getUser($request->email, $quickblock_token);

                        if ($user_exist['code'] === null) {
                            $create_user = $this->helper->createUser($quickblock_user_string, $request->email, $quickblock_token);
                            $user_create->qb_id = $quickblock_user_string;
                            $user_create->qb_password = $quickblock_user_string;
                            $user_create->save();
                        } else {
                            $user_create->qb_id = $user_exist['user']['login'];
                            $user_create->qb_password = $user_exist['user']['login'];
                            $user_create->save();
                        }

                        DB::commit();

                        // Store data into login history
                        $ip_address = $request->ip();
                        $browser = $request->header('User-Agent');
                        $login_history_data = [
                            'user_profile_id' => $user_profile_create->id,
                            'activity' => 'login',
                            'ip_address' => $ip_address,
                            'browser' => $browser
                        ];

                        Log::info('Creauthenticateate Login History: Params: ' . json_encode($login_history_data));
                        $login_history = LoginHistory::create($login_history_data);
                    }



                    $credentials = array(
                        'email' => $request->email,
                        'password' => 'login12*',
                    );
                    $credentials["is_active"] = 1;
                    $credentials["is_deleted"] = 0;
                    try {
                        if (!$token = JWTAuth::attempt($credentials)) {
                            $user_check_data = User::where("email", $request->email)->first();
                            if (isset($user_check_data->is_verified) && !$user_check_data->is_verified) {
                                $response_array = $this->helper->custom_response(false, array(), trans("translate.USER_NOT_VERIFIED"), "", "", false);
                                return response()->json($response_array, Response::HTTP_OK);
                            } else {
                                $response_array = $this->helper->custom_response(false, array(), trans("translate.INVALID_CREDENTIALS"), "", "", false);
                                return response()->json($response_array, Response::HTTP_OK);
                            }
                        }
                    } catch (JWTException $e) {
                        $response_array = $this->helper->custom_response(false, array(), trans("translate.ERROR_NOT_CREATED_TOKEN"), "", "", false);
                        return response()->json($response_array, 500);
                    }

                    $current_user = auth()->user();

                    $roles = $current_user->user_profile->user_roles;

                    $user_profiles_data = UserProfile::getUserProfileData()->where('id', $current_user->user_profile->id)->first();

                    if (!$roles->isEmpty()) {
                        $current_user->role = $roles[0];
                        $role_user_id = $current_user->role->id;
                        $role_id = $current_user->role->role_id;

                        // Added role_user_id and generated new JWT token
                        $token = JWTAuth::claims(['role_user_id' => $role_user_id])->fromUser($current_user);

                        JWTAuth::setToken($token);
                        $token_data = JWTAuth::getPayload();

                        $current_user->token = $token;

                        if (isset($current_user) && !empty($current_user)) {

                            $current_user->token = $token;
                            $current_user->token_expirytime = $token_data['exp'];
                            $current_user->user_profile->company = $user_profiles_data->company;
                            $current_user->user_profile->locations = $user_profiles_data->locations;
                            $current_user->user_profile->languages = $user_profiles_data->languages;
                            $current_user->common_chat_supervisor_id = env('COMMON_SUPERVISOR_ROOMID');
                            $current_user->common_test_audio_video_room_id = env('COMMON_TESTING_AUDIO_VIDEO_ROOMID');
                            $message = trans("translate.USER_LOGGEDIN_SUCCESSFULLY");
                            $response_array = $this->helper->custom_response(true, $current_user, $message);
                            return response()->json($response_array, Response::HTTP_OK);
                        }
                    }
                } else {
                    $message = trans("translate.USER_ROLE_NOT_FOUND");
                    $response_array = $this->helper->custom_response(true, array(), $message);
                    return response()->json($response_array, Response::HTTP_OK);
                }
            } else {
                $message = trans("translate.USER_NOT_AUTHENTICATE");
                $response_array = $this->helper->custom_response(false, array(), $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $exception) {
            $response_array = array(
                'status' => false,
                'message' => $exception->getMessage()
            );
            return response()->json($response_array, 500);
        }
    }

    /**
     * Logout user from postal and expired JWt token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $token,
     * @return $response,
     * @throws Exception If try block get any errors.
     */
    public function logout(Request $request) {

        $validator = Validator::make($request->all(), [
                    'token' => 'required'
        ]);

        $token = $request->header("token");
        $current_user = $this->helper->getLoginUser();

        try {
            $headers = \Request::header();
            if (isset($headers['authorization'])) {
                $token_data = explode(" ", $headers['authorization'][0]);
            }

            $delete_active_interpreter = ActiveInterpreter::where('user_profile_id', $current_user->user_profile->id)->delete();

            $delete_active_push_devices = UserPushDevice::where('role_user_id', $current_user->user_profile->user_roles[0]->id)->delete();

            JWTAuth::invalidate($token_data[1]);

            $response_array = array(
                'status' => true,
                'message' => trans("translate.USER_LOGGED_OUT_SUCCESSFULLY")
            );

            return response()->json($response_array);
        } catch (JWTException $exception) {
            $response_array = array(
                'status' => false,
                'message' => $exception->getMessage()
            );
            return response()->json($response_array, 500);
        }
    }

    /**
     * perform if user forgot the passsword and want to generate new password
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $email,
     * @return $response,
     * @throws Exception If try block get any errors.
     */
    public function forget_password(Request $request) {
        try {
            Log::info('call forget_password : Params : ' . json_encode($request->all()));

            $validator = Validator::make($request->all(), [
                        'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));

                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }
            $length = 10;
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randstring = '';
            for ($i = 0; $i < $length; $i++) {
                $randstring .= $characters[rand(0, $charactersLength - 1)];
            }


            // Check if user exists or not
            $user = User::where('email', $request->email)->where('is_deleted', 0)->first();
            if (isset($user) && !empty($user)) {
                $password = Hash::make("$randstring");
                $user->password = $password;
                $user->is_forgeted = 1;
                $user->save();

                if (empty($user) || $user === null) {
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.EMAIL_NOT_EXISTS"));

                    return response()->json($response_array, Response::HTTP_OK);
                }

                // now if user exists generate forget password token
                $token = $this->helper->random_string(20);
                $token_data = array(
                    'email' => $request->email,
                    'token' => $token,
                    'user_profile_id' => $user->user_profile->id,
                    'expired_time' => strtotime('+1 day')
                );

                // add token related data in forget_passwords table
                $forget_password = Token::create($token_data);

                // send verification email to user after added user details in database
                if (isset($forget_password->id) && $forget_password->id != "") {
                    $template_replace_data = array(
                        'fullname' => $user->first_name . " " . $user->last_name,
                        'forget_password_link' => '<a href="#"  style="color: white;background-color: #173a67;padding: 10px 20px;text-decoration: none;" > ' . $randstring . '</a>',
                        'LOGO' => Config::get('settings.APP_LOGO'),
                        'project_name' => Config::get('settings.APP_NAME'),
                        'app_store_logo' => Config::get('settings.APPLE_STORE_LOGO'),
                        'play_store_logo' => Config::get('settings.PLAY_STORE_LOGO'),
                        'app_store_link' => Config::get('settings.APPLE_STORE_LINK'),
                        'play_store_link' => Config::get('settings.PLAY_STORE_LINK'),
                    );

                    // $template = new EmailTemplate();
                    $template_details = EmailTemplate::where('email_templates.template_key', "forget_password")
                                    ->leftjoin("email_template_contents", "email_template_contents.email_template_id", "=", "email_templates.id")
                                    ->select('email_templates.id', 'email_templates.template_title', 'email_template_contents.email_body', 'email_template_contents.email_subject')->first();

                    $this->helper->send_email($request->email, $template_replace_data, $template_details);
                }

                $data = array("email" => $request->email);
                $response_array = $this->helper->custom_response(true, $data, trans("translate.FORGET_PASSWORD_EMAIL_SENT_SUCCESSFULLY"));

                return response()->json($response_array, Response::HTTP_CREATED);
            } else {
                $response_array = $this->helper->custom_response(false, array(), trans("translate.USER_NOT_FOUND"));

                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) { // if found any error
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Used to update user password in database set by user
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $password,
     * @param  string $confirm_password,
     * @param  string $token,
     * @return boolean true/false,
     * @throws Exception If try block get any errors.
     */
    public function link_expiry() {

        if (isset($_GET['token'])) {
            $token = $_GET['token'];
            // Check if user exists or not
            $token_data = Token::where('token', '=', $token)->first();

            if (!empty($token_data)) {
                if ($token_data->is_used == 1) {
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.LINK_EXPIRED"));
                    return response()->json($response_array, Response::HTTP_OK);
                }

                $current_timestap = strtotime("now");
                $token_expired_time = $token_data->expired_time;
                if ($token_expired_time < $current_timestap) {
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.LINK_CANNOT_USE"));
                    return response()->json($response_array, Response::HTTP_OK);
                }

                $user = UserProfile::find($token_data->user_profile_id);
                $data = array("email" => $user->user->email);
                $response_array = $this->helper->custom_response(true, $data, trans("translate.LINK_USE"));
                return response()->json($response_array, Response::HTTP_CREATED);
            }
        }
        $response_array = $this->helper->custom_response(false, array(), trans("translate.LINK_CANNOT_USE"));
        return response()->json($response_array, Response::HTTP_OK);
    }

    /**
     * Used to update user password in database set by user
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $password,
     * @param  string $confirm_password,
     * @param  string $token,
     * @return boolean true/false,
     * @throws Exception If try block get any errors.
     */
    public function change_password(Request $request) {
        Log::info('call change_password : Params : ' . json_encode($request->all()));

        $validator = Validator::make($request->all(), [
                    'password' => 'required',
                    'confirm_password' => 'required',
        ]);

        if ($validator->fails()) {
            $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));

            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }

        $currentUser = $this->helper->getLoginUser();
        $user = User::find($currentUser->id);
        $user->password = Hash::make($request->password);
        $user->is_active = 1;
        $user->is_verified = 1;
        $user->is_forgeted = 0;
        $user->save();

        $data = array("email" => $user->email);
        $response_array = $this->helper->custom_response(true, $data, trans("translate.PASSWORD_UPDATED"));
        return response()->json($response_array, Response::HTTP_CREATED);
    }

    /**
     * Check email is exist in storage or not
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $email,
     * @return boolean true/false,
     * @throws Exception If try block get any errors.
     */
    public function checkEmailValidation(Request $request) {
        $user_data = User::where('is_deleted', 0)->where('email', $request->email)->get();

        if (count($user_data) != 0) {
            $message = trans("translate.EMAIL_FOUND");
            $response_array = $this->helper->custom_response(true, array(), $message);
            return response()->json($response_array, Response::HTTP_OK);
        } else {
            $response_array = $this->helper->custom_response(false, array(), trans(""));
            return response()->json($response_array, Response::HTTP_OK);
        }
    }

    /**
     * Get user login data by token
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $token,
     * @return string $user_data$,
     * @throws Exception If try block get any errors.
     */
    public function logged_user(Request $request) {
        $current_user = $this->helper->getLoginUser();

        // Fetch user role data
        $role = Auth::user()->getRoleByRoleUserID();
        if (empty($role)) {
            $message = trans("translate.USER_ROLE_NOT_FOUND");
            $response_array = $this->helper->custom_response(true, array(), $message);
            return response()->json($response_array, Response::HTTP_OK);
        }
        $user_profiles_data = UserProfile::getUserProfileData()->where('id', $current_user->user_profile->id)->first();
        $current_user->user_profile->company = $user_profiles_data->company;
        $current_user->user_profile->locations = $user_profiles_data->locations;
        $current_user->user_profile->languages = $user_profiles_data->languages;

        $current_user->role = $role;
        $role_id = $role->role_id;

        unset($current_user->password);
        unset($current_user->user_roles);

        if (!empty($current_user)) {
            $message = trans("translate.USER_DATA_SHOWN_SUCCESSFULLY");
            $response_array = $this->helper->custom_response(true, $current_user, $message);
            return response()->json($response_array, Response::HTTP_OK);
        } else {
            $response_array = $this->helper->custom_response(true, array(), trans("translate.EMPTY_LIST"));
            return response()->json($response_array, Response::HTTP_OK);
        }
    }

    /**
     * Get specific user data from storage.
     * @param  \Illuminate\Http\Request  $request,
     * @return $user
     * @throws Exception If try block get any errors.
     */
    public function getUserData(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                        'email' => 'required',
            ]);

            if ($validator->fails()) {
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }

            $user = User::getUserData()->where('email', $request->email)->first();

            if (!empty($user)) {
                $user->is_password_required = 1;
                if ((strpos($request->email, "amazon.com") !== FALSE || strpos($request->email, "amazon.in") !== FALSE) && $user->user_profile->user_roles[0]->role_id == Config::get('constants.options.supervisor_id')) {
                    $user->is_password_required = 0;
                }
                $user->profile_photo = !empty($user->profile_photo) ? url($user->profile_photo) : '';
                $message = trans("translate.USER_DETAIL");
                $response_array = $this->helper->custom_response(true, $user, $message);
                return response()->json($response_array, Response::HTTP_CREATED);
            } else {
                $message = trans("translate.DATA_NOT_FOUND");
                $response_array = $this->helper->custom_response(false, $user, $message);
                return response()->json($response_array, Response::HTTP_CREATED);
            }
        } catch (\Exception $ex) { // if found any error
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get login user data and its role data based on token
     *
     * @param string $token,
     * @return $currentUser,
     * @throws Exception If try block get any errors.
     */
    public function getRoleData() {
        try {

            $role_data = Role::orderBy("id", "desc")->where('is_deleted', 0)->get();
            foreach ($role_data as $role) {
                $roleObj = new Role();
                $role['permissions'] = $roleObj->getRolePermissionDetail($role->id);
            }

            if (!empty($role_data)) {
                $message = trans("translate.ROLE_LIST");
                $response_array = $this->helper->custom_response(true, $role_data, $message);
                return response()->json($response_array, Response::HTTP_CREATED);
            } else {
                $response_array = $this->helper->custom_response(false, array(), trans("translate.EMPTY_LIST"));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $ex) { // if found any error
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get login user permisssion data based on token
     *
     * @param string $token,
     * @return $roles_data_with_permission,
     * @throws Exception If try block get any errors.
     */
    public function getPermissionData() {
        try {

            $permission_data = Permission::where('is_deleted', '=', 0)->orderBy('id', 'desc')->get();
            if (!empty($permission_data)) {
                $message = trans("translate.PERMISSION_LIST");
                $response_array = $this->helper->custom_response(true, $permission_data, $message);
                return response()->json($response_array, Response::HTTP_OK);
            } else {
                $response_array = $this->helper->custom_response(false, array(), trans("translate.EMPTY_LIST"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) { // if found any error
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function update_profile(Request $request) {
        try {
            $current_user = $this->helper->getLoginUser();

            Log::info('Call register user: Params: ' . json_encode($request->all()));
            /* $validator = Validator::make($request->all(), [
              'first_name' => 'required',
              'last_name' => 'required',
              'email' => 'required',
              'phone' => 'required',
              'gender' => 'required',
              'date_of_join' => 'required',
              'date_of_birth' => 'required'
              ]);

              if ($validator->fails()) {
              $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
              return response()->json($response_array, Response::HTTP_BAD_REQUEST);
              } */

            $user = User::find($current_user->id);

            $user->email = isset($request->email) && !empty($request->email) ? $request->email : $user->email;
            $user->phone = isset($request->phone) && !empty($request->phone) ? $request->phone : $user->phone;
            $user->save();

            if ($request->hasfile('photo')) {
                $attatchment = $request->file('photo');
                $attachment_data = $this->helper->upload_attachment($attatchment, 'users');
                $profile_photo = '';
                if (!empty($attachment_data)) {
                    $profile_photo = $attachment_data['attachment_path'];
                }
            }
            $user_profile = UserProfile::find($current_user->user_profile->id);
            $user_profile->profile_photo = (isset($profile_photo) && !empty($profile_photo)) ? $profile_photo : $user_profile->profile_photo;
            $user_profile->first_name = (isset($request->first_name) && !empty($request->first_name)) ? $request->first_name : $user_profile->first_name;
            $user_profile->last_name = (isset($request->last_name) && !empty($request->last_name)) ? $request->last_name : $user_profile->last_name;
            $user_profile->gender = (isset($request->gender) && !empty($request->gender)) ? $request->gender : $user_profile->gender;
            $user_profile->date_of_join = (isset($request->date_of_join) && !empty($request->date_of_join)) ? $request->date_of_join : $user_profile->date_of_join;
            $user_profile->date_of_birth = (isset($request->date_of_birth) && !empty($request->date_of_birth)) ? $request->date_of_birth : $user_profile->date_of_birth;
            $user_profile->save();

            $user_profile_location = Location::where('user_profile_id', $current_user->user_profile->id)->first();

            $user_profile_location->city_id = (isset($request->city) && !empty($request->city)) ? $request->city : $user_profile_location->city_id;
            $user_profile_location->miles = (isset($request->miles) && !empty($request->miles)) ? $request->miles : $user_profile_location->miles;
            $user_profile_location->region = (isset($request->region) && !empty($request->region)) ? $request->region : $user_profile_location->region;
            $user_profile_location->site = (isset($request->site) && !empty($request->site)) ? $request->site : $user_profile_location->site;
            $user_profile_location->save();

            $active_interpreter_data = ActiveInterpreter::where('user_profile_id', $current_user->user_profile->id)->delete();
            $status = (isset($request->status) && !empty($request->status)) ? $request->status : 1;

            $roles = $current_user->user_profile->user_roles;

            if (isset($request->languages) && !empty($request->languages)) {
                $user_lanaguages_data = UserLanguage::where('user_profile_id', $current_user->user_profile->id)->delete();
                foreach ($request->languages AS $language) {
                    $user_lanaguages_data = [
                        'user_profile_id' => $current_user->user_profile->id,
                        'language_id' => $language,
                        'ranking' => 1,
                        'is_active' => 1,
                    ];
                    $active_interpreter = UserLanguage::create($user_lanaguages_data);
                }
            }

            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                if (in_array($role_id, $this->supplier_roles)) {
                    if (isset($current_user->user_profile->languages) && !empty($current_user->user_profile->languages)) {
                        foreach ($current_user->user_profile->languages AS $language) {
                            $active_interpreter_data = [
                                'user_profile_id' => $current_user->user_profile->id,
                                'language_id' => $language->language_id,
                                'ranking' => $language->ranking,
                                'is_active' => 1,
                                'status' => $status,
                            ];
                            $active_interpreter = ActiveInterpreter::create($active_interpreter_data);
                        }
                    }
                }
            }
            if (isset($user) && !empty($user) && isset($user_profile) && !empty($user_profile)) {
                $current_user = $this->helper->getLoginUser();
                unset($current_user->password);
                $current_user = User::find($current_user->id);
                $user_profiles_data = UserProfile::getUserProfileData()->where('id', $current_user->user_profile->id)->first();
                
                $current_user->user_profile->company = $user_profiles_data->company;
                $current_user->user_profile->locations = $user_profiles_data->locations;
                $current_user->user_profile->languages = $user_profiles_data->languages;
                $message = trans("translate.USER_PROFILE_UPDATED");
                $response_array = $this->helper->custom_response(true, $current_user, $message);
                return response()->json($response_array, Response::HTTP_OK);
            } else {
                $response_array = $this->helper->custom_response(false, array(), trans("translate.EMPTY_LIST"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) { // if found any error
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function interpreters_list(Request $request) {
        try {
            Log::info('Call interpreter lists: Params: ' . json_encode($request->all()));
            $current_user = $this->helper->getLoginUser();
            $interpreter_lists = User::getUserData()
                    ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                    ->join('role_users', 'user_profies.id', '=', 'role_users.user_profile_id')
                    ->join('roles', 'roles.id', "=", 'role_users.role_id')
                    ->join('user_languages', 'user_profies.id', "=", 'user_languages.user_profile_id')
                    ->whereIn('roles.id', $this->supplier_roles);
            if (isset($request->language_id) && !empty($request->language_id)) {
                $interpreter_lists = $interpreter_lists->where('user_languages.language_id', $request->language_id);
            }
            $interpreter_lists = $interpreter_lists->get();
            if (isset($interpreter_lists) && !$interpreter_lists->isEmpty()) {
                $message = trans("translate.INTERPRETERLISTS");
                $response_array = $this->helper->custom_response(true, $interpreter_lists, $message);
                return response()->json($response_array, Response::HTTP_OK);
            } else {
                $response_array = $this->helper->custom_response(false, array(), trans("translate.EMPTY_LIST"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) { // if found any error
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function index() {
        try {
            $currentUser = $this->helper->getLoginUser();

            $user_lists = User::getUserData()
                    ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                    ->join('role_users', 'user_profies.id', '=', 'role_users.user_profile_id')
                    ->join('roles', 'roles.id', "=", 'role_users.role_id');

            if ($currentUser->role == 'interpreter') {
                $user_lists = $user_lists->whereIn('roles.id', $this->supplier_roles);
            }
            if ($currentUser->role == 'supervisor') {
                $user_lists = $user_lists->whereIn('roles.id', $this->consumer_roles);
            }
            $user_lists = $user_lists->where('users.is_active', '=', '1');
            $user_lists = $user_lists->orderBy('users.id', 'DESC')->groupBy('users.id')->get();
            if (isset($user_lists) && !$user_lists->isEmpty()) {
                $message = trans("translate.USER_LIST");
                $response_array = $this->helper->custom_response(true, $user_lists, $message);
                return response()->json($response_array, Response::HTTP_OK);
            } else {
                $response_array = $this->helper->custom_response(false, array(), trans("translate.EMPTY_LIST"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function destroy($id) {
        try {
            $user_data = User::getUserData()->where('id', $id)->get();

            $user_lanaguages_data = UserLanguage::where('user_profile_id', $user_data[0]->user_profile->id)->delete();
            $user_profile_location = Location::where('user_profile_id', $user_data[0]->user_profile->id)->delete();
            $user_profile = UserProfile::where('id', $user_data[0]->user_profile->id)->delete();
            $user = User::find($id);
            $user->is_active = 0;
            $user->is_deleted = 1;
            $user->save();
            $response_array = $this->helper->custom_response(true, $id, trans("translate.DELETE_USER_DATA"));
            return response()->json($response_array, Response::HTTP_OK);
        } catch (\Exception $ex) { // if found any error
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function details($id) {
        try {
            $user_data = User::getUserData()->where('id', $id)->get();
            $response_array = $this->helper->custom_response(true, $user_data, trans("translate._USER_DETAILS"));
            return response()->json($response_array, Response::HTTP_OK);
        } catch (\Exception $ex) { // if found any error
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateUserData($request, $id) {
        try {
            /* $current_user = $this->helper->getLoginUser(); */

            $user_data = User::getUserData()->where('id', $id)->get();

            $user = User::find($id);

            $user->email = isset($request->email) && !empty($request->email) ? $request->email : "";
            $user->phone = isset($request->phone) && !empty($request->phone) ? $request->phone : "";
            $user->save();

            if ($request->hasfile('profile_photo')) {
                $attatchment = $request->file('profile_photo');
                $attachment_data = $this->helper->upload_attachment($attatchment, 'users');
                $profile_photo = '';
                if (!empty($attachment_data)) {
                    $profile_photo = $attachment_data['attachment_path'];
                }
            }

            $user_profile = UserProfile::find($user_data[0]->user_profile->id);
            $user_profile->profile_photo = (isset($profile_photo) && !empty($profile_photo)) ? $profile_photo : "";
            $user_profile->company_id = (isset($request->company_id) && !empty($request->company_id)) ? $request->company_id : "";
            $user_profile->first_name = (isset($request->first_name) && !empty($request->first_name)) ? $request->first_name : "";
            $user_profile->last_name = (isset($request->last_name) && !empty($request->last_name)) ? $request->last_name : "";
            $user_profile->gender = (isset($request->gender) && !empty($request->gender)) ? $request->gender : "";
            $user_profile->date_of_join = (isset($request->date_of_join) && !empty($request->date_of_join)) ? $request->date_of_join : "";
            $user_profile->date_of_birth = (isset($request->date_of_birth) && !empty($request->date_of_birth)) ? $request->date_of_birth : "";
            $user_profile->save();

            $user_profile_location = Location::where('user_profile_id', $user_data[0]->user_profile->id)->first();
            if (isset($user_profile_location) && !empty($user_profile_location)) {
                $user_profile_location->city_id = (isset($request->city) && !empty($request->city)) ? $request->city : "";
                $user_profile_location->miles = (isset($request->mile) && !empty($request->mile)) ? $request->mile : "";
                $user_profile_location->region = (isset($request->region) && !empty($request->region)) ? $request->region : "";
                $user_profile_location->site = (isset($request->site) && !empty($request->site)) ? $request->site : "";
                $user_profile_location->save();
            } else {
                $user_profile_location = [
                    'user_profile_id' => $user_data[0]->user_profile->id,
                    'city_id' => (isset($request->city) && !empty($request->city)) ? $request->city : "",
                    'miles' => (isset($request->mile) && !empty($request->mile)) ? $request->mile : "",
                    'region' => (isset($request->region) && !empty($request->region)) ? $request->region : "",
                    'site' => (isset($request->site) && !empty($request->site)) ? $request->site : ""
                ];
                $user_profile_location = Location::create($user_profile_location);
            }

            $user_lanaguages_data = UserLanguage::where('user_profile_id', $user_data[0]->user_profile->id)->delete();

            if (isset($request->languages) && !empty($request->languages)) {
                foreach ($request->languages AS $language) {
                    $user_lanaguages_data = [
                        'user_profile_id' => $user_data[0]->user_profile->id,
                        'language_id' => $language,
                        'ranking' => 1,
                        'is_active' => 1,
                    ];
                    $user_language = UserLanguage::create($user_lanaguages_data);
                }
            }
            $user['user_profile'] = $user_profile;
            $user['locations'] = $user_profile_location;
            $user['languages'] = $user_lanaguages_data;

            return $user;
        } catch (\Exception $ex) { // if found any error
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function storeUserData($request) {
        try {

            $currentUser = $this->helper->getLoginUser();

            $register_data = array(
                'email' => isset($request->email) ? $request->email : "",
                'phone' => isset($request->phone) ? $request->phone : "",
                'authorization_key' => isset($request->authorization_key) && !empty($request->authorization_key) ? $request->authorization_key : '',
                'social_type' => isset($request->social_type) && !empty($request->social_type) ? $request->social_type : '',
                'qb_authorization' => isset($request->qb_authorization) && !empty($request->qb_authorization) ? $request->qb_authorization : '',
                'qb_id' => isset($request->qb_id) && !empty($request->qb_id) ? $request->qb_id : '',
                'qb_password' => isset($request->qb_password) && !empty($request->qb_password) ? $request->qb_password : '',
                'login_type' => isset($request->login_type) && !empty($request->login_type) ? $request->login_type : '',
                'is_active' => 0,
                'is_verified' => 0,
                'is_deleted' => 0,
                'is_forgeted' => 0,
                'created_by' => $currentUser->user_profile->id,
                'updated_by' => 0
            );
            Log::info('Create User Regiater: Params: ' . json_encode($register_data));

            $user = User::create($register_data);

            if ($request->hasfile('profile_photo')) {
                $attatchment = $request->file('profile_photo');
                $attachment_data = $this->helper->upload_attachment($attatchment, 'users');
                $profile_photo = '';
                if (!empty($attachment_data)) {
                    $profile_photo = $attachment_data['attachment_path'];
                }
            }

            $user_profile_data = array(
                'user_id' => $user->id,
                'company_id' => isset($request->company_id) && !empty($request->company_id) ? $request->company_id : "",
                'first_name' => isset($request->first_name) && !empty($request->first_name) ? $request->first_name : "",
                'last_name' => isset($request->last_name) && !empty($request->last_name) ? $request->last_name : "",
                'profile_photo' => isset($request->profile_photo) && !empty($request->profile_photo) ? $request->profile_photo : "",
                'gender' => isset($request->gender) && !empty($request->gender) ? $request->gender : "",
                'date_of_join' => isset($request->date_of_join) && !empty($request->date_of_join) ? $request->date_of_join : "",
                'date_of_birth' => isset($request->date_of_birth) && !empty($request->date_of_birth) ? $request->date_of_birth : "",
            );

            $user_profile = UserProfile::create($user_profile_data);

            $user_profile_location = array(
                'user_profile_id' => $user_profile->id,
                'city_id' => isset($request->city) && !empty($request->city) ? $request->city : "",
                'mile' => isset($request->company_id) && !empty($request->company_id) ? $request->company_id : "",
                'region' => isset($request->first_name) && !empty($request->first_name) ? $request->first_name : "",
                'site' => isset($request->last_name) && !empty($request->last_name) ? $request->last_name : ""
            );

            $user_profile_location = Location::create($user_profile_location);

            if (isset($request->languages) && !empty($request->languages)) {
                foreach ($request->languages AS $language) {
                    $user_lanaguages_data = [
                        'user_profile_id' => $user_profile->id,
                        'language_id' => $language,
                        'ranking' => 1,
                        'is_active' => 1,
                    ];
                    $active_interpreter = UserLanguage::create($user_lanaguages_data);
                }
            }

            if (!empty($user) && !empty($user) && isset($user_profile) && !empty($user_profile)) {
                // Add  user role 
                $role_data = [
                    'user_profile_id' => $user_profile->id,
                    'role_id' => $request->role_id,
                ];

                Log::info('Create user role: Params:' . json_encode($role_data));
                $user_role = UserRole::create($role_data);

                if (isset($user) && !empty($user) && isset($user_role) && !empty($user_role)) {
                    //Send verification email to user to create password
                    $random_string = $this->helper->random_string(15);
                    $token_data = array(
                        'email' => $user->email,
                        'token' => $random_string,
                        'user_id' => $user->id,
                        'expired_time' => strtotime('+1 Day'),
                        'is_used' => 0
                    );
                    Log::info('Create user create password token  : Params : ' . json_encode($token_data));
                    $add_token = Token::create($token_data);

                    //Send verification email to user to create password
                    $template_replace_data = array(
                        'fullname' => $user->name,
                        'forget_password_link' => '<a style="color: white;background-color: #173a67;padding: 10px 20px;text-decoration: none;" href="' . Config::get('settings.FORGET_PASSWORD_URL') . $random_string . '"> Set Password</a>',
                        'LOGO' => Config::get('settings.APP_LOGO'),
                        'project_name' => Config::get('settings.APP_NAME'),
                        'app_store_logo' => Config::get('settings.APPLE_STORE_LOGO'),
                        'play_store_logo' => Config::get('settings.PLAY_STORE_LOGO'),
                        'app_store_link' => Config::get('settings.APPLE_STORE_LINK'),
                        'play_store_link' => Config::get('settings.PLAY_STORE_LINK'),
                    );

                    $template_details = $this->helper->getEmailTemplate('forget_password');

                    $send_data = $this->helper->send_email($request->email, $template_replace_data, $template_details);
                }

                return $user;
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(Request $request) {
        try {
            Log::info('Call register user: Params: ' . json_encode($request->all()));

            $validator = Validator::make($request->all(), [
                        'role_id' => 'required',
                        'first_name' => 'required',
                        'last_name' => 'required',
                        'email' => 'required|unique:users',
                        'phone' => 'required',
                        'gender' => 'required',
                        'profile_photo' => 'required',
                        'date_of_join' => 'required',
                        'date_of_birth' => 'required',
                        'company_id' => 'required'
            ]);

            if ($validator->fails()) {
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));

                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }


            $currentUser = $this->helper->getLoginUser();
            if (isset($currentUser->role) && $currentUser->role == 'super_admin') {
                if ($currentUser->role == 'super_admin') {
                    $result = $this->storeUserData($request);
                    if (isset($result) && !empty($result)) {
                        $message = trans("translate.USER_REGISTER_SUCCESSFULLY");
                        $response_array = $this->helper->custom_response(true, $result, $message);
                        return response()->json($response_array, Response::HTTP_OK);
                    }else{
                        $response_array = $this->helper->custom_response(true, array(), trans("translate.USER_NOT_REGISTER_SUCCESSFULLY"));
                        return response()->json($response_array, Response::HTTP_OK);        
                    }
                }
            } else {
                $response_array = $this->helper->custom_response(true, array(), trans("translate.ONLY_ADMIN_ADD_USER"));
                return response()->json($response_array, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(Request $request, $id) {

        Log::info('Call register user: Params: ' . json_encode($request->all()));

        $validator = Validator::make($request->all(), [
                    'role_id' => 'required',
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required|unique:users,email,' . $id,
                    'phone' => 'required',
                    'gender' => 'required',
                    'profile_photo' => 'required',
                    'date_of_join' => 'required',
                    'date_of_birth' => 'required',
                    'company_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));

            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }

        $currentUser = $this->helper->getLoginUser();

        if ($currentUser->role == 'super_admin') {
            $result = $this->updateUserData($request, $id);
            if (isset($result) && !empty($result)) {
                $message = trans("translate.USER_UPDATE_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $result, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }
        }else{
            $response_array = $this->helper->custom_response(true, array(), trans("translate.USER_NOT_UPDATE_SUCCESSFULLY"));
                        return response()->json($response_array, Response::HTTP_OK);
        }
    }

}
