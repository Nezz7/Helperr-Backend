<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;
use Image;
class userController extends Controller
{

    public function signup(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        $user = new User([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' =>  bcrypt($request->input('password')),
            'hp' => 2 , 
            'skills' => []
        ]);

        $user->save();

        return response()->json(['message' => 'User created'],201);


    }

    public function signin(Request $request){

        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $credentials = $request->only('email','password');
        try{
            if (!$token =JWTAuth::attempt($credentials)){
                return response()->json( ['errors' => ['Invalid Credentials']] ,401);
            } }
            catch(JWTException $e){
        return response()->json(['errors' => ['Could not create Token!']],500);

    }
        return response()->json(['token' => $token], 200);
    }


    public function getprofile(){

        $user = JWTAuth::parseToken()->toUser();

        return response()->json($user,200);
    }


    public function test(){
        return response()->json(['response' => "HAAHA"], 200);
    }

    public function addskills(Request $request)
    {
        $user = JWTAuth::parseToken()->toUser();
        if ( $user->skills) 
            {$user->skills= array_values(array_unique(array_merge($user->skills,$request->input("skills"))));}
        else 
            { $user->skills = array_values(array_unique($request->input("skills"))); }
        $user->save();
        return response()->json($user,201);
    }

    public function removeskills(Request $request)
    {
        
        $user = JWTAuth::parseToken()->toUser();
        $newskills= array_diff($user->skills,$request->input("skills"));
        $user->skills = array_values($newskills);
        $user->save();
        return response()->json($user,201);
    }

    public function clearskills(){
        $user = JWTAuth::parseToken()->toUser();
        $user->skills = [];
        $user->save();
        return response()->json($user,201);
    }

    public function updateattribute(Request $request, $attribute){

        if ( ! in_array($attribute,["name" , "description", "occupation", "institution"]) ) {
            return response()->json(['errors' => ["attribute doesn't exist"]],404);
        }
        
        $user = JWTAuth::parseToken()->toUser();
        $user->$attribute = $request->input($attribute);
        $user->save();
        return response()->json($user,201);
    }


    public function uploadavatar(Request $request){

        
        $user = JWTAuth::parseToken()->toUser();

        $this->validate($request, [
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
        ]);
    
        
        
        $filename = $user->id . '.jpg';
        $location = public_path('uploads/avatar/' . $filename);
        if (file_exists($location.$filename)) unlink($location.$filename);
        
        $image = $request->file('photo');
        $imageobj = Image::make($image)->encode('jpg', 75 );
        $imageobj->save($location);


        return $imageobj->response("jpg");
    }

    public function getavatar(){
        $user = JWTAuth::parseToken()->toUser();
        $filename = $user->id . '.jpg';
        $location = public_path('uploads/avatar/');
        
        if (file_exists($location.$filename))  $imageobj = Image::make(imagecreatefromjpeg($location . $filename));
        else $imageobj = Image::make(imagecreatefromjpeg($location . "default.jpg"));

        return $imageobj->response("jpg");
    }

    public function gethistory(){
        
        $user = JWTAuth::parseToken()->toUser();
        $helpmes = help_me_request::where('maker_id',$user->id)->get();
        $helpyou = help_me_request::where('helper_id',$user->id)->get();   
        return response()->json(["helpme" => $helpmes,"helpyou"=>$helpyou],200);

    }

}
