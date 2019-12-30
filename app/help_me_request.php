<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class help_me_request extends Model
{
    protected $fillable = [
        'short_description', 'description','maker_id','helper_id','help_session_id','cost','status','skills', 'score','title'
    ];
    protected $casts = [   
        'skills' => 'array',
        'helper_queue' => 'array'
    ];
    public function session(){
        return $this->hasOne(HelpSession::class,"request_id");
    }
    public function maker(){
        return $this->hasOne(User::class,"maker_id");
    }
}
