<?php

namespace App\Http\Controllers;

use App\HelpSession;
use Illuminate\Http\Request;
use JWTAuth;
use App\help_me_request;
use App\User; 

function relatesto($session,$user){
    if ($user->helper_sessions->contains($session)) return 1;
    if ( $user->helpee_sessions->contains($session) ) return -1;
    return 0;
}

class HelpSessionController extends Controller
{
    

    public static function create($helpme,$helpee,$helper)
    {

        $help_session = $helpme->session()->create(["helper_id" => $helper->id, "helpee_id" => $helpee->id, "helper_name" => $helper->name,"helpee_name" => $helpee->name]);
        $helpme->help_session_id=$help_session->id;
        $helpme->push();
        return $help_session;
    }

    public function get($id){

        $user = JWTAuth::parseToken()->toUser();
        $session = HelpSession::find($id);
        if (relatesto($session,$user)) return response()->json($session, 200);

        return response()->json(["errors" => ["bad session id"]],404);
    }

    public function getashelper(){
        $user = JWTAuth::parseToken()->toUser();
        $sessions = $user->helper_sessions;
        return response()->json($sessions, 200);
    }

    public function getashelpee(){
            $user = JWTAuth::parseToken()->toUser();
            $sessions = $user->helpee_sessions;
            return response()->json($sessions, 201);
    }


    public function head($id,$n){
        $user = JWTAuth::parseToken()->toUser();
        $session = HelpSession::find($id);
        if (! relatesto($session,$user)) return response()->json(["errors" => ["bad session id"]],404);
        $messages = $session->messages->sortByDesc("created_at")->take($n);
        return response()->json($messages,200);

    }

    public function allmessages($id){
        
        $user = JWTAuth::parseToken()->toUser();
        $session = HelpSession::find($id);
        if (! relatesto($session,$user)) return response()->json(["errors" => ["bad session id"]],404);
        $messages = $session->messages;
        return response()->json($messages,200);

    }

    public function postmessage(Request $request,$id){
        $user = JWTAuth::parseToken()->toUser();
        $session = HelpSession::find($id);
        if (! relatesto($session,$user)) return response()->json(["errors" => ["bad session id"]],404);
        if (in_array($session->status,["canceled","TLE","failed","succeeded"])) return response()->json(["errors" => ["session closed"]],403);

        if (relatesto($session,$user) + 1) {$source="helper";} else {$source="helpee";}
      
        
        $message = SessionMessageController::create($request,$session,$source);

        return response()->json($message,201);
    }

    public function submitwork($id){
        $user = JWTAuth::parseToken()->toUser();
        $session = HelpSession::find($id);
        if (! relatesto($session,$user)) return response()->json(["errors" => ["bad session id"]],404);
        if (in_array($session->status,["canceled","TLE","failed","succeeded"])) return response()->json(["errors" => ["session finished"]],403);

        if (relatesto($session,$user) + 1) {$source="helper";} else {$source="helpee";}
        if ($session->status == "pending") {
            if ($source == "helpee") return response()->json(["errors" => ["can't submit work as helpee"]],404);
            $session->status = "submitted";
            $session->save();
        }

        
        return response()->json($message,201);
    }

    public function acceptwork($id){
        
        $user = JWTAuth::parseToken()->toUser();
        $session = HelpSession::find($id);
        if (! relatesto($session,$user)) return response()->json(["errors" => ["bad session id"]],404);
        if (! $session->status=="submitted") return response()->json(["errors" => ["bad session"]],403);

        if (relatesto($session,$user) + 1) {$source="helper";} else {$source="helpee";}
        if ($source == "helper") return response()->json(["errors" => ["can't accept own work"]],403);
        $session->status="review";
        $session->save();
        return response()->json($session,201);

    }

    public function declinework($id){
        
        $user = JWTAuth::parseToken()->toUser();
        $session = HelpSession::find($id);
        if (! relatesto($session,$user)) return response()->json(["errors" => ["bad session id"]],404);
        if (! $session->status=="submitted") return response()->json(["errors" => ["bad session"]],403);

        if (relatesto($session,$user) + 1) {$source="helper";} else {$source="helpee";}
        if ($source == "helper") return response()->json(["errors" => ["can't decline own work"]],403);
        $session->status="failed";
        $helpme = help_me_request::find($session->request_id);
        $helpme->status = "failed";
        $helpme-save();
        $session->push();
        return response()->json($helpme,200);
    }

    public function submitreview(Request $request, $id){

        $user = JWTAuth::parseToken()->toUser();
        $session = HelpSession::find($id);
        if (! relatesto($session,$user)) return response()->json(["errors" => ["bad session id"]],404);
        if (! $session->status=="review") return response()->json(["errors" => ["bad session"]],403);
        if (relatesto($session,$user) + 1) {$source="helper";} else {$source="helpee";}

        $this->validate($request, [
            'review' => 'required',
            'score' => 'required|lte:5|gte:0',
        ]);
            
        $helpme = help_me_request::find($session->request_id);
        if ($source=="helper"){
            $session->helper_review=$request->input('review');
            $session->helper_score=$request->input('score');
            $session->helpee->score = ($session->helpee->score * $session->helpee->totalreviews + $request->input('score')) / ($session->helpee->totalreviews + 1) ;
            $session->helpee->totalreviews = $session->helpee->totalreviews + 1 ; 
            // transact hp
            $session->helper->hp = $session->helper->hp + $helpme->cost;
            $session->push();
            return response()->json($session->helper,200);
            
        }
        else {
            $session->helpee_review=$request->input('review');
            $session->helpee_score=$request->input('score');
            $session->status = "succeeded";
            $session->helper->score = ($session->helper->score * $session->helper->totalreviews + $request->input('score')) / ($session->helper->totalreviews + 1) ;
            $session->helper->totalreviews = $session->helper->totalreviews + 1 ; 
            $session->push();

            
            $helpme->status="succeeded";
            $helpme->push();
            
            return response()->json(["req" => $helpme ,"helpee" => $session->helper],200);
        }
    }
    
    
    public function cancel(){
        
        $user = JWTAuth::parseToken()->toUser();
        $session = HelpSession::find($id);
        if (! relatesto($session,$user)) return response()->json(["errors" => ["bad session id"]],404);
        if (in_array($session->status,["canceled","review","submitted","succeeded"])) return response()->json(["errors" => ["bad session"]],403);

        if (relatesto($session,$user) + 1) {$source="helper";} else {$source="helpee";}
        $session->status="canceled";
        $helpme = help_me_request::find($session->request_id);
        $helpme->status = "failed";
        $session->helpee->hp = $session->helpee->hp + $helpme->cost;
        $helpme->save();
        $session->push();
    }


}
