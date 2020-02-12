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
use App\Http\Models\Front\LoginModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class LoginController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->LoginModel = new LoginModel();
	}
	public function LoginView()
	{
		if(Session::has('UserLogin'))
		{
			return Redirect::route('UserDashboard');
		}
		else
		{
			$Data['Title'] 				= 'Login';
			$Data['Menu'] 				= 'Login';
			return View('Front/Pages/LoginView')->with($Data);
		}		
	}

	public function LoginDetails(Request $request)
	{
		$Data 			= $request->all();
		
		$Type			= $Data['Type'];
		$EmailMobile	= $Data['EmailMobile'];
		$Password 		= $Data['Password'];

		$ValidateLogin = $this->LoginModel->ValidateLogin($Type,$EmailMobile,$Password);

		if(!empty($ValidateLogin))
		{
			if($ValidateLogin->password==$Password)
			{				
				Session::put('UserID', $ValidateLogin->id);
				Session::put('UserName', $ValidateLogin->first_name.' '.$ValidateLogin->last_name);
				Session::put('UserImage', $ValidateLogin->image);
				Session::put('UserLogin', 1);

				if($request->has('remember_me'))
				{
					setcookie ("EmailOrPhone",$EmailMobile,time()+ (86400 * 14));
					setcookie ("Password",$Password,time()+ (86400 * 14));
					setcookie ("remember_me",1,time()+ (86400 * 14));			
				}
				else
				{
					setcookie ("Email","");
					setcookie ("Password","");
					setcookie ("remember_me","");
				}
				if(Session::has('ReferURL'))
				{
					$Route = Session::get('ReferURL');
					Session::put('ReferURL', '');
					Session::forget('ReferURL');
					return redirect( route($Route));
				}
				else
				{
					return redirect( route('UserDashboard' ));					
				}
			}
			else
			{
				Session::flash('message', 'InValid Password.'); 
	          	Session::flash('alert-class', 'alert-danger'); 
	          	return redirect( route('LoginView' ));
			}
		}
		else
      	{
          	Session::flash('message', 'Email or Password Invalid.'); 
          	Session::flash('alert-class', 'alert-danger'); 
          	return redirect( route('LoginView' ));
      	}

	}

	public function UserLogout()
	{
		Session::put('UserID', '');
		Session::put('UserName', '');
		Session::put('UserImage', '');
		Session::put('UserLogin', '');


		Session::forget('UserID');
		Session::forget('UserName');
		Session::forget('UserImage');
		Session::forget('UserLogin');

		Session::flash('message', 'Logout Successfully!.'); 
        Session::flash('alert-class', 'alert-success'); 
		return redirect( route('LoginView' ));

	}
}