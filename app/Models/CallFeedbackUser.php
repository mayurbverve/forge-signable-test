<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallFeedbackUser extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;
    //use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'call_feedback_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $fillable = ['call_id', 'feedback_type', 'to_user_profile_id', 'to_user_role_id', 'to_user_rating', 'disposition_id', 'comment', 'created_by', 'updated_by'];
    
    public function calls() {
        return $this->belongsTo('App\Models\Call', 'call_id');
    }
    public function user_profile() {
        return $this->belongsTo('App\Models\UserProfile', 'to_user_profile_id');
    }
    public function call_details() {
        return $this->hasMany('App\Models\CallDetail', 'call_id');
    }

    public static function getfeedbackData(){
        $feedback_data = CallFeedbackUser::select('id','call_id', 'feedback_type', 'to_user_profile_id', 'to_user_role_id', 'to_user_rating', 'disposition_id', 'comment', 'created_by', 'updated_by')
            ->with(['user_profile' => function ($query) {
                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth');
            },
            'calls' => function ($query) {
                $query->select('id', 'from_user_profile_id', 'from_user_role_id', 'purpose_id', 'language_id', 'action', 'status', 'remarks', 'is_recall')->with([
                    'purpose' => function ($query){
                        $query->select('id','name','description');
                    },
                    'language' => function ($query){
                        $query->select('id','name','is_active');
                    },
                    'call_details' => function ($query) {
                        $query->select('id', 'call_id', 'user_profile_id', 'user_role_id', 'start_time', 'end_time', 'duration', 'band_width', 'resolution', 'is_called_failed', 'call_detail', 'status')->with(['user_profile' => function ($query) {
                                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')->with('company');
                            }, 'user_role' => function ($query) {
                                $query->select('id', 'name', 'display_name');
                            }, 'status' => function ($query) {
                                $query->select('id', 'name', 'value');
                            }
                        ])->where('call_details.status','>','40')->where('call_details.user_role_id','=','2')->orderBy('call_details.id', 'DESC')->first();
                    }
                ])->orderBy('calls.id','DESC');
            }
        ]);                                                         
        return $feedback_data;
    }

}
