<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helper\Helper;
use App\Models\Language;
use JWTAuth;
use JWTFactory;
use Config;
use Log;
use DB;

class LanguageController extends Controller {

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
    public function index(Request $request)
    {
        try {
            
            $language = Language::getLanguageData()->get();
            if (!$language->isEmpty()) {
                $message = trans("translate.LANGUAGE_LIST_RECORD");
                $response_array = $this->helper->custom_response(true, $language, $message);
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
            Log::info('Create Language: Params:'.json_encode($request->all()));
            $validator = validator::make($request->all(),[
                'name' => 'required',
            ]);

            if($validator->fails()){
                $response_array = $this->helper->custom_response(false,array(),implode(",", $validator->messages()->all()));
                return response()->json($response_array,Response::HTTP_BAD_REQUEST);
            }

            $language_data = [
                'name' => (isset($request->name) && !empty($request->name)) ? $request->name : '',
                'is_active' => (isset($request->active) && !empty($request->active)) ? $request->active : 0,
            ];
            Log::info('Create Language: Params:'.json_encode($request->all()));
            $language = Language::create($language_data);
            if(isset($language) && !empty($language)){
                $response_array = $this->helper->custom_response(true,$language,trans("translate.LANGUAGE_ADDED_SUCCESSFULLY"));
                return response()->json($response_array,Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(false,array(),trans("translate.LANGUAGE_ADDED_FAILED"));
                return response()->json($response_array,Response::HTTP_BAD_REQUEST);
            }
        }catch(\Exception $ex){
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(Request $request,$id){
        /*if(empty($id)){
            $response_array = $this->helper->custom_response(false,array(),trans("translate.ID_NOT_FOUND"));
        }*/
        try{
            Log::info('Update Language: Params:'.json_encode($request->all()));

            $validator = validator::make($request->all(),[
                'name' => 'required'
            ]);

            if($validator->fails()){
                $response_array = $this->helper->custom_response(false,array(),implode(",", $validator->messages()->all()));
                return response()->json($response_array,Response::HTTP_BAD_REQUEST);
            }

            $language = Language::find($id);
            $language->name = (isset($request->name) && !empty($request->name)) ? $request->name : '';
            $language->is_active = (isset($request->active) && !empty($request->active)) ? $request->active : '';
            $language->save();
            Log::info('Update Language: Params:'.json_encode($request->all()));
            if(isset($language) && !empty($language)){
                $response_array = $this->helper->custom_response(true,$language,trans("translate.LANGUAGE_UPDATED_SUCCESSFULLY"));
                return response()->json($response_array,Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(false,array(),trans("translate.LANGUAGE_UPDATED_FAILED"));
                return response()->json($response_array,Response::HTTP_BAD_REQUEST);
            }

        }catch(\Exception $ex){
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function destroy($id){
        try{
            Log::info('Delete Language: Params:'.json_encode($id));
             if(!empty($id)){
                //$disposition = Disposition::find($id);
                $language = Language::where('id',$id)->delete();
                Log::info('Delete Language: Params:'.json_encode($language));
                if(isset($language) && !empty($language)){
                    $response_array = $this->helper->custom_response(true,$language,trans("translate.LANGUAGE_DELETED_SUCCESSFULLY"));
                    return response()->json($response_array,Response::HTTP_OK);
                }else{
                    $response_array = $this->helper->custom_response(false,array(),trans("translate.LANGUAGE_DELETED_FAILED"));
                    return response()->json($response_array,Response::HTTP_BAD_REQUEST);
                }
             }else{
                $response_array = $this->helper->custom_response(false,array(),trans("translate.LANGUAGE_ID_NOT_FOUND"));
                return response()->json($response_array,Response::HTTP_BAD_REQUEST);
             }   
        }catch(\Exception $ex){
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);   
        }
    }
}
