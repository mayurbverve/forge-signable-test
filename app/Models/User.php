<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Trebol\Entrust\Traits\EntrustUserTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use App\Models\UserProfile;
use Config;
use JWTAuth;

class User extends  Authenticatable implements JWTSubject, Auditable {

    use Notifiable;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email','phone', 'password', 'qb_id', 'qb_password','is_active', 'is_verified', 'is_deleted', 'is_forgeted', 'reg_source', 'created_by', 'updated_by'];

    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return [];
    }

    public function user_profile(){
        return $this->hasOne('App\Models\UserProfile', 'user_id');
    }
    
    
     /**
     * Get the role record associated with the user.
     */
    public function getRoleByRoleUserID() {
        $token_data = JWTAuth::getPayload();
        
//        print_r($token_data);die;
        $role_user_id = isset($token_data['role_user_id']) ? $token_data['role_user_id'] : '';

        if (!$role_user_id) {
            return false;
        }

        $role_user = UserRole::where('role_users.id', $role_user_id)
                ->leftjoin("roles", "roles.id", "=", "role_users.role_id")
                ->select('role_users.id', 'role_users.role_id', 'role_users.user_profile_id',  'roles.name as role_name', 'roles.display_name as role_display_name')
                ->first();
        return $role_user;
    }
    
    
    public static function getUserData() {
        
        $user_data =User::select( 'users.id', 'email','phone', 'users.is_active', 'is_verified', 'is_deleted','qb_id','qb_password')
                ->with(['user_profile' =>function($query){
                    $query->select('user_profies.id','user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth','avg_user_rating')
                            ->with(['user_roles','company' =>function($query){
                                $query->select('companies.id','company_name', 'company_type', 'con_name', 'con_email', 'company_address1', 'company_address2', 'company_city','company_state', 'company_country', 'company_zipcode');
                            },'locations' => function($query){
                                $query->select('locations.id','user_profile_id','city_id', 'miles','region', 'site')->with('city','mile','regions');
                            }]);
                }]);
        
        
        return $user_data;
    }
    
    
    public static function getActiveInterpreter(){
        $supplier_roles = array(2);
        $interpreter_lists = User::getUserData()
                    ->join('user_profies', 'users.id', '=', 'user_profies.user_id')
                    ->join('role_users', 'user_profies.id', '=', 'role_users.user_profile_id')
                    ->join('roles', 'roles.id', "=", 'role_users.role_id')
                    ->join('user_languages', 'user_profies.id', "=", 'user_languages.user_profile_id')
                    ->join('active_interpreters', 'user_profies.id', "=", 'active_interpreters.user_profile_id')
                    ->whereIn('roles.id', $supplier_roles)->where(['active_interpreters.is_active' => 1, 'active_interpreters.status' => 1])->orderBy('user_profies.id',"ASC");
        return $interpreter_lists;
    }


    public static function getActiveUserData() {
        
        $user_data =User::select( 'users.id', 'email','phone', 'users.is_active', 'is_verified', 'is_deleted','qb_id','qb_password')
                ->with(['user_profile' =>function($query){
                    $query->select('user_profies.id','user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')
                            ->with(['user_roles','company' =>function($query){
                                $query->select('companies.id','company_name', 'company_type', 'con_name', 'con_email', 'company_address1', 'company_address2', 'company_city','company_state', 'company_country', 'company_zipcode');
                            },'locations' => function($query){
                                $query->select('locations.id','user_profile_id','city_id', 'miles','region', 'site');
                            }]);
                }]);
        
        
        return $user_data;
    }
}
