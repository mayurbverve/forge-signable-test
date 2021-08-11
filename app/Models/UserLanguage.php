<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLanguage extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;
    //use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_languages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_profile_id', 'language_id', 'ranking'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

    public function user_profile() {
        return $this->belongsTo('App\Models\UserProfile', 'user_profile_id');
    }
    
    public function language() {
        return $this->belongsTo('App\Models\Language', 'language_id');
    }

}
