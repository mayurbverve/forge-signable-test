<?php

namespace App\Models\Traits;

use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Auth;

trait SetTimeZone {

    public $tz = 'UTC';

    public function getTz() {

        $this->tz = Auth::user()->timezone;

        return $this->tz;
    }

    public function getCreatedAtAttribute($value) {
        if(isset(Auth::user()->timezone)){
            $value = Carbon::parse($value)->tz(Auth::user()->timezone)->format('Y-m-d H:i:s');
        }
        return $value;
    }

    public function getUpdatedAtAttribute($value) {
        if(isset(Auth::user()->timezone)){
            $value = Carbon::parse($value)->tz(Auth::user()->timezone)->format('Y-m-d H:i:s');
        }

        return $value;
    }

}
