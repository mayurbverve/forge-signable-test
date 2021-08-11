<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TicketAttachment;

class Ticket extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;
    use SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tickets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['category', 'assign_user_profile_id','assign_role_id','subject','message','from_user_profile_id','from_user_role_id'];

    public function attachment(){
        return $this->hasMany('App\Models\TicketAttachment', 'id');
    }
    public function ticket_action(){
        return $this->hasMany('App\Models\TicketAction', 'id');
    }
    public function ticket_action_attachment(){
        return $this->hasMany('App\Models\TicketActionAttachment', 'id');
    }

    public function from_user_profile() {
        return $this->belongsTo('App\Models\UserProfile', 'from_user_profile_id');
    }
    public function from_user_role() {
        return $this->belongsTo('App\Models\Role', 'from_user_role_id');
    }
    public function assign_user_profile() {
        return $this->belongsTo('App\Models\UserProfile', 'assign_user_profile_id');
    }
    public function assign_user_role() {
        return $this->belongsTo('App\Models\Role', 'assign_role_id');
    }
    /*public static function getTicketData() {
        $ticket = Ticket::select('id','category', 'assign_user_profile_id','assign_role_id','subject','message','from_user_profile_id','from_user_role_id','status')
            ->with(['from_user_profile' => function($query) {
                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth');
                },
                'from_user_role' => function ($query) {
                    $query->select('id', 'name', 'display_name');
                },
                'attachment' => function ($query) {
                    $query->select('id', 'ticket_id','attachment_path', 'attachment_type', 'attachment_name');
                },
                'ticket_action' => function ($query) {
                    $query->select('id','ticket_id', 'action_type','action','action_user_profile_id','action_user_role_id');
                },
                'ticket_action_attachment' => function ($query) {
                    $query->select('id','ticket_id','action_id','attachment_path', 'attachment_type', 'attachment_name');
                }
            ]);
        return $ticket; 
    }*/



    public static function getTicketData() {
        $ticket = Ticket::select('id','category', 'assign_user_profile_id','assign_role_id','subject','message','from_user_profile_id','from_user_role_id','status','created_at','updated_at')
            ->with(['from_user_profile' => function($query) {
                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')->with(['user' => function ($query) {
                            $query->select('id', 'email');
                        }]);
                },
                'from_user_role' => function ($query) {
                    $query->select('id', 'name', 'display_name');
                },'assign_user_profile' => function($query) {
                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')->with(['user' => function ($query) {
                            $query->select('id', 'email');
                        }]);
                },
                'assign_user_role' => function ($query) {
                    $query->select('id', 'name', 'display_name');
                }
            ]);
        return $ticket; 
    }
    
}
