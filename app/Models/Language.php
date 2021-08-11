<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Illuminate\Database\Eloquent\SoftDeletes;

class Language extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'languages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'is_active'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

    public function active_interpreters(){
       return  $this->hasMany('App\Models\ActiveInterpreter', 'language_id','id')->where('status',1);
    }
  
    public function getImageAttribute($value){
        
        $public_url = url('/uploads/languages');
        if(isset($value) && !empty($value) && file_exists(public_path().'/uploads/languages/'.$value)){
            $value = $public_url.'/'. $value;
        }else{
            $value = null;
        }
         return $value;
    }
    
    
    
    public static function getActiveInterpreterData(){
        $active_interpereter_data = Language::select('id','name','image','is_active')->withCount(['active_interpreters'])->where('is_active',1);
        return $active_interpereter_data;
    }

    public static function getLanguageData() {
        $public_url = url('/');
        $language_data = Language::select('id','name', 'image','is_active')->orderBy('id', 'DESC');
        return $language_data;
    }
}
