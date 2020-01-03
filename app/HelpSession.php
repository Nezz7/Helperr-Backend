<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\SessionMessage;

class HelpSession extends Model
{
    protected $guarded = [];

    public function helper(){
        return $this->belongsTo(User::class,'helper_id');
    }
    
    public function helpee(){
        return $this->belongsTo(User::class,'helpee_id');
    }

    public function messages(){
       return $this->hasMany(SessionMessage::class, 'help_session_id');
    }

    public function request(){
        return $this->belongsTo(help_me_request::class,'help_session_id');
    }
}
