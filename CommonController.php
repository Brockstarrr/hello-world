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
use App\Http\Models\Front\HomeModel;
use App\Http\Models\API\CommonModel;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Pagination;

class CommonController extends Controller 
{
	public function __construct(Request $request)
	{
		$this->HomeModel 	= new HomeModel();
		$this->CommonModel 	= new CommonModel();
	}	
	public function CategoryListAPI(Request $request)
	{
		$Data 			= $request->all();
		$Category   	= array();	
		$Response   	= array();	

		$CategoryList 	= $this->CommonModel->CategoryList();
		if(!empty($CategoryList))
		{
			foreach ($CategoryList as $cl) 
			{	
				if($cl->banner!=''){
					$Banner = asset('public/Front/Category').'/'.$cl->banner;
				}else{ 
					$Banner = asset('public/Front/Design/img/pro_pic.png');
				}
				$Sample['CatID'] 	= $cl->id;
				$Sample['CatName'] 	= $cl->category;
				$Sample['Banner'] 	= $Banner;
				array_push($Category, $Sample);
			}
		}
		$Response = ['Status'=>true,'Message'=>'Category List.','Category'=>$Category];
		
	  	return response()->json($Response);
	}
	public function SubCategoryListAPI(Request $request)
	{
		$Data 			= $request->all();
		$SubCategory   	= array();	
		$Response   	= array();	
		$CatID 			= $Data['CatID'];	
		$SubCategoryList= $this->CommonModel->SubCategoryList($CatID);
		if(!empty($SubCategoryList))
		{
			foreach ($SubCategoryList as $cl) 
			{	
				$Sample['SubCatID'] 	= $cl->id;
				$Sample['SubCatName'] 	= $cl->position;
				array_push($SubCategory, $Sample);
			}
		}
		$Response = ['Status'=>true,'Message'=>'Sub Category List.','SubCategory'=>$SubCategory];
		
	  	return response()->json($Response);
	}
	public function CategoryPreferenceAPI(Request $request)
	{
		$Data 			= $request->all();
		$CategoryPreference   	= array();	
		$Response   	= array();	
		$CatID 			= $Data['CatID'];	
		$CategoryPreferenceList= $this->CommonModel->CategoryPreferenceList($CatID);
		if(!empty($CategoryPreferenceList))
		{
			foreach ($CategoryPreferenceList as $cl) 
			{	
				$Sample['PreferenceID'] = $cl->id;
				$Sample['Preference'] 	= $cl->preference;
				array_push($CategoryPreference, $Sample);
			}
		}
		$Response = ['Status'=>true,'Message'=>'Category Preference List.','CategoryPreference'=>$CategoryPreference];
		
	  	return response()->json($Response);
	}

	public function CatSubCatListAPI(Request $request)
	{
		$Data 			= $request->all();
		$Category   	= array();	
		$Response   	= array();	

		$CategoryList 	= $this->CommonModel->CategoryList();
		if(!empty($CategoryList))
		{
			foreach ($CategoryList as $cl) 
			{	
				if($cl->banner!=''){
					$Banner = asset('public/Front/Category').'/'.$cl->banner;
				}else{ 
					$Banner = asset('public/Front/Design/img/pro_pic.png');
				}
				$Sample['CatID'] 	= $cl->id;
				$Sample['CatName'] 	= $cl->category;
				$Sample['Banner'] 	= $Banner;
				$Sample['SubCat'] 	= $this->GetSubCat($cl->id);
				array_push($Category, $Sample);
			}
		}
		else
		{
			$Sample['CatID'] 	= "";
			$Sample['CatName'] 	= "";
			$Sample['Banner'] 	= "";
			$Sample['SubCat'] 	= $this->GetSubCat($cl->id);
			array_push($Category, $Sample);
		}
		$Response = ['Status'=>true,'Message'=>'Category List.','Category'=>$Category];
		
	  	return response()->json($Response);
	}
	public function GetSubCat($CatID)
	{
		$SubCategory =array();
		$SubCategoryList= $this->CommonModel->SubCategoryList($CatID);
		if(!empty($SubCategoryList))
		{
			foreach ($SubCategoryList as $cl) 
			{	
				$Sample['SubCatID'] 	= $cl->id;
				$Sample['SubCatName'] 	= $cl->position;
				array_push($SubCategory, $Sample);
			}
		}
		else
		{
			$Sample['SubCatID'] 	= "";
			$Sample['SubCatName'] 	= "";
			array_push($SubCategory, $Sample);
		}
		return $SubCategory;
	}
	public function AboutUsAPI(Request $request)
	{
		$Data 			= $request->all();
		$AboutUsDetails	= $this->HomeModel->GetAboutUs();
		$AboutUs['AboutUs'] 	= $AboutUsDetails->about_desc;
		$AboutUs['OurStory'] 	= $AboutUsDetails->our_desc;
		$AboutUs['Leadership'] 	= $AboutUsDetails->leadership_desc;
		$Response = ['Status'=>true,
					'Message'=>'About Us Page.',
					'AboutUs'=>$AboutUs];
		return response()->json($Response);
	}

	public function PrivacyPolicyAPI(Request $request)
	{
		$Data 			= $request->all();
		$PrivacyPolicyDetails = $this->HomeModel->GetContent(2);
		$PrivacyPolicy['Title'] 	= $PrivacyPolicyDetails->title;
		$PrivacyPolicy['Description'] 	= $PrivacyPolicyDetails->description;
		$Response = ['Status'=>true,
					'Message'=>'Privacy Policy Page.',
					'PrivacyPolicy'=>$PrivacyPolicy];
		return response()->json($Response);
	}

	public function GetOpeningTimeSlotAPI(Request $request)
	{
		$Data 			= $request->all();
		$OpeningTimeSlot   	= array();	
		$Response   	= array();	

		$GetOpeningTimeSlot 	= $this->CommonModel->GetOpeningTimeSlot();
		if(!empty($GetOpeningTimeSlot))
		{
			foreach ($GetOpeningTimeSlot as $cl) 
			{	
				$Sample['ID'] 			= $cl->id;
				$Sample['TimeSLot'] 	= $cl->time_slot;
				array_push($OpeningTimeSlot, $Sample);
			}
		}
		$Response = ['Status'=>true,'OpeningTimeSlot'=>$OpeningTimeSlot];
		
	  	return response()->json($Response);
	}
}