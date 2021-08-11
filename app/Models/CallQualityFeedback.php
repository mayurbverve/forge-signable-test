<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallQualityFeedback extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;
    //use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'call_quality_feedback';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['call_id', 'call_quality_rate', 'is_group_call', 'created_by', 'updated_by'];
}
