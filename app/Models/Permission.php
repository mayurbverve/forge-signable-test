<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Trebol\Entrust\EntrustPermission;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;


class Permission extends EntrustPermission implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'display_name', 'description'];

}
