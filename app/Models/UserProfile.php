<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Location;
use DB;

class UserProfile extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_profies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth','avg_user_rating'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

    public function getProfilePhotoAttribute($value){
        $public_url = ('/');
        $user_url = url('/uploads/users/');
        if(isset($value) && !empty($value) && file_exists(public_path().DS.$value)){
            $value = url($value);
        }else{
            $value = $public_url.'/default.png';
        }
         return $value;
    }
    
    public function company() {
        return $this->belongsTo('App\Models\Company', 'company_id');
    }
    public function call() {
        return $this->belongsTo('App\Models\Call', 'id');
    }

    public function user() {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function locations() {
        return $this->hasOne('App\Models\Location', 'user_profile_id');
    }

    public function languages() {
        return $this->belongsToMany('App\Models\Language', 'user_languages', 'user_profile_id', 'language_id')->select('user_languages.language_id', 'languages.name AS language_name', 'languages.is_active', 'user_languages.ranking');
    }
    
    

    public function user_roles() {
        return $this->belongsToMany('App\Models\Role', 'role_users', 'user_profile_id', 'role_id')->select('role_users.id', 'role_users.role_id', 'role_users.user_profile_id', 'roles.name as role_name', 'roles.display_name as role_display_name')->orderBy('role_users.role_id');
    }

        public static function getUserProfileData() {
        $public_url = url('/');
        $user_profile = UserProfile::select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')
                ->with(['company' => function($query) use($public_url) {
                $query->select('id', 'company_name', 'company_type', 'con_name', 'con_email', 'company_address1', 'company_address2', 'company_city', 'company_state', 'company_country', 'company_zipcode');
            }, 'locations' => function($query) use($public_url) {
                $query->select('id','user_profile_id', 'city_id', 'miles', 'region', 'site')->with('city','regions','mile');
            }, 'languages']);
        return $user_profile;
    }
}
