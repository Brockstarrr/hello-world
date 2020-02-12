<?php
namespace App\Http\Controllers\Front;
use Route;
use Mail;
use Auth, Hash;
use Validator;
use Session;
use Redirect;
use DB;
use Crypt;
use Illuminate\Http\Request;
use App\Http\Models\Front\UserModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class SkillAssessmentController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->UserModel = new UserModel();
	}

	public function SkillsAssessment(){
		$UserID = Session::get('UserID');
		$Data['Title'] 				= 'Skill Assessment';
		$Data['Menu'] 				= 'SkillsAssessment';
		$Data['Content'] 			= $this->UserModel->OnBoardingQuizStart();
		$Data['ExampAttempted'] 	= $this->UserModel->ExampAttempted($UserID);
		$Data['ResultPending'] 		= $this->UserModel->ResultPending($UserID);
		$Data['LevelList'] 		= $this->UserModel->SkillLevel();
		return View('Front/Pages/User/SkillsAssessment')->with($Data);
	}

	public function SkillsAssessmentStart(){
		$UserID = Session::get('UserID');
		
		Session::forget('Question');
		Session::forget('LevelID');
		Session::forget('CatID');

		$Data['Title'] 				= 'Skill Assessment Start';
		$Data['Menu'] 				= 'SkillsAssessment';
		$Data['LevelList'] 		= $this->UserModel->SkillLevel();
		return View('Front/Pages/User/SkillAssessmentStart')->with($Data);
	}
	public function GetSkillAssessmentQuizQuestion(Request $request){
		$Data 				= $request->all();

		$level_id 		= $Data['level_id'];
		$cat_id 			= $Data['cat_id'];
		$QuestionNo 	= $Data['QuestionNo'];
		$GetTotalAttempedQuestion 	= $Data['TotalQuestion'];
		$QuestionID 	= $Data['QuestionID'];
		
		Session::put('LevelID',$level_id);	
		Session::put('CatID',$cat_id);
		Session::save();	

		$UserID = Session::get('UserID');
		$Question = $this->UserModel->GetSkillAssessmentQuizQuestion($level_id,$cat_id,$GetTotalAttempedQuestion,$QuestionID);
		if($Question['DBCount'] > 0)
		{
			$GetTotaldQuestion = $this->UserModel->GetSkillAssessmentTotalQuestion($level_id,$cat_id);
			$QuestionNo = $QuestionNo+1;

			$GetQuestion = $Question['GetQuestion'];

			$Choice = json_decode($GetQuestion->choice);

			$Bar = $QuestionNo * (100/$GetTotaldQuestion);
			?>
			<input type="hidden" value="<?php echo $GetQuestion->id; ?>" id="QuestionID">
			<input type="hidden" value="<?=$level_id?>" id="level_id">
			<input type="hidden" value="<?=$cat_id?>" id="cat_id">
			<div class="cus_prog">
				<div class="container">
					<div class="cus_prog_cont">
						<a href="<?php echo route('UserDashboard'); ?>"><img src="<?=asset('public/Front/Design/img/bar_cross.png')?>"/></a>
						<div class="progress">
							<div class="progress-bar" role="progressbar" 
								style="width:<?php echo round($Bar); ?>%;" aria-valuenow="<?php echo round($Bar); ?>" aria-valuemin="0" 
								aria-valuemax="100">
								<span><?php echo round($Bar); ?>%</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="on_board_cont read">
				<div class="container">
					<div class="quiz_cont">
						<h3><?php echo $QuestionNo.'. '.$GetQuestion->question; ?></h3>
						<div class="quiz_ques cus_radio">
						 	<?php foreach($Choice as $key=>$value){ ?>
							<div class="custom-control custom-radio">
								<input onclick='Hide("Err");' type="radio" value="<?php echo $key; ?>" class="custom-control-input" id="customRadio_<?php echo $key; ?>" name="exampleRadios"/>
								<label class="custom-control-label" for="customRadio_<?php echo $key; ?>"><?php echo $value; ?></label>
							</div>
							<?php } ?>
						</div>
					</div>
					<center><span id='Err'></span></center>
				</div>
			</div>
			<div class="board_footer text-center">
				<div class="container">
					<a href="javascript:void(0);" onclick="CheckQuestion();" class="btn cus_btn big">Check</a>
				</div>
			</div>
			<?php
		}
		else
		{
			echo 'No Data Found';
			//return redirect('skills-assessment');
		}
		exit();
	}

	public function GetSkillAssessmentQuizResult(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();

		$LevelID 		= Session::get('LevelID');	
		$CatID 			= Session::get('CatID');

		$QuestionID		= $Data['QuestionID'];
		$Selected 		= $Data['Selected'];
		$QuestionNo   = $Data['QuestionNo'];

		$Save['QuestionID'] = $QuestionID;
		$Save['Selected'] 	= $Selected;
		$Save['UserID'] 		= $UserID;

  	if(Session::has('Question'))
  	{
  		$OldSession = Session::get('Question');  
  		array_push($OldSession, $Save);
			Session::forget('Question');  		
  		Session::put('Question',$OldSession);
			Session::save();
  	}
  	else
  	{
  		$SaveData[] = $Save;
			Session::put('Question',$SaveData);
			Session::save();
  	}
		
		//echo '<pre>';print_r(Session::get('Question'));echo '</pre>';

		$GetQuestion 	= $this->UserModel->GetSkillQuestion($QuestionID);

		$GetTotaldQuestion = $this->UserModel->GetSkillAssessmentTotalQuestion($LevelID,$CatID);
		
		$Choice 		= json_decode($GetQuestion->choice);
		$RightAnswer 	= $GetQuestion->answer;

		$Bar = $QuestionNo * (100/$GetTotaldQuestion);
		?>
			<input type="hidden" value="<?=$QuestionID?>" id="QuestionID">
			<input type="hidden" value="<?=$LevelID?>" id="level_id">
			<input type="hidden" value="<?=$CatID?>" id="cat_id">
			<div class="cus_prog">
				<div class="container">
					<div class="cus_prog_cont">
						<a href="<?php echo route('UserDashboard'); ?>"><img src="<?=asset('public/Front/Design/img/bar_cross.png')?>"/></a>
						<div class="progress">
							<div class="progress-bar" role="progressbar" 
								style="width:<?php echo round($Bar); ?>%;" aria-valuenow="<?php echo round($Bar); ?>" aria-valuemin="0" 
								aria-valuemax="100">
								<span><?php echo round($Bar); ?>%</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="on_board_cont read">
				<div class="container">
					<div class="quiz_cont">
						<h3><?php echo $QuestionNo.'. '.$GetQuestion->question; ?></h3>
						<div class="quiz_ques cus_radio">
						 	<?php foreach($Choice as $key=>$value){ ?>
							<div class="custom-control custom-radio <?php if($RightAnswer==$key){ echo "right"; } else { echo "wrong"; }?>">
								<input disabled <?php if($Selected==$key){ echo "checked"; }?> onclick='Hide("Err");' type="radio" value="<?php echo $key; ?>" class="custom-control-input" id="customRadio_<?php echo $key; ?>" name="exampleRadios"/>
								<label class="custom-control-label" for="customRadio_<?php echo $key; ?>"><?php echo $value; ?></label>
							</div>
							<?php } ?>
						</div>
						<?php if($RightAnswer==$Selected){ ?>
						<div class="correct">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z"/></svg>
							<span>You are correct</span>
						</div>
						<?php } else {?>
						<div class="correct wr">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm121.6 313.1c4.7 4.7 4.7 12.3 0 17L338 377.6c-4.7 4.7-12.3 4.7-17 0L256 312l-65.1 65.6c-4.7 4.7-12.3 4.7-17 0L134.4 338c-4.7-4.7-4.7-12.3 0-17l65.6-65-65.6-65.1c-4.7-4.7-4.7-12.3 0-17l39.6-39.6c4.7-4.7 12.3-4.7 17 0l65 65.7 65.1-65.6c4.7-4.7 12.3-4.7 17 0l39.6 39.6c4.7 4.7 4.7 12.3 0 17L312 256l65.6 65.1z"/></svg>
							<span>You are wrong</span>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="board_footer text-center">
				<div class="container">
					<?php if($QuestionNo == $GetTotaldQuestion){?>
					<a href="<?=route('SaveSkillAssessmentResult')?>" class="btn cus_btn big">Complete Your Quiz</a>
					<?php } else {?>
					<a href="javascript:void(0);" onclick="ContinueQuestion(<?=$QuestionID?>);" class="btn cus_btn big">Continue</a>
					<?php }?>
				</div>
			</div> 
			
			<?php
		exit();
	}
	public function SaveSkillAssessmentResult(){
		if(Session::has('Question')){
			$SkilArr = Session::get('Question');

			$ExamIDData = DB::table('serial_no')->select('*')->where('id',1)->first();
			$ExamID = $ExamIDData->serial_type.$ExamIDData->serial_no;

			/*Update SerialNo*/
			$UpdateExamID = $ExamIDData->serial_no+1;
			DB::table('serial_no')->where('id',1)->update(['serial_no'=>$UpdateExamID]);
			/*Update SerialNo*/

			foreach ($SkilArr as $row) {
				$QuestionDetails    	= $this->UserModel->GetSkillQuestion($row['QuestionID']);
				$Details['exam_id'] 	= $ExamID;
		    $Details['profile_id']= $row['UserID'];
		    $Details['quiz_id']   = $row['QuestionID'];
		    $Details['cat_id']   	= Session::get('CatID');
		    $Details['level_id']  = Session::get('LevelID');
		    $Details['question']  = $QuestionDetails->question;
		    $Details['choice']    = $QuestionDetails->choice;
		    $Details['answer']    = $QuestionDetails->answer;
		    $Details['your_answer'] = $row['Selected'];
		    $Details['add_date'] 	= date('Y-m-d H:i:s');
				//echo '<pre>';print_r($Details);
				$this->UserModel->SaveSkillResult($Details);
			}
			Session::forget('Question');
			Session::forget('LevelID');
			Session::forget('CatID');

			return redirect()->route('SkillsAssessment');
		}else{
			return redirect()->route('SkillsAssessment');
		}
		//echo '<pre>';print_r(Session::get('Question'));echo '</pre>';
	}
	public function GetSkillCategoryList(Request $request){
		$Data = $request->all();
		$level_id = $Data['level_id'];
		$CategoryArr = $this->UserModel->SkillCategory($level_id);
		echo '<option value="">Select Category</option>';
		foreach ($CategoryArr as $cat) {
			echo '<option value="'.$cat->id.'">'.$cat->category.'</option>';
		}
	}
}
?>