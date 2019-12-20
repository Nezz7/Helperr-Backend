<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Image;

class ProfileController extends Controller
{

    public function allprofiles(){

        return response()->json(User::all(), 200);
    }

    public function search(Request $request){
        
        $query = User::query();

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

        if ($request->input("score")) {$query->where("score", ">=",$request->input("score"));}
        if ($request->input("hp")) {$query->where("hp", ">=",$request->input("hp"));}
        if ($request->input("occupation")) {$query->where("occupation",$request->input("occupation"));}
        if ($request->input("institution")) {$query->where("institution",$request->input("institution"));}
        if ($request->input("id")) {$query->find($request->input("id"));}
        if ($request->input("name")) {$query->where("name",$request->input("name"));}


        if ($query == User::query()) {return response()->json(["errors"=>["bad parameters"]], 406);}
        return response()->json($query->get(), 200);

    }


    public function getprofile($id){
        $user = User::find($id);
        return response()->json($user , 200);
    }

    public function getavatar($id){

        $filename = $id . '.jpg';
        
        $location = public_path('uploads/avatar/');
        if (file_exists($location.$filename))  $imageobj = Image::make(imagecreatefromjpeg($location . $filename));
        else $imageobj = Image::make(imagecreatefromjpeg($location . "default.jpg"));

        return $imageobj->response("jpg");
    }



}
