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
use App\Http\Models\API\SignupModel;
use App\Http\Models\API\LoginModel;
use App\Http\Models\API\SocialLoginModel;
use App\Http\Models\API\CommonModel;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Pagination;

class SocialLoginController extends Controller 
{
	public function __construct(Request $request)
	{
		$this->SignupModel 			= new SignupModel();
		$this->LoginModel 			= new LoginModel();
		$this->SocialLoginModel 	= new SocialLoginModel();
		$this->CommonModel 			= new CommonModel();
		$this->Pagination 			= new Pagination();
	}	
	public function FacebookLoginAPI(Request $request)
	{
		$Data 		= $request->all();	
		$Response   = array();	
		$FacebookID = $Data['FacebookID'];	
		$FirstName 	= $Data['FirstName'];	
		$Mobile 	= $Data['Mobile'];	
		$Email 		= $Data['Email'];

		if($FacebookID=='')
		{
			$Response = ['Status' => false,'Message' => 'FacebookID Parameter Missing.'];
			return response()->json($Response);
		}			
		$CheckExist = $this->SocialLoginModel->CheckExistFacebook($FacebookID);
		if(!empty($CheckExist))
		{
			$UserID = $CheckExist->id;
			$LoginDetails = $this->LoginModel->LoginDetails($UserID);

			$UserDetails['UserID'] 				= $LoginDetails->id;
			$UserDetails['AccessToken'] 		= $LoginDetails->access_token;
			$UserDetails['FirstName'] 			= $LoginDetails->first_name;
			$UserDetails['LastName'] 			= $LoginDetails->last_name;
			$UserDetails['ProfileImage'] 		= '';				

			$Response['Status'] 				= true;
			$Response['Message'] 				= 'Facebook Login Successfully.';
			$Response['OnboardingQuizStatus'] 	= $LoginDetails->onboarding_quiz_status;
			$Response['UserLoginDetails'] 		= $UserDetails;

			return response()->json($Response);
		}
		else
	  	{
	  		if($Email=='')
			{
				$Response = ['Status' => false,'Message' => 'Email Parameter Missing.'];
				return response()->json($Response);
			}
	  		$Details['code'] 		= $this->Pagination->GenerateRandomCode(6); 
	  		$Details['f_id'] 		= $FacebookID;
	  		$Details['first_name'] 	= $FirstName;
	  		$Details['email'] 		= $Email;
	  		$Details['phone'] 		= $Mobile;

	  		$CheckEmail = $this->SocialLoginModel->CheckEmail($Email);
	  		if($CheckEmail>0)
	  		{
	  			$this->SocialLoginModel->UpdateUser($Details,$Email);
	  			
	  			$UserData = $this->SocialLoginModel->UserDetails($Email);

	  			$UserID = $UserData->id;
				$LoginDetails = $this->LoginModel->LoginDetails($UserID);

	  			$UserDetails['UserID'] 				= $UserData->id;
				$UserDetails['AccessToken'] 		= $UserData->access_token;
				$UserDetails['FirstName'] 			= $UserData->first_name;
				$UserDetails['LastName'] 			= $UserData->last_name;
				$UserDetails['ProfileImage'] 		= '';

	  			$Response['Status'] 				= true;
				$Response['Message'] 				= 'Facebook Login Successfully.';
				$Response['OnboardingQuizStatus'] 	= $LoginDetails->onboarding_quiz_status;
				$Response['UserLoginDetails'] 		= $UserDetails;
				return response()->json($Response);
	  		}
	  		else
	  		{
	  			$SaveDetails = $this->SocialLoginModel->SaveDetailsFacebook($Details);
		  		if($SaveDetails!='')
		  		{
		  			$UserID = $SaveDetails;
					$LoginDetails = $this->LoginModel->LoginDetails($UserID);

					$UserDetails['UserID'] 				= $LoginDetails->id;
					$UserDetails['AccessToken'] 		= $LoginDetails->access_token;
					$UserDetails['FirstName'] 			= $LoginDetails->first_name;
					$UserDetails['LastName'] 			= $LoginDetails->last_name;
					$UserDetails['ProfileImage'] 		= '';				

					$Response['Status'] 				= true;
					$Response['Message'] 				= 'Facebook Login Successfully.';
					$Response['OnboardingQuizStatus'] 	= $LoginDetails->onboarding_quiz_status;
					$Response['UserLoginDetails'] 		= $UserDetails;

					return response()->json($Response);
		  		}
		  		else
		  		{
					$Response['Status'] 				= false;
					$Response['Message'] 				= 'Something wrong please try again.';
					return response()->json($Response);
		  		}
	  		}	  		
	  	}
	}

	public function LinkedInLoginAPI(Request $request)
	{
		$Data 		= $request->all();	
		$Response   = array();	
		$LinkedInID = $Data['LinkedInID'];	
		$FirstName 	= $Data['FirstName'];	
		$Mobile 	= $Data['Mobile'];	
		$Email 		= $Data['Email'];	

		if($LinkedInID=='')
		{
			$Response = ['Status' => false,'Message' => 'LinkedInID Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckExist = $this->SocialLoginModel->CheckExistLinkedIn($LinkedInID);
		if(!empty($CheckExist))
		{
			$UserID = $CheckExist->id;
			$LoginDetails = $this->LoginModel->LoginDetails($UserID);

			$UserDetails['UserID'] 				= $LoginDetails->id;
			$UserDetails['AccessToken'] 		= $LoginDetails->access_token;
			$UserDetails['FirstName'] 			= $LoginDetails->first_name;
			$UserDetails['LastName'] 			= $LoginDetails->last_name;
			$UserDetails['ProfileImage'] 		= '';				

			$Response['Status'] 				= true;
			$Response['Message'] 				= 'LinkedIn Login Successfully.';
			$Response['OnboardingQuizStatus'] 	= $LoginDetails->onboarding_quiz_status;
			$Response['UserLoginDetails'] 		= $UserDetails;

			return response()->json($Response);
		}
		else
	  	{
	  		if($Email=='')
			{
				$Response = ['Status' => false,'Message' => 'Email Parameter Missing.'];
				return response()->json($Response);
			}
	  		$Details['code'] 		= $this->Pagination->GenerateRandomCode(6); 
	  		$Details['l_id'] 		= $LinkedInID;
	  		$Details['first_name'] 	= $FirstName;
	  		$Details['email'] 		= $Email;
	  		$Details['phone'] 		= $Mobile;

	  		$CheckEmail = $this->SocialLoginModel->CheckEmail($Email);
	  		if($CheckEmail>0)
	  		{
	  			$this->SocialLoginModel->UpdateUser($Details,$Email);
	  			
	  			$UserData = $this->SocialLoginModel->UserDetails($Email);

	  			$UserID = $UserData->id;
				$LoginDetails = $this->LoginModel->LoginDetails($UserID);

	  			$UserDetails['UserID'] 				= $UserData->id;
				$UserDetails['AccessToken'] 		= $UserData->access_token;
				$UserDetails['FirstName'] 			= $UserData->first_name;
				$UserDetails['LastName'] 			= $UserData->last_name;
				$UserDetails['ProfileImage'] 		= '';

	  			$Response['Status'] 				= true;
				$Response['Message'] 				= 'LinkedIn Login Successfully.';
				$Response['OnboardingQuizStatus'] 	= $LoginDetails->onboarding_quiz_status;
				$Response['UserLoginDetails'] 		= $UserDetails;
				return response()->json($Response);
	  		}
	  		else
	  		{
	  			$SaveDetails = $this->SocialLoginModel->SaveDetailsLinkedIn($Details);
		  		if($SaveDetails!='')
		  		{
		  			$UserID = $SaveDetails;
					$LoginDetails = $this->LoginModel->LoginDetails($UserID);

					$UserDetails['UserID'] 				= $LoginDetails->id;
					$UserDetails['AccessToken'] 		= $LoginDetails->access_token;
					$UserDetails['FirstName'] 			= $LoginDetails->first_name;
					$UserDetails['LastName'] 			= $LoginDetails->last_name;
					$UserDetails['ProfileImage'] 		= '';				

					$Response['Status'] 				= true;
					$Response['Message'] 				= 'LinkedIn Login Successfully.';
					$Response['OnboardingQuizStatus'] 	= $LoginDetails->onboarding_quiz_status;
					$Response['UserLoginDetails'] 		= $UserDetails;

					return response()->json($Response);
		  		}
		  		else
		  		{
					$Response['Status'] 				= false;
					$Response['Message'] 				= 'Something wrong please try again.';
					return response()->json($Response);
		  		}
	  		}
	  		
	  	}
	}

	public function GoogleLoginAPI(Request $request)
	{
		$Data 		= $request->all();	
		$Response   = array();	
		$GoogleID 	= $Data['GoogleID'];	
		$FirstName 	= $Data['FirstName'];	
		$Mobile 	= $Data['Mobile'];	
		$Email 		= $Data['Email'];	

		if($GoogleID=='')
		{
			$Response = ['Status' => false,'Message' => 'GoogleID Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckExist = $this->SocialLoginModel->CheckExistGoogle($GoogleID);
		if(!empty($CheckExist))
		{
			$UserID = $CheckExist->id;
			$LoginDetails = $this->LoginModel->LoginDetails($UserID);

			$UserDetails['UserID'] 				= $LoginDetails->id;
			$UserDetails['AccessToken'] 		= $LoginDetails->access_token;
			$UserDetails['FirstName'] 			= $LoginDetails->first_name;
			$UserDetails['LastName'] 			= $LoginDetails->last_name;
			$UserDetails['ProfileImage'] 		= '';				

			$Response['Status'] 				= true;
			$Response['Message'] 				= 'Google Login Successfully.';
			$Response['OnboardingQuizStatus'] 	= $LoginDetails->onboarding_quiz_status;
			$Response['UserLoginDetails'] 		= $UserDetails;

			return response()->json($Response);
		}
		else
	  	{
	  		if($Email=='')
			{
				$Response = ['Status' => false,'Message' => 'Email Parameter Missing.'];
				return response()->json($Response);
			}
	  		$Details['code'] 		= $this->Pagination->GenerateRandomCode(6); 
	  		$Details['g_id'] 		= $GoogleID;
	  		$Details['first_name'] 	= $FirstName;
	  		$Details['email'] 		= $Email;
	  		$Details['phone'] 		= $Mobile;

	  		$CheckEmail = $this->SocialLoginModel->CheckEmail($Email);
	  		if($CheckEmail>0)
	  		{
	  			$this->SocialLoginModel->UpdateUser($Details,$Email);
	  			
	  			$UserData = $this->SocialLoginModel->UserDetails($Email);

	  			$UserID = $UserData->id;
				$LoginDetails = $this->LoginModel->LoginDetails($UserID);

	  			$UserDetails['UserID'] 				= $UserData->id;
				$UserDetails['AccessToken'] 		= $UserData->access_token;
				$UserDetails['FirstName'] 			= $UserData->first_name;
				$UserDetails['LastName'] 			= $UserData->last_name;
				$UserDetails['ProfileImage'] 		= '';

	  			$Response['Status'] 				= true;
				$Response['Message'] 				= 'Google Login Successfully.';
				$Response['OnboardingQuizStatus'] 	= $LoginDetails->onboarding_quiz_status;
				$Response['UserLoginDetails'] 		= $UserDetails;
				return response()->json($Response);
	  		}
	  		else
	  		{
	  			$SaveDetails = $this->SocialLoginModel->SaveDetailsGoogle($Details);
		  		if($SaveDetails!='')
		  		{
		  			$UserID = $SaveDetails;
					$LoginDetails = $this->LoginModel->LoginDetails($UserID);

					$UserDetails['UserID'] 				= $LoginDetails->id;
					$UserDetails['AccessToken'] 		= $LoginDetails->access_token;
					$UserDetails['FirstName'] 			= $LoginDetails->first_name;
					$UserDetails['LastName'] 			= $LoginDetails->last_name;
					$UserDetails['ProfileImage'] 		= '';				

					$Response['Status'] 				= true;
					$Response['Message'] 				= 'Google Login Successfully.';
					$Response['OnboardingQuizStatus'] 	= $LoginDetails->onboarding_quiz_status;
					$Response['UserLoginDetails'] 		= $UserDetails;

					return response()->json($Response);
		  		}
		  		else
		  		{
					$Response['Status'] 				= false;
					$Response['Message'] 				= 'Something wrong please try again.';
					return response()->json($Response);
		  		}
	  		}
	  		
	  	}
	}

	public function VerifyOTPAPI($Mobile,$OTP)
	{		
		$CheckOTPExist = $this->SignupModel->CheckOTPExist($Mobile,$OTP);
		if($CheckOTPExist)
		{
			return true;
		}
		else
		{			
			return false;
		}
	}
}