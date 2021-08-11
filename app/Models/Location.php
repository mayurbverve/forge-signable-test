<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'locations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_profile_id','city_id', 'miles','region', 'site'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

    
  public function user_profile(){
        return $this->belongsTo('App\Models\UserProfile', 'user_profile_id');
    }
    
    public function regions() {
        return $this->belongsTo('App\Models\Region', 'region');
    }
    
    
    public function mile() {
        return $this->belongsTo('App\Models\Mile', 'miles');
    }
    
    public function city(){
        return $this->belongsTo('App\Models\City', 'city_id');
    }

}
