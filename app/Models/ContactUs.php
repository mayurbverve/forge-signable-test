<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactUs extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','email','comments'];
}
