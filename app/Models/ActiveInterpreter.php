<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;

class ActiveInterpreter extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'active_interpreters';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_profile_id', 'language_id', 'ranking', 'is_active', 'status'];

    public function user_profile(){
        return $this->belongsTo('App\Models\UserProfile', 'user_profile_id');
    }
    
    public function language(){
        return $this->belongsTo('App\Models\Language', 'language_id');
    }
    
    public static function getActiveInterpreterData() {
        $active_interpreter_data = ActiveInterpreter::select('id', 'user_profile_id', 'language_id', 'ranking', 'is_active', 'status')
                ->with(['user_profile','language']);
        return $active_interpreter_data;
    }

}
