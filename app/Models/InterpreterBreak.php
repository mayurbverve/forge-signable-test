<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterpreterBreak extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;
    //use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'interpreter_breaks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function break_reason() {
        return $this->belongsTo('App\Models\BreakReason', 'break_reason_id');
    }

    public function user_profile() {
        return $this->belongsTo('App\Models\UserProfile', 'user_profile_id');
    }

    public function assign_to() {
        return $this->belongsTo('App\Models\UserProfile', 'assign_to');
    }
    protected $fillable = ['user_profile_id', 'break_reason_id','day','break_start_time','break_end_time','duration','status','approved_at','assign_to'];

    public static function getBreakReasonData() {
        $break_reason_data = InterpreterBreak::select('id','user_profile_id', 'break_reason_id', 'day','break_start_time','break_end_time','duration','status','approved_at','assign_to')
                ->with(['break_reason' => function($query) {
                $query->select('id', 'name');
            },'user_profile' => function($query){
                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth');
            },'assign_to' => function($query){
                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth');
            }
        ]);

        return $break_reason_data;
    }

}
