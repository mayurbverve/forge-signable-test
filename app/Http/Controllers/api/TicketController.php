<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helper\Helper;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketAction;
use App\Models\TicketActionAttachment;
use JWTAuth;
use JWTFactory;
use Config;
use Log;
use DB;
use Carbon\Carbon;

class TicketController extends Controller {

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        try {
            $current_user = $this->helper->getLoginUser();
            $role = Auth::user()->getRoleByRoleUserID();
            $current_user->role = $role;
            $current_user_profile_id = $current_user->user_profile->id;
            $current_user_role_id = $current_user->role->role_id;

            $tickets = Ticket::getTicketData();
            if (in_array($current_user_role_id, $this->supplier_roles) || in_array($current_user_role_id, $this->consumer_roles)) {
                $tickets = $tickets->where('from_user_profile_id',$current_user_profile_id);
            }else{
                /*$tickets->whereHas('ticket_action', function($query) use($current_user_profile_id) {
                    $query->orWhere('action_user_profile_id', $current_user_profile_id);
                });*/
                $tickets = $tickets->Where('assign_user_profile_id',$current_user_profile_id);
            }
            $tickets = $tickets->get();
            if (!$tickets->isEmpty()) {
                //echo "<pre>"; print_r($tickets);exit();
                foreach ($tickets as $key => $value) {
                    $status = '';
                    if($value['status'] == 1){
                        $status = 'Open';
                    }
                    if($value['status'] == 2){
                        $status = 'Assigned';
                    }
                    if($value['status'] == 3){
                        $status = 'Resolved';
                    }
                    if($value['status'] == 4){
                        $status = 'Reopen';
                    }
                    if($value['status'] == 5){
                        $status = 'Closed';
                    }
                    if($value['status'] == 6){
                        $status = 'Rejected';
                    }
                    $tickets[$key]->status = $status;
                    $tickets[$key]->attachment = TicketAttachment::where('ticket_id',$value->id)->get();
                    $public_path = url('/');
                    $url_path = '';
                    foreach ($tickets[$key]->attachment  as $ticket_attachment_key=> $ticket_attachment_value) {
                        $url_path = $public_path .'/'.$ticket_attachment_value['attachment_path'];
                        $tickets[$key]->attachment[$ticket_attachment_key]->attachment_path_url = $url_path;
                    }

                    $tickets[$key]->ticket_action = TicketAction::where('ticket_id',$value->id)->get();

                    $tickets[$key]->ticket_action_attachment = TicketActionAttachment::where('ticket_id',$value->id)->get();
                    foreach ($tickets[$key]->ticket_action_attachment  as $ticket_action_key => $ticket_action_value) {
                        $url_path = $public_path .'/'.$ticket_action_value['attachment_path'];
                        $tickets[$key]->ticket_action_attachment[$ticket_action_key]->attachment_path_url = $url_path;
                    }
                }
                $message = trans("translate.TICKET_LIST_RECORD");
                $response_array = $this->helper->custom_response(true, $tickets, $message);
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
    /*public function index(){
        try {
            $current_user = $this->helper->getLoginUser();
            $role = Auth::user()->getRoleByRoleUserID();
            $current_user->role = $role;
            $current_user_profile_id = $current_user->user_profile->id;
            $current_user_role_id = $current_user->role->role_id;

            $tickets = Ticket::getTicketData();
            if (in_array($current_user_role_id, $this->supplier_roles) || in_array($current_user_role_id, $this->consumer_roles)) {
                $tickets = $tickets->where('from_user_profile_id',$current_user_profile_id);
            }else{
                $tickets = $tickets->where('assign_user_profile_id',$current_user_profile_id);
            }
            $tickets = $tickets->get();
            if (!$tickets->isEmpty()) {
                //echo "<pre>"; print_r($tickets);exit();
                foreach ($tickets as $key => $value) {
                    $status = '';
                    if($value['status'] == 1){
                        $status = 'Open';
                    }
                    if($value['status'] == 2){
                        $status = 'Re-open';
                    }
                    if($value['status'] == 3){
                        $status = 'Forworded';
                    }
                    if($value['status'] == 4){
                        $status = 'Resolved';
                    }
                    $tickets[$key]->status = $status;
                    $tickets[$key]->attachment = TicketAttachment::where('ticket_id',$value->id)->get();
                    $tickets[$key]->ticket_action = TicketAction::where('ticket_id',$value->id)->get();
                    $tickets[$key]->ticket_action_attachment = TicketActionAttachment::where('ticket_id',$value->id)->get();
                }
                $message = trans("translate.TICKET_LIST_RECORD");
                $response_array = $this->helper->custom_response(true, $tickets, $message);
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
    }*/ 

    public function details($id){
        try {
            $tickets = Ticket::getTicketData()->where('id',$id)->get();
            if (!$tickets->isEmpty()) {
                foreach ($tickets as $key => $value) {
                    $status = '';
                    if($value['status'] == 1){
                        $status = 'Open';
                    }
                    if($value['status'] == 2){
                        $status = 'Assigned';
                    }
                    if($value['status'] == 3){
                        $status = 'Resolved';
                    }
                    if($value['status'] == 4){
                        $status = 'Reopen';
                    }
                    if($value['status'] == 5){
                        $status = 'Closed';
                    }
                    if($value['status'] == 6){
                        $status = 'Rejected';
                    }
                    $tickets[$key]->status = $status;
                    $tickets[$key]->attachment = TicketAttachment::where('ticket_id',$value->id)->get();
                    $public_path = url('/');
                    $url_path = '';
                    foreach ($tickets[$key]->attachment  as $ticket_attachment_key=> $ticket_attachment_value) {
                        $url_path = $public_path .'/'.$ticket_attachment_value['attachment_path'];
                        $tickets[$key]->attachment[$ticket_attachment_key]->attachment_path_url = $url_path;
                    }

                    $tickets[$key]->ticket_action = TicketAction::where('ticket_id',$value->id)->get();

                    $tickets[$key]->ticket_action_attachment = TicketActionAttachment::where('ticket_id',$value->id)->get();
                    foreach ($tickets[$key]->ticket_action_attachment  as $ticket_action_key => $ticket_action_value) {
                        $url_path = $public_path .'/'.$ticket_action_value['attachment_path'];
                        $tickets[$key]->ticket_action_attachment[$ticket_action_key]->attachment_path_url = $url_path;
                    }
                }
                $message = trans("translate.TICKET_LIST_RECORD");
                $response_array = $this->helper->custom_response(true, $tickets, $message);
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
            $current_user = $this->helper->getLoginUser();
            $role = Auth::user()->getRoleByRoleUserID();
            $current_user->role = $role;
            $current_user_profile_id = $current_user->user_profile->id;
            $current_user_role_id = $current_user->role->role_id;
            Log::info('Create Ticket: Params: ' . json_encode($request->all()));
            $validator = Validator::make($request->all(),[
                'subject' => 'required',
                'message' => 'required'
            ]);

            if($validator->fails()){
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }
            $assign_user_profile_id = ''; 
            $assign_role_id = ''; 
            if (in_array($current_user_role_id, $this->supplier_roles)) {
                $assign_user_profile_id = 109; 
                $assign_role_id = 1;
            }

            if (in_array($current_user_role_id, $this->consumer_roles)) {

                $assign_user_profile_id = 57; 
                $assign_role_id = 4; 
            }

            $ticket_data = [
                'category' => (isset($request->category) && !empty($request->category)) ? $request->category : '',
                'subject' => (isset($request->subject) && !empty($request->subject)) ? $request->subject : '',
                'message' => (isset($request->message) && !empty($request->message)) ? $request->message : '',
                'assign_user_profile_id' => (isset($request->assign_user_profile_id) && !empty($request->assign_user_profile_id)) ? $request->assign_user_profile_id : $assign_user_profile_id,
                'assign_role_id' => (isset($request->assign_role_id) && !empty($request->assign_role_id)) ? $request->assign_role_id : $assign_role_id,
                'from_user_profile_id' => $current_user_profile_id,
                'from_user_role_id' => $current_user_role_id
            ];   
            Log::info('Create Ticket: Params: ' . json_encode($ticket_data));
            $ticket = Ticket::create($ticket_data);

            $ticket_attachment = [];
            if ($request->hasfile('attachment')) 
            {
                foreach ($request->file('attachment') AS $key => $attachment) 
                {
                    $attachment_data = $this->helper->upload_attachment($attachment, 'ticket');
                    $attachment_obj = new TicketAttachment();
                    $attachment_obj->attachment_name = $attachment_data['attachment_name'];
                    $attachment_obj->attachment_path = $attachment_data['attachment_path'];
                    $attachment_obj->attachment_type = 1; // 1 - image 
                    $attachment_obj->ticket_id = $ticket->id;
                    $attachment_obj->created_at = Carbon::now();
                    $attachment_obj->save();
                }
            }

            if (isset($ticket) && !empty($ticket)) {
                $message = trans("translate.TICKET_ADDED_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $ticket, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(false, array(), trans("translate.TICKET_ADDED_FAILED"));
                return response()->json($response_array, Response::HTTP_OK);
            }
            
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    } 

    public function update(Request $request,$id){
        try {
            $current_user = $this->helper->getLoginUser();
            $role = Auth::user()->getRoleByRoleUserID();
            $current_user->role = $role;
            $current_user_profile_id = $current_user->user_profile->id;
            $current_user_role_id = $current_user->role->role_id;
            
            Log::info('Updated Ticket: Params: ' . json_encode($request->all()));
            $validator = Validator::make($request->all(),[
                'subject' => 'required',
                'message' => 'required'
            ]);

            if($validator->fails()){
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }
             
            $assign_user_profile_id = ''; 
            $assign_role_id = ''; 
            if (in_array($current_user_role_id, $this->supplier_roles)) {
                $assign_user_profile_id = 109; 
                $assign_role_id = 1;
            }

            if (in_array($current_user_role_id, $this->consumer_roles)) {

                $assign_user_profile_id = 57; 
                $assign_role_id = 4; 
            }

            $ticket = Ticket::find($id);
            $ticket->subject = (isset($request->subject) && !empty($request->subject)) ? $request->subject : '';
            $ticket->message = (isset($request->message) && !empty($request->message)) ? $request->message : '';
            $ticket->category = (isset($request->category) && !empty($request->category)) ? $request->category : '';
            $ticket->assign_user_profile_id = (isset($request->assign_user_profile_id) && !empty($request->assign_user_profile_id)) ? $request->assign_user_profile_id : $assign_user_profile_id;
            $ticket->assign_role_id = (isset($request->assign_role_id) && !empty($request->assign_role_id)) ? $request->assign_role_id : $assign_role_id;
            $ticket->from_user_profile_id = $current_user_profile_id;
            $ticket->from_user_role_id = $current_user_role_id;
            $ticket->save();

            $ticket_attachment = [];
            if ($request->hasfile('attachment')) 
            {
                if(!empty($ticket->id)){
                    $ticket_delete = TicketAttachment::where('ticket_id',$ticket->id)->delete();
                }
                foreach ($request->file('attachment') AS $key => $attachment) 
                {
                    $attachment_data = $this->helper->upload_attachment($attachment, 'ticket');
                    $attachment_obj = new TicketAttachment();
                    $attachment_obj->attachment_name = $attachment_data['attachment_name'];
                    $attachment_obj->attachment_path = $attachment_data['attachment_path'];
                    $attachment_obj->attachment_type = 1; // 1 - image 
                    $attachment_obj->ticket_id = $ticket->id;
                    $attachment_obj->created_at = Carbon::now();
                    $attachment_obj->save();
                }
            }

            Log::info('Updated Ticket: Params: ' . json_encode($ticket));
            if (isset($ticket) && !empty($ticket)) {
                $message = trans("translate.TICKET_UPDATED_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $ticket, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(true, array(), trans("translate.TICKET_UPDATED_FAILED"));
                return response()->json($response_array, Response::HTTP_OK);
            }
            
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

    public function destroy($id){
        try {
            Log::info('Delete Ticket: Params: ' . json_encode($id));
            if(!empty($id)){
                $ticket_delete = TicketAttachment::where('ticket_id',$id)->delete();
            }   
            $ticket = Ticket::where('id',$id)->delete();
            Log::info('Delete ticket: Params: ' . json_encode($ticket));
            if (isset($ticket) && !empty($ticket)) {
                $message = trans("translate.TICKET_DELETED_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $ticket, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(true, array(), trans("translate.TICKET_DELETED_FAILED"));
                return response()->json($response_array, Response::HTTP_OK);
            }
            
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    } 


    public function ticket_on_action(Request $request){
        try {
            $current_user = $this->helper->getLoginUser();
            $role = Auth::user()->getRoleByRoleUserID();
            $current_user->role = $role;
            $current_user_profile_id = $current_user->user_profile->id;
            $current_user_role_id = $current_user->role->role_id;
            Log::info('Create Ticket: Params: ' . json_encode($request->all()));
            $validator = Validator::make($request->all(),[
                'ticket_id' => 'required',
                'action_type' => 'required',
                'action' => 'required',
                'status' => 'required'
            ]);

            if($validator->fails()){
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }
            $ticket_data = [
                'ticket_id' => (isset($request->ticket_id) && !empty($request->ticket_id)) ? $request->ticket_id : '',
                'action_type' => (isset($request->action_type) && !empty($request->action_type)) ? $request->action_type : '',
                'action' => (isset($request->action) && !empty($request->action)) ? $request->action : '',
                'action_user_profile_id' => (isset($request->action_user_profile_id) && !empty($request->action_user_profile_id)) ? $request->action_user_profile_id : $current_user_profile_id,
                'action_user_role_id' => (isset($request->action_user_role_id) && !empty($request->action_user_role_id)) ? $request->action_user_role_id : $current_user_role_id
            ];   
            Log::info('Create Ticket Action: Params: ' . json_encode($ticket_data));
            $ticket_action = TicketAction::create($ticket_data);

            $ticket = Ticket::where('id',$request->ticket_id)->update(['status' => $request->status]);

            $ticket_attachment = [];
            if ($request->hasfile('attachment')) 
            {
                foreach ($request->file('attachment') AS $key => $attachment) 
                {
                    $attachment__action_data = $this->helper->upload_attachment($attachment, 'ticket_action');
                    $attachment_obj = new TicketActionAttachment();
                    $attachment_obj->attachment_name = $attachment__action_data['attachment_name'];
                    $attachment_obj->attachment_path = $attachment__action_data['attachment_path'];
                    $attachment_obj->attachment_type = 1; // 1 - image 
                    $attachment_obj->ticket_id = $request->ticket_id;
                    $attachment_obj->action_id = $ticket_action->id;
                    $attachment_obj->created_at = Carbon::now();
                    $attachment_obj->save();
                }
            }

            if (isset($ticket_action) && !empty($ticket_action)) {
                $message = trans("translate.TICKET_ACTION_ADDED_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $ticket_action, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(false, array(), trans("translate.TICKET_ACTION_ADDED_FAILED"));
                return response()->json($response_array, Response::HTTP_OK);
            }
            
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }


    public function ticket_on_action_update(Request $request,$id){
        try {
            $current_user = $this->helper->getLoginUser();
            $role = Auth::user()->getRoleByRoleUserID();
            $current_user->role = $role;
            $current_user_profile_id = $current_user->user_profile->id;
            $current_user_role_id = $current_user->role->role_id;
            
            Log::info('Updated Ticket: Params: ' . json_encode($request->all()));
            $validator = Validator::make($request->all(),[
                'ticket_id' => 'required',
                'action_type' => 'required',
                'action' => 'required',
                'status' => 'required'
            ]);

            if($validator->fails()){
                $response_array = $this->helper->custom_response(false, array(), implode(",", $validator->messages()->all()));
                return response()->json($response_array, Response::HTTP_BAD_REQUEST);
            }
             


            $ticket_action = TicketAction::find($id);
            $ticket_action->ticket_id = (isset($request->ticket_id) && !empty($request->ticket_id)) ? $request->ticket_id : '';
            $ticket_action->action_type = (isset($request->action_type) && !empty($request->action_type)) ? $request->action_type : '';
            $ticket_action->action = (isset($request->action) && !empty($request->action)) ? $request->action : '';
            $ticket_action->action_user_profile_id = (isset($request->action_user_profile_id) && !empty($request->action_user_profile_id)) ? $request->action_user_profile_id : $current_user_profile_id;
            $ticket_action->action_user_role_id = (isset($request->action_user_role_id) && !empty($request->action_user_role_id)) ? $request->action_user_role_id : $current_user_role_id;
            $ticket_action->save();

            $ticket = Ticket::where('id',$request->ticket_id)->update(['status' => $request->status]);

            $ticket_attachment = [];
            if ($request->hasfile('attachment')) 
            {
                foreach ($request->file('attachment') AS $key => $attachment) 
                {
                    $attachment__action_data = $this->helper->upload_attachment($attachment, 'ticket_action');
                    $attachment_obj = new TicketActionAttachment();
                    $attachment_obj->attachment_name = $attachment__action_data['attachment_name'];
                    $attachment_obj->attachment_path = $attachment__action_data['attachment_path'];
                    $attachment_obj->attachment_type = 1; // 1 - image 
                    $attachment_obj->ticket_id = $request->ticket_id;
                    $attachment_obj->action_id = $ticket_action->id;
                    $attachment_obj->created_at = Carbon::now();
                    $attachment_obj->save();
                }
            }

            Log::info('Updated Ticket: Params: ' . json_encode($ticket));
            if (isset($ticket_action) && !empty($ticket_action)) {
                $message = trans("translate.TICKET_ACTION_UPDATED_SUCCESSFULLY");
                $response_array = $this->helper->custom_response(true, $ticket_action, $message);
                return response()->json($response_array, Response::HTTP_OK);
            }else{
                $response_array = $this->helper->custom_response(true, array(), trans("translate.TICKET_ACTON_UPDATED_FAILED"));
                return response()->json($response_array, Response::HTTP_OK);
            }
            
        } catch (\Exception $ex) {
            $response_array = $this->helper->sendError($ex->getMessage(), 500);
            Log::info('Error captured: ' . json_encode($response_array));
            return response()->json($response_array, Response::HTTP_BAD_REQUEST);
        }
    }

}   
