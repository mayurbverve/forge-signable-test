<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterpreterBreaksTemp extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;
    //use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'interpreter_breaks_temp';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_profile_id', 'break_reason','day','break_start_time','break_end_time'];

}
