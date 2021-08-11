<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketAttachment extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ticket_attachments';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['ticket_id','attachment_path', 'attachment_type', 'attachment_name'];

    /**
     * Get the incident record associated with the attachments
     */

}
