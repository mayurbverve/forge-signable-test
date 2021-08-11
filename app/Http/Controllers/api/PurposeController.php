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
use JWTAuth;
use JWTFactory;
use Config;
use Log;
use DB;

class PurposeController extends Controller {

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
            /*$current_user = $this->helper->getLoginUser();
            if($current_user->role == 'supplier_admin'){
                
            }*/
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

    public function store(Request $request){
        try {
            Log::info('Create purposes: Params: ' . json_encode($request->all()));
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
            Log::info('Create purpose: Params: ' . json_encode($purpose_data));
            $purpose = Purpose::create($purpose_data);

            if (isset($purpose) && !empty($purpose)) {
                $message = trans("translate.PURPOSE_ADDED_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $purpose, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(false, array(), trans("translate.PURPOSE_ADDED_FAILED"));
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
            Log::info('Updated purposes: Params: ' . json_encode($request->all()));
            $validator = Validator::make($request->all(),[
                'name' => 'required'
            ]);

            if($validator->fails()){
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }
             


            $purpose = Purpose::find($id);
            $purpose->name = (isset($request->name) && !empty($request->name)) ? $request->name : '';
            $purpose->description = (isset($request->description) && !empty($request->description)) ? $request->description : '';
            $purpose->save();
            Log::info('Updated purpose: Params: ' . json_encode($purpose));
            if (isset($purpose) && !empty($purpose)) {
                $message = trans("translate.PURPOSE_UPDATED_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $purpose, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(true, array(), trans("translate.PURPOSE_UPDATED_FAILED"));
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
            Log::info('Delete purposes: Params: ' . json_encode($id));

            $purpose = Purpose::where('id',$id)->delete();
            Log::info('Delete purpose: Params: ' . json_encode($purpose));
            if (isset($purpose) && !empty($purpose)) {
                $message = trans("translate.PURPOSE_DELETED_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $purpose, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(true, array(), trans("translate.PURPOSE_DELETED_FAILED"));
                return response()->json($response_array, Response::HTTP_OK);
            }
            
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    } 

}
