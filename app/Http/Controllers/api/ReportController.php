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
use App\Models\CallFeedbackUser;
use App\Models\CallQualityFeedback;
use App\Models\UserLanguage;
use App\Http\Controllers\api\PushNotificationController;
use App\Models\Company;
use App\Models\CallStatus;
use JWTAuth;
use JWTFactory;
use Config;
use Log;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PDF;
use Storage;

class ReportController extends Controller {

    protected $helper;
    protected $supplier_roles;
    protected $consumer_roles;
    protected $qa_manger_roles;

    /**
     * UserController constructor.
     */
    public function __construct() {
        $this->helper = new Helper();
        $this->supplier_roles = array(2);
        $this->consumer_roles = array(3);
        $this->qa_manger_roles = array(5);
    }

    
    

    



/*====================================================================*/
    //new create 20 may updated ui changes 

    // show date(format)ata in postmen routes 
    
    public function call_report_history(Request $request) {
        try {
            $limit = isset($request->limit) && !empty($request->limit) ? $request->limit : 20;
            $from_date = isset($request->from_date) && !empty($request->from_date) ? $request->from_date : '';
            $to_date = isset($request->to_date) && !empty($request->to_date) ? $request->to_date : '';
            $search_name = isset($request->name) && !empty($request->name) ? $request->name : '';
            $search_fron_user_profile_id = isset($request->user_profile_id) && !empty($request->user_profile_id) ? $request->user_profile_id : '';
            $current_user = auth()->user();
            $current_user_profile_id = $current_user->user_profile->id;
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;
                $supplier_roles = $this->supplier_roles;

                $calls_datas = Call::getCallReportData();
                $total_failed_calls_count = Call::where('status','<', 50);
                
                    if (in_array($role_id, $this->supplier_roles)) {
                        $calls_datas->whereHas('call_details', function($query) use($current_user_profile_id,$supplier_roles) {
                            $query->where('user_profile_id', $current_user_profile_id)->whereIn('user_role_id', $supplier_roles);
                        });

                        if($search_name != ''){
                            $calls_datas->whereHas('from_user_profile', function($query) use($current_user_profile_id,$search_name) {
                                $query->where('first_name', 'LIKE', '%'.$search_name.'%'); 
                            });
                        }

                        if($search_fron_user_profile_id != ''){
                            $calls_datas = $calls_datas->where('from_user_profile_id', $search_fron_user_profile_id);
                        }
                    }

                if (in_array($role_id, $this->consumer_roles)) {
                    $calls_datas = $calls_datas->where('from_user_profile_id', $current_user_profile_id)->whereIn('from_user_role_id', $this->consumer_roles);
                }
                if($from_date != ''){
                    $calls_datas->whereHas('call_details', function($query) use($from_date) {
                        $query->whereDate('start_time' , '>=', $from_date);
                    });

                    $total_failed_calls_count = $total_failed_calls_count->whereDate('created_at' , '>=', $from_date);
                }
                if($to_date != ''){
                    $calls_datas = $calls_datas->whereHas('call_details', function($query) use($to_date) {
                        $query->whereDate('start_time' , '<=', $to_date);
                    });
                    $total_failed_calls_count = $total_failed_calls_count->whereDate('created_at' , '<=', $to_date);
                }
                if (isset($request->miles) && !empty($request->miles)) {
                    $calls_datas->whereHas('from_user_profile', function($query) use($request) {
                        $query->whereHas('locations', function($query) use($request) {
                            $query->where('miles', $request->miles);
                        });
                    });
                }
                if (isset($request->region) && !empty($request->region)) {
                    $calls_datas->whereHas('from_user_profile', function($query) use($request) {
                        $query->whereHas('locations', function($query) use($request) {
                            $query->where('region', $request->region);
                        });
                    });
                }
                if (isset($request->site) && !empty($request->site)) {
                    $calls_datas->whereHas('from_user_profile', function($query) use($request) {
                        $query->whereHas('locations', function($query) use($request) {
                            $query->where('site', $request->site);
                        });
                    });
                }

                $calls_datas = $calls_datas->get();
                $calls_datas_count = count($calls_datas);
                $total_failed_calls_count = $total_failed_calls_count->get()->count();
                $average_times = '';
                $average_language = '';
                $average_purpose = '';
                $average_call_duration_times = '';
                // available interpreter
                $total_available_interpreter = ActiveInterpreter::where('status',1)->GroupBy('user_profile_id')->get()->count();



                 // Total Average Duratoin

                /*if (in_array($role_id, $this->supplier_roles)) {
                    $average_call_duration_times = CallDetail::select(\DB::raw("DATEDIFF(end_time, start_time)AS day_diff"))->join('calls', 'calls.id', "=", 'call_details.call_id')->where('user_profile_id', $current_user_profile_id)->get()->avg('day_diff');
                    //$average_call_duration_times = CallDetail::where('user_profile_id', $current_user_profile_id)->avg('duration');
                }
                if (in_array($role_id, $this->consumer_roles)) {
                    /*$average_call_duration_times = CallDetail::select(DB::raw("AVG(duration) AS duration"))->where('user_profile_id', $current_user_profile_id)->first();
                    $average_call_duration_times = CallDetail::select(\DB::raw("DATEDIFF(end_time, start_time)AS day_diff"))->join('calls', 'calls.id', "=", 'call_details.call_id')->where('user_profile_id', $current_user_profile_id)->get()->avg('day_diff');

                }*/

                /*if (in_array($role_id, $this->consumer_roles)) {
                    $average_call_duration_times = CallDetail::where('from_user_profile_id', $current_user_profile_id)->avg('duration');
                }*/
                /*$hours = floor($average_call_duration_times / 3600);
                $mins = floor(($average_call_duration_times - $hours * 3600) / 60);
                $s = $average_call_duration_times - ($hours * 3600 + $mins * 60);
                $average_call_duration_times = $hours . ":" . $mins . ":" . floor($s);*/
                

                $total_quality_feedback = CallQualityFeedback::get()->count();
                $call_quality_rate_sum_count = CallQualityFeedback::sum('call_quality_rate');// get popular language id and count number 
                if(isset($total_quality_feedback) && !empty($total_quality_feedback)){
                $avg_call_qualit_rating = $call_quality_rate_sum_count / $total_quality_feedback;
                }else{
                    $avg_call_qualit_rating =0;
                }
                 // Total Average Times
                $average_times = CallDetail::select(DB::raw("AVG(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) AS average_times"))->whereNotNull(['start_time', 'end_time'])->join('calls', 'calls.id', "=", 'call_details.call_id');
                if (in_array($role_id, $this->supplier_roles)) {
                    $average_times = $average_times->where('call_details.user_profile_id', $current_user_profile_id);
                }
                if (in_array($role_id, $this->consumer_roles)) {
                    $average_times = $average_times->where('calls.from_user_profile_id', $current_user_profile_id);
                }

                $average_times = $average_times->first();
                $hours = floor($average_times['average_times'] / 3600);
                $mins = floor(($average_times['average_times'] - $hours * 3600) / 60);
                $s = $average_times['average_times'] - ($hours * 3600 + $mins * 60);
                $average_times1 = $hours . ":" . $mins . ":" . floor($s);
                $average_times = date('H:i:s', strtotime($average_times1));
                // Total Average Language
                /*$average_language = Call::select(DB::raw("ROUND(AVG(language_id)) AS average_lanuage_id"))
                    ->join('call_details', 'call_details.call_id', "=", 'calls.id');*/

                 $average_language = Call::select(DB::raw("COUNT(calls.language_id) AS count"),'languages.id','languages.name')->join('call_details', 'call_details.call_id', "=", 'calls.id')->join('languages', 'calls.language_id', "=", 'languages.id');

                if (in_array($role_id, $this->supplier_roles)) {
                    $average_language = $average_language->where('call_details.user_profile_id', $current_user_profile_id);
                }

                if (in_array($role_id, $this->consumer_roles)) {
                    $average_language = $average_language->where('from_user_profile_id', $current_user_profile_id);
                }
                $average_language = $average_language->GroupBy('calls.language_id')->orderBy('count','DESC')->first();
                /*$average_language = $average_language->first();
                $average_language = Language::select('id', 'name', 'is_active')->where('id', $average_language['average_lanuage_id'])->first();*/

                // Total Average Purpose
                $average_purpose = Call::select(DB::raw("COUNT(calls.purpose_id) AS count"),'purposes.id','purposes.name','purposes.description')->join('call_details', 'call_details.call_id', "=", 'calls.id')->join('purposes', 'calls.purpose_id', "=", 'purposes.id');


                /*$average_purpose = Call::select(DB::raw("ROUND(AVG(purpose_id)) AS average_purpose_id"))->join('call_details', 'call_details.call_id', "=", 'calls.id');*/
                if (in_array($role_id, $this->supplier_roles)) {
                    $average_purpose = $average_purpose->where('call_details.user_profile_id', $current_user_profile_id);
                }
                if (in_array($role_id, $this->consumer_roles)) {
                    $average_purpose = $average_purpose->where('from_user_profile_id', $current_user_profile_id);
                }
                $average_purpose = $average_purpose->GroupBy('calls.purpose_id')->orderBy('count','DESC')->first();

                //$average_purpose = Purpose::select('id', 'name', 'description')->where('id', $average_purpose['average_purpose_id'])->first();

                $total_interpreter_count = ActiveInterpreter::GroupBy('user_profile_id')->get()->count();
                if(isset($total_interpreter_count) && !empty($total_interpreter_count)){
                $avg_calls_per_interpreter = $calls_datas_count / $total_interpreter_count;
                }else{
                    $avg_calls_per_interpreter =0;
                }
                $average_detail = [
                    "total_calls" => $calls_datas_count,
                    "total_failed_calls_count" => $total_failed_calls_count,
                    "avg_call_qualit_rating" => $avg_call_qualit_rating,
                    "total_available_interpreter" => $total_available_interpreter,
                    "avg_calls_per_interpreter" => $avg_calls_per_interpreter,
                    "average_call_duration_times" => $average_times,
                    "average_times" => $average_times,
                    "average_language" => $average_language,
                    "average_purpose" => $average_purpose,
                    "average_call_waiting_times" => '00:01:00',
                    "avg_call_drops_failures" => '0.65'
                ];
                $data = array();
                if (isset($calls_datas) && !empty($calls_datas)) {
                    foreach($calls_datas AS $calls_data){
                        $calls_data->call_detail = '';
                       if(isset($calls_data->call_details[0]) && !empty($calls_data->call_details[0])){
                            $calls_data->call_detail = $calls_data->call_details[0];
                            $date_format = date("d-m-Y H:i:s", strtotime($calls_data->call_details[0]->start_time));
                            if($date_format == '01-01-1970 00:00:00'){
                                $calls_data->call_detail->start_time = '';
                            }else{

                                $newtimestamp = strtotime($calls_data->call_details[0]->start_time.' + 5 hours + 30 minute');
                                $calls_data->call_detail->start_time =  date('Y-m-d H:i:s', $newtimestamp);
                            }

                            if(isset($calls_data->call_details[0]->duration) && !empty($calls_data->call_details[0]->duration)){
                                $calls_data->call_details[0]->duration = date('H:i:s', strtotime($calls_data->call_details[0]->duration));
                            }
                            if (in_array($role_id, $this->supplier_roles)) {
                                $calls_data->call_detail->user_feedback_data = '';
                                $calls_data->call_detail->user_quality_feedback_data = '';
                                $calls_data->call_detail->user_feedback_data = CallFeedbackUser::where('call_id',$calls_data->call_detail->call_id)->where('created_by',$calls_data->call_detail->user_profile_id)->first();

                                $calls_data->call_detail->user_quality_feedback_data = CallQualityFeedback::where('call_id',$calls_data->call_detail->call_id)->where('created_by',$calls_data->call_detail->user_profile_id)->first();

                            }
                       }
                        unset($calls_data->call_details);
                        if (in_array($role_id, $this->consumer_roles)) {
                            $calls_data->user_feedback_data = CallFeedbackUser::where('call_id',$calls_data->id)->where('created_by',$calls_data->from_user_profile_id)->first();


                            $calls_data->user_quality_feedback_data = CallQualityFeedback::where('call_id',$calls_data->id)->where('created_by',$calls_data->from_user_profile_id)->first();

                        }
                    }

                    $data['average_detail'] = $average_detail;                    
                    $data['call_datas'] = $calls_datas;
                    $response_array = $this->helper->custom_response(true, $data, trans("translate.CALL_HISTORY_DATA"),true,$calls_datas_count);
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

   public function active_call_report(Request $request) {
        try {
            $data = array();
            $limit = isset($request->limit) && !empty($request->limit) ? $request->limit : 20;
            $current_user = auth()->user();
            $current_user_profile_id = $current_user->user_profile->id;
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;


                $status = (isset($request->status) && !empty($request->status)) ? $request->status : 40;



                $calls_datas = Call::getCallReportData()->where('status', $status);
                
                // Supplier Admin (Signable Interpreters) if Supplier Admin then show All Supplier Roles active call recocrds 
                $supplier_roles = $this->supplier_roles;
                if (in_array($role_id, $this->supplier_roles)) {
                    $calls_datas->whereHas('call_details', function($query) use($current_user_profile_id,$supplier_roles) {
                        $query->where('user_profile_id', $current_user_profile_id)->whereIn('user_role_id', $supplier_roles);
                    });
                }

                // Company Admin (Amazon)  => if Company Admin then show All Company Roles active call recocrds 
                if (in_array($role_id, $this->consumer_roles)) {
                    $calls_datas = $calls_datas->where('calls.from_user_profile_id', $current_user_profile_id)->whereIn('from_user_role_id', $this->consumer_roles);
                }
                $calls_datas = $calls_datas->get();

                $calls_datas_count = count($calls_datas);
                $average_times = '';
                $average_language = '';
                $average_purpose = '';
                $avg_call_wait_time = '';
                $most_active_location = '';
                // available interpreter
                $total_available_interpreter = ActiveInterpreter::where('status',1)->count();
                if(!empty($calls_datas_count && $calls_datas_count != 0)){
                    $avg_call_wait_time = '00:00:45';
                    $most_active_location = 'BLR-H';

                     // Total Average Times
                    $average_times = CallDetail::select(DB::raw("AVG(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) AS average_times"))->whereNotNull(['start_time', 'end_time'])->join('calls', 'calls.id', "=", 'call_details.call_id');
                    if (in_array($role_id, $this->supplier_roles)) {
                        $average_times = $average_times->where('call_details.user_profile_id', $current_user_profile_id);
                    }
                    if (in_array($role_id, $this->consumer_roles)) {
                        $average_times = $average_times->where('calls.from_user_profile_id', $current_user_profile_id);
                    }

                    $average_times = $average_times->first();
                    $hours = floor($average_times['average_times'] / 3600);
                    $mins = floor(($average_times['average_times'] - $hours * 3600) / 60);
                    $s = $average_times['average_times'] - ($hours * 3600 + $mins * 60);
                    $average_times = $hours . ":" . $mins . ":" . floor($s);
                    // Total Average Language
                    $average_language = Call::select(DB::raw("ROUND(AVG(language_id)) AS average_lanuage_id"))
                        ->join('call_details', 'call_details.call_id', "=", 'calls.id');

                    if (in_array($role_id, $this->supplier_roles)) {
                        $average_language = $average_language->where('call_details.user_profile_id', $current_user_profile_id);
                    }

                    if (in_array($role_id, $this->consumer_roles)) {
                        $average_language = $average_language->where('from_user_profile_id', $current_user_profile_id);
                    }
                    $average_language = $average_language->first();
                    $average_language = Language::select('id', 'name', 'is_active')->where('id', $average_language['average_lanuage_id'])->first();

                    // Total Average Purpose
                    $average_purpose = Call::select(DB::raw("ROUND(AVG(purpose_id)) AS average_purpose_id"))
                    ->join('call_details', 'call_details.call_id', "=", 'calls.id');
                    if (in_array($role_id, $this->supplier_roles)) {
                        $average_purpose = $average_purpose->where('call_details.user_profile_id', $current_user_profile_id);
                    }
                    if (in_array($role_id, $this->consumer_roles)) {
                        $average_purpose = $average_purpose->where('from_user_profile_id', $current_user_profile_id);
                    }
                    $average_purpose = $average_purpose->first();
                        $average_purpose = Purpose::select('id', 'name', 'description')->where('id', $average_purpose['average_purpose_id'])->first();



                }
                // Call by location Records get        
                $locations_records = ['miles','region','site'];
                if (in_array('miles', $locations_records)) {
                    $locations_by_mile = Call::select('locations.miles','miles.value')
                        ->join('locations', 'locations.user_profile_id', "=", 'calls.from_user_profile_id')
                        ->join('miles', 'miles.id', "=", 'locations.miles')
                        ->where('calls.status',$status)
                        ->selectRaw('count(locations.miles) as total_miles_call')
                        ->GroupBy('locations.miles');
                    if (in_array($role_id, $this->supplier_roles)) {
                        $locations_by_mile = $locations_by_mile->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    if (in_array($role_id, $this->consumer_roles)) {
                        $locations_by_mile = $locations_by_mile->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    $locations_by_mile = $locations_by_mile->get();
                }

                if (in_array('region', $locations_records)) {
                    $locations_by_region = Call::select('locations.region','regions.value')
                        ->join('locations', 'locations.user_profile_id', "=", 'calls.from_user_profile_id')
                        ->join('regions', 'regions.id', "=", 'locations.region')
                        ->where('calls.status',$status)
                        ->selectRaw('count(locations.region) as total_region_call')
                        ->GroupBy('locations.region');
                    if (in_array($role_id, $this->supplier_roles)) {
                        $locations_by_region = $locations_by_region->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    if (in_array($role_id, $this->consumer_roles)) {
                        $locations_by_region = $locations_by_region->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    $locations_by_region = $locations_by_region->get();
                }

                if (in_array('site', $locations_records)) {
                    $locations_by_site = Call::select('locations.site')
                        ->join('locations', 'locations.user_profile_id', "=", 'calls.from_user_profile_id')
                        ->where('calls.status',$status)
                        ->selectRaw('count(locations.site) as total_site_call')
                        ->GroupBy('locations.site');
                    if (in_array($role_id, $this->supplier_roles)) {
                        $locations_by_site = $locations_by_site->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    if (in_array($role_id, $this->consumer_roles)) {
                        $locations_by_site = $locations_by_site->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    $locations_by_site = $locations_by_site->get();
                }

                $average_detail = [
                    "total_calls" => $calls_datas_count,
                    "total_available_interpreter" => $total_available_interpreter,
                    "average_times" => $average_times,
                    "average_language" => $average_language,
                    "average_purpose" => $average_purpose,
                    "avg_call_wait_time" => $avg_call_wait_time,
                    "most_active_location" => $most_active_location
                ];

                //Top Languages get 
                $popular_call_lang = Call::select('language_id')->selectRaw('count(language_id) as total_lang_call')->GroupBy('language_id')->orderBy('total_lang_call','DESC')->get(); // get popular language id and count number

                foreach ($popular_call_lang as $key => $value) {
                    $popular_call_lang[$key]->language_name = Language::where('id',$value['language_id'])->pluck('name')->first(); // get popular language name
                }


                //Top Purpose get 
                $popular_call_purpose = Call::select('purpose_id')->selectRaw('count(purpose_id) as total_purpose_call')->GroupBy('purpose_id')->orderBy('total_purpose_call','DESC')->get(); // get popular purpose id and count number 
                foreach ($popular_call_purpose as $key => $value) {
                    $popular_call_purpose[$key]->purpose_name = Purpose::where('id',$value['purpose_id'])->pluck('description')->first(); // get popular Purpose name
                }
                if (isset($calls_datas) && !empty($calls_datas)) {
                    foreach($calls_datas AS $calls_data){
                         $calls_data->call_detail = '';
                       if(isset($calls_data->call_details[0]) && !empty($calls_data->call_details[0])){
                            $calls_data->call_detail = $calls_data->call_details[0];
                            $date_format = date("d-m-Y H:i:s", strtotime($calls_data->call_details[0]->start_time));
                            if($date_format == '01-01-1970 00:00:00'){
                                $calls_data->call_detail->start_time = '';
                            }else{

                                $newtimestamp = strtotime($calls_data->call_details[0]->start_time.' + 5 hours + 30 minute');
                                $calls_data->call_detail->start_time =  date('Y-m-d H:i:s', $newtimestamp);
                            }

                            if(isset($calls_data->call_details[0]->duration) && !empty($calls_data->call_details[0]->duration)){
                                $calls_data->call_details[0]->duration = date('H:i:s', strtotime($calls_data->call_details[0]->duration));
                            }
                       }
                       unset($calls_data->call_details);
                    }
                    $data['popular_top_lang'] = $popular_call_lang;                    
                    $data['popular_top_purpose'] = $popular_call_purpose;                    
                    $data['locations_wise_reports']['locations_by_mile'] = $locations_by_mile;
                    $data['locations_wise_reports']['locations_by_region'] = $locations_by_region;
                    $data['locations_wise_reports']['locations_by_site'] = $locations_by_site;    
                    $data['average_detail'] = $average_detail;                    
                    $data['call_datas'] = $calls_datas;
                    $response_array = $this->helper->custom_response(true, $data, trans("translate.ACTIVE_CALL_DATA"),true,$calls_datas_count);
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

    public function active_user_report(Request $request) {
        try {
            $current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;


                // Company Admin (Amazon) => if Company Admin then show All Company Roles active users recocrds 
                if ($role_id == 5) {  
                    $status = (isset($request->status) && !empty($request->status)) ? $request->status : 2;
                    $user_profiles_data = User::getUserData()
                            ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                            ->join('role_users', 'user_profies.id', '=', 'role_users.user_profile_id')
                            ->join('roles', 'roles.id', "=", 'role_users.role_id')
                            ->join('user_languages', 'user_profies.id', "=", 'user_languages.user_profile_id')
                            ->join('calls', 'user_profies.id', "=", 'calls.from_user_profile_id')
                            ->where('calls.status', $status)
                            ->whereIn('calls.from_user_role_id', $this->consumer_roles)
                            ->GroupBy('user_profies.id');
                    $user_profiles_data = $user_profiles_data->get()->toArray();
                }
                $user_profiles_data_count = count($user_profiles_data);
                if (isset($user_profiles_data) && !empty($user_profiles_data)) {
                    $total_available_interpreter = ActiveInterpreter::where('status',1)->count();
                     $avg_call_wait_time = '';
                    $most_active_location = '';
                    $average_detail = [
                        "total_calls" => $user_profiles_data_count,
                        "total_available_interpreter" => $total_available_interpreter,
                        "avg_call_wait_time" => $avg_call_wait_time,
                        "most_active_location" => $most_active_location
                    ];
                    $data['average_detail'] = $average_detail;
                    $data['data'] = $user_profiles_data;
                    $response_array = $this->helper->custom_response(true, $data, trans("translate.ACTIVE_USER_DATA"),true,$user_profiles_data_count);
                    return response()->json($response_array, Response::HTTP_OK);
                } else {
                    $data = [];
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

    public function frequent_user_report(Request $request) {
        try {
            $from_date = isset($request->from_date) && !empty($request->from_date) ? $request->from_date : '';
            $to_date = isset($request->to_date) && !empty($request->to_date) ? $request->to_date : '';
            $current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                $status = (isset($request->status) && !empty($request->status)) ? $request->status : 40;

                $avg_call_per_user = Call::GroupBy('from_user_profile_id')->avg('from_user_profile_id');

                $user_on_call = Call::getCallReportData()->where('status', $status);

                // Company Admin (Amazon) => if Company Admin then show All Company Roles active users recocrds 
                $user_profiles_data_count = '';

                $user_profiles_data = User::getUserData()
                        ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                        ->join('calls', 'user_profies.id', "=", 'calls.from_user_profile_id')
                        ->selectRaw('count(calls.from_user_profile_id) as total_call')
                        ->orderBy('total_call','DESC')
                        ->whereIn('calls.from_user_role_id',$this->consumer_roles);

                        /*if (isset($request->from_date) && !empty($request->from_date)) {
                            $user_profiles_data = $user_profiles_data->whereDate('start_time' , '>=', $from_date);
                        }*/
                        if($from_date != ''){
                            $user_profiles_data = $user_profiles_data->whereDate('calls.created_at', '>=', date($from_date));

                            $user_on_calluser_on_call = $user_on_call->whereDate('calls.created_at', '>=', date($from_date));
                        }
                        if($to_date != ''){
                            $user_profiles_data = $user_profiles_data->whereDate('calls.created_at', '<=', date($to_date));
                            $user_on_call = $user_on_call->whereDate('calls.created_at', '<=', date($to_date));
                        }

                        $user_profiles_data = $user_profiles_data->GroupBy('calls.from_user_profile_id')->get();
                        $user_on_call = $user_on_call->GroupBy('calls.from_user_profile_id')->get();

                        $user_profiles_data_count = count($user_profiles_data);
                        $user_on_call = count($user_on_call);
                

                if (!empty($user_profiles_data)) {
                    $total_available_interpreter = ActiveInterpreter::where('status',1)->GroupBy('user_profile_id')->get()->count();
                    
                    $avg_call_wait_time = '';
                    $most_active_location = '';
                    $average_detail = [
                        "total_users" => $user_profiles_data_count,
                        "user_on_call" => $user_on_call,
                        "avg_call_per_user" => $avg_call_per_user,
                        "total_available_interpreter" => $total_available_interpreter,
                        "avg_call_wait_time" => $avg_call_wait_time,
                        "most_active_location" => $most_active_location
                    ];

                    foreach ($user_profiles_data as $key => $row_data) {
                        $row_data->user_profile->user_role = '';
                        if(isset($row_data->user_profile->user_roles[0]) && !empty($row_data->user_profile->user_roles[0])){
                            $row_data->user_profile->user_role = $row_data->user_profile->user_roles[0];
                            unset($row_data->user_profile->user_roles);
                        }

                        //GET CALl COUNT
                        /*$user_profiles_data[$key]->calls_count = Call::where('from_user_profile_id',$row_data->user_profile->id)->get()->count();*/
                    }

                    $data['average_detail'] = $average_detail;
                    $data['data'] = $user_profiles_data;
                    $message = trans("translate.FREQUENT_USER_REPORT_DATA");
                } else {
                    $data = [];
                    $message = trans("translate.FREQUENT_USER_REPORT_DATA_NOT_FOUND");
                }
            }
            $response_array = $this->helper->custom_response(true, $data, $message,true,$user_profiles_data_count);
            return response()->json($response_array, Response::HTTP_OK);
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function supervisor_user_report(Request $request) {
        try {
            $current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;


                // Company Admin (Amazon) => if Company Admin then show All Company Roles active users recocrds 
                $user_profiles_data_count = '';
                // companies.id = 2 = amazone users 
                $user_profiles_data = User::getUserData()
                    ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                    ->join('companies', 'user_profies.company_id', "=", 'companies.id')
                    ->join('role_users', 'user_profies.id', "=", 'role_users.user_profile_id')
                    ->where('companies.id',2);
                    
                $user_profiles_data = $user_profiles_data->get();

                $user_profiles_data_count = count($user_profiles_data);
                

                if (!empty($user_profiles_data)) {
                    $total_available_interpreter = ActiveInterpreter::GroupBy('user_profile_id')->where('status',1)->count();
                    $avg_call_wait_time = '';
                    $most_active_location = '';
                    $average_detail = [
                        "total_supervisor" => $user_profiles_data_count,
                        "total_available_interpreter" => $total_available_interpreter,
                    ];

                    foreach ($user_profiles_data as $key => $row_data) {
                        $row_data->user_profile->user_role = '';
                        if(isset($row_data->user_profile->user_roles[0]) && !empty($row_data->user_profile->user_roles[0])){
                            $row_data->user_profile->user_role = $row_data->user_profile->user_roles[0];
                            unset($row_data->user_profile->user_roles);
                        }
                    }

                    $data['average_detail'] = $average_detail;
                    $data['data'] = $user_profiles_data;
                    $message = trans("translate.SUPERVISOR_USER_REPORT_DATA");
                } else {
                    $data = [];
                    $message = trans("translate.SUPERVISOR_USER_REPORT_DATA_NOT_FOUND");
                }
            }
            $response_array = $this->helper->custom_response(true, $data, $message,true,$user_profiles_data_count);
            return response()->json($response_array, Response::HTTP_OK);
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function interpreter_user_report(Request $request) {
        try {
            $current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;
            $search_name = isset($request->name) && !empty($request->name) ? $request->name : '';
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;


                // Company Admin (Amazon) => if Company Admin then show All Company Roles active users recocrds 
                $user_profiles_data_count = '';
                // companies.id = 1 = Interpreters users 
                $user_profiles_data = User::getUserData()
                    ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                    ->join('companies', 'user_profies.company_id', "=", 'companies.id')
                    ->join('role_users', 'user_profies.id', "=", 'role_users.user_profile_id')
                    ->where('companies.id',1);
                    
                    if($search_name != ''){
                        $user_profiles_data = $user_profiles_data->where('first_name', 'LIKE', '%'.$search_name.'%'); 
                    }
                $user_profiles_data = $user_profiles_data->get();

                $user_profiles_data_count = count($user_profiles_data);
                
                //All Interpreter Count 
                $total_interpreter_count = ActiveInterpreter::GroupBy('user_profile_id')->get()->count();
                //Available Interpreter Count - status - 1
                $total_available_interpreter_count = ActiveInterpreter::where('status',1)->GroupBy('user_profile_id')->get()->count();
                //Busy on onther call Interpreter Count  - status - 4
                $total_busy_interpreter_on_call_count = ActiveInterpreter::where('status',4

            )->GroupBy('user_profile_id')->get()->count();
                $average_detail = [
                    "total_interpreter_count" => $total_interpreter_count,
                    "total_available_interpreter_count" => $total_available_interpreter_count,
                    "total_busy_interpreter_on_call_count" => $total_busy_interpreter_on_call_count,
                ];
                if (!empty($user_profiles_data)) {

                    foreach ($user_profiles_data as $key => $row_data) {
                        $row_data->user_profile->user_role = '';
                        if(isset($row_data->user_profile->user_roles[0]) && !empty($row_data->user_profile->user_roles[0])){
                            $row_data->user_profile->user_role = $row_data->user_profile->user_roles[0];
                            unset($row_data->user_profile->user_roles);
                        }
                        

                        //GET Language
                        $language_id = UserLanguage::where('user_profile_id',$row_data->id)->where('ranking',1)->pluck('language_id')->first();
                        $user_profiles_data[$key]->interpreter_language = Language::where('id',$language_id)->pluck('name')->first();
                        //GET CALl COUNT
                        $user_profiles_data[$key]->calls_count = CallDetail::where('user_profile_id',$row_data->id)->GroupBy('call_id')->get()->count();


                        // Total Average Duratoin
                        //$average_call_duration_times = CallDetail::where('user_profile_id',$row_data->id)->GroupBy('call_id')->avg('duration');
                        $average_call_duration_times = CallDetail::where('user_profile_id',$row_data->id)->whereNotNull(['duration'])->avg('duration');
                        $hours = floor($average_call_duration_times / 3600);
                        $mins = floor(($average_call_duration_times - $hours * 3600) / 60);
                        $s = $average_call_duration_times - ($hours * 3600 + $mins * 60);
                        $average_call_duration_times = $hours . ":" . $mins . ":" . floor($s);
                        $user_profiles_data[$key]->average_call_duration_times = $average_call_duration_times;
                        //GET INTERPRETER STATUS 
                        
                        $status = ActiveInterpreter::where('user_profile_id',$row_data->id)->pluck('status')->first();
                        $interpreter_status = 'Not Available';
                        if($status == 1){
                            $interpreter_status = 'Available';   
                        }
                        if($status == 2){
                            $interpreter_status = 'Busy(offline)';   
                        }
                        if($status == 3){
                            $interpreter_status = 'Busy(On Break)';   
                        }
                        if($status == 4){
                            $interpreter_status = 'Busy(Another Call)';   
                        }
                        $user_profiles_data[$key]->status = $interpreter_status;

                        $row_data->user_profile->calls_count = $user_profiles_data[$key]->calls_count;
                        $row_data->user_profile->average_call_duration_times = $user_profiles_data[$key]->average_call_duration_times;
                    }

                    $data['average_detail'] = $average_detail;
                    $data['data'] = $user_profiles_data;
                    $message = trans("translate.INTERPRETER_USER_REPORT_DATA");
                } else {
                    $data = [];
                    $message = trans("translate.INTERPRETER_USER_REPORT_DATA_NOT_FOUND");
                }
            }
            $response_array = $this->helper->custom_response(true, $data, $message,true,$user_profiles_data_count);
            return response()->json($response_array, Response::HTTP_OK);
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }






    // api url show in postmen routes  

    public function call_report_history_api(Request $request) {
        try {
            $Filepath = url('/') . "/call_report_template/";
            $message = trans("translate.CALL_REPORT_DATA");
            $response_array = $this->helper->custom_response(true, $Filepath, $message);
            return response()->json($response_array, Response::HTTP_OK);
        }catch (\Exception $ex) {
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
        }catch (\Exception $ex) {
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
        }catch (\Exception $ex) {
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
        }catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }
    // show data in template routes

    public function active_call_report_history_template(Request $request) {
        try {
            $current_user = auth()->user();
            $current_user_profile_id = $current_user->user_profile->id;
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                $supplier_roles = array(2, 3, 4); // Signable Interpreters
                $consumer_roles = array(5, 6, 7); // Company Amazon


                $status = (isset($request->status) && !empty($request->status)) ? $request->status : 2;
                $calls_datas = Call::getCallReportData();
                
                // Supplier Admin (Signable Interpreters) if Supplier Admin then show All Supplier Roles active call recocrds 
                if ($role_id == 2) { 
                    $calls_datas = $calls_datas->where('status', $status);
                    $calls_datas->whereHas('call_details', function($query) use($supplier_roles) {
                        $query->whereIn('user_role_id', $supplier_roles);
                    });
                }

                // Supplier Supervisor (Signable Interpreters)  => if Supplier Supervisor then show own active call recocrds 
                if ($role_id == 3) { 
                    $calls_datas = $calls_datas->where('status', $status);
                    $calls_datas->whereHas('call_details', function($query) use($current_user_profile_id,$supplier_roles) {
                        $query->where('user_profile_id', $current_user_profile_id)->whereIn('user_role_id', $supplier_roles);
                    });
                }

                // Company Admin (Amazon)  => if Company Admin then show All Company Roles active call recocrds 
                if ($role_id == 5) { 
                    $calls_datas = $calls_datas->where('status', $status)->whereIn('from_user_role_id', $consumer_roles);
                }

                // Company Supervisor (Amazon)  => if Company Supervisor then show own active call recocrds 
                if ($role_id == 6) { 
                    $calls_datas = $calls_datas->where('status', $status)->where('from_user_profile_id', $current_user_profile_id)->whereIn('from_user_role_id', $consumer_roles);
                }

                $calls_datas = $calls_datas->get();
                $calls_datas_count = count($calls_datas);
                $avg_call_wait_time = '';
                $most_active_location = '';
                // available interpreter
                $total_available_interpreter = ActiveInterpreter::where('status',1)->count();
                $average_detail = [
                    "total_calls" => $calls_datas_count,
                    "total_available_interpreter" => $total_available_interpreter,
                    "avg_call_wait_time" => $avg_call_wait_time,
                    "most_active_location" => $most_active_location
                ];
                $data = array();
                if (isset($calls_datas) && !empty($calls_datas)) {
                    foreach($calls_datas AS $calls_data){
                       
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

    public function call_report_history_template(Request $request) {
        try {
            $current_user = auth()->user();
            $current_user_profile_id = $current_user->user_profile->id;
            $roles = $current_user->user_profile->user_roles;
            if ($roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                $supplier_roles = array(2, 3, 4); // Signable Interpreters
                $consumer_roles = array(5, 6, 7); // Company Amazon


                $status = (isset($request->status) && !empty($request->status)) ? $request->status : 2;
                $calls_datas = Call::getCallReportData();
                
                // Supplier Admin (Signable Interpreters) if Supplier Admin then show All Supplier Roles active call recocrds 
                if ($role_id == 2) { 
                    $calls_datas->whereHas('call_details', function($query) use($supplier_roles) {
                        $query->whereIn('user_role_id', $supplier_roles);
                    });
                }

                // Supplier Supervisor (Signable Interpreters)  => if Supplier Supervisor then show own active call recocrds 
                if ($role_id == 3) { 
                    $calls_datas->whereHas('call_details', function($query) use($current_user_profile_id,$supplier_roles) {
                        $query->where('user_profile_id', $current_user_profile_id)->whereIn('user_role_id', $supplier_roles);
                    });
                }

                // Company Admin (Amazon)  => if Company Admin then show All Company Roles active call recocrds 
                if ($role_id == 5) { 
                    $calls_datas = $calls_datas->whereIn('from_user_role_id', $consumer_roles);
                }

                // Company Supervisor (Amazon)  => if Company Supervisor then show own active call recocrds 
                if ($role_id == 6) { 
                    $calls_datas = $calls_datas->where('from_user_profile_id', $current_user_profile_id)->whereIn('from_user_role_id', $consumer_roles);
                }

                $calls_datas = $calls_datas->get();
                $calls_datas_count = count($calls_datas);
                $avg_call_wait_time = '';
                $most_active_location = '';
                // available interpreter
                $total_available_interpreter = ActiveInterpreter::where('status',1)->count();
                $average_detail = [
                    "total_calls" => $calls_datas_count,
                    "total_available_interpreter" => $total_available_interpreter,
                    "avg_call_wait_time" => $avg_call_wait_time,
                    "most_active_location" => $most_active_location
                ];
                $data = array();
                if (isset($calls_datas) && !empty($calls_datas)) {
                    foreach($calls_datas AS $calls_data){
                       
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

    public function active_user_report_history_template(Request $request) {
        try {
            $current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;
            if ($roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                $consumer_roles = array(5, 6, 7); // Company Amazon


                // Company Admin (Amazon) => if Company Admin then show All Company Roles active users recocrds 
                if ($role_id == 5) {  
                    $status = (isset($request->status) && !empty($request->status)) ? $request->status : 2;
                    $user_profiles_data = User::getUserData()
                            ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                            ->join('role_users', 'user_profies.id', '=', 'role_users.user_profile_id')
                            ->join('roles', 'roles.id', "=", 'role_users.role_id')
                            ->join('user_languages', 'user_profies.id', "=", 'user_languages.user_profile_id')
                            ->join('calls', 'user_profies.id', "=", 'calls.from_user_profile_id')
                            ->where('calls.status', $status)
                            ->whereIn('calls.from_user_role_id', [5, 6, 7])
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

    public function frequent_user_report_history_template(Request $request) {
        try {
            /*$current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;*/
            $roles = 'tets';
            //if (!$roles->isEmpty()) {
            if ($roles != '') {
                /*$current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;*/
                $consumer_roles = array(5, 6, 7); // Company Amazon


                // Company Admin (Amazon) => if Company Admin then show All Company Roles active users recocrds 
                $role_id = 5;
                if ($role_id == 5) {

                    $user_profiles_data = User::getUserData()
                            ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                            ->join('calls', 'user_profies.id', "=", 'calls.from_user_profile_id')
                            ->selectRaw('count(calls.from_user_profile_id) as total_call')
                            ->orderBy('total_call','DESC')
                            ->whereIn('calls.from_user_role_id',$consumer_roles)
                            ->GroupBy('calls.from_user_profile_id');

                            $user_profiles_data = $user_profiles_data->get()->toArray();

                            $user_profiles_data_count = count($user_profiles_data);
                }
                if (!empty($user_profiles_data)) {
                    return response()->view('template.frequent_user_report_template', compact('user_profiles_data'));
                } else {
                    $user_profiles_data = [];
                    return response()->view('template.frequent_user_report_template', compact('user_profiles_data'));
                }
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }
    // end new create 20 may updated ui changes 



    public function interpreter_report(Request $request) {
        try {
            $current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                $supplier_roles = $this->supplier_roles;
                $consumer_roles = $this->consumer_roles;


                
                    //$status = (isset($request->status) && !empty($request->status)) ? $request->status : 4;
                    $user_profiles_data = User::getUserData()
                            ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                            ->join('role_users', 'user_profies.id', '=', 'role_users.user_profile_id')
                            ->join('roles', 'roles.id', "=", 'role_users.role_id')
                            ->join('user_languages', 'user_profies.id', "=", 'user_languages.user_profile_id')
                            ->join('call_details', 'user_profies.id', "=", 'call_details.user_profile_id')
                            ->GroupBy('call_details.user_profile_id');

                    $user_profiles_data = $user_profiles_data->get();
                    $user_profiles_data_count = count($user_profiles_data);

                    //All Interpreter Count
                    $total_interpreter_count = ActiveInterpreter::GroupBy('user_profile_id')->count();
                    //Available Interpreter Count - status - 1
                    $total_available_interpreter_count = ActiveInterpreter::where('status',1)->GroupBy('user_profile_id')->count();
                    //Busy on onther call Interpreter Count  - status - 3
                    $total_busy_interpreter_on_call_count = ActiveInterpreter::where('status',3)->GroupBy('user_profile_id')->count();
                    $interpreter_detail = [
                        "total_interpreter_count" => $total_interpreter_count,
                        "total_available_interpreter_count" => $total_available_interpreter_count,
                        "total_busy_interpreter_on_call_count" => $total_busy_interpreter_on_call_count,
                    ];

                if (isset($user_profiles_data) && !empty($user_profiles_data)) {
                    foreach ($user_profiles_data as $key => $row_data) {
                        $user_profiles_data[$key]->calls_count = CallDetail::where('user_profile_id',$row_data->id)->GroupBy('user_profile_id')->count();
                    }
                    $data['interpreter_detail'] = $interpreter_detail;
                    $data['data'] = $user_profiles_data;
                    $response_array = $this->helper->custom_response(true, $data, trans("translate.INTERPRETER_USER_DATA"),true,$user_profiles_data_count);
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


    //20-07-2021 export apis 

    // EXPPORT PDF REPORT APIS

    //call history report export 
    public function call_report_history_export(Request $request) {
        try {
            $current_user = auth()->user();
            $is_email = isset($request->is_email) && !empty($request->is_email) ? $request->is_email : '';
            $limit = isset($request->limit) && !empty($request->limit) ? $request->limit : 20;
            $from_date = isset($request->from_date) && !empty($request->from_date) ? $request->from_date : '';
            $to_date = isset($request->to_date) && !empty($request->to_date) ? $request->to_date : '';
            $search_name = isset($request->name) && !empty($request->name) ? $request->name : '';
            $search_fron_user_profile_id = isset($request->user_profile_id) && !empty($request->user_profile_id) ? $request->user_profile_id : '';
            $current_user = auth()->user();
            $current_user_profile_id = $current_user->user_profile->id;
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;
                $supplier_roles = $this->supplier_roles;

                $calls_datas = Call::getCallReportData();
                $total_failed_calls_count = Call::where('status','<', 50);
                
                    if (in_array($role_id, $this->supplier_roles)) {
                        $calls_datas->whereHas('call_details', function($query) use($current_user_profile_id,$supplier_roles) {
                            $query->where('user_profile_id', $current_user_profile_id)->whereIn('user_role_id', $supplier_roles);
                        });

                        if($search_name != ''){
                            $calls_datas->whereHas('from_user_profile', function($query) use($current_user_profile_id,$search_name) {
                                $query->where('first_name', 'LIKE', '%'.$search_name.'%'); 
                            });
                        }

                        if($search_fron_user_profile_id != ''){
                            $calls_datas = $calls_datas->where('from_user_profile_id', $search_fron_user_profile_id);
                        }
                    }

                if (in_array($role_id, $this->consumer_roles)) {
                    $calls_datas = $calls_datas->where('from_user_profile_id', $current_user_profile_id)->whereIn('from_user_role_id', $this->consumer_roles);
                }
                if($from_date != ''){
                    $calls_datas->whereHas('call_details', function($query) use($from_date) {
                        $query->whereDate('start_time' , '>=', $from_date);
                    });

                    $total_failed_calls_count = $total_failed_calls_count->whereDate('created_at' , '>=', $from_date);
                }
                if($to_date != ''){
                    $calls_datas = $calls_datas->whereHas('call_details', function($query) use($to_date) {
                        $query->whereDate('start_time' , '<=', $to_date);
                    });
                    $total_failed_calls_count = $total_failed_calls_count->whereDate('created_at' , '<=', $to_date);
                }
                if (isset($request->miles) && !empty($request->miles)) {
                    $calls_datas->whereHas('from_user_profile', function($query) use($request) {
                        $query->whereHas('locations', function($query) use($request) {
                            $query->where('miles', $request->miles);
                        });
                    });
                }
                if (isset($request->region) && !empty($request->region)) {
                    $calls_datas->whereHas('from_user_profile', function($query) use($request) {
                        $query->whereHas('locations', function($query) use($request) {
                            $query->where('region', $request->region);
                        });
                    });
                }
                if (isset($request->site) && !empty($request->site)) {
                    $calls_datas->whereHas('from_user_profile', function($query) use($request) {
                        $query->whereHas('locations', function($query) use($request) {
                            $query->where('site', $request->site);
                        });
                    });
                }

                $calls_datas = $calls_datas->get();
                $calls_datas_count = count($calls_datas);
                $total_failed_calls_count = $total_failed_calls_count->get()->count();
                $average_times = '';
                $average_language = '';
                $average_purpose = '';
                $average_call_duration_times = '';
                // available interpreter
                $total_available_interpreter = ActiveInterpreter::where('status',1)->GroupBy('user_profile_id')->get()->count();
                

                $total_quality_feedback = CallQualityFeedback::get()->count();
                $call_quality_rate_sum_count = CallQualityFeedback::sum('call_quality_rate');// get popular language id and count number 
                if(isset($total_quality_feedback) && !empty($total_quality_feedback)){
                $avg_call_qualit_rating = $call_quality_rate_sum_count / $total_quality_feedback;
                }else{
                    $avg_call_qualit_rating =0;
                }
                 // Total Average Times
                $average_times = CallDetail::select(DB::raw("AVG(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) AS average_times"))->whereNotNull(['start_time', 'end_time'])->join('calls', 'calls.id', "=", 'call_details.call_id');
                if (in_array($role_id, $this->supplier_roles)) {
                    $average_times = $average_times->where('call_details.user_profile_id', $current_user_profile_id);
                }
                if (in_array($role_id, $this->consumer_roles)) {
                    $average_times = $average_times->where('calls.from_user_profile_id', $current_user_profile_id);
                }

                $average_times = $average_times->first();
                $hours = floor($average_times['average_times'] / 3600);
                $mins = floor(($average_times['average_times'] - $hours * 3600) / 60);
                $s = $average_times['average_times'] - ($hours * 3600 + $mins * 60);
                $average_times1 = $hours . ":" . $mins . ":" . floor($s);
                $average_times = date('H:i:s', strtotime($average_times1));
                // Total Average Language
                /*$average_language = Call::select(DB::raw("ROUND(AVG(language_id)) AS average_lanuage_id"))
                    ->join('call_details', 'call_details.call_id', "=", 'calls.id');*/

                 $average_language = Call::select(DB::raw("COUNT(calls.language_id) AS count"),'languages.id','languages.name')->join('call_details', 'call_details.call_id', "=", 'calls.id')->join('languages', 'calls.language_id', "=", 'languages.id');

                if (in_array($role_id, $this->supplier_roles)) {
                    $average_language = $average_language->where('call_details.user_profile_id', $current_user_profile_id);
                }

                if (in_array($role_id, $this->consumer_roles)) {
                    $average_language = $average_language->where('from_user_profile_id', $current_user_profile_id);
                }
                $average_language = $average_language->GroupBy('calls.language_id')->orderBy('count','DESC')->first();
                /*$average_language = $average_language->first();
                $average_language = Language::select('id', 'name', 'is_active')->where('id', $average_language['average_lanuage_id'])->first();*/

                // Total Average Purpose
                $average_purpose = Call::select(DB::raw("COUNT(calls.purpose_id) AS count"),'purposes.id','purposes.name','purposes.description')->join('call_details', 'call_details.call_id', "=", 'calls.id')->join('purposes', 'calls.purpose_id', "=", 'purposes.id');


                /*$average_purpose = Call::select(DB::raw("ROUND(AVG(purpose_id)) AS average_purpose_id"))->join('call_details', 'call_details.call_id', "=", 'calls.id');*/
                if (in_array($role_id, $this->supplier_roles)) {
                    $average_purpose = $average_purpose->where('call_details.user_profile_id', $current_user_profile_id);
                }
                if (in_array($role_id, $this->consumer_roles)) {
                    $average_purpose = $average_purpose->where('from_user_profile_id', $current_user_profile_id);
                }
                $average_purpose = $average_purpose->GroupBy('calls.purpose_id')->orderBy('count','DESC')->first();

                //$average_purpose = Purpose::select('id', 'name', 'description')->where('id', $average_purpose['average_purpose_id'])->first();

                $total_interpreter_count = ActiveInterpreter::GroupBy('user_profile_id')->get()->count();
                if(isset($total_interpreter_count) && !empty($total_interpreter_count)){
                $avg_calls_per_interpreter = $calls_datas_count / $total_interpreter_count;
                }else{
                    $avg_calls_per_interpreter =0;
                }
                $average_detail = [
                    "total_calls" => $calls_datas_count,
                    "total_failed_calls_count" => $total_failed_calls_count,
                    "avg_call_qualit_rating" => $avg_call_qualit_rating,
                    "total_available_interpreter" => $total_available_interpreter,
                    "avg_calls_per_interpreter" => $avg_calls_per_interpreter,
                    "average_call_duration_times" => $average_times,
                    "average_times" => $average_times,
                    "average_language" => $average_language,
                    "average_purpose" => $average_purpose
                ];
                $data = array();
                if (isset($calls_datas) && !empty($calls_datas)) {
                    foreach($calls_datas AS $calls_data){
                        $calls_data->call_detail = '';
                       if(isset($calls_data->call_details[0]) && !empty($calls_data->call_details[0])){
                            $calls_data->call_detail = $calls_data->call_details[0];
                            $date_format = date("d-m-Y H:i:s", strtotime($calls_data->call_details[0]->start_time));
                            if($date_format == '01-01-1970 00:00:00'){
                                $calls_data->call_detail->start_time = '';
                            }else{

                                $newtimestamp = strtotime($calls_data->call_details[0]->start_time.' + 5 hours + 30 minute');
                                $calls_data->call_detail->start_time =  date('d-m-Y H:i:s', $newtimestamp);
                            }

                            if(isset($calls_data->call_details[0]->duration) && !empty($calls_data->call_details[0]->duration)){
                                $calls_data->call_details[0]->duration = date('H:i:s', strtotime($calls_data->call_details[0]->duration));
                            }
                            if (in_array($role_id, $this->supplier_roles)) {
                                $calls_data->call_detail->user_feedback_data = '';
                                $calls_data->call_detail->user_quality_feedback_data = '';
                                $calls_data->call_detail->user_feedback_data = CallFeedbackUser::where('call_id',$calls_data->call_detail->call_id)->where('created_by',$calls_data->call_detail->user_profile_id)->first();

                                $calls_data->call_detail->user_quality_feedback_data = CallQualityFeedback::where('call_id',$calls_data->call_detail->call_id)->where('created_by',$calls_data->call_detail->user_profile_id)->first();

                            }
                       }
                        unset($calls_data->call_details);
                        if (in_array($role_id, $this->consumer_roles)) {
                            $calls_data->user_feedback_data = CallFeedbackUser::where('call_id',$calls_data->id)->where('created_by',$calls_data->from_user_profile_id)->first();


                            $calls_data->user_quality_feedback_data = CallQualityFeedback::where('call_id',$calls_data->id)->where('created_by',$calls_data->from_user_profile_id)->first();

                        }
                    }

                    $data['average_detail'] = $average_detail;                    
                    $data['call_datas'] = $calls_datas;


                    $extension = $request->export_type;
                    if (!empty($extension)) {
                        $extension = $extension;
                    } else {
                        $extension = 'pdf';
                    }

                    $Filepath = '';

                    if($extension == 'pdf'){
                        $folderName = 'call_report_export_pdf';
                        $fileName = 'call_history_report_' . time(). '.pdf';
                        $templateName = 'call_report_pdf_template';

                        $ressult = ReportController::getPdfExport($folderName,$fileName,$templateName,$data);

                    }else{
                        $folderPath = public_path('call_report_export_csv');
                        $folderName = 'call_report_export_csv';
                        $fileName = 'call_history_report_' . time(). '.csv';
                        $title = 'Call Report Excel Sheet';

                        $ressult = ReportController::getCallHistoryReportCsvExport($title,$folderName,$fileName,$data);
                    }

                    if(!empty($is_email) && $is_email == 1){
                        $subject = 'Call History Report File Download';
                        $attachment[] = $ressult;
                        $result_data = ReportController::send_download_report_mail($current_user->email,$current_user->user_profile->first_name,$current_user->user_profile->last_name,$ressult,$subject,$attachment);

                        if($result_data == 1){
                            $message = trans("translate.ACTIVE_CALL_HISTORY_DATA_FILE_SENT_VIA_EMAIL");
                        }else{
                            $message = trans("translate.ACTIVE_CALL_HISTORY_DATA_FILE_NOT_SENT_VIA_EMAIL");
                        }
                        $response_array = $this->helper->custom_response(true, array(), $message);
                    }else{
                        $response_array = $this->helper->custom_response(true, $ressult, trans("translate.CALL_HISTORY_DATA_EXPORT"));
                    }
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
    
    // call history report csv file generate
    public function getCallHistoryReportCsvExport($title,$folderName,$fileName,$data){
        $folderPath = public_path($folderName);
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
        $Filepath = '';
        if (isset($data['call_datas']) && !empty($data['call_datas'])) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', '#');
            $sheet->setCellValue('B1', 'Interpreter');
            $sheet->setCellValue('C1', 'Language');
            $sheet->setCellValue('D1', 'Purpose');
            $sheet->setCellValue('E1', 'Duration');
            $sheet->setCellValue('F1', 'Date');
            $sheet->setCellValue('G1', 'Feedback');

            $rowCount = 2;
            $cnt = 1;

            foreach ($data['call_datas'] as $key => $row_data) {
                if (isset($row_data['call_details']) && !empty($row_data['call_details'])) {
                    $start_time = (isset($row_data['call_detail']['start_time'])?$row_data['call_detail']['start_time']:'');
                    $end_time = (isset($row_data['call_detail']['end_time'])?$row_data['call_detail']['end_time']:'');
                    $duration = (isset($row_data['call_detail']['duration'])?$row_data['call_detail']['duration']:'');
                    $first_name = (isset($row_data['call_detail']['user_profile']['first_name'])?$row_data['call_detail']['user_profile']['first_name']:'');
                    $last_name = (isset($row_data['call_detail']['user_profile']['last_name'])?$row_data['call_detail']['user_profile']['last_name']:'');
                    
                    $interpreter_name = $first_name." ".$last_name;
                    if(empty($interpreter_name)){
                      $interpreter_name = 'No Interpreter Found';  
                    }
                } 
                $language_name = $purpose_name = '';
                if(isset($row_data['language'])) {
                  $language_name = $row_data['language']['name'];
                }

                if(isset($row_data['purpose'])) {
                  $purpose_name = $row_data['purpose']['description'];
                }
                if (isset($row_data['user_feedback_data']) && !empty($row_data['user_feedback_data'])) {   
                  $feedback = $row_data['user_feedback_data']['to_user_rating'];
                }else{
                  $feedback = 'pending';
                }

                $sheet->setCellValue('A' . $rowCount, $cnt);
                $sheet->setCellValue('B' . $rowCount, $interpreter_name);
                $sheet->setCellValue('C' . $rowCount, $language_name);
                $sheet->setCellValue('D' . $rowCount, $purpose_name);
                $sheet->setCellValue('E' . $rowCount, date('H:i:s',strtotime($duration)));
                $sheet->setCellValue('F' . $rowCount, date('d-m-Y H:i:s',strtotime($start_time)));
                $sheet->setCellValue('G' . $rowCount, $feedback);

                $rowCount++;
                $cnt++;
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setPreCalculateFormulas(false);

            $writer->save($folderPath . "/" . $fileName);

            header("Content-Type: application/vnd.ms-excel");

            $Filepath = url('/') . "/".$folderName."/" . $fileName;

        }
        return $Filepath;
    }

    //active call history report export
   public function active_call_report_history_export(Request $request) {
        try {
            $data = array();
            $limit = isset($request->limit) && !empty($request->limit) ? $request->limit : 20;
            $is_email = isset($request->is_email) && !empty($request->is_email) ? $request->is_email : '';
            $current_user = auth()->user();
            $current_user_profile_id = $current_user->user_profile->id;
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;


                $status = (isset($request->status) && !empty($request->status)) ? $request->status : 40;



                $calls_datas = Call::getCallReportData()->where('status', $status);
                
                // Supplier Admin (Signable Interpreters) if Supplier Admin then show All Supplier Roles active call recocrds 
                $supplier_roles = $this->supplier_roles;
                if (in_array($role_id, $this->supplier_roles)) {
                    $calls_datas->whereHas('call_details', function($query) use($current_user_profile_id,$supplier_roles) {
                        $query->where('user_profile_id', $current_user_profile_id)->whereIn('user_role_id', $supplier_roles);
                    });
                }

                // Company Admin (Amazon)  => if Company Admin then show All Company Roles active call recocrds 
                if (in_array($role_id, $this->consumer_roles)) {
                    $calls_datas = $calls_datas->where('calls.from_user_profile_id', $current_user_profile_id)->whereIn('from_user_role_id', $this->consumer_roles);
                }
                $calls_datas = $calls_datas->get();

                $calls_datas_count = count($calls_datas);
                $average_times = '';
                $average_language = '';
                $average_purpose = '';
                $avg_call_wait_time = '';
                $most_active_location = '';
                // available interpreter
                $total_available_interpreter = ActiveInterpreter::where('status',1)->count();
                if(!empty($calls_datas_count && $calls_datas_count != 0)){
                    $avg_call_wait_time = '00:00:45';
                    $most_active_location = 'BLR-H';

                     // Total Average Times
                    $average_times = CallDetail::select(DB::raw("AVG(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) AS average_times"))->whereNotNull(['start_time', 'end_time'])->join('calls', 'calls.id', "=", 'call_details.call_id');
                    if (in_array($role_id, $this->supplier_roles)) {
                        $average_times = $average_times->where('call_details.user_profile_id', $current_user_profile_id);
                    }
                    if (in_array($role_id, $this->consumer_roles)) {
                        $average_times = $average_times->where('calls.from_user_profile_id', $current_user_profile_id);
                    }

                    $average_times = $average_times->first();
                    $hours = floor($average_times['average_times'] / 3600);
                    $mins = floor(($average_times['average_times'] - $hours * 3600) / 60);
                    $s = $average_times['average_times'] - ($hours * 3600 + $mins * 60);
                    $average_times = $hours . ":" . $mins . ":" . floor($s);
                    // Total Average Language
                    $average_language = Call::select(DB::raw("ROUND(AVG(language_id)) AS average_lanuage_id"))
                        ->join('call_details', 'call_details.call_id', "=", 'calls.id');

                    if (in_array($role_id, $this->supplier_roles)) {
                        $average_language = $average_language->where('call_details.user_profile_id', $current_user_profile_id);
                    }

                    if (in_array($role_id, $this->consumer_roles)) {
                        $average_language = $average_language->where('from_user_profile_id', $current_user_profile_id);
                    }
                    $average_language = $average_language->first();
                    $average_language = Language::select('id', 'name', 'is_active')->where('id', $average_language['average_lanuage_id'])->first();

                    // Total Average Purpose
                    $average_purpose = Call::select(DB::raw("ROUND(AVG(purpose_id)) AS average_purpose_id"))
                    ->join('call_details', 'call_details.call_id', "=", 'calls.id');
                    if (in_array($role_id, $this->supplier_roles)) {
                        $average_purpose = $average_purpose->where('call_details.user_profile_id', $current_user_profile_id);
                    }
                    if (in_array($role_id, $this->consumer_roles)) {
                        $average_purpose = $average_purpose->where('from_user_profile_id', $current_user_profile_id);
                    }
                    $average_purpose = $average_purpose->first();
                        $average_purpose = Purpose::select('id', 'name', 'description')->where('id', $average_purpose['average_purpose_id'])->first();



                }
                // Call by location Records get        
                $locations_records = ['miles','region','site'];
                if (in_array('miles', $locations_records)) {
                    $locations_by_mile = Call::select('locations.miles','miles.value')
                        ->join('locations', 'locations.user_profile_id', "=", 'calls.from_user_profile_id')
                        ->join('miles', 'miles.id', "=", 'locations.miles')
                        ->where('calls.status',$status)
                        ->selectRaw('count(locations.miles) as total_miles_call')
                        ->GroupBy('locations.miles');
                    if (in_array($role_id, $this->supplier_roles)) {
                        $locations_by_mile = $locations_by_mile->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    if (in_array($role_id, $this->consumer_roles)) {
                        $locations_by_mile = $locations_by_mile->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    $locations_by_mile = $locations_by_mile->get();
                }

                if (in_array('region', $locations_records)) {
                    $locations_by_region = Call::select('locations.region','regions.value')
                        ->join('locations', 'locations.user_profile_id', "=", 'calls.from_user_profile_id')
                        ->join('regions', 'regions.id', "=", 'locations.region')
                        ->where('calls.status',$status)
                        ->selectRaw('count(locations.region) as total_region_call')
                        ->GroupBy('locations.region');
                    if (in_array($role_id, $this->supplier_roles)) {
                        $locations_by_region = $locations_by_region->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    if (in_array($role_id, $this->consumer_roles)) {
                        $locations_by_region = $locations_by_region->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    $locations_by_region = $locations_by_region->get();
                }

                if (in_array('site', $locations_records)) {
                    $locations_by_site = Call::select('locations.site')
                        ->join('locations', 'locations.user_profile_id', "=", 'calls.from_user_profile_id')
                        ->where('calls.status',$status)
                        ->selectRaw('count(locations.site) as total_site_call')
                        ->GroupBy('locations.site');
                    if (in_array($role_id, $this->supplier_roles)) {
                        $locations_by_site = $locations_by_site->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    if (in_array($role_id, $this->consumer_roles)) {
                        $locations_by_site = $locations_by_site->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    $locations_by_site = $locations_by_site->get();
                }

                $average_detail = [
                    "total_calls" => $calls_datas_count,
                    "total_available_interpreter" => $total_available_interpreter,
                    "average_times" => $average_times,
                    "average_language" => $average_language,
                    "average_purpose" => $average_purpose,
                    "avg_call_wait_time" => $avg_call_wait_time,
                    "most_active_location" => $most_active_location
                ];

                //Top Languages get 
                $popular_call_lang = Call::select('language_id')->selectRaw('count(language_id) as total_lang_call')->GroupBy('language_id')->orderBy('total_lang_call','DESC')->get(); // get popular language id and count number

                foreach ($popular_call_lang as $key => $value) {
                    $popular_call_lang[$key]->language_name = Language::where('id',$value['language_id'])->pluck('name')->first(); // get popular language name
                }


                //Top Purpose get 
                $popular_call_purpose = Call::select('purpose_id')->selectRaw('count(purpose_id) as total_purpose_call')->GroupBy('purpose_id')->orderBy('total_purpose_call','DESC')->get(); // get popular purpose id and count number 
                foreach ($popular_call_purpose as $key => $value) {
                    $popular_call_purpose[$key]->purpose_name = Purpose::where('id',$value['purpose_id'])->pluck('description')->first(); // get popular Purpose name
                }
                if (isset($calls_datas) && !empty($calls_datas)) {
                    foreach($calls_datas AS $calls_data){
                        if(isset($calls_data->call_details[0]) && !empty($calls_data->call_details[0])){
                            $calls_data->call_detail = $calls_data->call_details[0];
                            $date_format = date("d-m-Y H:i:s", strtotime($calls_data->call_details[0]->start_time));
                            if($date_format == '01-01-1970 00:00:00'){
                                $calls_data->call_detail->start_time = '';
                            }else{

                                $newtimestamp = strtotime($calls_data->call_details[0]->start_time.' + 5 hours + 30 minute');
                                $calls_data->call_detail->start_time =  date('d-m-Y H:i:s', $newtimestamp);
                            }

                            if(isset($calls_data->call_details[0]->duration) && !empty($calls_data->call_details[0]->duration)){
                                $calls_data->call_details[0]->duration = date('H:i:s', strtotime($calls_data->call_details[0]->duration));
                            }
                            unset($calls_data->call_details);
                        }
                    }
                    $data['popular_top_lang'] = $popular_call_lang;                    
                    $data['popular_top_purpose'] = $popular_call_purpose;                    
                    $data['locations_wise_reports']['locations_by_mile'] = $locations_by_mile;
                    $data['locations_wise_reports']['locations_by_region'] = $locations_by_region;
                    $data['locations_wise_reports']['locations_by_site'] = $locations_by_site;    
                    $data['average_detail'] = $average_detail;                    
                    $data['call_datas'] = $calls_datas;


                    $extension = $request->export_type;
                    if (!empty($extension)) {
                        $extension = $extension;
                    } else {
                        $extension = 'pdf';
                    }

                    $Filepath = '';

                    if($extension == 'pdf'){
                        $folderName = 'active_call_report_export_pdf';
                        $fileName = 'active_call_history_report_' . time(). '.pdf';
                        $templateName = 'active_call_report_pdf_template';

                        $ressult = ReportController::getPdfExport($folderName,$fileName,$templateName,$data);

                    }else{
                        $folderPath = public_path('active_call_report_export_csv');
                        $folderName = 'active_call_report_export_csv';
                        $fileName = 'active_call_history_report_' . time(). '.csv';
                        $title = 'Active Call Report Excel Sheet';

                        $ressult = ReportController::getActiveCallHistoryReportCsvExport($title,$folderName,$fileName,$data);
                    }

                    if(!empty($is_email) && $is_email == 1){
                        $subject = 'Active Call History Report File Download';
                        $attachment[] = $ressult;
                        $result_data = ReportController::send_download_report_mail($current_user->email,$current_user->user_profile->first_name,$current_user->user_profile->last_name,$ressult,$subject,$attachment);
                        if($result_data == 1){
                            $message = trans("translate.ACTIVE_CALL_HISTORY_DATA_FILE_SENT_VIA_EMAIL");
                        }else{
                            $message = trans("translate.ACTIVE_CALL_HISTORY_DATA_FILE_NOT_SENT_VIA_EMAIL");
                        }
                        $response_array = $this->helper->custom_response(true, array(), $message);

                    }else{
                        $response_array = $this->helper->custom_response(true, $ressult, trans("translate.ACTIVE_CALL_HISTORY_DATA_EXPORT"));
                    }
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

    // active call history report csv file generate
    public function getActiveCallHistoryReportCsvExport($title,$folderName,$fileName,$data){
        $folderPath = public_path($folderName);
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
        $Filepath = '';
        if (isset($data['call_datas']) && !empty($data['call_datas'])) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', '#');
            $sheet->setCellValue('B1', 'Interpreter');
            $sheet->setCellValue('C1', 'Caller');
            $sheet->setCellValue('D1', 'Purpose');
            $sheet->setCellValue('E1', 'Language');
            $sheet->setCellValue('F1', 'Date');
            $sheet->setCellValue('G1', 'Call Duration');

            $rowCount = 2;
            $cnt = 1;

            foreach ($data['call_datas'] as $key => $row_data) { 
                if (isset($row_data['call_details']) && !empty($row_data['call_details'])) {   
                    $start_time = (isset($row_data['call_detail']['start_time'])?$row_data['call_detail']['start_time']:'');
                    $duration = (isset($row_data['call_detail']['duration'])?$row_data['call_detail']['duration']:'');
                    $first_name = (isset($row_data['call_detail']['user_profile']['first_name'])?$row_data['call_detail']['user_profile']['first_name']:'');
                    $last_name = (isset($row_data['call_detail']['user_profile']['last_name'])?$row_data['call_detail']['user_profile']['last_name']:'');
                    
                    $interpreter_name = $first_name." ".$last_name;
                    if(empty($interpreter_name)){
                      $interpreter_name = 'No Interpreter Found';  
                    }

                    
                }
                $caller_first_name = (isset($row_data['from_user_profile']['first_name'])?$row_data['from_user_profile']['first_name']:'');
                $caller_last_name = (isset($row_data['from_user_profile']['first_namelast_name'])?$row_data['from_user_profile']['last_name']:'');
                $caller_name = $caller_first_name .' '. $caller_last_name;
                $language = (isset($row_data['language']['name'])?$row_data['language']['name']:'');
                $purpose = (isset($row_data['purpose']['description'])?$row_data['purpose']['description']:'');
              

                $sheet->setCellValue('A' . $rowCount, $cnt);
                $sheet->setCellValue('B' . $rowCount, $interpreter_name);
                $sheet->setCellValue('C' . $rowCount, $caller_name);
                $sheet->setCellValue('D' . $rowCount, $purpose);
                $sheet->setCellValue('E' . $rowCount, $language);
                $sheet->setCellValue('F' . $rowCount, date('d-m-Y H:i:s',strtotime($start_time)));
                $sheet->setCellValue('G' . $rowCount, date('H:i:s',strtotime($duration)));

                $rowCount++;
                $cnt++;
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setPreCalculateFormulas(false);

            $writer->save($folderPath . "/" . $fileName);

            header("Content-Type: application/vnd.ms-excel");

            $Filepath = url('/') . "/".$folderName."/" . $fileName;

        }
        return $Filepath;
    }

    //Interpreter user report export 
    public function interpreter_user_report_export(Request $request) {
        try {
            $current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;
            $is_email = isset($request->is_email) && !empty($request->is_email) ? $request->is_email : '';
            $search_name = isset($request->name) && !empty($request->name) ? $request->name : '';
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;


                // Company Admin (Amazon) => if Company Admin then show All Company Roles active users recocrds 
                $user_profiles_data_count = '';
                // companies.id = 1 = Interpreters users 
                $user_profiles_data = User::getUserData()
                    ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                    ->join('companies', 'user_profies.company_id', "=", 'companies.id')
                    ->join('role_users', 'user_profies.id', "=", 'role_users.user_profile_id')
                    ->where('companies.id',1);
                    
                    if($search_name != ''){
                        $user_profiles_data = $user_profiles_data->where('first_name', 'LIKE', '%'.$search_name.'%'); 
                    }
                $user_profiles_data = $user_profiles_data->get();

                $user_profiles_data_count = count($user_profiles_data);
                
                //All Interpreter Count 
                $total_interpreter_count = ActiveInterpreter::GroupBy('user_profile_id')->get()->count();
                //Available Interpreter Count - status - 1
                $total_available_interpreter_count = ActiveInterpreter::where('status',1)->GroupBy('user_profile_id')->get()->count();
                //Busy on onther call Interpreter Count  - status - 4
                $total_busy_interpreter_on_call_count = ActiveInterpreter::where('status',4

            )->GroupBy('user_profile_id')->get()->count();
                $average_detail = [
                    "total_interpreter_count" => $total_interpreter_count,
                    "total_available_interpreter_count" => $total_available_interpreter_count,
                    "total_busy_interpreter_on_call_count" => $total_busy_interpreter_on_call_count,
                ];
                if (!empty($user_profiles_data)) {

                    foreach ($user_profiles_data as $key => $row_data) {
                        $row_data->user_profile->user_role = '';
                        if(isset($row_data->user_profile->user_roles[0]) && !empty($row_data->user_profile->user_roles[0])){
                            $row_data->user_profile->user_role = $row_data->user_profile->user_roles[0];
                            unset($row_data->user_profile->user_roles);
                        }
                        

                        //GET Language
                        $language_id = UserLanguage::where('user_profile_id',$row_data->id)->where('ranking',1)->pluck('language_id')->first();
                        $user_profiles_data[$key]->interpreter_language = Language::where('id',$language_id)->pluck('name')->first();
                        //GET CALl COUNT
                        $user_profiles_data[$key]->calls_count = CallDetail::where('user_profile_id',$row_data->id)->GroupBy('call_id')->get()->count();


                        // Total Average Duratoin
                        //$average_call_duration_times = CallDetail::where('user_profile_id',$row_data->id)->GroupBy('call_id')->avg('duration');
                        $average_call_duration_times = CallDetail::where('user_profile_id',$row_data->id)->whereNotNull(['duration'])->avg('duration');
                        $hours = floor($average_call_duration_times / 3600);
                        $mins = floor(($average_call_duration_times - $hours * 3600) / 60);
                        $s = $average_call_duration_times - ($hours * 3600 + $mins * 60);
                        $average_call_duration_times = $hours . ":" . $mins . ":" . floor($s);
                        $user_profiles_data[$key]->average_call_duration_times = $average_call_duration_times;
                        //GET INTERPRETER STATUS 
                        
                        $status = ActiveInterpreter::where('user_profile_id',$row_data->id)->pluck('status')->first();
                        $interpreter_status = 'Not Available';
                        if($status == 1){
                            $interpreter_status = 'Available';   
                        }
                        if($status == 2){
                            $interpreter_status = 'Busy(offline)';   
                        }
                        if($status == 3){
                            $interpreter_status = 'Busy(On Break)';   
                        }
                        if($status == 4){
                            $interpreter_status = 'Busy(Another Call)';   
                        }
                        $user_profiles_data[$key]->status = $interpreter_status;

                        $row_data->user_profile->calls_count = $user_profiles_data[$key]->calls_count;
                        $row_data->user_profile->average_call_duration_times = $user_profiles_data[$key]->average_call_duration_times;
                    }

                    $data['average_detail'] = $average_detail;
                    $data['data'] = $user_profiles_data;


                    $extension = $request->export_type;
                    if (!empty($extension)) {
                        $extension = $extension;
                    } else {
                        $extension = 'pdf';
                    }

                    $Filepath = '';

                    if($extension == 'pdf'){
                        $folderName = 'interpreter_user_report_export_pdf';
                        $fileName = 'interpreter_user_report_' . time(). '.pdf';
                        $templateName = 'interpreter_user_report_pdf_template';

                        $ressult = ReportController::getPdfExport($folderName,$fileName,$templateName,$data);

                    }else{
                        $folderPath = public_path('interpreter_user_report_export_csv');
                        $folderName = 'interpreter_user_report_export_csv';
                        $fileName = 'interpreter_user_report_' . time(). '.csv';
                        $title = 'Interpreter User Report Excel Sheet';

                        $ressult = ReportController::getInterpreterUserReportCsvExport($title,$folderName,$fileName,$data);
                    }

                    if(!empty($is_email) && $is_email == 1){
                        $subject = 'Interpreter User History Report File Download';
                        $attachment[] = $ressult;
                        $result_data = ReportController::send_download_report_mail($current_user->email,$current_user->user_profile->first_name,$current_user->user_profile->last_name,$ressult,$subject,$attachment);
                        if($result_data == 1){
                            $message = trans("translate.INTERPRETER_USER_REPORT_DATA_FILE_SENT_VIA_EMAIL");
                        }else{
                            $message = trans("translate.INTERPRETER_USER_REPORT_DATA_FILE_NOT_SENT_VIA_EMAIL");
                        }
                        $response_array = $this->helper->custom_response(true, array(), $message);

                    }else{
                        $response_array = $this->helper->custom_response(true, $ressult, trans("translate.INTERPRETER_USER_REPORT_DATA__EXPORT"));
                    }
                    return response()->json($response_array, Response::HTTP_OK);

                    

                } else {
                    $data = [];
                    $message = trans("translate.INTERPRETER_USER_REPORT_DATA_NOT_FOUND");
                    $response_array = $this->helper->custom_response(true, array(), $message);
                }
            }
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    // interpreter user report csv file generate
    public function getInterpreterUserReportCsvExport($title,$folderName,$fileName,$data){
        $folderPath = public_path($folderName);
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
        $Filepath = '';
        if (isset($data['data']) && !empty($data['data'])) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', '#');
            $sheet->setCellValue('B1', 'Interpreter');
            $sheet->setCellValue('C1', 'Language');
            $sheet->setCellValue('D1', 'Calls');
            $sheet->setCellValue('E1', 'Avg Rating');
            $sheet->setCellValue('F1', 'Status');

            $rowCount = 2;
            $cnt = 1;

            foreach ($data['data'] as $key => $row_data) { 
                if (isset($row_data['user_profile']) && !empty($row_data['user_profile'])) {   
                    $first_name = (isset($row_data['user_profile']['first_name'])?$row_data['user_profile']['first_name']:'');
                    $last_name = (isset($row_data['user_profile']['last_name'])?$row_data['user_profile']['last_name']:'');
                    $avg_user_rating = (isset($row_data['user_profile']['avg_user_rating'])?$row_data['user_profile']['avg_user_rating']:'');
                    
                    $interpreter_name = $first_name." ".$last_name;
                    if(empty($interpreter_name)){
                      $interpreter_name = 'No Interpreter Found';  
                    }

                    if(!empty($row_data['user_profile']['profile_photo'] && $row_data['user_profile']['profile_photo'] != '//default.png')){
                      $user_img =url($row_data['user_profile']['profile_photo']);
                    }else{
                      $user_img =url('/uploads/users/default.png');
                    }
                }
                $interpreter_language = (isset($row_data['interpreter_language'])?$row_data['interpreter_language']:'');
                $calls_count = (isset($row_data['calls_count'])?$row_data['calls_count']:'');
                $status = (isset($row_data['status'])?$row_data['status']:'');
              

                $sheet->setCellValue('A' . $rowCount, $cnt);
                $sheet->setCellValue('B' . $rowCount, $interpreter_name);
                $sheet->setCellValue('C' . $rowCount, $interpreter_language);
                $sheet->setCellValue('D' . $rowCount, $calls_count);
                $sheet->setCellValue('E' . $rowCount, $avg_user_rating);
                $sheet->setCellValue('F' . $rowCount, $status);

                $rowCount++;
                $cnt++;
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setPreCalculateFormulas(false);

            $writer->save($folderPath . "/" . $fileName);

            header("Content-Type: application/vnd.ms-excel");

            $Filepath = url('/') . "/".$folderName."/" . $fileName;

        }
        return $Filepath;
    }


    // Amazon user report export 
    public function supervisor_user_report_export(Request $request) {
        try {
            $current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;
            $is_email = isset($request->is_email) && !empty($request->is_email) ? $request->is_email : '';
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;


                // Company Admin (Amazon) => if Company Admin then show All Company Roles active users recocrds 
                $user_profiles_data_count = '';
                // companies.id = 2 = amazone users 
                $user_profiles_data = User::getUserData()
                    ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                    ->join('companies', 'user_profies.company_id', "=", 'companies.id')
                    ->join('role_users', 'user_profies.id', "=", 'role_users.user_profile_id')
                    ->where('companies.id',2);
                    
                $user_profiles_data = $user_profiles_data->get();

                $user_profiles_data_count = count($user_profiles_data);
                

                if (!empty($user_profiles_data)) {
                    $total_available_interpreter = ActiveInterpreter::GroupBy('user_profile_id')->where('status',1)->count();
                    $avg_call_wait_time = '';
                    $most_active_location = '';
                    $average_detail = [
                        "total_supervisor" => $user_profiles_data_count,
                        "total_available_interpreter" => $total_available_interpreter,
                    ];

                    /*foreach ($user_profiles_data as $key => $row_data) {
                        echo "<pre>"; print_r($row_data);exit();
                        $row_data->user_profile->user_role = '';
                        if(isset($row_data->user_profile->user_roles[0]) && !empty($row_data->user_profile->user_roles[0])){
                            $row_data->user_profile->user_role = $row_data->user_profile->user_roles[0];
                            unset($row_data->user_profile->user_roles);
                        }
                    }*/
                        

                    $data['average_detail'] = $average_detail;
                    $data['data'] = $user_profiles_data;
                    
                    $extension = $request->export_type;
                    if (!empty($extension)) {
                        $extension = $extension;
                    } else {
                        $extension = 'pdf';
                    }

                    $Filepath = '';

                    if($extension == 'pdf'){
                        $folderName = 'supervisor_user_report_export_pdf';
                        $fileName = 'supervisor_user_report_' . time(). '.pdf';
                        $templateName = 'supervisor_user_report_pdf_template';

                        $ressult = ReportController::getPdfExport($folderName,$fileName,$templateName,$data);

                    }else{
                        $folderPath = public_path('supervisor_user_report_export_pdf_csv');
                        $folderName = 'supervisor_user_report_export_pdf_csv';
                        $fileName = 'supervisor_user_report_' . time(). '.csv';
                        $title = 'Supervisor User History Excel Sheet';

                        $ressult = ReportController::getSupervisorUserReportCsvExport($title,$folderName,$fileName,$data);
                    }

                    if(!empty($is_email) && $is_email == 1){
                        $subject = 'Supervisor User History Report File Download';
                        $attachment[] = $ressult;
                        $result_data = ReportController::send_download_report_mail($current_user->email,$current_user->user_profile->first_name,$current_user->user_profile->last_name,$ressult,$subject,$attachment);
                        if($result_data == 1){
                            $message = trans("translate.SUPERVISOR_USER_REPORT_DATA_FILE_SENT_VIA_EMAIL");
                        }else{
                            $message = trans("translate.SUPERVISOR_USER_REPORT_DATA_FILE_NOT_SENT_VIA_EMAIL");
                        }
                        $response_array = $this->helper->custom_response(true, array(), $message);

                    }else{
                        $response_array = $this->helper->custom_response(true, $ressult, trans("translate.SUPERVISOR_USER_REPORT_DATA_EXPORT"));
                    }
                    return response()->json($response_array, Response::HTTP_OK);


                } else {
                    $data = [];
                    $message = trans("translate.SUPERVISOR_USER_REPORT_DATA_NOT_FOUND");
                }
            }
            $response_array = $this->helper->custom_response(true, $data, $message,true,$user_profiles_data_count);
            return response()->json($response_array, Response::HTTP_OK);
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    // Supervisor user report csv file generate
    public function getSupervisorUserReportCsvExport($title,$folderName,$fileName,$data){
        $folderPath = public_path($folderName);
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
        $Filepath = '';
        if (isset($data['data']) && !empty($data['data'])) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', '#');
            $sheet->setCellValue('B1', 'User ID');
            $sheet->setCellValue('C1', 'Email ID');
            $sheet->setCellValue('E1', 'Company');
            $sheet->setCellValue('F1', 'City');
            $sheet->setCellValue('G1', 'Site');

            $rowCount = 2;
            $cnt = 1;

            foreach ($data['data'] as $key => $row_data) { 
                if (isset($row_data['user_profile']) && !empty($row_data['user_profile'])) {   
                    $user_id = (isset($row_data['user_profile']['user_id'])?$row_data['user_profile']['user_id']:'');
                    $company_name = (isset($row_data['user_profile']['company']['company_name'])?$row_data['user_profile']['company']['company_name']:'');
                    $city_name = (isset($row_data['user_profile']['locations']['city']['name'])?$row_data['user_profile']['locations']['city']['name']:'');
                    $site_name = (isset($row_data['user_profile']['locations']['site'])?$row_data['user_profile']['locations']['site']:'');


                    if(!empty($row_data['user_profile']['profile_photo'] && $row_data['user_profile']['profile_photo'] != '//default.png')){
                      $user_img =url($row_data['user_profile']['profile_photo']);
                    }else{
                      $user_img =url('/uploads/users/default.png');
                    }
                }
                $email = (isset($row_data['email'])?$row_data['email']:'');
              

                $sheet->setCellValue('A' . $rowCount, $cnt);
                $sheet->setCellValue('B' . $rowCount, $user_id);
                $sheet->setCellValue('C' . $rowCount, $email);
                $sheet->setCellValue('E' . $rowCount, $company_name);
                $sheet->setCellValue('F' . $rowCount, $city_name);
                $sheet->setCellValue('G' . $rowCount, $site_name);

                $rowCount++;
                $cnt++;
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setPreCalculateFormulas(false);

            $writer->save($folderPath . "/" . $fileName);

            header("Content-Type: application/vnd.ms-excel");

            $Filepath = url('/') . "/".$folderName."/" . $fileName;

        }
        return $Filepath;
    }

    // Frequent user report export -> User report
    public function frequent_user_report_export(Request $request) {
        try {
            $is_email = isset($request->is_email) && !empty($request->is_email) ? $request->is_email : '';
            $from_date = isset($request->from_date) && !empty($request->from_date) ? $request->from_date : '';
            $to_date = isset($request->to_date) && !empty($request->to_date) ? $request->to_date : '';
            $current_user = auth()->user();
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;

                $status = (isset($request->status) && !empty($request->status)) ? $request->status : 40;

                $avg_call_per_user = Call::GroupBy('from_user_profile_id')->avg('from_user_profile_id');

                $user_on_call = Call::getCallReportData()->where('status', $status);

                // Company Admin (Amazon) => if Company Admin then show All Company Roles active users recocrds 
                $user_profiles_data_count = '';

                $user_profiles_data = User::getUserData()
                        ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                        ->join('calls', 'user_profies.id', "=", 'calls.from_user_profile_id')
                        ->selectRaw('count(calls.from_user_profile_id) as total_call')
                        ->orderBy('total_call','DESC')
                        ->whereIn('calls.from_user_role_id',$this->consumer_roles);

                        /*if (isset($request->from_date) && !empty($request->from_date)) {
                            $user_profiles_data = $user_profiles_data->whereDate('start_time' , '>=', $from_date);
                        }*/
                        if($from_date != ''){
                            $user_profiles_data = $user_profiles_data->whereDate('calls.created_at', '>=', date($from_date));

                            $user_on_calluser_on_call = $user_on_call->whereDate('calls.created_at', '>=', date($from_date));
                        }
                        if($to_date != ''){
                            $user_profiles_data = $user_profiles_data->whereDate('calls.created_at', '<=', date($to_date));
                            $user_on_call = $user_on_call->whereDate('calls.created_at', '<=', date($to_date));
                        }

                        $user_profiles_data = $user_profiles_data->GroupBy('calls.from_user_profile_id')->get();
                        $user_on_call = $user_on_call->GroupBy('calls.from_user_profile_id')->get();

                        $user_profiles_data_count = count($user_profiles_data);
                        $user_on_call = count($user_on_call);
                

                if (!empty($user_profiles_data)) {
                    $total_available_interpreter = ActiveInterpreter::where('status',1)->GroupBy('user_profile_id')->get()->count();
                    
                    $avg_call_wait_time = '';
                    $most_active_location = '';
                    $average_detail = [
                        "total_users" => $user_profiles_data_count,
                        "user_on_call" => $user_on_call,
                        "avg_call_per_user" => $avg_call_per_user,
                        "total_available_interpreter" => $total_available_interpreter,
                        "avg_call_wait_time" => $avg_call_wait_time,
                        "most_active_location" => $most_active_location
                    ];

                    foreach ($user_profiles_data as $key => $row_data) {
                        $row_data->user_profile->user_role = '';
                        if(isset($row_data->user_profile->user_roles[0]) && !empty($row_data->user_profile->user_roles[0])){
                            $row_data->user_profile->user_role = $row_data->user_profile->user_roles[0];
                            unset($row_data->user_profile->user_roles);
                        }

                        //GET CALl COUNT
                        /*$user_profiles_data[$key]->calls_count = Call::where('from_user_profile_id',$row_data->user_profile->id)->get()->count();*/
                    }

                    $data['average_detail'] = $average_detail;
                    $data['data'] = $user_profiles_data;
                    
                    $extension = $request->export_type;
                    if (!empty($extension)) {
                        $extension = $extension;
                    } else {
                        $extension = 'pdf';
                    }

                    $Filepath = '';

                    if($extension == 'pdf'){
                        $folderName = 'frequent_user_report_export_pdf';
                        $fileName = 'frequent_user_report_' . time(). '.pdf';
                        $templateName = 'frequent_user_report_pdf_template';

                        $ressult = ReportController::getPdfExport($folderName,$fileName,$templateName,$data);

                    }else{
                        $folderPath = public_path('frequent_user_report_export_csv');
                        $folderName = 'frequent_user_report_export_csv';
                        $fileName = 'frequent_user_report_' . time(). '.csv';
                        $title = 'Frequent User Report Excel Sheet';

                        $ressult = ReportController::getFrequentUserReportCsvExport($title,$folderName,$fileName,$data);
                    }

                    if(!empty($is_email) && $is_email == 1){
                        $subject = 'Frequent User Report File Download';
                        $attachment[] = $ressult;
                        $result_data = ReportController::send_download_report_mail($current_user->email,$current_user->user_profile->first_name,$current_user->user_profile->last_name,$ressult,$subject,$attachment);
                        if($result_data == 1){
                            $message = trans("translate.FREQUENT_USER_REPORT_FILE_SENT_VIA_EMAIL");
                        }else{
                            $message = trans("translate.FREQUENT_USER_REPORT_DATA_FILE_NOT_SENT_VIA_EMAIL");
                        }
                        $response_array = $this->helper->custom_response(true, array(), $message);

                    }else{
                        $response_array = $this->helper->custom_response(true, $ressult, trans("translate.FREQUENT_USER_REPORT_DATA_EXPORT"));
                    }
                    return response()->json($response_array, Response::HTTP_OK);
                } else {
                    $data = [];
                    $message = trans("translate.FREQUENT_USER_REPORT_DATA_NOT_FOUND");
                }
            }
            $response_array = $this->helper->custom_response(true, $data, $message,true,$user_profiles_data_count);
            return response()->json($response_array, Response::HTTP_OK);
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    // Frequent user report csv file generate
    public function getFrequentUserReportCsvExport($title,$folderName,$fileName,$data){
        $folderPath = public_path($folderName);
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
        $Filepath = '';
        if (isset($data['data']) && !empty($data['data'])) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', '#');
            $sheet->setCellValue('B1', 'Users');
            $sheet->setCellValue('C1', 'Employee ID');
            $sheet->setCellValue('D1', 'Role/Funcation');
            $sheet->setCellValue('G1', 'Site');
            $sheet->setCellValue('E1', 'Total Calls');

            $rowCount = 2;
            $cnt = 1;

            foreach ($data['data'] as $key => $row_data) { 
                if (isset($row_data['user_profile']) && !empty($row_data['user_profile'])) {   
                    $first_name = (isset($row_data['user_profile']['first_name'])?$row_data['user_profile']['first_name']:'');
                    $last_name = (isset($row_data['user_profile']['last_name'])?$row_data['user_profile']['last_name']:'');
                    $user_id = (isset($row_data['user_profile']['user_id'])?$row_data['user_profile']['user_id']:'');
                    
                    $user_name = $first_name." ".$last_name;
                    if(empty($user_name)){
                      $user_name = 'No User Found';  
                    }
                    $role_name = (isset($row_data['user_profile']['user_role']['role_display_name'])?$row_data['user_profile']['user_role']['role_display_name']:'');
                    $site_name = (isset($row_data['user_profile']['locations']['site'])?$row_data['user_profile']['locations']['site']:'');

                    if(!empty($row_data['user_profile']['profile_photo'] && $row_data['user_profile']['profile_photo'] != '//default.png')){
                      $user_img =url($row_data['user_profile']['profile_photo']);
                    }else{
                      $user_img =url('/uploads/users/default.png');
                    }
                }
                $total_call = (isset($row_data['total_call'])?$row_data['total_call']:'');
              

                $sheet->setCellValue('A' . $rowCount, $cnt);
                $sheet->setCellValue('B' . $rowCount, $user_name);
                $sheet->setCellValue('C' . $rowCount, $user_id);
                $sheet->setCellValue('D' . $rowCount, $role_name);
                $sheet->setCellValue('G' . $rowCount, $site_name);
                $sheet->setCellValue('E' . $rowCount, $total_call);

                $rowCount++;
                $cnt++;
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setPreCalculateFormulas(false);

            $writer->save($folderPath . "/" . $fileName);

            header("Content-Type: application/vnd.ms-excel");

            $Filepath = url('/') . "/".$folderName."/" . $fileName;

        }
        return $Filepath;
    }


    // load data to html template and save pdf to the folder and return the pdf file path
    public function getPdfExport($folderName,$fileName,$templateName,$data){
        $folderPath = public_path($folderName);
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        $pdf = PDF::loadView($templateName, compact('data'));
        $pdf->save($folderPath . '/' . $fileName);
        $Filepath = url('/') . "/".$folderName."/" . $fileName;

        return $Filepath;
    }

    // send pdf and csv file email to the logged user
    public function send_download_report_mail($to_email,$user_first_name,$user_last_name,$file_link,$subject,$attachment){
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
        $template_details->email_subject = $subject;

        $send_data = $this->helper->send_email('mayursinh@mailinator.com', $template_replace_data, $template_details,$attachment);

        return $send_data;
    }

    public function qa_active_call_report(Request $request) {
        try {
            $data = array();
            $limit = isset($request->limit) && !empty($request->limit) ? $request->limit : 20;
            $current_user = auth()->user();
            $current_user_profile_id = $current_user->user_profile->id;
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;


                $status = (isset($request->status) && !empty($request->status)) ? $request->status : 40;



                $calls_datas = Call::getCallReportData()->where('status', $status);
                $calls_datas = $calls_datas->get();

                $calls_datas_count = count($calls_datas);
                $average_times = '';
                $average_language = '';
                $average_purpose = '';
                $avg_call_wait_time = '';
                $most_active_location = '';
                // available interpreter
                $total_available_interpreter = ActiveInterpreter::where('status',1)->count();
                if(!empty($calls_datas_count && $calls_datas_count != 0)){
                    $avg_call_wait_time = '00:00:45';
                    $most_active_location = 'BLR-H';

                     // Total Average Times
                    $average_times = CallDetail::select(DB::raw("AVG(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) AS average_times"))->whereNotNull(['start_time', 'end_time'])->join('calls', 'calls.id', "=", 'call_details.call_id');
                    $average_times = $average_times->first();
                    $hours = floor($average_times['average_times'] / 3600);
                    $mins = floor(($average_times['average_times'] - $hours * 3600) / 60);
                    $s = $average_times['average_times'] - ($hours * 3600 + $mins * 60);
                    $average_times1 = $hours . ":" . $mins . ":" . floor($s);
                    $average_times = date('H:i:s', strtotime($average_times1));
                    // Total Average Language
                    $average_language = Call::select(DB::raw("ROUND(AVG(language_id)) AS average_lanuage_id"))
                        ->join('call_details', 'call_details.call_id', "=", 'calls.id');
                    $average_language = $average_language->first();
                    $average_language = Language::select('id', 'name', 'is_active')->where('id', $average_language['average_lanuage_id'])->first();

                    // Total Average Purpose
                    $average_purpose = Call::select(DB::raw("ROUND(AVG(purpose_id)) AS average_purpose_id"))
                    ->join('call_details', 'call_details.call_id', "=", 'calls.id');
                    $average_purpose = $average_purpose->first();
                        $average_purpose = Purpose::select('id', 'name', 'description')->where('id', $average_purpose['average_purpose_id'])->first();



                }
                // Call by location Records get        
                $locations_records = ['miles','region','site'];
                if (in_array('miles', $locations_records)) {
                    $locations_by_mile = Call::select('locations.miles','miles.value')
                        ->join('locations', 'locations.user_profile_id', "=", 'calls.from_user_profile_id')
                        ->join('miles', 'miles.id', "=", 'locations.miles')
                        ->where('calls.status',$status)
                        ->selectRaw('count(locations.miles) as total_miles_call')
                        ->GroupBy('locations.miles');
                    if (in_array($role_id, $this->supplier_roles)) {
                        $locations_by_mile = $locations_by_mile->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    if (in_array($role_id, $this->consumer_roles)) {
                        $locations_by_mile = $locations_by_mile->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    $locations_by_mile = $locations_by_mile->get();
                }

                if (in_array('region', $locations_records)) {
                    $locations_by_region = Call::select('locations.region','regions.value')
                        ->join('locations', 'locations.user_profile_id', "=", 'calls.from_user_profile_id')
                        ->join('regions', 'regions.id', "=", 'locations.region')
                        ->where('calls.status',$status)
                        ->selectRaw('count(locations.region) as total_region_call')
                        ->GroupBy('locations.region');
                    if (in_array($role_id, $this->supplier_roles)) {
                        $locations_by_region = $locations_by_region->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    if (in_array($role_id, $this->consumer_roles)) {
                        $locations_by_region = $locations_by_region->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    $locations_by_region = $locations_by_region->get();
                }

                if (in_array('site', $locations_records)) {
                    $locations_by_site = Call::select('locations.site')
                        ->join('locations', 'locations.user_profile_id', "=", 'calls.from_user_profile_id')
                        ->where('calls.status',$status)
                        ->selectRaw('count(locations.site) as total_site_call')
                        ->GroupBy('locations.site');
                    if (in_array($role_id, $this->supplier_roles)) {
                        $locations_by_site = $locations_by_site->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    if (in_array($role_id, $this->consumer_roles)) {
                        $locations_by_site = $locations_by_site->where('locations.user_profile_id', $current_user_profile_id);
                    }
                    $locations_by_site = $locations_by_site->get();
                }

                $average_detail = [
                    "total_calls" => $calls_datas_count,
                    "total_available_interpreter" => $total_available_interpreter,
                    "average_times" => $average_times
                ];

                /*$average_detail = [
                    "total_calls" => $calls_datas_count,
                    "total_available_interpreter" => $total_available_interpreter,
                    "average_times" => $average_times,
                    "average_language" => $average_language,
                    "average_purpose" => $average_purpose,
                    "avg_call_wait_time" => $avg_call_wait_time,
                    "most_active_location" => $most_active_location
                ];*/

                //Top Languages get 
                $popular_call_lang = Call::select('language_id')->selectRaw('count(language_id) as total_lang_call')->GroupBy('language_id')->orderBy('total_lang_call','DESC')->get(); // get popular language id and count number

                foreach ($popular_call_lang as $key => $value) {
                    $popular_call_lang[$key]->language_name = Language::where('id',$value['language_id'])->pluck('name')->first(); // get popular language name
                }


                //Top Purpose get 
                $popular_call_purpose = Call::select('purpose_id')->selectRaw('count(purpose_id) as total_purpose_call')->GroupBy('purpose_id')->orderBy('total_purpose_call','DESC')->get(); // get popular purpose id and count number 
                foreach ($popular_call_purpose as $key => $value) {
                    $popular_call_purpose[$key]->purpose_name = Purpose::where('id',$value['purpose_id'])->pluck('description')->first(); // get popular Purpose name
                }
                if (isset($calls_datas) && !empty($calls_datas)) {
                    foreach($calls_datas AS $calls_data){
                         $calls_data->call_detail = '';
                       if(isset($calls_data->call_details[0]) && !empty($calls_data->call_details[0])){
                            $calls_data->call_detail = $calls_data->call_details[0];
                            $date_format = date("d-m-Y H:i:s", strtotime($calls_data->call_details[0]->start_time));
                            if($date_format == '01-01-1970 00:00:00'){
                                $calls_data->call_detail->start_time = '';
                            }else{

                                $newtimestamp = strtotime($calls_data->call_details[0]->start_time.' + 5 hours + 30 minute');
                                $calls_data->call_detail->start_time =  date('Y-m-d H:i:s', $newtimestamp);
                            }

                            if(isset($calls_data->call_details[0]->duration) && !empty($calls_data->call_details[0]->duration)){
                                $calls_data->call_details[0]->duration = date('H:i:s', strtotime($calls_data->call_details[0]->duration));
                            }
                       $join_calls_details = array();
                       $join_calls_details_data = CallDetail::select('user_profile_id','user_role_id')->where('call_id',$calls_data->id)->where('user_role_id',$this->qa_manger_roles)->GroupBy('user_profile_id')->get()->toArray();

                       $final_array = [];
                       if(!empty($join_calls_details_data)){
                           foreach ($join_calls_details_data as $key => $value) {
                                $join_calls_details['join_calls'] = 1;
                                $join_calls_details['qa_user_id'] = $value['user_profile_id'];
                                $join_calls_details['user_role_id'] = $value['user_role_id'];
                                $final_array[] = $join_calls_details;
                           }
                       }else{
                            $join_calls_details['join_calls'] = 0;
                            $join_calls_details['qa_user_id'] = NULL;
                            $join_calls_details['qa_user_role'] = NULL;
                            $final_array[] = $join_calls_details;
                       }
                        
                        $calls_data->call_details[0]->join_calls_details = $final_array;
                    }
                        unset($calls_data->call_details);
                    }
                    
                    $data['average_detail'] = $average_detail;                    
                    $data['call_datas'] = $calls_datas;
                    $response_array = $this->helper->custom_response(true, $data, trans("translate.QA_MANAGER_ACTIVE_CALL_DATA"),true,$calls_datas_count);
                    return response()->json($response_array, Response::HTTP_OK);
                } else {
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.QA_MANAGER_ACTIVE_CALL_DATA_NOT_FOUND"));
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

    public function qa_call_report_history(Request $request) {
        try {
            $limit = isset($request->limit) && !empty($request->limit) ? $request->limit : 20;
            $from_date = isset($request->from_date) && !empty($request->from_date) ? $request->from_date : '';
            $to_date = isset($request->to_date) && !empty($request->to_date) ? $request->to_date : '';
            $search_name = isset($request->name) && !empty($request->name) ? $request->name : '';
            $search_fron_user_profile_id = isset($request->user_profile_id) && !empty($request->user_profile_id) ? $request->user_profile_id : '';
            $current_user = auth()->user();
            $current_user_profile_id = $current_user->user_profile->id;
            $roles = $current_user->user_profile->user_roles;
            if (!$roles->isEmpty()) {
                $current_user->role = $roles[0];
                $role_user_id = $current_user->role->id;
                $role_id = $current_user->role->role_id;
                $supplier_roles = $this->supplier_roles;
                $qa_manger_roles = $this->qa_manger_roles;

                $calls_datas = Call::getCallReportData();

                $qa_call_datas = CallDetail::select('call_id','user_profile_id','user_role_id')->where('user_profile_id', $current_user_profile_id)->whereIn('user_role_id', $qa_manger_roles)->get()->toArray();
                    

                /*$calls_datas->whereHas('call_details', function($query) use($current_user_profile_id,$qa_manger_roles) {
                    $query->where('user_profile_id', $current_user_profile_id)->whereIn('user_role_id', $qa_manger_roles);
                });*/
                
                if($from_date != ''){
                    $calls_datas->whereHas('call_details', function($query) use($from_date) {
                        $query->whereDate('start_time' , '>=', $from_date);
                    });

                }
                if($to_date != ''){
                    $calls_datas = $calls_datas->whereHas('call_details', function($query) use($to_date) {
                        $query->whereDate('start_time' , '<=', $to_date);
                    });
                }
                if (isset($request->miles) && !empty($request->miles)) {
                    $calls_datas->whereHas('from_user_profile', function($query) use($request) {
                        $query->whereHas('locations', function($query) use($request) {
                            $query->where('miles', $request->miles);
                        });
                    });
                }
                if (isset($request->region) && !empty($request->region)) {
                    $calls_datas->whereHas('from_user_profile', function($query) use($request) {
                        $query->whereHas('locations', function($query) use($request) {
                            $query->where('region', $request->region);
                        });
                    });
                }
                if (isset($request->site) && !empty($request->site)) {
                    $calls_datas->whereHas('from_user_profile', function($query) use($request) {
                        $query->whereHas('locations', function($query) use($request) {
                            $query->where('site', $request->site);
                        });
                    });
                }

                $calls_datas = $calls_datas->get();
                $calls_datas_count = count($qa_call_datas);
                $average_times = '';

                

                 // Total Average Times
                $average_times = CallDetail::select(DB::raw("AVG(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) AS average_times"))->whereNotNull(['start_time', 'end_time'])->join('calls', 'calls.id', "=", 'call_details.call_id');
                
                if (in_array($role_id, $this->qa_manger_roles)) {
                    $average_times = $average_times->where('call_details.user_profile_id', $current_user_profile_id);
                }

                $average_times = $average_times->first();

                $hours = floor($average_times['average_times'] / 3600);
                $mins = floor(($average_times['average_times'] - $hours * 3600) / 60);
                $s = $average_times['average_times'] - ($hours * 3600 + $mins * 60);
                $average_times1 = $hours . ":" . $mins . ":" . floor($s);

                $average_times = date('H:i:s', strtotime($average_times1));
                
                $user_feedback_provided_count = CallFeedbackUser::where('created_by',$current_user_profile_id)->get()->count();
                
                $user_feedback_pending_count =  $calls_datas_count - $user_feedback_provided_count;                

                $average_detail = [
                    "total_calls" => $calls_datas_count,
                    "average_times" => $average_times,
                    "user_feedback_provided_count" => $user_feedback_provided_count,
                    "user_feedback_pending_count" => $user_feedback_pending_count
                ];


                $data = array();
                $final_array = array();
                if (isset($qa_call_datas) && !empty($qa_call_datas)) {
                    foreach ($qa_call_datas as $key => $value) {
                        if (isset($calls_datas) && !empty($calls_datas)) {
                            foreach ($calls_datas as $calls_data) {
                                if($calls_data->id == $value['call_id']){
                                    $calls_data->call_detail = '';
                                   if(isset($calls_data->call_details[0]) && !empty($calls_data->call_details[0])){
                                        $calls_data->call_detail = $calls_data->call_details[0];
                                        $date_format = date("d-m-Y H:i:s", strtotime($calls_data->call_details[0]->start_time));
                                        if($date_format == '01-01-1970 00:00:00'){
                                            $calls_data->call_detail->start_time = '';
                                        }else{

                                            $newtimestamp = strtotime($calls_data->call_details[0]->start_time.' + 5 hours + 30 minute');
                                            $calls_data->call_detail->start_time =  date('Y-m-d H:i:s', $newtimestamp);
                                        }

                                        if(isset($calls_data->call_details[0]->duration) && !empty($calls_data->call_details[0]->duration)){
                                            $calls_data->call_details[0]->duration = date('H:i:s', strtotime($calls_data->call_details[0]->duration));
                                        }
                                        
                                        $calls_data->call_detail->user_feedback_data = '';
                                        $calls_data->call_detail->user_quality_feedback_data = '';
                                        $calls_data->call_detail->user_feedback_data = CallFeedbackUser::where('call_id',$calls_data->call_detail->call_id)->where('created_by',$current_user_profile_id)->where('to_user_role_id',2)->first();

                                        $calls_data->call_detail->user_quality_feedback_data = CallQualityFeedback::where('call_id',$calls_data->call_detail->call_id)->where('created_by',$current_user_profile_id)->first();
                                   }
                                    unset($calls_data->call_details);
                                    $calls_data->user_feedback_data = CallFeedbackUser::where('call_id',$calls_data->id)->where('to_user_role_id',3)->where('created_by',$current_user_profile_id)->first();


                                    $calls_data->user_quality_feedback_data = CallQualityFeedback::where('call_id',$calls_data->id)->where('created_by',$current_user_profile_id)->first();
                                $final_array[] = $calls_data;
                                }
                            }
                        }
                    }

                    $data['average_detail'] = $average_detail;                    
                    $data['call_datas'] = $final_array;
                    $response_array = $this->helper->custom_response(true, $data, trans("translate.QA_MANAGER_CALL_HISTORY_DATA"),true,$calls_datas_count);
                    return response()->json($response_array, Response::HTTP_OK);
                } else {
                    $response_array = $this->helper->custom_response(false, array(), trans("translate.QA_MANAGER_CALL_HISTORY_DATA_NOT_FOUND"));
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
} 