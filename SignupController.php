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
use App\Http\Models\Front\SignupModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class SignupController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->SignupModel 	= new SignupModel();
		$this->Common 		= new Common();
	}
	public function SignupView(Request $request, $Code='')
	{
		if(Session::has('UserLogin'))
		{
			return Redirect::route('UserDashboard');
		}
		else
		{
			$Data['Title'] 	= 'Signup';
			$Data['Menu'] 	= 'Signup';
			$Data['Code'] 	= $Code;
			return View('Front/Pages/SignupView')->with($Data);
		}
		
	}
	public function CheckEmailMobile(Request $request)
	{
		$Data 	= $request->all();
		$Email 	= $Data['Email'];
		$Mobile = $Data['Mobile'];

		$EmailCount = $this->SignupModel->CheckEmail($Email);
		$MobileCount = $this->SignupModel->CheckMobile($Mobile);

		if($EmailCount==0 && $MobileCount==0)
		{
			$this->SendOTP($Mobile);
		}
		$Response['Email'] 	= $EmailCount;
		$Response['Mobile'] = $MobileCount;

		echo json_encode($Response);
		exit();
	}

	public function SendOTP($Mobile)
	{
		$ValidTillTimeStamp = strtotime(date('Y-m-d H:i:s', strtotime('now +30 minutes')));
		$OTPData=array('phone'=>$Mobile,	
					   'otp'=>$this->Common->GenerateOTP(4),
					   'valid_till'=>$ValidTillTimeStamp);
		$SaveOTPDetails = $this->SignupModel->SaveOTPDetails($OTPData);
		return true;
	}

	public function ResendOTP(Request $request)
	{
		$Data 	= $request->all();
		$Mobile = $Data['Mobile'];

		$ValidTillTimeStamp = strtotime(date('Y-m-d H:i:s', strtotime('now +30 minutes')));
		$OTPData=array('phone'=>$Mobile,	
					   'otp'=>$this->Common->GenerateOTP(4),
					   'valid_till'=>$ValidTillTimeStamp);
		$SaveOTPDetails = $this->SignupModel->SaveOTPDetails($OTPData);

		if($SaveOTPDetails)
		{
			$Message = $this->Common->AlertErrorMsg('Success','OTP Has Been Sent.');
			$Response['Status']  = 1;
			$Response['Message'] = $Message;
		}
		else
		{
			$Message = $this->Common->AlertErrorMsg('Danger','Something Wrong Please Try Again.');			
			$Response['Status']  = 0;
			$Response['Message'] = $Message;
		}
		echo json_encode($Response);
		exit();
	}

	public function VerifyOTP(Request $request)
	{
		$Data 	= $request->all();
		$Mobile = $Data['Mobile'];
		$OTP 	= $Data['OTP'];

		$CheckOTPExist = $this->SignupModel->CheckOTPExist($Mobile,$OTP);
		if($CheckOTPExist)
		{
			$Message = $this->Common->AlertErrorMsg('Success','OTP Verified.');
			$Response['Status']  = 1;
			$Response['Message'] = $Message;
		}
		else
		{
			$Message = $this->Common->AlertErrorMsg('Danger','OTP Not Valid Or Expired.');			
			$Response['Status']  = 0;
			$Response['Message'] = $Message;
		}
		echo json_encode($Response);
		exit();
	}

	public function SignupDetails(Request $request)
	{
		$Data 					= $request->all();
		$Details['code'] 		= $this->Common->GenerateRandomId(8);
		$Details['first_name'] 	= $Data['FirstName'];
		$Details['last_name'] 	= $Data['LastName'];
		$Details['email']		= $Data['Email'];
		$Details['phone'] 		= $Data['Mobile'];
		$Details['password'] 	= $Data['Password'];
		$Referral				= $Data['Referral'];
		$SignupDetails = $this->SignupModel->SignupDetails($Details,$Referral);
		if($SignupDetails)
		{
			Session::flash('message', 'Signup Successfully!. Now You Can Login.'); 
	        Session::flash('alert-class', 'alert-success'); 
	        return Redirect::route('LoginView');
		}
		else
      	{
          Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
          Session::flash('alert-class', 'alert-danger'); 
          return Redirect::route('SignupView');
      	}

	}


	public function PasswordRecoveryView()
	{
		if(Session::has('UserLogin'))
		{
			return Redirect::route('UserDashboard');
		}
		else
		{
			$Data['Title'] 	= 'Password Recovery';
			$Data['Menu'] 	= 'PasswordRecovery';
			return View('Front/Pages/PasswordRecovery')->with($Data);
		}
		
	}

	public function CheckEmailMobileExist(Request $request)
	{
		$Data 			= $request->all();
		$EmailMobile 	= $Data['EmailMobile'];
		$Type 			= $Data['Type'];
		$Response 		= array();
		$CheckEmailMobileExist = $this->SignupModel->CheckEmailMobileExist($Type,$EmailMobile);
		if($CheckEmailMobileExist==1)
		{
			$this->SendPasswordRecoveryOTP($EmailMobile,$Type);
			$Message = "";
			$Response['Status'] = 1;
			$Response['Message'] = $Message;
		}
		else
		{
			if($Type == "Email")
			{
				$Message = 'Email Does Not Exist.';				
			}
			else if($Type == "Mobile")
			{
				$Message = 'Mobile Number Does Not Exist.';
			}
			$Response['Status'] = 0;
			$Response['Message'] = $Message;
		}
		echo json_encode($Response);
		exit();
	}

	public function SendPasswordRecoveryOTP($EmailMobile,$Type)
	{
		$ValidTillTimeStamp = strtotime(date('Y-m-d H:i:s', strtotime('now +30 minutes')));

		$OTPData['otp'] 		= $this->Common->GenerateOTP(4);
		$OTPData['valid_till'] 	= $ValidTillTimeStamp;
		if($Type == "Email")
		{
			$OTPData['email'] 		= $EmailMobile;		
		}
		else if($Type == "Mobile")
		{
			$OTPData['phone'] 		= $EmailMobile;
		}
		
		$SaveOTPDetails = $this->SignupModel->SavePasswordRecoveryOTPDetails($Type,$OTPData);
		return true;
	}
	public function VerifyPasswordRecoveryOTP(Request $request)
	{
		$Data 	= $request->all();
		$EmailMobile = $Data['EmailMobile'];
		$Type 	= $Data['Type'];
		$OTP 	= $Data['OTP'];

		$CheckOTPExist = $this->SignupModel->CheckPasswordRecoveryOTPExist($EmailMobile,$OTP,$Type);
		if($CheckOTPExist)
		{
			$Message = 'OTP Verified.';
			$Response['Status']  = 1;
			$Response['Message'] = $Message;
		}
		else
		{
			$Message = 'OTP Not Valid Or Expired.';			
			$Response['Status']  = 0;
			$Response['Message'] = $Message;
		}
		echo json_encode($Response);
		exit();
	}

	public function ResendPasswordRecoveryOTP(Request $request)
	{
		$Data 		 = $request->all();
		$Type 			= $Data['Type'];
		$EmailMobile = $Data['EmailMobile'];

		$ValidTillTimeStamp = strtotime(date('Y-m-d H:i:s', strtotime('now +30 minutes')));
		
		$OTPData['otp'] 		= $this->Common->GenerateOTP(4);
		$OTPData['valid_till'] 	= $ValidTillTimeStamp;
		if($Type == "Email")
		{
			$OTPData['email'] 		= $EmailMobile;		
		}
		else if($Type == "Mobile")
		{
			$OTPData['phone'] 		= $EmailMobile;
		}

		$SaveOTPDetails = $this->SignupModel->SavePasswordRecoveryOTPDetails($Type,$OTPData);

		if($SaveOTPDetails)
		{
			$Message = $this->Common->AlertErrorMsg('Success','OTP Has Been Sent.');
			$Response['Status']  = 1;
			$Response['Message'] = $Message;
		}
		else
		{
			$Message = $this->Common->AlertErrorMsg('Danger','Something Wrong Please Try Again.');			
			$Response['Status']  = 0;
			$Response['Message'] = $Message;
		}
		echo json_encode($Response);
		exit();
	}

	public function ChangePassword(Request $request)
	{
		$Data 			= $request->all();
		
		$EmailMobile 	= $Data['EmailMobile'];
		$Type 			= $Data['Type'];
		$Password 		= $Data['Password'];

		$ChangePassword = $this->SignupModel->ChangePassword($EmailMobile,$Type,$Password);
		if($ChangePassword)
		{
			Session::flash('message', 'Password Changed Successfully!. Now You Can Login With New Password.'); 
	        Session::flash('alert-class', 'alert-success'); 
	        return Redirect::route('LoginView');
		}
		else
      	{
          	Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
          	Session::flash('alert-class', 'alert-danger'); 
          	return Redirect::route('LoginView');
      	}
	}


}