<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallDetail extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'call_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['call_id', 'user_profile_id', 'user_role_id', 'start_time', 'end_time', 'duration','band_width','resolution','is_called_failed','call_detail','reason_id','status', 'feedback'];

    
    
    public function user_profile() {
        return $this->belongsTo('App\Models\UserProfile', 'user_profile_id');
    }

    public function user_role() {
        return $this->belongsTo('App\Models\Role', 'user_role_id');
    }
    
    public function status() {
        return $this->belongsTo('App\Models\CallStatus', 'status');
    }

    public function calls() {
        return $this->belongsTo('App\Models\Call', 'call_id');
    }
    
    public function reason() {
        return $this->belongsTo('App\Models\Reason', 'reason_id');
    }
    
    
    
    public static function getCallDetailData() {
        $call_data = CallDetail::select('id', 'call_id', 'user_profile_id', 'user_role_id','reason', 'start_time', 'end_time', 'duration','band_width','resolution','is_called_failed','call_detail','status', 'feedback')
                ->with(['user_profile' => function($query) {
                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth');
            },
            'user_role' => function($query) {
                $query->select('id', 'name', 'display_name');
            },
            'purpose' => function($query) {
                $query->select('id', 'name', 'description');
            },
            'status' => function ($query) {
                $query->select('id', 'name', 'value');
            }
        ]);

        return $call_data;
    }

    public static function getInterpreterCallDetailData() {
        $call_data = CallDetail::select('id', 'call_id', 'user_profile_id', 'user_role_id','reason', 'start_time', 'end_time', 'duration','band_width','resolution','is_called_failed','call_detail','status', 'feedback')
                ->with(['user_profile' => function($query) {
                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')
                ->with(['company' =>function($query){
                    $query->select('companies.id','company_name', 'company_type', 'con_name', 'con_email', 'company_address1', 'company_address2', 'company_city','company_state', 'company_country', 'company_zipcode');
                }]);
            },
            'user_role' => function($query) {
                $query->select('id', 'name', 'display_name');
            },
            'status' => function ($query) {
                $query->select('id', 'name', 'value');
            },
            'calls' => function ($query) {
                $query->select('id', 'from_user_profile_id', 'from_user_role_id', 'purpose_id', 'language_id', 'action', 'status', 'remarks', 'is_recall')->with([
                    'purpose' => function ($query){
                        $query->select('id','name','description');
                    },
                    'language' => function ($query){
                        $query->select('id','name','is_active');
                    }
                ])->orderBy('calls.id','DESC');
            }
        ]);

        return $call_data;
    }
}
