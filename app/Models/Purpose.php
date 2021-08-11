<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purpose extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    //use SetTimeZone;
    use SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'purposes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description'];

    public static function getPurposeData() {
        $purpose = Purpose::select('id','name', 'description');
        return $purpose;
    }
    
}
