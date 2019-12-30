<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Illuminate\Validation\Rule;
use App\help_me_request;
use App\User;
use App\Http\Controllers\HelpSessionController;

function consume($helpme,$helpee,$helper){
    if ($helpee->hp < $helpme->cost) return response()->json(["errors"=>["unsufficient hp"]], 406);
    if ($helpme->maker_id != $helpee->id) return response()->json(["errors"=>["unauthorized helpee id"]], 403);

    $helpee->hp= $helpee->hp - $helpme->cost;
    $helpme->status="pending";
    if (sizeof($helpme->helper_queue)){
    $temp = array_values(array_diff($helpme->helper_queue,array($helper->id)));
    $helpme->helper_queue=$temp;}
    $helpme->helper_id=$helper->id;
    $helpee->save();
    $helpme->save();
    //CREATE AND RETURN HELP SESSION
    return response()->json(HelpSessionController::create($helpme,$helpee,$helper), 201);


}


class HelpmeController extends Controller
{
    public function createhelpme(Request $request){
        $user = JWTAuth::parseToken()->toUser();
        $this->validate($request, [
            'short_description' => 'required',
            'title' => 'required',
            'skills' => 'required',
            'cost' =>'nullable|lte:' . $user->hp,
            'status' => [ 'nullable' , Rule::in(["open","selective"])
        ],
        ]);
        
        $status="open";
        if ($request->input('status')) $status=$request->input('status');
        $cost=1;
        if ($request->input('cost')) $status=$request->input('cost');
        $helpmerequest = new help_me_request([
            'short_description' => $request->input('short_description'),
            'skills' => $request->input('skills'),
            'cost' => $cost,
            'status' => $status,
            'maker_id' => $user->id,
            'score' => $user->score,
            'title' => $request->input('title'),
            'helper_queue' => []
        ]);

        if ($request->input('description')) $helpmerequest->description=$request->input('description');

       $helpmerequest->save();
        return response()->json(
            $helpmerequest,201);
    }
    public function deletehelpme($id){
        $user = JWTAuth::parseToken()->toUser();
        $to_delete = help_me_request::find($id);
        
        if (($to_delete) and ($to_delete->maker_id != $user->id) or (in_array($to_delete->status, ["pending","failed","succeeded"]))) {
            return response()->json(["errors" => "Forbidden action"],403);}
        if($to_delete) { $to_delete->delete();}
        return response()->json($user,200);
    }

    public function getmyhelpme($id){
        $user = JWTAuth::parseToken()->toUser();
        $helpme = help_me_request::find($id);
        
        if ($helpme and ($helpme->maker_id==$user->id)) return response()->json(help_me_request::find($id),200);
        return response()->json(null,404);
        

    }

    public function gethelpme($id){
        $helpme = help_me_request::find($id);
        if (in_array($helpme->status,["open","selective"])){   
        return response()->json(help_me_request::find($id),200);
        }
        return response()->json(null,404);
    }

    public function getallhelpme(){
        $user = JWTAuth::parseToken()->toUser();
        $helpmes= help_me_request::where("maker_id",$user->id)->get();
        return response()->json($helpmes,200);
    }

    public function getconsumedhelpme(){
        $user = JWTAuth::parseToken()->toUser();
        $helpmes= help_me_request::where("helper_id",$user->id)->get();
        return response()->json($helpmes,200);
    }

    public function addskills(Request $request,$id){

        $user = JWTAuth::parseToken()->toUser();
        $helpme= help_me_request::find($id);

        if( (! $helpme) || ($helpme->maker_id != $user->id) ) return response()->json(null,404);

        if (  $helpme->skills) 
            { $helpme->skills= array_unique(array_merge($helpme->skills,$request->input("skills")));}
        else 
            { $helpme->skills = array_unique($request->input("skills")); }
        $helpme->save();

        return response()->json($helpme,201);

    }

    public function removeskills(Request $request,$id)
    {
        
        $user = JWTAuth::parseToken()->toUser();
        $helpme = help_me_request::find($id);
        if( (! $helpme) || ($helpme->maker_id != $user->id) ) return response()->json(null,404);

        $newskills= array_diff($helpme->skills,$request->input("skills"));
        $helpme->skills = $newskills;
        $helpme->save();
        return response()->json($helpme,201);
    }

    public function updatehelpme(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->toUser();
        $helpme = help_me_request::find($id);
        
        if( (! $helpme) || ($helpme->maker_id != $user->id) ) return response()->json(null,404);
        if (! (in_array($helpme->status,["open","selective","failed"]))) return response()->json(["errors" => "can't modify used request"], 403);
        $this->validate($request, [
            'short_description' => 'nullable|min:5',
            'cost' =>'nullable|lte:' . $user->hp,
            'status' => [ 'nullable' , Rule::in(["open","selective"])
        ],
        ]);
        
        if( $request->input('title')) $helpme->title = $request->input('title');
        if( $request->input('description')) $helpme->description = $request->input('description');
        if( $request->input('short_description'))  $helpme->short_description = $request->input('short_description');
        if( $request->input('cost'))  $helpme->cost = $request->input('cost');
        if( $request->input('status'))  $helpme->status = $request->input('status');
        
        $helpme->save();
        return response()->json($helpme,201);
    }

    public function search(Request $request){
        
        $user = JWTAuth::parseToken()->toUser();
        $query = help_me_request::query();
        $this->validate($request,[
            'status' => [ 'nullable' , Rule::in(["open","selective"])]
        ]);
        if ($request->input("skills")){
            foreach ($request->input("skills") as $skill){
                $query->where("skills","like","%" . $skill . "%");
            }
        }

        if ($request->input("description")){
            $words = explode(" ", $request->input("description"));
            
            foreach ($words as $word){
                $query->orWhere("description","like", "%" . $word . "%");
            }
        }

        if ($request->input("short_description")){
            $words = explode(" ", $request->input("short_description"));
            
            foreach ($words as $word){
                $query->orWhere("short_description","like", "%" . $word . "%");
            }
        }

        if ($request->input("title")){
            $words = explode(" ", $request->input("title"));
            
            foreach ($words as $word){
                $query->orWhere("title","like", "%" . $word . "%");
            }
        }

        if ($request->input("helper_id")){
            foreach ($request->input("helper_id") as $helper){
                $query->where("helper_queue","like","%" . $skill . "%");
            }
        }

        if ($request->input("score")) {$query->where("score", ">=",$request->input("score"));}
        if ($request->input("cost")) {$query->where("cost", ">=",$request->input("cost"));}
        if ($request->input("id")) {$query->find($request->input("id"));}
        if ($request->input("status")) {$query->where($request->input("status"));}


        if ($query == help_me_request::query()) {return response()->json(["errors"=>["bad parameters"]], 406);}
        $resultset = $query->where("maker_id", "!=", $user->id)->get();
        foreach ($resultset as $result){
            $result["maker"]= User::find($result["maker_id"]);
        }
        return response()->json($resultset, 200);

    }


    public function selecthelper(Request $request,$id){
        $user = JWTAuth::parseToken()->toUser();
        $helpme = help_me_request::find($id);

        if ((! $helpme) || ( ! $helpme->status =='selective') || ($request->input("helper_id")==$user->id)) return response()->json(["errors"=>["bad helpme id"]], 404);
        if ( ! in_array($request->input("helper_id"),$helpme->helper_queue)) return response()->json(["errors"=>["helper not in queue"]], 406);
        $helper = User::find($request->input("helper_id"));
        return consume($helpme,$user,$helper);
    }

    public function consumehelpme($id){
        
        $helper = JWTAuth::parseToken()->toUser();
        $helpme = help_me_request::find($id);
        if( (! $helpme) || ($helpme->maker_id == $helper->id) ) return response()->json(["errors"=>["bad helpme id"]],404);
        $helpee = User::find($helpme->maker_id);

        if ($helpme->status=='open') {
            return consume($helpme,$helpee,$helper);
        }
        elseif ($helpme->status=='selective'){

                $helpme->helper_queue= array_unique(array_merge($helpme->helper_queue,array($helper->id)));
                $helpme->save();
                return response()->json($helpme,201);
            
        }
        else{
            return response()->json(["errors"=>["request already consumed"]], 403);
        }
    }
}
