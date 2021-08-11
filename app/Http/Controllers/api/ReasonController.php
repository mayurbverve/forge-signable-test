<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helper\Helper;
use App\Models\Reason;
use JWTAuth;
use JWTFactory;
use Config;
use Log;
use DB;

class ReasonController extends Controller {

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
    public function index()
    {

        try {
            
            $reasons = Reason::getReasonsData()->get();
            
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

    public function store(Request $request){
        try {
            Log::info('Create Reason: Params: ' . json_encode($request->all()));
            $validator = Validator::make($request->all(),[
                'name' => 'required'
            ]);

            if($validator->fails()){
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }
            $purpose_data = [
                'name' => (isset($request->name) && !empty($request->name)) ? $request->name : '',
                'description' => (isset($request->description) && !empty($request->description)) ? $request->description : '',
            ];   
            Log::info('Create Reason: Params: ' . json_encode($purpose_data));
            $reason = Reason::create($purpose_data);

            if (isset($reason) && !empty($reason)) {
                $message = trans("translate.REASON_ADDED_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $reason, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(false, array(), trans("translate.REASON_ADDED_FAILED"));
                return response()->json($response_array, Response::HTTP_OK);
            }
            
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    } 

    public function update(Request $request,$id)
    {
        try {
            Log::info('Updated Reason: Params: ' . json_encode($request->all()));
            $validator = Validator::make($request->all(),[
                'name' => 'required'
            ]);

            if($validator->fails()){
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }
             


            $reason = Reason::find($id);
            $reason->name = (isset($request->name) && !empty($request->name)) ? $request->name : '';
            $reason->description = (isset($request->description) && !empty($request->description)) ? $request->description : '';
            $reason->save();
            Log::info('Updated Reason: Params: ' . json_encode($reason));
            if (isset($reason) && !empty($reason)) {
                $message = trans("translate.REASON_UPDATED_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $reason, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(true, array(), trans("translate.REASON_UPDATED_FAILED"));
                return response()->json($response_array, Response::HTTP_OK);
            }
            
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function destroy($id)
    {
        try {
            Log::info('Delete Reason: Params: ' . json_encode($id));

            $reason = Reason::where('id',$id)->delete();
            Log::info('Delete Reason: Params: ' . json_encode($reason));
            if (isset($reason) && !empty($reason)) {
                $message = trans("translate.REASON_DELETED_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $reason, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(true, array(), trans("translate.REASON_DELETED_FAILED"));
                return response()->json($response_array, Response::HTTP_OK);
            }
            
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    } 

}
