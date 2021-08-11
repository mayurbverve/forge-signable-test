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

class DashboardController extends Controller {

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

    public function getDashboardcount(Request $request) {
        try {

            $status = 50; //  less than 50 status call is failed calls 
            $from_date = isset($request->from_date) && !empty($request->from_date) ? $request->from_date : '';
            $to_date = isset($request->to_date) && !empty($request->to_date) ? $request->to_date : '';
            $total_calls_count = new Call;
            $total_failed_calls_count = Call::where('status','<', $status);
            if($from_date != ''){
                $total_calls_count = $total_calls_count->whereDate('created_at' , '>=', $from_date);
                $total_failed_calls_count = $total_failed_calls_count->whereDate('created_at' , '>=', $from_date);
            }
            if($to_date != ''){
                $total_calls_count = $total_calls_count->whereDate('created_at' , '<=', $to_date);
                $total_failed_calls_count = $total_failed_calls_count->whereDate('created_at' , '<=', $to_date);
            }
            $total_calls_count = $total_calls_count->get()->count();
            $total_failed_calls_count = $total_failed_calls_count->get()->count();


            
            $total_quality_feedback = CallQualityFeedback::get()->count();
            $call_quality_rate_sum_count = CallQualityFeedback::sum('call_quality_rate');// get popular language id and count number 
            if(isset($total_quality_feedback) && !empty($total_quality_feedback)){
                $avg_call_quality = $call_quality_rate_sum_count / $total_quality_feedback;
            }else{
                $avg_call_quality =0;
            }
            $avg_call_wait_time = '00:00:00';
            $avg_interpreter_rating = '4.9';
            $data = [
                'total_calls_count' => $total_calls_count,
                'total_failed_calls_count' => $total_failed_calls_count,
                "avg_call_wait_time" => $avg_call_wait_time,
                "avg_call_quality" => $avg_call_quality
            ];

            // Language 
            $total_call_lang_count = Call::where('language_id', '!=', '')->get()->count(); // get total language count
            $popular_call_lang = Call::select('language_id')->selectRaw('count(language_id) as total_lang_call')->GroupBy('language_id')->orderBy('total_lang_call','DESC')->first(); // get popular language id and count number 
            $popular_lang_name = Language::where('id',$popular_call_lang['language_id'])->pluck('name')->first(); // get popular language name
            $popular_lang_count = $popular_call_lang['total_lang_call']; // get popularcount number 

            $language_highlights = [
                "total_call_lang_count" => $total_call_lang_count,
                "popular_lang_name" => $popular_lang_name,
                "popular_lang_count" => $popular_lang_count,
            ];
            
            // purpose

            $total_call_purpose_count = Call::where('purpose_id', '!=', '')->get()->count(); // get total purpose count
            $popular_call_purpose = Call::select('purpose_id')->selectRaw('count(purpose_id) as total_purpose_call')->GroupBy('purpose_id')->orderBy('total_purpose_call','DESC')->first(); // get popular purpose id and count number 

            $popular_purpose_name = Purpose::where('id',$popular_call_purpose['purpose_id'])->pluck('description')->first(); // get popular language name
            $popular_purpose_count = $popular_call_purpose['total_purpose_call']; // get popular count number 
            $purpose_highlights = [
                "total_call_purpose_count" => $total_call_purpose_count,
                "popular_purpose_name" => $popular_purpose_name,
                "popular_purpose_count" => $popular_purpose_count,
            ];



            //User Feedback & Summary

            $total_call_feedback_count = Call::get()->count(); // feedback total call count
            $total_feedback_received_count = CallFeedbackUser::get()->count(); // User Feedback Received Count
            $total_pending_feedback_count = $total_call_feedback_count - $total_feedback_received_count;
            $user_feedback_summary = [
                "total_call_feedback_count" => $total_call_feedback_count,
                "total_feedback_received_count" => $total_feedback_received_count,
                "total_pending_feedback_count" => $total_pending_feedback_count,
                "avg_interpreter_rating" => $avg_interpreter_rating,
            ];


            $data['highlights']['language']= $language_highlights;
            $data['highlights']['purpose']= $purpose_highlights;
            $data['user_feedback_summary']= $user_feedback_summary;
            $response_array = $this->helper->custom_response(true, $data, trans("translate.DASHBOARD_COUNT_DATA"));
            return response()->json($response_array, Response::HTTP_OK);

        }catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function getTrendscount(Request $request) {
        try {
            $validator = Validator::make($request->all(),[
                'from_date' => 'required',
                'to_date' => 'required',
            ]);

            if($validator->fails()){
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }



            $from_date = isset($request->from_date) && !empty($request->from_date) ? $request->from_date : '';
            $to_date = isset($request->to_date) && !empty($request->to_date) ? $request->to_date : '';
            $format_filter = isset($request->format_filter) && !empty($request->format_filter) ? $request->format_filter : '';
            $total_calls_count = '';
            $data = array();
            if($format_filter != ''){
                if($format_filter == 'year'){
                    $total_calls_count = DB::select("SELECT 
                                SUM(IF(month = 'Jan', total, 0)) AS 'Jan',
                                SUM(IF(month = 'Feb', total, 0)) AS 'Feb',
                                SUM(IF(month = 'Mar', total, 0)) AS 'Mar',
                                SUM(IF(month = 'Apr', total, 0)) AS 'Apr',
                                SUM(IF(month = 'May', total, 0)) AS 'May',
                                SUM(IF(month = 'Jun', total, 0)) AS 'Jun',
                                SUM(IF(month = 'Jul', total, 0)) AS 'Jul',
                                SUM(IF(month = 'Aug', total, 0)) AS 'Aug',
                                SUM(IF(month = 'Sep', total, 0)) AS 'Sep',
                                SUM(IF(month = 'Oct', total, 0)) AS 'Oct',
                                SUM(IF(month = 'Nov', total, 0)) AS 'Nov',
                                SUM(IF(month = 'Dec', total, 0)) AS 'Dec'
                                FROM (
                                    SELECT DATE_FORMAT(created_at, '%b') AS month, COUNT(*) as total
                                    FROM calls WHERE DATE_FORMAT(created_at,'%Y-%m-%d') >= '".$from_date."' AND DATE_FORMAT(created_at,'%Y-%m-%d') <= '".$to_date."'
                                    GROUP BY DATE_FORMAT(created_at, '%m-%Y')
                                ) as sub");        
                }
                if($format_filter == 'month'){
                    $total_calls_count = DB::select("SELECT 
                                SUM(IF(day = '01', total, 0)) AS '01',
                                SUM(IF(day = '02', total, 0)) AS '02',
                                SUM(IF(day = '03', total, 0)) AS '03',
                                SUM(IF(day = '04', total, 0)) AS '04',
                                SUM(IF(day = '05', total, 0)) AS '05',
                                SUM(IF(day = '06', total, 0)) AS '06',
                                SUM(IF(day = '07', total, 0)) AS '07',
                                SUM(IF(day = '08', total, 0)) AS '08',
                                SUM(IF(day = '09', total, 0)) AS '09',
                                SUM(IF(day = '10', total, 0)) AS '10',
                                SUM(IF(day = '11', total, 0)) AS '11',
                                SUM(IF(day = '12', total, 0)) AS '12',
                                SUM(IF(day = '13', total, 0)) AS '13',
                                SUM(IF(day = '14', total, 0)) AS '14',
                                SUM(IF(day = '15', total, 0)) AS '15',
                                SUM(IF(day = '16', total, 0)) AS '16',
                                SUM(IF(day = '17', total, 0)) AS '17',
                                SUM(IF(day = '18', total, 0)) AS '18',
                                SUM(IF(day = '19', total, 0)) AS '19',
                                SUM(IF(day = '20', total, 0)) AS '20',
                                SUM(IF(day = '21', total, 0)) AS '21',
                                SUM(IF(day = '22', total, 0)) AS '22',
                                SUM(IF(day = '23', total, 0)) AS '23',
                                SUM(IF(day = '24', total, 0)) AS '24',
                                SUM(IF(day = '25', total, 0)) AS '25',
                                SUM(IF(day = '26', total, 0)) AS '26',
                                SUM(IF(day = '27', total, 0)) AS '27',
                                SUM(IF(day = '28', total, 0)) AS '28',
                                SUM(IF(day = '29', total, 0)) AS '29',
                                SUM(IF(day = '30', total, 0)) AS '30',
                                SUM(IF(day = '31', total, 0)) AS '31'
                                FROM (
                            SELECT DATE_FORMAT(created_at, '%d') AS day, COUNT(*) as total
                            FROM calls WHERE DATE_FORMAT(created_at,'%Y-%m-%d') >= '".$from_date."' AND DATE_FORMAT(created_at,'%Y-%m-%d') <= '".$to_date."'
                            GROUP BY DATE_FORMAT(created_at,'%Y-%m-%d')) as sub");
                }

                if($format_filter == 'week'){
                    $total_calls_count = DB::select("SELECT 
                                SUM(IF(week = 'Monday', total, 0)) AS 'Monday',
                                SUM(IF(week = 'Tuesday', total, 0)) AS 'Tuesday',
                                SUM(IF(week = 'Wednesday', total, 0)) AS 'Wednesday',
                                SUM(IF(week = 'Thursday', total, 0)) AS 'Thursday',
                                SUM(IF(week = 'Friday', total, 0)) AS 'Friday',
                                SUM(IF(week = 'Saturday', total, 0)) AS 'Saturday',
                                SUM(IF(week = 'Sunday', total, 0)) AS 'Sunday'
                                FROM (
                            SELECT DATE_FORMAT(created_at, '%W') AS week, COUNT(*) as total
                            FROM calls WHERE DATE_FORMAT(created_at,'%Y-%m-%d') >= '".$from_date."' AND DATE_FORMAT(created_at,'%Y-%m-%d') <= '".$to_date."'
                            GROUP BY DATE_FORMAT(created_at,'%Y-%m-%d')) as sub");
                }


                foreach ($total_calls_count as $row_datas) {
                    foreach ($row_datas as $datas) {
                        $data[] = $datas;
                    }
                }
            }else{
                $total_calls_count = new Call;

                if($from_date != ''){
                    $total_calls_count = $total_calls_count->whereDate('created_at' , '>=', $from_date);
                }
                if($to_date != ''){
                    $total_calls_count = $total_calls_count->whereDate('created_at' , '<=', $to_date);
                }

                $total_calls_count = $total_calls_count->select('created_at',DB::raw("(COUNT(*)) as count"),DB::raw("DAY(created_at) as date"),DB::raw("DAYNAME(created_at) as dayname"),DB::raw("MONTHNAME(created_at) as monthname"),DB::raw("YEAR(created_at) as year"))->groupBy(DB::raw('Date(created_at)'))->orderBy('created_at','DESC')->get();

                $data = $total_calls_count;
            }
            if (isset($total_calls_count) && !empty($total_calls_count)) {
                
                $response_array = $this->helper->custom_response(true, $data, trans("translate.DASHBOARD_COUNT_DATA"));
                return response()->json($response_array, Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(false, array(), trans("translate.CALL_HISTORY_DATA_NOT_FOUND"));
                return response()->json($response_array, Response::HTTP_OK);
            }

        }catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }
    public function getTrendscount_old(Request $request) {
        try {
            $data = [];
            $status = 50; //  less than 50 status call is failed calls 
            $from_date = isset($request->from_date) && !empty($request->from_date) ? $request->from_date : '';
            $to_date = isset($request->to_date) && !empty($request->to_date) ? $request->to_date : '';
            $format_filter = isset($request->format_filter) && !empty($request->format_filter) ? $request->format_filter : '';
            $total_calls_count = new Call;
            //$total_calls_count = Call::selectRaw('count(created_at) as total_created_at_call');


            if($from_date != ''){
                $total_calls_count = $total_calls_count->whereDate('created_at' , '>=', $from_date);
            }
            if($to_date != ''){
                $total_calls_count = $total_calls_count->whereDate('created_at' , '<=', $to_date);
            }
            if($format_filter != ''){
                if($format_filter == 'year'){
                    $total_calls_count = $total_calls_count->select('created_at',DB::raw("(COUNT(*)) as count"),DB::raw("MONTHNAME(created_at) as monthname"),DB::raw("YEAR(created_at) as year"))->groupby('year','monthname');
                }
            }else{
                $total_calls_count = $total_calls_count->select('created_at',DB::raw("(COUNT(*)) as count"),DB::raw("DAY(created_at) as date"),DB::raw("DAYNAME(created_at) as dayname"),DB::raw("MONTHNAME(created_at) as monthname"),DB::raw("YEAR(created_at) as year"))->groupBy(DB::raw('Date(created_at)'));
            }



            $total_calls_count = $total_calls_count->orderBy('created_at','DESC')->get();
            $data['min_created_at_call_count'] = collect($total_calls_count)->min('count'); // 10
            $data['max_created_at_call_count'] = collect($total_calls_count)->max('count'); // 30

            $data['data'] = $total_calls_count;
            
            $response_array = $this->helper->custom_response(true, $data, trans("translate.DASHBOARD_COUNT_DATA"));
            return response()->json($response_array, Response::HTTP_OK);

        }catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function getLanguageTrendscount(Request $request) {
        try {
            $data = [];
            $status = 50; //  less than 50 status call is failed calls 
            $from_date = isset($request->from_date) && !empty($request->from_date) ? $request->from_date : '';
            $to_date = isset($request->to_date) && !empty($request->to_date) ? $request->to_date : '';
            $format_filter = isset($request->format_filter) && !empty($request->format_filter) ? $request->format_filter : '';
            $total_calls_count = Call::leftJoin('languages', 'calls.language_id', '=', 'languages.id');
            //$total_calls_count = Call::selectRaw('count(created_at) as total_created_at_call');


            if($from_date != ''){
                $total_calls_count = $total_calls_count->whereDate('created_at' , '>=', $from_date);
            }
            if($to_date != ''){
                $total_calls_count = $total_calls_count->whereDate('created_at' , '<=', $to_date);
            }
            if($format_filter != ''){
                if($format_filter == 'year'){

                    $total_calls_count = $total_calls_count->select('calls.language_id',DB::raw("(COUNT('calls.language_id')) as count"),'languages.name',DB::raw("MONTHNAME(calls.created_at) as monthname"),DB::raw("YEAR(calls.created_at) as year"))->groupBy('year','monthname','language_id')->orderBy('year','DESC')->orderBy('monthname','DESC')->get();
                }
            }else{
                $total_calls_count = $total_calls_count->select('calls.created_at',DB::raw('DATE_FORMAT(calls.created_at, "%d-%b-%Y") as create_date'),'calls.language_id',DB::raw("(COUNT('calls.language_id')) as count"),'languages.name')->groupBy(DB::raw('Date(calls.created_at)'),'language_id')->orderBy('created_at','DESC')->get();
            }

            $data['data'] = $total_calls_count;
            
            $response_array = $this->helper->custom_response(true, $data, trans("translate.DASHBOARD_COUNT_DATA"));
            return response()->json($response_array, Response::HTTP_OK);

        }catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function getLanguageTrendscount_data(Request $request) {
        try {
            $data = [];
            $status = 50; //  less than 50 status call is failed calls 
            $from_date = isset($request->from_date) && !empty($request->from_date) ? $request->from_date : '';
            $to_date = isset($request->to_date) && !empty($request->to_date) ? $request->to_date : '';
            $format_filter = isset($request->format_filter) && !empty($request->format_filter) ? $request->format_filter : '';
            $total_calls_count = Call::leftJoin('languages', 'calls.language_id', '=', 'languages.id');
            //$total_calls_count = Call::selectRaw('count(created_at) as total_created_at_call');

             $validator = Validator::make($request->all(), [
              'format_filter' => 'required'
              ]);

              if ($validator->fails()) {
              $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
              return response()->json($response_array, Response::HTTP_BAD_REQUEST);
              } 


            if($format_filter != ''){
                if($format_filter == 'year'){
                    $year = date("Y");
                    $total_calls_count = DB::select("SELECT languages.name,
                        SUM(if(MONTH(calls.created_at) = 1, 1,0)) as Jan,
                        SUM(if(MONTH(calls.created_at) = 2, 1,0)) as Feb,
                        SUM(if(MONTH(calls.created_at) = 3, 1,0)) as Mar,
                        SUM(if(MONTH(calls.created_at) = 4, 1,0)) as Apr,
                        SUM(if(MONTH(calls.created_at) = 5, 1,0)) as May,
                        SUM(if(MONTH(calls.created_at) = 6, 1,0)) as Jun,
                        SUM(if(MONTH(calls.created_at) = 7, 1,0)) as Jul,
                        SUM(if(MONTH(calls.created_at) = 8, 1,0)) as Aug,
                        SUM(if(MONTH(calls.created_at) = 9, 1,0)) as Sep,
                        SUM(if(MONTH(calls.created_at) = 10, 1,0)) as Oct,
                        SUM(if(MONTH(calls.created_at) = 11, 1,0)) as Nov,
                        SUM(if(MONTH(calls.created_at) = 12, 1,0)) as `Dec`
                    FROM calls LEFT JOIN languages ON languages.id = calls.language_id
                    WHERE YEAR(calls.created_at) = $year
                    GROUP by calls.language_id");
                }
                if($format_filter == 'month'){
                    $current_date = date("Y-m-d");
                    $previous_date = date("Y-m-d", strtotime("-1 month"));
                    $datediff = strtotime($current_date) - strtotime($previous_date); 
                    $month = date("m");
                    $total_calls_count = DB::select("SELECT languages.name,
                            SUM(if(day(calls.created_at) = 1, 1,0)) as '1',
                            SUM(if(day(calls.created_at) = 2, 1,0)) as '2',
                            SUM(if(day(calls.created_at) = 3, 1,0)) as '3',
                            SUM(if(day(calls.created_at) = 4, 1,0)) as '4',
                            SUM(if(day(calls.created_at) = 5, 1,0)) as '5',
                            SUM(if(day(calls.created_at) = 6, 1,0)) as '6',
                            SUM(if(day(calls.created_at) = 7, 1,0)) as '7',
                            SUM(if(day(calls.created_at) = 8, 1,0)) as '8',
                            SUM(if(day(calls.created_at) = 9, 1,0)) as '9',
                            SUM(if(day(calls.created_at) = 10, 1,0)) as '10',
                            SUM(if(day(calls.created_at) = 11, 1,0)) as '11',
                            SUM(if(day(calls.created_at) = 12, 1,0)) as '12',
                            SUM(if(day(calls.created_at) = 13, 1,0)) as '13',
                            SUM(if(day(calls.created_at) = 14, 1,0)) as '14',
                            SUM(if(day(calls.created_at) = 15, 1,0)) as '15',
                            SUM(if(day(calls.created_at) = 16, 1,0)) as '16',
                            SUM(if(day(calls.created_at) = 17, 1,0)) as '17',
                            SUM(if(day(calls.created_at) = 18, 1,0)) as '18',
                            SUM(if(day(calls.created_at) = 19, 1,0)) as '19',
                            SUM(if(day(calls.created_at) = 20, 1,0)) as '20',
                            SUM(if(day(calls.created_at) = 21, 1,0)) as '21',
                            SUM(if(day(calls.created_at) = 22, 1,0)) as '22',
                            SUM(if(day(calls.created_at) = 23, 1,0)) as '23',
                            SUM(if(day(calls.created_at) = 24, 1,0)) as '24',
                            SUM(if(day(calls.created_at) = 25, 1,0)) as '25',
                            SUM(if(day(calls.created_at) = 26, 1,0)) as '26',
                            SUM(if(day(calls.created_at) = 27, 1,0)) as '27',
                            SUM(if(day(calls.created_at) = 28, 1,0)) as '28',
                            SUM(if(day(calls.created_at) = 29, 1,0)) as '29',
                            SUM(if(day(calls.created_at) = 30, 1,0)) as '30',
                            SUM(if(day(calls.created_at) = 31, 1,0)) as '31'
                        FROM calls LEFT JOIN languages ON languages.id = calls.language_id
                        WHERE MONTH(calls.created_at) = $month
                        GROUP by calls.language_id");

                }
                if($format_filter == 'week'){
                    $current_date = date("Y-m-d");
                    $previous_week_date = date("Y-m-d", strtotime("-7 day"));
                    $total_calls_count = DB::select("SELECT languages.name,
                        SUM(if(DAYNAME(calls.created_at) = 'Monday', 1,0)) as 'Monday',
                        SUM(if(DAYNAME(calls.created_at) = 'Tuesday', 1,0)) as 'Tuesday',
                        SUM(if(DAYNAME(calls.created_at) = 'Wednesday', 1,0)) as 'Wednesday',
                        SUM(if(DAYNAME(calls.created_at) = 'Thursday', 1,0)) as 'Thursday',
                        SUM(if(DAYNAME(calls.created_at) = 'Friday', 1,0)) as 'Friday',
                        SUM(if(DAYNAME(calls.created_at) = 'Saturday', 1,0)) as 'Saturday',
                        SUM(if(DAYNAME(calls.created_at) = 'Sunday', 1,0)) as 'Sunday'
                    FROM calls LEFT JOIN languages ON languages.id = calls.language_id
                    WHERE DATE(calls.created_at) >= '".$previous_week_date."' AND DATE(calls.created_at) <= '".$current_date."'
                    GROUP by calls.language_id");
                }

                if($from_date != ''){
                    $current_year = date("Y");
                    $f_year = date("Y",strtotime($from_date));
                    if($f_year <= $current_year){
                        $total_calls_count = Call::leftJoin('languages', 'calls.language_id', '=', 'languages.id');
                        if($from_date != ''){
                            $total_calls_count = $total_calls_count->whereDate('calls.created_at' , '>=', $from_date);
                        }
                        if($to_date != ''){
                            $total_calls_count = $total_calls_count->whereDate('calls.created_at' , '<=', $to_date);
                        }
                        $total_calls_count = $total_calls_count->select('calls.language_id',DB::raw("(COUNT('calls.language_id')) as count"),'languages.name',DB::raw("MONTH(calls.created_at) as monthname"),DB::raw("YEAR(calls.created_at) as year"))->groupBy('year','monthname','language_id')->orderBy('year','DESC')->get();
                        $data = array();
                        $languages = array("English","Hindi","Kannada","Tamil","Telugu");
                        for ($i=1; $i <= 12; $i++) { 
                            foreach ($total_calls_count as $key => $value) {
                                if(in_array($value['name'], $languages)){ 
                                    if($i == $value['monthname']){
                                        $data[$i] = 'data';
                                    }else{
                                        $data[$i] = '0';
                                    }

                                }
                            }
                        }
                              //echo "<pre>"; print_r($data);exit();
                    }
                }
            }
            
            $response_array = $this->helper->custom_response(true, $total_calls_count, trans("translate.DASHBOARD_COUNT_DATA"));
            return response()->json($response_array, Response::HTTP_OK);

        }catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }
    
}