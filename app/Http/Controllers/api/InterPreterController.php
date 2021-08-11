<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helper\Helper;
use App\Models\City;
use App\Models\Purpose;
use App\Models\Disposition;
use App\Models\Language;
use App\Models\User;
use App\Models\Call;
use JWTAuth;
use JWTFactory;
use Config;
use Log;
use DB;

class InterPreterController extends Controller {

    protected $helper;
    protected $supplier_roles;
    protected $consumer_roles;

    /**
     * UserController constructor.
     */
    public function __construct() {
        $this->helper = new Helper();
        $this->supplier_roles = array(2);
        $this->consumer_roles = array(3);
    }

    public function language_wise_interpretes_list(Request $request) {
        try {
            $languges = [];
            $current_user = $this->helper->getLoginUser();
            //$call_languages = Call::select(DB::raw("(GROUP_CONCAT(language_id ORDER BY id SEPARATOR ',')) as languages"))->where('from_user_profile_id', $current_user->user_profile->id)->orderBy('id', "DESC")->first();
            $old_languges = [];
            $call_languages = Call::select('language_id')->where('from_user_profile_id', $current_user->user_profile->id)->orderBy('id', "DESC")->first();
            if (isset($call_languages) && !empty($call_languages)) {
                $old_languges[] = $call_languages->language_id;
                $call_languages = Call::select('language_id')->where(['from_user_profile_id' => $current_user->user_profile->id, ['language_id', "!=", $call_languages->language_id]])->orderBy('id', "DESC")->first();
                if (isset($call_languages) && !empty($call_languages)) {
                    $old_languges[] = $call_languages->language_id;
                }
            }
//            $call_languages = Call::select('language_id')->where('from_user_profile_id', $current_user->user_profile->id)->first();

            $languges['use_language'] = Language::getActiveInterpreterData()->whereIn('id', $old_languges)->get();

            $languges['other_language'] = Language::getActiveInterpreterData()->whereNotIn('id', $old_languges)->get();
            if (isset($languges) && !empty($languges)) {
                $message = trans("translate.LANGUAGE_INTERPRETERS_LISTS");
                $response_array = $this->helper->custom_response(true, $languges, $message);
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

    public function interpreters_list(Request $request) {
        try {
            Log::info('Call interpreter lists: Params: ' . json_encode($request->all()));
            $current_user = $this->helper->getLoginUser();

            $interpreter_lists = User::getUserData()
                            ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                            ->join('role_users', 'user_profies.id', '=', 'role_users.user_profile_id')
                            ->join('roles', 'roles.id', "=", 'role_users.role_id')
                            ->join('user_languages', 'user_profies.id', "=", 'user_languages.user_profile_id')
                            ->join('active_interpreters', 'user_profies.id', "=", 'active_interpreters.user_profile_id')
                            ->whereIn('roles.id', $this->supplier_roles)->where(['active_interpreters.is_active' => 1, 'active_interpreters.status' => 1])->groupBy('active_interpreters.user_profile_id');
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

}
