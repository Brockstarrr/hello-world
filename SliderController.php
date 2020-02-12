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

class SliderController extends Controller 
{
	public function __construct(Request $request)
	{		

	}

	public function Slider()
	{
		$Details = DB::table('slider')->where('id',1)->first(); 
			
		$Data['Title'] 				= 'Slider List';
		$Data['Menu'] 				= 'CMS';
		$Data['SubMenu'] 			= 'Slider';
		$Data['Details'] 			= $Details;
		return View('Admin/CMS/Slider')->with($Data);
	}

	public function SaveSlider(Request $request){
		$Data = $request->all();

		if(!empty($Data['video'])){
			$image = $Data['video'];
   	 	$Path = 'public/Front/Slider';
      $extension = $image->getClientOriginalExtension();
      $ImageName = 'Slider.'.$extension;
      $Upload = $image->move($Path, $ImageName);
      $Save['video'] 		= $ImageName;
		}

		if(!empty($Data['title'])){

			$Json['title'] 				= $Data['title'];
			$Json['description']	= $Data['description'];

			$Save['description'] 	= json_encode($Json);
			
		}
		//echo '<pre>';print_r($Data);die;

		DB::table('slider')->where('id',1)->update($Save);
		$msg = Common::AlertErrorMsg('Success','Slider Details Has been Saved.');
		Session::flash('message', $msg);
		return Redirect()->back();
	}
}