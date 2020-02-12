<?php
namespace App\Http\Controllers\Admin;
use Route;
use Mail;
use Auth, Hash;
use Validator;
use Session;
use Redirect;
use DB;
use Crypt;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class AboutUsController extends Controller 
{
	public function __construct(Request $request)
	{		

	}

	public function AboutUs()
	{
		$Details = DB::table('about_us')->where('id',1)->first(); 
			
		$Data['Title'] 				= 'About Us';
		$Data['Menu'] 				= 'CMS';
		$Data['SubMenu'] 			= 'AboutUs';
		$Data['Details'] 			= $Details;
		return View('Admin/CMS/AboutUs')->with($Data);
	}

	public function SaveAboutUs(Request $request){
		$Data = $request->all();

		if(!empty($Data['about_image'])){
			$image = $Data['about_image'];
   	 	$Path = 'public/Front/AboutUs';
      $extension = $image->getClientOriginalExtension();
      $ImageName = 'AboutUs.'.$extension;
      $Upload = $image->move($Path, $ImageName);
      $Save['about_image'] 		= $ImageName;
		}
		if(!empty($Data['our_image'])){
			$image = $Data['our_image'];
   	 	$Path = 'public/Front/AboutUs';
      $extension = $image->getClientOriginalExtension();
      $ImageName = 'OurImage.'.$extension;
      $Upload = $image->move($Path, $ImageName);
      $Save['our_image'] 		= $ImageName;
		}
		if(!empty($Data['leadership_image'])){
			$image = $Data['leadership_image'];
   	 	$Path = 'public/Front/AboutUs';
      $extension = $image->getClientOriginalExtension();
      $ImageName = 'LeadershipImage.'.$extension;
      $Upload = $image->move($Path, $ImageName);
      $Save['leadership_image'] 		= $ImageName;
		}
		$Save['about_desc'] 	= $Data['about_desc'];
		$Save['our_desc'] 		= $Data['our_desc'];
		$Save['leadership_desc'] = $Data['leadership_desc'];
		$Save['why_us'] 			= $Data['why_us'];

		$i = 0;
		$FaqArr = array();
		foreach ($Data['title'] as $a) {
			$Json['title'] 	= $a;
			$Json['description'] = $Data['description'][$i];
			$FaqArr[] 	= $Json;
			$i++;
		}
		$Save['why_desc'] = json_encode($FaqArr);
		DB::table('about_us')->where('id',1)->update($Save);
		$msg = Common::AlertErrorMsg('Success','About Us Details Has been Saved.');
		Session::flash('message', $msg);
		return Redirect()->back();
	}
}