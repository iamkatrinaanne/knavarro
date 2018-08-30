<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Mob_LoginController extends Controller
{
    public function goRegistration(){
        $revcenters = RevCenter::get();

        return response()->json([
            'revcenters' => $revcenters
        ]);
    }

    public function saveNewUser(Request $request){
        $user = new Users;
        $token = new UsersToken;
        $learningpath = new LearningPath;
        $learningpathnodestatus = new LearningPathNodeStatus;
        if($request->password == $request->confpassword){
            
        $user->user_ID = Uuid::generate()->string;
        $user->username = $request->username;
        $user->password = bcrypt($request->password);
        $user->firstname = $request->fname;
        $user->lastname = $request->lname;
        $user->email = $request->email;
        $user->revcenter_ID = $request->revcenter_ID;
        
        //extracting the learningpath
        $learningpath = LearningPath::where('revcenter_ID',$user->revcenter_ID)->orderby('createdat','desc')->first();
        $user->learningpath_ID = $learningpath->learningpath_ID;

        $user->gender = $request->gender;
        $user->isadmin = 0;
        $user->diagnostic = 0;
        $user->save();

        $token->user_ID = $user->user_ID;
        $token->token = Uuid::generate()->string;
        $token->save();

        $nodelist = LearningPathNodes::where('learningpath_ID',$user->learningpath_ID)->get();
        
        //population of the status tables for both the nodes and the lelssons inside the node
        foreach($nodelist as $node){

            if($node->node_ID != "Mock"){

            $total=0;
            //creation of lesson status within each node
            $lessonlist = Lessons::where('node_ID',$node->node_ID)->where('revcenter_ID',$user->revcenter_ID)->get();
            foreach($lessonlist as $lesson){
                LessonStatus::create([
                    'reviewee_ID' => $user->user_ID,
                    'learningpath_ID' =>$user->learningpath_ID,
                    'lesson_ID' => $lesson->lesson_ID,
                    'status'=>0,
                ]);

                $total++;
            }

            //creation of learningpath node status
        
            LearningPathNodeStatus::create([
                'learningpathnodestatus_ID' => Uuid::generate()->string,
                'reviewee_ID' => $user->user_ID,
                'learningpath_ID' => $user->learningpath_ID,
                'node_ID' => $node->node_ID,
                'status' => 0,
                'total'=> $total,
                'progress' => 0
                ]);

            }else{
                LearningPathNodeStatus::create([
                    'learningpathnodestatus_ID' => Uuid::generate()->string,
                    'reviewee_ID' => $user->user_ID,
                    'learningpath_ID' => $user->learningpath_ID,
                    'node_ID' => "Mock",
                    'status' => 0,
                    'total'=> 1,
                    'progress' => 0
                    ]);
            }

            $try=Users::where('user_ID',$user->user_ID);

            if($try){
                return response()->json([
                    'message' => 'Registration Successful!'
                ]);
            }else{
                return response()->json([
                    'message' => 'Registration Failed'
                ]);
            }
        } 

        return redirect (url('/'));
        }else{
        return redirect (url('/register'));
        }
    }

    public function confirmLogin(Request $request){
       if (Auth::attempt([ 
           'username'=>$request->username,
           'password'=>$request->password
           ])){

            return response()->json([
                'message' => 'Login attempt was Successful!'
            ]);

        }else{
            return response()->json([
                'message' => 'Login attempt Failed!'
            ]);
        }
    }

    public function logout(){
        Auth::logout();
       return redirect(url('/'));
    }
}
