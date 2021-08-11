<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\SetTimeZone;
use Illuminate\Database\Eloquent\SoftDeletes;

class Call extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SetTimeZone;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'calls';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['from_user_profile_id', 'from_user_role_id', 'purpose_id', 'language_id', 'action', 'status', 'remarks', 'is_recall', 'previous_call_id'];

    public function from_user_profile() {
        return $this->belongsTo('App\Models\UserProfile', 'from_user_profile_id');
    }

    public function from_user_role() {
        return $this->belongsTo('App\Models\Role', 'from_user_role_id');
    }

    public function purpose() {
        return $this->belongsTo('App\Models\Purpose', 'purpose_id');
    }

    public function language() {
        return $this->belongsTo('App\Models\Language', 'language_id');
    }

    public function status() {
        return $this->belongsTo('App\Models\CallStatus', 'status');
    }

    public function status_detail() {
        return $this->belongsTo('App\Models\CallStatus', 'status');
    }

    public function call_details() {
        return $this->hasMany('App\Models\CallDetail', 'call_id');
    }

    public function call_status_logs() {
        return $this->hasMany('App\Models\CallLog', 'call_id');
    }

    public function call_init_messages() {
        return $this->hasOne('App\Models\CallInitMessage', 'call_id');
    }

//    public function reason() {
//        return $this->hasMany('App\Models\Reason', 'reason_id');
//    }

    public static function getCallData() {
        $call_data = Call::select('id', 'from_user_profile_id', 'from_user_role_id', 'purpose_id', 'language_id', 'action', 'status', 'remarks', 'is_recall')
                ->with(['from_user_profile' => function ($query) {
                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')
                ->with(['company', 'locations' => function ($query) {
                        $query->with('city', 'mile', 'regions');
                    }]);
            },
            'from_user_role' => function ($query) {
                $query->select('id', 'name', 'display_name');
            },
            'purpose' => function ($query) {
                $query->select('id', 'name', 'description');
            },
            'language' => function ($query) {
                $query->select('id', 'name', 'is_active');
            },
            'status' => function ($query) {
                $query->select('id', 'name', 'value');
            },
            'status_detail' => function ($query) {
                $query->select('id', 'name', 'value');
            },
            'call_details' => function ($query) {
                $query->select('id', 'call_id', 'user_profile_id', 'user_role_id', 'start_time', 'end_time', 'duration', 'band_width', 'resolution', 'is_called_failed', 'call_detail', 'status', 'feedback')->with(['user_profile' => function ($query) {

                        $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')->with('company');
                    }, 'user_role' => function ($query) {
                        $query->select('id', 'name', 'display_name');
                    }, 'status' => function ($query) {
                        $query->select('id', 'name', 'value');
                    }
                ])->orderBy('call_details.id', 'DESC')->first();
            }
        ]);

        return $call_data;
    }

    public static function getCallReportData() {

        $call_report_data = Call::select('id', 'from_user_profile_id', 'from_user_role_id', 'purpose_id', 'language_id', 'action', 'status', 'remarks', 'is_recall')
                        ->with(['from_user_profile' => function ($query) {
                                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')->with(['company', 'locations']);
                            },
                            'from_user_role' => function ($query) {
                                $query->select('id', 'name', 'display_name');
                            },
                            'purpose' => function ($query) {
                                $query->select('id', 'name', 'description');
                            },
                            'language' => function ($query) {
                                $query->select('id', 'name', 'is_active');
                            },
                            'status' => function ($query) {
                                $query->select('id', 'name', 'value');
                            },
                            'call_details' => function ($query) {
                                $query->select('id', 'call_id', 'user_profile_id', 'user_role_id', 'start_time', 'end_time', 'duration', 'band_width', 'resolution', 'is_called_failed', 'call_detail', 'status', 'feedback')->with(['user_profile' => function ($query) {
                                        $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')->with('company');
                                    }, 'user_role' => function ($query) {
                                        $query->select('id', 'name', 'display_name');
                                    }, 'status' => function ($query) {
                                        $query->select('id', 'name', 'value');
                                    }
                                ])->where('call_details.status','>','40')->where('call_details.user_role_id','=','2')->orderBy('call_details.id', 'DESC');
                            }
                        ])->orderBy('calls.id', 'DESC')->GroupBy('calls.id');

        return $call_report_data;
    }

    public static function getQACallReportData() {

        $call_report_data = Call::select('id', 'from_user_profile_id', 'from_user_role_id', 'purpose_id', 'language_id', 'action', 'status', 'remarks', 'is_recall')
                        ->with(['from_user_profile' => function ($query) {
                                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')->with(['company', 'locations']);
                            },
                            'from_user_role' => function ($query) {
                                $query->select('id', 'name', 'display_name');
                            },
                            'purpose' => function ($query) {
                                $query->select('id', 'name', 'description');
                            },
                            'language' => function ($query) {
                                $query->select('id', 'name', 'is_active');
                            },
                            'status' => function ($query) {
                                $query->select('id', 'name', 'value');
                            },
                            'call_details' => function ($query) {
                                $query->select('id', 'call_id', 'user_profile_id', 'user_role_id', 'start_time', 'end_time', 'duration', 'band_width', 'resolution', 'is_called_failed', 'call_detail', 'status', 'feedback')->with(['user_profile' => function ($query) {
                                        $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')->with('company');
                                    }, 'user_role' => function ($query) {
                                        $query->select('id', 'name', 'display_name');
                                    }, 'status' => function ($query) {
                                        $query->select('id', 'name', 'value');
                                    }
                                ])->where('call_details.status','>','40')->orderBy('call_details.id', 'DESC');
                            }
                        ])->orderBy('calls.id', 'DESC')->GroupBy('calls.id');

        return $call_report_data;
    }

    public static function getSignableCallData() {
        $call_data = Call::select('id', 'from_user_profile_id', 'from_user_role_id', 'purpose_id', 'language_id', 'action', 'status', 'remarks', 'is_recall')
                ->with(['from_user_profile' => function ($query) {
                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')->with('company');
            },
            'from_user_role' => function ($query) {
                $query->select('id', 'name', 'display_name');
            },
            'purpose' => function ($query) {
                $query->select('id', 'name', 'description');
            },
            'language' => function ($query) {
                $query->select('id', 'name', 'is_active');
            },
            'status' => function ($query) {
                $query->select('id', 'name', 'value');
            }
        ]);

        return $call_data;
    }

    public static function getFrequentCallReportData() {

        $call_report_data = Call::select('id', 'from_user_profile_id', 'from_user_role_id', 'purpose_id', 'language_id', 'action', 'status', 'remarks', 'is_recall')
                ->with(['from_user_profile' => function ($query) {
                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')->with('company');
            },
            'from_user_role' => function ($query) {
                $query->select('id', 'name', 'display_name');
            },
            'purpose' => function ($query) {
                $query->select('id', 'name', 'description');
            },
            'language' => function ($query) {
                $query->select('id', 'name', 'is_active');
            },
            'status' => function ($query) {
                $query->select('id', 'name', 'value');
            }
        ]);

        return $call_report_data;
    }

    public static function getCallMessageData() {
        $call_data = Call::select('id', 'from_user_profile_id', 'from_user_role_id', 'purpose_id', 'language_id', 'action', 'status', 'remarks', 'is_recall', 'created_at')
                ->with(['from_user_profile' => function ($query) {
                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')
                ->with(['company', 'locations' => function ($query) {
                        $query->with('city', 'mile', 'regions');
                    }]);
            },
            'from_user_role' => function ($query) {
                $query->select('id', 'name', 'display_name');
            },
            'purpose' => function ($query) {
                $query->select('id', 'name', 'description');
            },
            'language' => function ($query) {
                $query->select('id', 'name', 'is_active');
            },
            'status' => function ($query) {
                $query->select('id', 'name', 'value');
            },
            'call_details' => function ($query) {
                $query->select('id', 'call_id', 'user_profile_id', 'user_role_id', 'start_time', 'end_time', 'duration', 'band_width', 'resolution', 'is_called_failed', 'call_detail', 'status', 'feedback')->with(['user_profile' => function ($query) {

                        $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')->with('company');
                    }, 'user_role' => function ($query) {
                        $query->select('id', 'name', 'display_name');
                    }, 'status' => function ($query) {
                        $query->select('id', 'name', 'value');
                    }
                ])->orderBy('call_details.id', 'DESC')->first();
            }
        ]);

        return $call_data;
    }

    public static function getCallDetailsData() {

        $call_report_data = Call::select('id', 'from_user_profile_id', 'from_user_role_id', 'purpose_id', 'language_id', 'action', 'status', 'remarks', 'is_recall')
                        ->with(['from_user_profile' => function ($query) {
                                $query->select('id', 'user_id', 'company_id', 'first_name', 'last_name', 'profile_photo', 'gender', 'date_of_join', 'date_of_birth')->with(['company', 'locations']);
                            },
                            'from_user_role' => function ($query) {
                                $query->select('id', 'name', 'display_name');
                            },
                            'purpose' => function ($query) {
                                $query->select('id', 'name', 'description');
                            },
                            'language' => function ($query) {
                                $query->select('id', 'name', 'is_active');
                            },
                            'status' => function ($query) {
                                $query->select('id', 'name', 'value');
                            }, 'call_init_messages' => function ($query) {
                                $query->select('id', 'call_id', 'supervisor_message_id', 'interpreter_message_id')->orderBy('call_init_messages.id', 'DESC')->latest();
                            }
                        ])->orderBy('calls.id', 'DESC')->GroupBy('calls.id');

        return $call_report_data;
    }

}
