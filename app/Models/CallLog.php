<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallLog extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'call_status_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['call_id', 'from_status', 'to_status', 'created_by','updated_by'];
    
    public function calls() {
        return $this->belongsTo('App\Models\Call', 'call_id');
    }
    
    public function from_status_detail() {
        return $this->belongsTo('App\Models\CallStatus', 'from_status');
    }
    public function to_status_detail() {
        return $this->belongsTo('App\Models\CallStatus', 'to_status');
    }
    
    public static function getCallLogData() {
        $call_data = CallLog::with(['calls', 'from_status_detail', 'to_status_detail']);

        return $call_data;
    }
    
}
