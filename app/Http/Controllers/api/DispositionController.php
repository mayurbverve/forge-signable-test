<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helper\Helper;
use App\Models\Disposition;
use JWTAuth;
use JWTFactory;
use Config;
use Log;
use DB;

class DispositionController extends Controller {

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
            
            $disposition = Disposition::getDispositionData();
            if(isset($request->type) && !empty($request->type)){
                $disposition = $disposition->where('type',$request->type);    
            }
            $disposition = $disposition->get();
            if (!$disposition->isEmpty()) {
                $message = trans("translate.DISPOSITION_LIST_RECORD");
                $response_array = $this->helper->custom_response(true, $disposition, $message);
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
            Log::info('Create Disposition: Params:'.json_encode($request->all()));
            $validator = validator::make($request->all(),[
                'name' => 'required',
            ]);

            if($validator->fails()){
                $response_array = $this->helper->custom_response(false,array(),implode(",", $validator->messages()->all()));
                return response()->json($response_array,Response::HTTP_BAD_REQUEST);
            }

            $disposition_data = [
                'name' => (isset($request->name) && !empty($request->name)) ? $request->name : '',
                'description' => (isset($request->description) && !empty($request->description)) ? $request->description : '',
                'type' => (isset($request->type) && !empty($request->type)) ? $request->type : '',
            ];
            Log::info('Create Disposition: Params:'.json_encode($request->all()));
            $disposition = Disposition::create($disposition_data);
            if(isset($disposition) && !empty($disposition)){
                $response_array = $this->helper->custom_response(true,$disposition,trans("translate.DISPOSITION_ADDED_SUCCESSFULLY"));
                return response()->json($response_array,Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(false,array(),trans("translate.DISPOSITION_ADDED_FAILED"));
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
            Log::info('Update Disposition: Params:'.json_encode($request->all()));

            $validator = validator::make($request->all(),[
                'name' => 'required'
            ]);

            if($validator->fails()){
                $response_array = $this->helper->custom_response(false,array(),implode(",", $validator->messages()->all()));
                return response()->json($response_array,Response::HTTP_BAD_REQUEST);
            }

            $disposition = Disposition::find($id);
            $disposition->name = (isset($request->name) && !empty($request->name)) ? $request->name : '';
            $disposition->description = (isset($request->description) && !empty($request->description)) ? $request->description : '';
            $disposition->type = (isset($request->type) && !empty($request->type)) ? $request->type : '';
            $disposition->save();
            Log::info('Update Disposition: Params:'.json_encode($request->all()));
            if(isset($disposition) && !empty($disposition)){
                $response_array = $this->helper->custom_response(true,$disposition,trans("translate.DISPOSITION_UPDATED_SUCCESSFULLY"));
                return response()->json($response_array,Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(false,array(),trans("translate.DISPOSITION_UPDATED_FAILED"));
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
            Log::info('Delete Disposition: Params:'.json_encode($id));
             if(!empty($id)){
                //$disposition = Disposition::find($id);
                $disposition = Disposition::where('id',$id)->delete();
                Log::info('Delete Disposition: Params:'.json_encode($disposition
                ));
                if(isset($disposition) && !empty($disposition)){
                    $response_array = $this->helper->custom_response(true,$disposition,trans("translate.DISPOSITION_DELETED_SUCCESSFULLY"));
                    return response()->json($response_array,Response::HTTP_OK);
                }else{
                    $response_array = $this->helper->custom_response(false,array(),trans("translate.DISPOSITION_DELETED_FAILED"));
                    return response()->json($response_array,Response::HTTP_BAD_REQUEST);
                }
             }else{
                $response_array = $this->helper->custom_response(false,array(),trans("translate.DISPOSITION_ID_NOT_FOUND"));
                return response()->json($response_array,Response::HTTP_BAD_REQUEST);
             }   
        }catch(\Exception $ex){
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);   
        }
    }
}
