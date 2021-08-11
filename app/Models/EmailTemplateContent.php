<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;

class EmailTemplateContent extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'email_template_contents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email_template_id', 'language_id', 'email_subject', 'email_body', 'is_deleted'];

    /**
     * Get the email template record associated with the email template content.
     */
    function email_template() {
        return $this->belongsTo('App\Models\EmailTemplate', 'email_template_id');
    }

    /**
     * Get the language record associated with the email template content.
     */
    function language() {
        return $this->belongsTo('App\Models\Language', 'language_id');
    }

    /**
     * Get Email Template data
     */
    public static function getEmailTemplateContentData() {
        $public_url = url('/');
        $email_template_content = EmailTemplateContent::with(['email_template' => function($query) {
                        $query->select(['email_templates.id', 'template_title']);
                    }, 'language' => function($query) {
                        $query->select(['languages.id', 'language_code', 'language_name']);
                    }])->where('is_deleted', 0)->orderBy('id', 'DESC');
        return $email_template_content;
    }

}
