<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Users;
use App\RevCenter;
use App\UsersToken;
use App\LearningPath;
use App\LearningPathNodes;
use App\LearningPathNodeStatus;
use App\LessonStatus;
use App\Lessons;
use App\UserRevCenter;
use App\UserLearningPath;
use Webpatser\Uuid\Uuid;
use Illuminate\Support\Facades\Auth;

class PreLoginController extends Controller
{
    //----------------------------ROUTING FUNCTIONS-----------------------------------

    public function goLanding(){
        return view('pre-login.index');
    }

    public function goRegistration(){
        $revcenters = RevCenter::get();
        $errormessage="";
        return view('pre-login.registration')->with(compact('revcenters'))
                                             ->with('errormessage',$errormessage);
    }

    public function goLogin(){
        return view('pre-login.login');
    }

    public function goAdminRegistration(){

        return view('pre-login.adminregisteration');
    }

    public function saveNewAdmin(Request $request){
        $generateduser_ID = $this->generateID();
        $generatedrevcenter_ID = $this->generateID();

        $this->createNewRevCenter($generatedrevcenter_ID,$request->revcenter);

        $this->createNewUser($generateduser_ID,$request->username,$request->password,$request->fname,$request->lname,$request->email,$request->gender,1,1);

        $this->createNewUserRevCenter($generateduser_ID,$generatedrevcenter_ID);

        return redirect(url('/login'));
        
    }

    public function saveNewUser(Request $request){

        $generateduser_ID = $this->generateID();

        if($request->revcenter == null){
            $errormessage = "Please select at least one Review Center";
            return redirect()->back()->with("errormessage",$errormessage);
        }
        
        if($request->password == $request->confpassword){
                                                                                        
        $this->createNewUser($generateduser_ID,$request->username,$request->password,$request->fname,$request->lname,$request->email,$request->gender,0,0);

        $this->createNewToken($generateduser_ID);

        foreach($request->revcenter as $row){
            $this->createNewUserRevCenter($generateduser_ID,$row);
            $learningpath=$this->getLearningPathbyrevcenter_ID($row);
            $this->createNewUserLearningPath($generateduser_ID,$learningpath->learningpath_ID,0);

            $nodelist = $this->getLearningPathNodesbyLearningPath_ID($learningpath->learningpath_ID);

            //population of the status tables for both the nodes and the lelssons inside the node
                foreach($nodelist as $node){

                    if($node->chapter_ID != "Mock"){

                    $total=0;

                    //creation of lesson status within each node
                    $lessonlist=$this->getLessonbyChapterIDandrevcenter_ID($node->chapter_ID,$row);
                    foreach($lessonlist as $lesson){

                        $this->createLessonStatus($generateduser_ID,$learningpath->learningpath_ID,$lesson->lesson_ID);

                        $total++;
                    }

                    //creation of learningpath node status

                    $this->createLearningPathNodeStatus($generateduser_ID,$learningpath->learningpath_ID,$node->chapter_ID,$total);
                    }else{
                        $this->createLearningPathNodeStatus($generateduser_ID,$learningpath->learningpath_ID,"Mock",1);
                    }
                } 
        }

        
        
        

        return redirect (url('/login'));
        }else{
        return redirect (url('/register'));
        }
    }

    public function confirmLogin(Request $request){
       if (Auth::attempt([ 
           'username'=>$request->username,
           'password'=>$request->password
           ])){

            $user = Users::where('username',$request->username)->first();
            //is user an admin?
            if($user->is_admin()){
                return redirect(url('revcenter/home'));
            }
            //is user a reviewee? s
            else{
                $diagnosticstatus = $user->diagnostic;
                return redirect(url('reviewee/home'));
            }

        }else{
            return redirect(url('/login'));
        }
    }

    public function logout(){
        Auth::logout();
       return redirect(url('/'));
    }




    
    //----------------------------REUSABLE FUNCTIONS-----------------------------------

    public function createNewRevCenter($revcenter_ID, $revcenter_name){

        RevCenter::create([
            'revcenter_ID'=> $revcenter_ID,
            'revcenter_name' => $revcenter_name
        ]);
    }

    public function createNewUserRevCenter($user_ID,$revcenter_ID){
        UserRevCenter::create([
            'user_ID'=>$user_ID,
            'revcenter_ID'=>$revcenter_ID
        ]);
    }

    public function createNewUserLearningPath($user_ID,$learningpath_ID,$status){
        UserLearningPath::create([
            'user_ID'=>$user_ID,
            'learningpath_ID'=>$learningpath_ID,
            'status'=>$status
        ]);
    }

    public function createNewUser($user_ID,$username,$password,$firstname,$lastname,$email,$gender,$isadmin,$diagnostic){

        Users::create([
            'user_ID'=>$user_ID,
            'username' => $username,
            'password' => bcrypt($password),
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'gender' => $gender,
            'isadmin' => $isadmin,
            'diagnostic' => $diagnostic,
            'remember_token' => Uuid::generate()->string

        ]);
    }

    public function createNewToken($user_ID){

        UsersToken::create([
            'user_ID' => $user_ID,
            'token' => Uuid::generate()->string
        ]);
    }

    public function createLessonStatus($reviewee_ID,$learningpath_ID,$lesson_ID){
        LessonStatus::create([
            'reviewee_ID' => $reviewee_ID,
            'learningpath_ID' =>$learningpath_ID,
            'lesson_ID' => $lesson_ID,
            'status'=>0,
        ]);
    }

    public function createLearningPathNodeStatus($user_ID,$learningpath_ID,$chapter_ID,$total){

        LearningPathNodeStatus::create([
            'learningpathnodestatus_ID' => Uuid::generate()->string,
            'reviewee_ID' => $user_ID,
            'learningpath_ID' => $learningpath_ID,
            'chapter_ID' => $chapter_ID,
            'status' => 0,
            'total'=> $total,
            'progress' => 0
            ]);
    }

    public function getLearningPathbyrevcenter_ID($revcenter_ID){
        
        $learningpath = LearningPath::where('revcenter_ID',$revcenter_ID)->orderby('createdat','desc')->first();
 
        return $learningpath;
    }

    public function generateID(){
        $id = Uuid::generate()->string;

        return $id;
    }

    public function getLearningPathNodesbyLearningPath_ID($learningpath_ID){

        $nodelist = LearningPathNodes::where('learningpath_ID',$learningpath_ID)->get();
        
        return $nodelist;
    }

    public function getLessonbyChapterIDandrevcenter_ID($chapter_ID,$revcenter_ID){
        $lessonlist = Lessons::where('chapter_ID',$chapter_ID)->where('revcenter_ID',$revcenter_ID)->get();
        return $lessonlist;
    }
}
