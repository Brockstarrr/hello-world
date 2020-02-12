<?php
namespace App\Http\Controllers\API;
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
use App\Http\Models\Front\ViewJobModel;
use App\Http\Models\API\OnBoardingModel;
use App\Http\Models\API\CommonModel;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Pagination;

class OnBoardingController extends Controller 
{
	public function __construct(Request $request)
	{
		$this->ViewJobModel = new ViewJobModel();
		$this->OnBoardingModel 	= new OnBoardingModel();
		$this->CommonModel 	= new CommonModel();
		$this->UserModel = new UserModel();
	}
	public function OnBoardingTestQuestion(Request $request)
	{
		$Data 			= $request->all();	
		$ViewJobList  	= array();
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		if($UserID=='')
		{
			$Response = ['Status'=>false,'Message'=>'UserID Missing.'];	
			return response()->json($Response);	
		}
		if($AccessToken=='')
		{
			$Response = ['Status'=>false,'Message'=>'AccessToken Missing.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);	
		if(!empty($CheckLoginDetails))
		{
			$GetOnBoardingQuizQuestion = $this->UserModel->GetOnBoardingQuizQuestion($UserID);
			if($GetOnBoardingQuizQuestion['Status']==0)
			{
				$ChoiceList = json_decode($GetOnBoardingQuizQuestion['GetQuestion']->choice);
				$Choice = array();
				foreach ($ChoiceList as $key => $value) 
				{
					$Sample=array();
					$Sample['ChoiceID']=$key;
					$Sample['Choice']=$value;
					array_push($Choice, $Sample);
				}

				$GetTotaldQuestion = $this->UserModel->GetTotaldQuestion();
				$QuestionNo = $this->UserModel->GetTotalAttempedQuestion($UserID)+1;
				$Bar = $QuestionNo * (100/$GetTotaldQuestion);

				$Response = ['Status'=>true,
							'Message'=>'Question For OnBoarding Test.',
							'QuestionStatus'=>true,
							'QuestionID'=>$GetOnBoardingQuizQuestion['GetQuestion']->id,
							'Question'=>$GetOnBoardingQuizQuestion['GetQuestion']->question,
							'Bar'=>$Bar,
							'Choice'=>$Choice
							];
			}
			else
			{
				$Response = ['Status'=>true,
							'Message'=>'Question For OnBoarding Test Has Finished.',
							'QuestionStatus'=>false,
							];
			}
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];		
		}
	  	return response()->json($Response);			
	}

	public function OnBoardingTestQuestionResult(Request $request)
	{
		$Data 			= $request->all();	
		$ViewJobList  	= array();
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$QuestionID 	= $Data['QuestionID'];	
		$Selected 		= $Data['Selected'];	
		if($UserID=='')
		{
			$Response = ['Status'=>false,'Message'=>'UserID Missing.'];	
			return response()->json($Response);	
		}
		if($AccessToken=='')
		{
			$Response = ['Status'=>false,'Message'=>'AccessToken Missing.'];	
			return response()->json($Response);	
		}
		if($QuestionID=='')
		{
			$Response = ['Status'=>false,'Message'=>'QuestionID Missing.'];	
			return response()->json($Response);	
		}
		if($Selected=='')
		{
			$Response = ['Status'=>false,'Message'=>'Selected Choice ID Missing.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);	
		if(!empty($CheckLoginDetails))
		{
			$CheckQuestionResultExist = $this->OnBoardingModel->CheckQuestionResultExist($UserID, $QuestionID);
			if($CheckQuestionResultExist)
			{
				$Response = ['Status'=>false,'Message'=>'You Have Allredy Answered This Question.'];
			}
			else
			{
				$SaveResult  	= $this->UserModel->SaveResult($QuestionID,$Selected,$UserID);
				$GetQuestion 	= $this->UserModel->GetQuestion($QuestionID);
				$RightAnswer 	= $GetQuestion->answer;
				if($RightAnswer==$Selected)
				{ 
					$AnswerStatus = true;		
				} 
				else 
				{
					$AnswerStatus = false;
				}
				$Response = ['Status'=>true,
							'Message'=>'Answer For Question.',
							'AnswerStatus'=>$AnswerStatus 
							];
			}
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];		
		}
	  	return response()->json($Response);	
	}
}