<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Nodes;
use App\LearningPath;
use App\LearningPathNodes;
use App\LearningPathNodeStatus;
use App\Lessons;
use App\TableofSpecs;
use App\TableofSpecsLessons;
use App\Users;
use App\RevCenter;
use App\UserRevCenter;
use Webpatser\Uuid\Uuid;
use App\QuestionBank;
use App\ResourceBank;
use App\LessonModule;
use App\ModuleQuestion;
use App\ModuleChoices;

use Illuminate\Support\Facades\Auth;

class RevCenterController extends Controller
{
    //----------------------------ROUTING FUNCTIONS-----------------------------------

    public function goHome(){
        return view('revcenter.home');
    }

    public function goSettings(){
        return view('revcenter.settingspage');
    }

    public function NewLearningPath(){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        $searchLearningPath = $this->getLearningPathbyrevcenter_ID($revcenter->revcenter_ID);

        //does this rev center already have a learning path?
        if($searchLearningPath){
           return redirect(url('/revcenter/settings/learningpath'));
        }


        //does this rev center have no learning path?
        else{

        $date=$this->getCurrentDate();

        $learningpath_ID = $this->generateID();
        
        //create new learning path
        $this->createNewLearningPath($learningpath_ID,$revcenter->revcenter_ID,$date);
        
        $nodes=$this->getNodebyrevcenter_ID($revcenter->revcenter_ID);

        return view('revcenter.newlearningpath')->with(compact('nodes'));
        }
    }

    public function goCurrentLearningPath(){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        $learningpath = $this->getLearningPathbyrevcenter_ID($revcenter->revcenter_ID);

        $nodes = $this->getNodebyrevcenter_ID($revcenter->revcenter_ID);
        $learningpathnodes = $this->getLearningPathNodebyID($learningpath->learningpath_ID);
        
        return view('revcenter.viewlearningpath')->with(compact('nodes'))
                                                 ->with(compact('learningpathnodes'));

    }

    public function editLearningPath(){
        
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        $learningpath = $this->getLearningPathbyrevcenter_ID($revcenter->revcenter_ID);

        $nodes = $this->getNodebyrevcenter_ID($revcenter->revcenter_ID);
        $learningpathnodes = $this->getLearningPathNodebyID($learningpath->learningpath_ID);
        
        return view('revcenter.newlearningpath')->with(compact('nodes'))
                                                ->with(compact('learningpathnodes'));
    }

    public function goLessonModuleSettings($lesson_name){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        $lesson = Lessons::where('lesson_name',$lesson_name)->where('revcenter_ID',$revcenter->revcenter_ID)->first();
        $resourcelist = LessonModule::where('lesson_ID',$lesson->lesson_ID)->orderby('index','asc')->get();

        return view('revcenter.lessonmodulesettings')->with(compact('lesson'))
                                                     ->with(compact('resourcelist'));
    }

    public function goConfirmQuestionCount($lesson_name){
        return view("revcenter.confirmquestioncount")->with('lesson_name',$lesson_name);
    }

    public function goNewModuleQuestion($lesson_name,Request $request){
        return view("revcenter.newmodulequestion")->with('lesson_name',$lesson_name)
                                                  ->with('choicecount',$request->count);
    }

    public function saveLearningPath(Request $request){
        //variable declarations
        $learningpath= new LearningPath;

        $user = $this->getCurrentUser();
        //getting the rev center of the user logged in

        $date=$this->getCurrentDate();

        $learningpath_ID = $this->generateID();
        
        //create new learning path
        $this->createNewLearningPath($learningpath_ID,$user->revcenter_ID,$date);


        //populate learning path nodes

        //prepopulate for mock exam node
        $this->createNewLearningPathNode($learningpath_ID,"Mock",0);

        //check if node 1 has content inside
        if($request->node1 != "empty"){

            $this->createNewLearningPathNode($learningpath_ID,$request->node1,1);

            $this->createNewLearningPathNodePrerequisite($learningpath_ID,"Mock",$request->node1);
        }

        //check if node 2 has content inside
        if($request->node2 != "empty"){

            $this->createNewLearningPathNode($learningpath_ID,$request->node2,2);

            $this->createNewLearningPathNodePrerequisite($learningpath_ID,"Mock",$request->node2);
        }

        //check if node 3 has content inside
        if($request->node3 != "empty"){

            $this->createNewLearningPathNode($learningpath_ID,$request->node3,3);

            $this->createNewLearningPathNodePrerequisite($learningpath_ID,"Mock",$request->node3);
        }

        //check if node 1's children has content
        //node 1's children are nodes 4 and 5

        if($request->node4 != "empty"){

            $this->createNewLearningPathNode($learningpath_ID,$request->node4,4);

            $this->createNewLearningPathNodePrerequisite($learningpath_ID,$request->node1,$request->node4);
        }

        if($request->node5 != "empty"){

            $this->createNewLearningPathNode($learningpath_ID,$request->node5,5);

            $this->createNewLearningPathNodePrerequisite($learningpath_ID,$request->node1,$request->node5);
        }

        //check if node 2's children has content
        //node 2's children are nodes 6 and 7
        
        if($request->node6 != "empty"){

            $this->createNewLearningPathNode($learningpath_ID,$request->node6,6);

            $this->createNewLearningPathNodePrerequisite($learningpath_ID,$request->node2,$request->node6);
        }

        if($request->node7 != "empty"){
            
            $this->createNewLearningPathNode($learningpath_ID,$request->node7,7);

            $this->createNewLearningPathNodePrerequisite($learningpath_ID,$request->node2,$request->node7);

        }

        //check if node 3's children has content
        //node 3's children are nodes 8 and 9
        
        if($request->node8 != "empty"){
            
            $this->createNewLearningPathNode($learningpath_ID,$request->node8,8);

            $this->createNewLearningPathNodePrerequisite($learningpath_ID,$request->node3,$request->node8);

        }

        if($request->node9 != "empty"){
            
            $this->createNewLearningPathNode($learningpath_ID,$request->node9,9);

            $this->createNewLearningPathNodePrerequisite($learningpath_ID,$request->node3,$request->node9);

        }

        return redirect(url('/revcenter/settings/learningpath'));
    }

    public function LearningPathNewSuccess(){
        return view('revcenter.learningpathsuccess');
    }

    public function deleteChapter($chapter_name){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);

        $nodetodelete = LearningPathNodes::where('learningpathnode_ID',$chapter_name)->first();

        $subnodes = LearningPathNodes::where('parent_ID',$chapter_name)->get();

        foreach($subnodes as $subnode){
            $subsubnodes = LearningPathNodes::where('parent_ID',$subnode->learningpathnode_ID)->get();
            foreach ($subsubnodes as $subsubnode){
                $subsubnode->delete();
            }
            $subnode->delete();
        }
        $nodetodelete->delete();
        
        $nodes = $this->getNodebyrevcenter_ID($revcenter->revcenter_ID);
        $learningpathnodes = $this->getLearningPathNodebyID($learningpath->learningpath_ID);
        
        return redirect(url('revcenter/settings/learningpath/edit'));
    }

    public function goNodeList(){
        $user = $this->getCurrentUser();

        $nodes = $this->getNodebyrevcenter_ID($user->revcenter_ID);

        return view('revcenter.nodelist')->with(compact('nodes'));
    }

    public function goNodeDetails($node_ID){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        $chapter = $this->getChapterbyChapterNameandrevcenter_ID($node_ID,$revcenter->revcenter_ID);

        $lessonlist = $this->getLessonbyChapter_IDandrevcenter_ID($chapter->chapter_ID,$revcenter->revcenter_ID);
        $toslist =TableofSpecs::where('revcenter_ID',$revcenter->revcenter_ID)->where('type',"VAL")->get();
        $toslessonlist = TableofSpecsLessons::get();
        $node = $this->getChapterbyChapter_ID($chapter->chapter_ID);

        return view('revcenter.viewnode')->with(compact('node'))
                                         ->with(compact('lessonlist'))
                                         ->with(compact('toslist'))
                                         ->with(compact('toslessonlist'));

    }

    public function goNewNode(){
        return view('revcenter.newnode');
    }

    public function goNewSubnode($node){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        $learningpath=$this->getLearningPathbyrevcenter_ID($revcenter->revcenter_ID);
        $parentchapter = LearningPathNodes::where('learningpathnode_ID',$node)->first();

        return view('revcenter.newsubnode')->with(compact('parentchapter'));
    }

    public function saveNode(Request $request){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        $learningpath=$this->getLearningPathbyrevcenter_ID($revcenter->revcenter_ID);

        $generatedchapter_ID=$this->generateID();
        $generatednode_ID=$this->generateID();
        $this->createNewNode($generatedchapter_ID,$request->chaptername,$revcenter->revcenter_ID,$request->desc);
        $this->createNewLearningPathNode($generatednode_ID,$learningpath->learningpath_ID,$generatedchapter_ID,"Mock");
        
        return redirect(url('/revcenter/settings/learningpath/edit'));
    }

    public function saveSubNode(Request $request){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        $learningpath=$this->getLearningPathbyrevcenter_ID($revcenter->revcenter_ID);

        //confirm if this chapter name has already been used
        $createdChapter=$this->getChapterbyChapterNameandrevcenter_ID($request->chaptername,$revcenter->revcenter_ID);
        $generatednode_ID =$this->generateID();
        if($createdChapter == null){

            $generatedchapter_ID=$this->generateID();

            $this->createNewNode($generatedchapter_ID,$request->chaptername,$revcenter->revcenter_ID,$request->desc);
            $this->createNewLearningPathNode($generatednode_ID,$learningpath->learningpath_ID,$generatedchapter_ID,$request->chapterparent);
        }else{
            $this->createNewLearningPathNode($generatednode_ID,$learningpath->learningpath_ID,$createdChapter->chapter_ID,$request->chapterparent);
        }

        return redirect(url('/revcenter/settings/learningpath/edit'));
    }

    public function viewExamTypes(){
        return view('revcenter.examtypes');
    }

    public function NewValidatoryTOS($lesson_name){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        echo $lesson_name;
        $lesson=$this->getLessonbyRevCenter_IDandLessonName($revcenter->revcenter_ID,$lesson_name);

        return view('revcenter.newvalidatorytos')->with(compact('lesson'));
    }

    public function NewDiagnosticTOS(){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);

        $nodelist=$this->getNodebyrevcenter_ID($revcenter->revcenter_ID);
        $lessonlist=$this->getLessonbyRevCenter_ID($revcenter->revcenter_ID);
        return view('revcenter.newdiagnostictos')->with(compact('nodelist'))
                                                 ->with(compact('lessonlist'));
    }

    public function NewMockTOS(){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);

        $nodelist=$this->getNodebyrevcenter_ID($revcenter->revcenter_ID);
        $lessonlist=$this->getLessonbyRevCenter_ID($revcenter->revcenter_ID);
        return view('revcenter.newmocktos')->with(compact('nodelist'))
                                                 ->with(compact('lessonlist'));
    }

    public function SaveValidatoryTOS(Request $request){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        $date=$this->getCurrentDate();

        $lessonused = Lessons::where('lesson_ID',$request->lesson)->first();
        $generatedTOSkey = $this->generateID();

        $this->createNewTableofSpecs($generatedTOSkey,$revcenter->revcenter_ID,$request->type,null,$date);

        $this->createNewTableofSpecsLesson($generatedTOSkey,$request->lesson,$request->questioncount,null);

        return redirect (url('/revcenter/settings/node/viewlesson/'.$lessonused->lesson_name));
    }

    //for both mock and diagnostic
    public function SaveExamTOS(Request $request){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        $date=$this->getCurrentDate();

        $generatedTOSkey = $this->generateID();

        $this->createNewTableofSpecs($generatedTOSkey,$revcenter->revcenter_ID,$request->type,$request->timer,$date);

        foreach($request->entry as $key => $entry){
            $this->createNewTableofSpecsLesson($generatedTOSkey,$entry["lesson"],$entry["questioncount"],null);
        }
            return redirect (url('/revcenter/settings/learningpath'));
    }

    public function TOSSuccess(){
        return view('revcenter.tossuccess');
    }

    public function goNewLesson($node_ID){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        $chapter=$this->getChapterbyChapterNameandrevcenter_ID($node_ID,$revcenter->revcenter_ID);
        $lessonlist=$this->getLessonbyRevCenter_ID($revcenter->revcenter_ID);

        return view('revcenter.newlesson')->with('chapter_name',$chapter->chapter_name)
                                          ->with(compact('lessonlist'))
                                          ->with('node_ID',$chapter->chapter_ID)
                                          ->with('node_name',$node_ID);
    }

    public function SaveLesson(Request $request){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        //getting the rev center of the user logged in

        Lessons::create([
            'lesson_ID' => Uuid::generate()->string,
            'lesson_name' => $request->lesson_name,
            'chapter_ID' => $request->chapter_ID,
            'revcenter_ID' => $revcenter->revcenter_ID,
            'lessondesc' => $request->lessondesc,
            'parent_ID' => null
        ]);

        return redirect(url('/revcenter/settings/node/viewnode/'.$request->chapter_name));
    }

    public function goLessonDetails($lesson_ID){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        $lesson = $this->getLessonbyRevCenter_IDandLessonName($revcenter->revcenter_ID,$lesson_ID);

        $questionlist = $this->getQuestionsbyLesson_ID($lesson->lesson_ID);
        $resourcelist = $this->getResourcesbyLesson_ID($lesson->lesson_ID);
        return view('revcenter.lessondetails')->with(compact('lesson'))
                                              ->with(compact('resourcelist'))
                                              ->with(compact('questionlist'))
                                              ->with(compact('resourcelist'));
    }

    public function goNewResource($lesson_ID){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        $lesson = $this->getLessonbyRevCenter_IDandLessonName($revcenter->revcenter_ID,$lesson_ID);
        
        
        return view('revcenter.newresource')->with('lesson_ID',$lesson->lesson_ID)
                                            ->with('lesson_name',$lesson->lesson_name);
    }

    public function saveBackupResource(Request $request){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);

        $generated_ID = $this->generateID();
        $generatedmodule_ID = $this->generateID();

        $lesson = Lessons::where('lesson_name',$request->lesson_name)->where('revcenter_ID',$revcenter->revcenter_ID)->first();
        ResourceBank::create([
            'resource_ID'=>$generated_ID,
            'lesson_ID'=>$lesson->lesson_ID,
            'resource'=>$request->resource
        ]);
        return redirect(url('/revcenter/settings/node/viewlesson/'.$request->lesson_name));
    }

    public function uploadResource(Request $request){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);

        $generated_ID = $this->generateID();
        $generatedmodule_ID = $this->generateID();

        $lesson = Lessons::where('lesson_name',$request->lesson_name)->where('revcenter_ID',$revcenter->revcenter_ID)->first();
        
        $index = $this->getIndex($lesson->lesson_ID);
        ResourceBank::create([
            'resource_ID'=>$generated_ID,
            'lesson_ID'=>$lesson->lesson_ID,
            'resource'=>$request->resource
        ]);

        if ($index == null){
            LessonModule::create([
                'lessonmodule_ID'=>$generatedmodule_ID,
                'lesson_ID'=>$lesson->lesson_ID,
                'resource_ID'=>$generated_ID,
                'modulequestions_ID'=>null,
                'index'=>1
            ]);
        }else{
            $currentindex=$index->index;
            $index = $currentindex += 1;
            LessonModule::create([
            'lessonmodule_ID'=>$generatedmodule_ID,
            'lesson_ID'=>$lesson->lesson_ID,
            'resource_ID'=>$generated_ID,
            'modulequestions_ID'=>null,
            'index'=>$index
        ]);
        }
        return redirect(url('/revcenter/settings/lesson/'.$request->lesson_name.'/module'));
    }

    public function saveModuleQuestion($lesson_name, Request $request){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);

        $generated_ID = $this->generateID();
        $generatedmodule_ID = $this->generateID();

        $lesson = Lessons::where('lesson_name',$request->lesson_name)->where('revcenter_ID',$revcenter->revcenter_ID)->first();
        $index = $this->getIndex($lesson->lesson_ID);
        
        ModuleQuestion::create([
            'modulequestion_ID'=>$generated_ID,
            'question'=>$request->question
        ]);

        foreach($request->choice as $key => $entry){
            ModuleChoices::create([
                'modulechoice_ID'=>$this->generateID(),
                'modulequestion_ID'=>$generated_ID,
                'choice'=>$entry["choice"],
                'response'=>$entry["response"]
            ]);
        }

        if ($index == null){
            LessonModule::create([
                'lessonmodule_ID'=>$generatedmodule_ID,
                'lesson_ID'=>$lesson->lesson_ID,
                'resource_ID'=>null,
                'modulequestions_ID'=>$generated_ID,
                'index'=>1
            ]);
        }else{
            $currentindex=$index->index;
            $index = $currentindex += 1;
            LessonModule::create([
            'lessonmodule_ID'=>$generatedmodule_ID,
            'lesson_ID'=>$lesson->lesson_ID,
            'resource_ID'=>null,
            'modulequestions_ID'=>$generated_ID,
            'index'=>$index
            ]);
        }
        return redirect(url('/revcenter/settings/lesson/'.$lesson_name.'/module'));
    }

    public function testModule($lesson_name,$index){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);

        $lesson = Lessons::where('lesson_name',$lesson_name)->where('revcenter_ID',$revcenter->revcenter_ID)->first();

        $lessonmodule = LessonModule::where('lesson_ID',$lesson->lesson_ID)->where('index',$index)->first();

        if($lessonmodule == null){
            return redirect(url("/revcenter/settings/lesson/".$lesson_name."/module"));
        }
        if($lessonmodule->resource_ID){
            return view("revcenter.testlessonmodule")->with(compact('lessonmodule'))
                                                     ->with(compact('lesson'));
        }else{
            $choices = ModuleChoices::where('modulequestion_ID',$lessonmodule->modulequestions_ID)->inRandomOrder()->get();
            return view("revcenter.testquestionmodule")->with(compact('lessonmodule'))
                                                       ->with(compact('lesson'))
                                                       ->with(compact('choices'));
        }
    }

    public function getResponse(Request $request){
        $nextindex = $request->index;
        $choice=ModuleChoices::where('modulechoice_ID',$request->choice)->first();
        $lesson=$request->lesson;

        return view("revcenter.displayresponse")->with('response',$choice->response)
                                                ->with('lesson',$lesson)
                                                ->with('index',$nextindex);
    }

    public function goScanPDF($lesson_ID){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        $lesson = $this->getLessonbyRevCenter_IDandLessonName($revcenter->revcenter_ID,$lesson_ID);
        return view('revcenter.selectPDF')->with('lesson_ID',$lesson->lesson_ID)
                                            ->with('lesson_name',$lesson->lesson_name);
    }

    
    public function uploadPDF(Request $request){

        $file = $request->file('scanpdf');
        $new_name = rand().'.'.$file->getClientOriginalExtension(); 
        $file->move(public_path("resources"),$new_name);

        $filepath="/resources/".$new_name;
        dd($filepath);
        // return view("revcenter.newquestionsuccess")->with('filepath',$filepath)
        //                                            ->with('filename',$new_name)
        //                                            ->with('lesson_ID',$request->lesson_ID);
        }

    public function goNewQuestion($lesson_ID){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        $lesson = $this->getLessonbyRevCenter_IDandLessonName($revcenter->revcenter_ID,$lesson_ID);
        return view('revcenter.newquestion')->with('lesson_ID',$lesson->lesson_ID)
                                            ->with('lesson_name',$lesson->lesson_name);
    }

    public function goNewBackupResource($lesson_ID){
        $user = $this->getCurrentUser();
        $revcenter = $this->getRevCenter($user->user_ID);
        $lesson = $this->getLessonbyRevCenter_IDandLessonName($revcenter->revcenter_ID,$lesson_ID);
        return view('revcenter.newbackupresource')->with('lesson_ID',$lesson->lesson_ID)
                                            ->with('lesson_name',$lesson->lesson_name);
    }
 
    public function uploadQuestion(Request $request){
        $generatedquestion_ID=$this->generateID();
        $correctanswer_ID=$this->generateID();
        

        $this->createNewQuestion($generatedquestion_ID,$request->lesson_ID,$request->question,$correctanswer_ID,$request->difficulty);
        $this->createNewChoice($correctanswer_ID,$generatedquestion_ID,$request->rAnswer,"1");

        foreach($request->choice as $choice){
            $choice_ID=$this->generateID();
            $this->createNewChoice($choice_ID,$generatedquestion_ID,$choice,"0");
        }

        return redirect(url('/revcenter/settings/node/viewlesson/'.$request->lesson_name));
    }


    public function viewResource($resource){
        $resourceused = ResourceBank::where('resource',$resource)->first();
        $lessonused = Lessons::where('lesson_ID',$resourceused->lesson_ID)->first();

        return view("revcenter.viewresource")->with(compact('lessonused'))
                                             ->with('resource',$resource);
    }

    public function viewQuestion($question){
        $questionused = QuestionBank::where('question',$question)->first();
        $lessonused = Lessons::where('lesson_ID',$questionused->lesson_ID)->first();

        return view("revcenter.viewquestion")->with(compact('lessonused'))
                                             ->with('question',$question);
    }

    

    
     //----------------------------REUSABLE FUNCTIONS-----------------------------------

     public function getCurrentUser(){
        //determining which user is currently logged on
        $user_ID = Auth::id();
        //getting the user table where the user id in previous line exists
        $user = Users::where('user_ID',$user_ID)->first();

        return $user;
    }

    public function getRevCenter($user_ID){
        $userrevcenter = UserRevCenter::where('user_ID',$user_ID)->first();
        $revcenter = RevCenter::where('revcenter_ID',$userrevcenter->revcenter_ID)->first();

        return $revcenter;
    }
    
    public function getLearningPathbyrevcenter_ID($revcenter_ID){
       $learningpath = LearningPath::where('revcenter_ID',$revcenter_ID)->orderby('createdat','desc')->first();

       return $learningpath;
    }

    public function getNodebyrevcenter_ID($revcenter_ID){
        $nodes=Nodes::where('revcenter_ID',$revcenter_ID)->get();
        return $nodes;
    }

    public function getLearningPathNodebyID($learningpath_ID){
        $learningpathnode = LearningPathNodes::where('learningpath_ID',$learningpath_ID)->get();

        return $learningpathnode;
    }

    public function getLessonbyChapter_IDandrevcenter_ID($chapter_ID,$revcenter_ID){
        $lesson = Lessons::where('chapter_ID',$chapter_ID)->where('revcenter_ID',$revcenter_ID)->get();

        return $lesson;
    }

    public function getChapterbyChapter_ID($chapter_ID){
        $chapter = Nodes::where('chapter_ID',$chapter_ID)->first();

        return $chapter;
    }

    public function getLessonbyRevCenter_IDandLessonName($revcenter_ID,$lesson_name){

        $lesson= Lessons::where('revcenter_ID',$revcenter_ID)->where('lesson_name',$lesson_name)->first();

        return $lesson;
    }

    public function getLessonbyRevCenter_ID($revcenter_ID){

        $lesson= Lessons::where('revcenter_ID',$revcenter_ID)->get();

        return $lesson;
    }

    public function getResourcesbyLesson_ID($lesson_ID){
        
        $resources= ResourceBank::where('lesson_ID',$lesson_ID)->orderby('resource_ID','desc')->get();

        return $resources;
    }

    public function getQuestionsbyLesson_ID($lesson_ID){
        
        $questions= QuestionBank::where('lesson_ID',$lesson_ID)->orderby('question_ID','desc')->paginate(10);

        return $questions;
    }

    public function getCurrentDate(){
        date_default_timezone_set('Asia/Manila');
        $date=date("Y-m-d H:i:s");

        return $date;
    }

    public function getChapterbyChapterNameandrevcenter_ID($chapter_name,$revcenter_ID){
        $chapter = Nodes::where('chapter_name',$chapter_name)->where('revcenter_ID',$revcenter_ID)->first();

        return $chapter;
    }

    public function createNewLearningPath($learningpath_ID,$revcenter_ID,$date){
        LearningPath::create([
            'learningpath_ID'=>$learningpath_ID,
            'revcenter_ID'=>$revcenter_ID,
            'createdat'=>$date
        ]);
    }

    public function createNewLearningPathNode($generated_ID,$learningpath_ID,$chapter_ID,$parent){
        LearningPathNodes::create([
            'learningpathnode_ID'=>$generated_ID,
            'learningpath_ID'=>$learningpath_ID,
            'chapter_ID'=>$chapter_ID,
            'parent_ID'=>$parent,
        ]);
    }

    public function createNewNode($generated_ID,$chaptername,$revcenter_ID,$desc){
        Nodes::create([
            'chapter_ID'=>$generated_ID,
            'chapter_name'=>$chaptername,
            'revcenter_ID'=>$revcenter_ID,
            'description'=>$desc
        ]);
    }

    public function createNewTableofSpecs($generatedTOSkey,$revcenter_ID,$type,$timer,$date){

        TableofSpecs::create([
            'tableofspecs_ID'=>$generatedTOSkey,
            'revcenter_ID'=>$revcenter_ID,
            'type'=>$type,
            'timer'=>$timer,
            'datecreated'=>$date
        ]);
    }

    public function createNewTableofSpecsLesson($generatedTOSkey,$lesson_ID,$questioncount,$timer){
        TableofSpecsLessons::create([
            'tableofspecs_ID'=>$generatedTOSkey,
            'lesson_ID'=>$lesson_ID,
            'questionsnumber'=>$questioncount,
            'timer'=>$timer
        ]);
    }

    public function createNewResource($lesson_ID,$new_name){
        ResourceBank::create([
            'resource_ID'=>Uuid::generate()->string,
            'lesson_ID'=>$lesson_ID,
            'resource'=>$new_name,
        ]);

    }

    public function createNewQuestion($question_ID,$lesson_ID,$question,$correctanswer,$difficulty){
        QuestionBank::create([
            'question_ID'=>$question_ID,
            'lesson_ID'=>$lesson_ID,
            'question'=>$question,
            'correctanswer'=>$correctanswer,
            'difficulty'=>$difficulty
        ]);
    }

    public function createNewChoice($choice_ID,$question_ID,$choice,$response){
        ModuleChoices::create([
            'modulechoice_ID'=>$choice_ID,
            'modulequestion_ID'=>$question_ID,
            'choice'=>$choice,
            'response'=>$response
        ]);
    }

    public function getLearningPathNodebyLearningPath_IDandChapter_ID($learningpath_ID,$chapter_ID){
        $learningpathnode = LearningPathNodes::where('learningpath_ID',$learningpath_ID)->where('chapter_ID',$chapter_ID)->first();
        return $learningpathnode;
    }

    public function generateID(){
        $id = Uuid::generate()->string;

        return $id;
    }

    public function getIndex($lesson_ID){
        $index = LessonModule::where('lesson_ID',$lesson_ID)->orderby('index','desc')->first();
        return $index;
    }
}
