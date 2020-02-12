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
use App\Http\Models\Front\ReferModel;
use App\Http\Models\Front\UserModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class ReferController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->ReferModel 	= new ReferModel();
		$this->UserModel 	= new UserModel();
		$this->Common 		= new Common();
	}

	public function ReferToFriend()
	{
		$UserID 				= Session::get('UserID');
		$UserDetails 			= $this->UserModel->UserDetails($UserID);
		$Data['Title'] 			= 'Refer A Friend';
		$Data['Menu'] 			= 'Refer';
		$Data['UserDetails'] 	= $UserDetails;
		
		return View('Front/Pages/User/Refer')->with($Data);
	}

	public function SendInvite(Request $request)
	{
		$Alert ='';
		$Response=array();
		$UserID 				= Session::get('UserID');
		$UserDetails 			= $this->UserModel->UserDetails($UserID);

		$Data = $request->all();

		$EmailMobile = $Data['EmailMobile'];
		$Type = $Data['Type'];

		$Datas['Link']	= route('SignupViewForRefer',array('code'=>$UserDetails->code));
		$EmailMessage = View('Front/EmailTemplates/SendInvites')->with($Datas);
		$SMSMessage = "";
		if($Type=='Email')
		{			
			$Send = $this->SendInviteMail($EmailMobile, $EmailMessage);
			if($Send)
			{
				$Alert = $this->Common->AlertErrorMsg('Success','Invitation Sent Sucessfully.');
			}
			else
			{
				$Alert = $this->Common->AlertErrorMsg('Danger','Something Wrong Please Try Again.');
			}
		}
		else if($Type=="Mobile")
		{
			$Send = $this->SendInviteSMS($EmailMobile, $SMSMessage);
			if($Send)
			{
				$Alert = $this->Common->AlertErrorMsg('Success','Invitation Sent Sucessfully.');
			}
			else
			{
				$Alert = $this->Common->AlertErrorMsg('Danger','Something Wrong Please Try Again.');
			}
		}
		echo $Alert;
		exit();
	}
	function SendInviteSMS($Mobile,$Message)
	{
		return true;
	}
	function SendInviteMail($Email,$Message) { 

	    $New_Line = "\n";

	    $Headers = "MIME-Version: 1.0" .$New_Line;
	    $Headers .= "Content-type: text/html; charset=iso-8859-1" .$New_Line;
	    $Headers .= "Content-Transfer-Encode: 7bit " .$New_Line;

	    $Headers .= "X-Mailer: PHP " .$New_Line;  
	    $Subject = "Invites";       
	    $mail_sent = mail($Email, $Subject, $Message, $Headers);
	    return $mail_sent;
	}
}