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
use App\Http\Models\Front\HomeModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class HomeController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->HomeModel = new HomeModel();
	}

	public function HomePage()
	{
		$Data['Title'] 				= 'Home Page';
		$Data['Menu'] 				= 'HomePage';
		$Data['Category'] 			= $this->HomeModel->HomeCategory();
		$Data['HomeSlider'] 		= $this->HomeModel->HomeSlider();
		$Data['HappyCanditate'] 	= $this->HomeModel->HappyCanditate();
		$Data['Features'] 			= $this->HomeModel->Features();
		$Data['NeedAJob'] 			= $this->HomeModel->NeedAJobFeatures();
		$Data['NeedAPro'] 			= $this->HomeModel->NeedAProFeatures();
		$Data['Background'] 		= $this->HomeModel->GetContent(5);
		return View('Front/Pages/HomePage')->with($Data);
	}
	public function AboutUs(Request $request)
	{
		$Data['Title'] 				= 'About Us';
		$Data['Menu'] 				= 'HomePage';
		$Data['AboutUs'] 				= $this->HomeModel->GetAboutUs();
		
		return View('Front/Pages/AboutUs')->with($Data);
	}
	public function TermsOfUse(Request $request)
	{
		$Data['Title'] 				= 'Terms Of Use';
		$Data['Menu'] 				= 'HomePage';
		$Data['Data'] 				= $this->HomeModel->GetContent(1);
		return View('Front/Pages/TermsOfUse')->with($Data);
	}
	public function PrivacyPolicy(Request $request)
	{
		$Data['Title'] 				= 'Privacy Policy';
		$Data['Menu'] 				= 'HomePage';
		$Data['Data'] 				= $this->HomeModel->GetContent(2);
		return View('Front/Pages/PrivacyPolicy')->with($Data);
	}
	public function HelpAndSupport(Request $request)
	{
		$Data['Title'] 				= 'Help & Support';
		$Data['Menu'] 				= 'HomePage';
		$Data['Data'] 				= $this->HomeModel->GetContent(3);
		return View('Front/Pages/HelpAndSupport')->with($Data);
	}
	public function Career(Request $request)
	{
		$Data['Title'] 				= 'Career';
		$Data['Menu'] 				= 'HomePage';
		$Data['Data'] 				= $this->HomeModel->GetContent(4);
		return View('Front/Pages/Career')->with($Data);
	}
	public function FAQ(Request $request)
	{
		$Data['Title'] 				= 'FAQ';
		$Data['Menu'] 				= 'HomePage';
		$Data['FAQ'] 				= $this->HomeModel->GetFAQ();

		return View('Front/Pages/FAQ')->with($Data);
	}
	public function BackgroundCheck(Request $request)
	{
		$Data['Title'] 				= 'Background Check';
		$Data['Menu'] 				= 'HomePage';
		$Data['Data'] 				= $this->HomeModel->GetContent(5);
		return View('Front/Pages/BackgroundCheck')->with($Data);
	}
	public function FeatureList(Request $request)
	{
		$FeatureList 				= $this->HomeModel->Features();
		$Data['Title'] 				= 'Feature';
		$Data['Menu'] 				= 'Feature';
		$Data['Features'] 			= $FeatureList;
		return View('Front/Pages/FeatureList')->with($Data);
	}
	public function ContactUs(Request $request)
	{
		$ContactUs 					= $this->HomeModel->ContactUs();
		$Data['Title'] 				= 'Contact Us';
		$Data['Menu'] 				= 'HomePage';
		$Data['ContactUs'] 			= $ContactUs;
		return View('Front/Pages/ContactUs')->with($Data);
	}
	public function ContactUsSave(Request $request)
	{
		$Data 					= $request->all();
		$Details['name'] 		= $Data['Name'];
		$Details['email']		= $Data['Email'];
		$Details['phone'] 		= $Data['Phone'];
		$Details['message'] 	= $Data['Message'];
		$Save = $this->HomeModel->ContactUsSave($Details);
		if($Save)
		{
			Session::flash('message', 'Thanks For Contacting Us. We Will Catch You Soon.'); 
	        Session::flash('alert-class', 'alert-success'); 
	        return Redirect::route('ContactUs');
		}
		else
      	{
          Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
          Session::flash('alert-class', 'alert-danger'); 
          return Redirect::route('ContactUs');
      	}
	}

	public function SignMeUp(Request $request)
	{
		$Data 					= $request->all();
		$Details['email']		= $Data['Email'];
		$Save = $this->HomeModel->SignMeUpSave($Details);
		if($Save)
		{
			echo 1;
		}
		else
      	{
         	echo 0;
      	}
      	exit();
	}	
	public function SendText(Request $request)
	{
		$Data 					= $request->all();
		$Details['mobile']		= $Data['Mobile'];
		/*$Save = $this->HomeModel->SendTextSave($Details);
		if($Save)
		{
			echo 1;
		}
		else
      	{
         	echo 0;
      	}*/
      	echo 1;
      	exit();
	}	

	public function Offers(Request $request)
	{
		$Data['Title'] 				= 'Offers';
		$Data['Menu'] 				= 'Offers';
		$Data['Offers'] 			= $this->HomeModel->Offers();		
		return View('Front/Pages/Offers')->with($Data);
	}

	public function GetOpenNotification(Request $request)
	{
		$Data 			= $request->all();
		$ID					= $Data['ID'];
		$Notification = DB::table('user_notifications')
												->where('id',$ID)
												->first();

		$Update['read_unread'] = '0';
		DB::table('user_notifications')->where('id',$ID)->update($Update);
		echo $Notification->link;
		exit();
	}
}