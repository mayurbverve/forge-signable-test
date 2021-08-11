<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Carbon\Carbon;
use Auth;

class UserNotification extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['role_user_id', 'notification_type', 'notification_title', 'notification_message', 'sent_date', 'content_id', 'is_read', 'read_date', 'ip_address', 'action_by'];

//     public function getSentDateAttribute($value) {
//        if(isset(Auth::user()->timezone)){
//            $value = Carbon::parse($value)->tz(Auth::user()->timezone)->format('Y-m-d H:i:s');
//        }
//        return $value;
//    }
    
}
