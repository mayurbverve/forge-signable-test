<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;

class Disposition extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dispositions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'type'];

    

    /**
     * Get Email Template data
     */
    public static function getDispositionData() {
        $public_url = url('/');
        $disposition_data = Disposition::select('id','name', 'description','type')->orderBy('id', 'DESC');
        return $disposition_data;
    }

}
