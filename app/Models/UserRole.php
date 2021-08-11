<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;

class UserRole extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'role_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','user_profile_id','role_id', 'user_id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

    /**
     * Get the user record associated with the role.
     */
    public function user() {
        return $this->belongsTo('App\Models\UserProfile', 'user_profile_id');
    }

    /**
     * Get the role record associated with the user.
     */
    public function role() {
        return $this->belongsTo('App\Models\Role', 'role_id');
    }
  

}
