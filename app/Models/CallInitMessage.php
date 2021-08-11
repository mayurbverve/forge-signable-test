<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallInitMessage extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'call_init_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['call_id', 'supervisor_message_id', 'interpreter_message_id'];

    public function calls() {
        return $this->belongsTo('App\Models\Call', 'call_id');
    }

    public static function getCallInitMessageData() {
        $call_data = CallInitMessage::with('calls');

        return $call_data;
    }

}
