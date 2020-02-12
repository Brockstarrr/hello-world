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

use App\Http\Models\API\OtherModel;

use App\Http\Models\API\CommonModel;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Pagination;



class OtherController extends Controller 

{

	public function __construct(Request $request)

	{

		$this->OtherModel 	= new OtherModel();

		$this->CommonModel 	= new CommonModel();

	}	

	public function ContactUsAPI(Request $request)
	{
		$Data 			= $request->all();
		$Category   	= array();	
		$Response   	= array();	

		$Name 			= $Data['Name'];
		$Email			= $Data['Email'];
		$Phone		= $Data['Phone'];
		$Message		= $Data['Message'];
		if($Name=='')
		{
			$Response = ['Status'=>false,'Message'=>'Name Missing.'];	
			return response()->json($Response);	
		}
		if($Email=='')
		{
			$Response = ['Status'=>false,'Message'=>'Email Missing.'];	
			return response()->json($Response);	
		}
		if($Phone=='')
		{
			$Response = ['Status'=>false,'Message'=>'Phone Missing.'];	
			return response()->json($Response);	
		}
		if($Message=='')
		{
			$Response = ['Status'=>false,'Message'=>'Message Missing.'];	
			return response()->json($Response);	
		}
		$Details['name'] 	= $Name;
		$Details['email'] 	= $Email;
		$Details['phone'] = $Phone;
		$Details['message'] = $Message;
		$SaveContactUs = $this->OtherModel->SaveContactUs($Details);
		if($SaveContactUs)
		{
			$Response = ['Status'=>true,'Message'=>'Thanks For Contacting Us. We Will Catch You Soon.'];
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];			
		}
		
	  	return response()->json($Response);
	}

	public function FaqAPI(Request $request)

	{

		$Data 			= $request->all();

		$Category   	= array();	

		$Response   	= array();	



		

		$FaqDetails = $this->OtherModel->FaqDetails();

		

		$Response = ['Status'=>true,

					'Message'=>'Faq Details.',

					'FaqDetails'=>$FaqDetails];

		

	  	return response()->json($Response);

	}

}