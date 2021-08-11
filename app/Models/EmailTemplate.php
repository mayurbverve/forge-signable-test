<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;

class EmailTemplate extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'email_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['template_title','template_key', 'is_active', 'is_deleted', 'created_by', 'updated_by'];

    /**
     * Get the email template content record associated with the email template.
     */
    function template_content() {
        return $this->hasMany('App\Models\EmailTemplateContent', 'template_id', 'id');
    }

    /**
     * Get Email Template data
     */
    public static function getEmailTemplateData() {
        $public_url = url('/');
        $email_template_data = EmailTemplate::where('is_deleted', 0)->orderBy('id', 'DESC');
        return $email_template_data;
    }

}
