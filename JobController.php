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
use App\Http\Models\Front\JobPostModel;
use App\Http\Models\Front\ViewJobModel;
use App\Http\Models\API\JobModel;
use App\Http\Models\API\CommonModel;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Pagination;
use App\Helpers\Common;
class JobController extends Controller 
{
	public function __construct(Request $request)
	{
		$this->JobPostModel = new JobPostModel();
		$this->ViewJobModel = new ViewJobModel();
		$this->JobModel 	= new JobModel();
		$this->CommonModel 	= new CommonModel();
		$this->Common 	= new Common();
	}
	public function ViewJobListAPI(Request $request)
	{
		$Data 			= $request->all();	
		$ViewJobList  	= array();
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$Category 		= $Data['CategoryID'];
		$SubCat 		= $Data['SubCat'];
		$StartDate 		= $Data['StartDate'];
		$EndDate 		= $Data['EndDate'];
		$SortBy 		= $Data['SortBy'];
		$PayRangeMin   	= $Data['PayRangeMin'];
		$PayRangeMax   	= $Data['PayRangeMax'];
		$DistanceFrom   = 0;
		$DistanceTo   	= 5;
		if($Data['DistanceFrom']!='')
		{
			$DistanceFrom   = $Data['DistanceFrom'];
			if($Data['DistanceFrom']==1)
			{				
				$DistanceFrom   = 0;
			}			
		}
		if($Data['DistanceTo']!='')
		{
			$DistanceTo   	= $Data['DistanceTo'];			
		}
		$latitude   	= $Data['Lat'];
		$longitude   	= $Data['Lng'];
		$SearchKeyword  = $Data['SearchKeyword'];
		$PayType  		= $Data['PayType'];


		$Search['SortBy'] 		= $SortBy;
		$Search['Category'] 	= $Category;
		$Search['SubCat'] 		= $SubCat;
		$Search['StartDate']	= $StartDate;
		$Search['EndDate'] 		= $EndDate;
		$Search['SearchKeyword']= $SearchKeyword;
		$Search['PayRangeMin'] 	= $PayRangeMin;
		$Search['PayRangeMax'] 	= $PayRangeMax;
		$Search['PayType'] 		= $PayType;
			
		$ViewJobs 			= $this->JobModel->ViewJobList($Search);
		$UniqueArray = array();
		if(!empty($ViewJobs))
		{
			foreach ($ViewJobs as $hjl) 
			{
				$JobID 			= $hjl->id;
				$JobTotalReviews= $this->ViewJobModel->JobTotalReviews($JobID);
				$JobReviews 	= $this->ViewJobModel->JobReviews($JobID);

				$Distance = 0;
				if($latitude)
				{
					$Distance = $this->Common->DistanceCalculate($latitude,$longitude,$hjl->latitude,$hjl->longitude,"K");
					$Distance = explode('.',$Distance);
					$Distance = reset($Distance);
					if($DistanceFrom <= $Distance AND $DistanceTo >= $Distance){
						//$Distance = 'Done';
						if($hjl->image!=''){
							$Banner = asset('public/Front/Users/Jobs').'/'.$hjl->image;
						}else{ 
							$Banner = asset('public/Front/Design/img/pro_pic.png');
						}
						$JobID 					= $hjl->id;
						$Sample['JobID'] 		= $JobID;
						$Sample['JobTitle'] 	= $hjl->job_title;
						$Sample['Address'] 		= $hjl->address;
						$Sample['ByUserName'] 	= $hjl->username;
						$Sample['Banner'] 		= $Banner;
						$Sample['PostedOn'] 	= $hjl->add_date;
						$Sample['Favourite'] 	= 0;
						$Sample['TotalReviews'] = $JobReviews;
						$Sample['Rating'] 	= ceil($JobTotalReviews/5);
						$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
						if(!empty($CheckLoginDetails))
						{
							$Sample['Favourite'] 	= $this->ViewJobModel->JobFavStatus($UserID,$JobID);
						}
						$Sample['Openings'] = $this->GetOpeningsDetails($JobID);
						if(!in_array($JobID,$UniqueArray))
						{
							array_push($ViewJobList, $Sample);							
						}
						array_push($UniqueArray, $JobID);
					}else{
						continue;
					}
				}
				else
				{
					if($hjl->image!=''){
						$Banner = asset('public/Front/Users/Jobs').'/'.$hjl->image;
					}else{ 
						$Banner = asset('public/Front/Design/img/pro_pic.png');
					}
					$JobID 					= $hjl->id;
					$Sample['JobID'] 		= $JobID;
					$Sample['JobTitle'] 	= $hjl->job_title;
					$Sample['Address'] 		= $hjl->address;
					$Sample['ByUserName'] 	= $hjl->username;
					$Sample['Banner'] 		= $Banner;
					$Sample['PostedOn'] 	= $hjl->add_date;
					$Sample['JobFavStatus'] 	= 0;
					$Sample['JobApplyStatus'] 	= 0;
					$Sample['TotalReviews'] = $JobReviews;
					$Sample['TotalRating'] 	= ceil($JobTotalReviews/5);
					$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
					if(!empty($CheckLoginDetails))
					{
						$Sample['JobFavStatus'] 	= $this->ViewJobModel->JobFavStatus($UserID,$JobID);
						$Sample['JobApplyStatus']   = $this->ViewJobModel->JobApplyStatus($UserID,$JobID);
					}
					$Sample['Openings'] = $this->GetOpeningsDetails($JobID);
					if(!in_array($JobID,$UniqueArray))
					{
						array_push($ViewJobList, $Sample);							
					}
					array_push($UniqueArray, $JobID);
				}
			}
		}
		$Response = ['Status'=>true,
					'Message'=>'Job List.',
					'ViewJobList'=>$ViewJobList
					];
		
	  	return response()->json($Response);
	}
	public function GetOpeningsDetails($JobID)
	{
		$List = array();
		$OpeningList = $this->ViewJobModel->OpeningList($JobID);
		

		if(!empty($OpeningList))
		{
			
			foreach($OpeningList as $ol)
			{
				$Line='';
				$SubCatName = $this->JobPostModel->GetSubCategoryName($ol->sub_cat);
				$PayType =  $ol->pay_type;
				if($PayType=='1')
				{
					$PayRateString = 'Fixed';
				}
				else if($PayType=='2')
				{
					$PayRateString = 'Hourly';
				}

				$Line.= 'Need '.$ol->openings.' '.$SubCatName;
				
				if($ol->job_for=='2')
				{
					$Line.=' From '.date('M, d-Y',strtotime($ol->start_date)).' To '.date('M, d-Y',strtotime($ol->end_date)); 
				}
				else
				{
					$Line.=' on '.date('M, d-Y',strtotime($ol->start_date)); 
				}
				$Line.=', will get: $'.$ol->pay_rate.'/'.$PayRateString;
				array_push($List, $Line);
			}
		}
		return $List;
	}
	public function ViewJobDetailsAPI(Request $request)
	{
		$Data 			= $request->all();	
		$ViewJobList  	= array();
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$JobID 			= $Data['JobID'];	
		$ViewJobDetails = $this->ViewJobModel->GetJobDetail($JobID);
		$CheckLoginDetails  		= $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($ViewJobDetails))
		{
			
			if($ViewJobDetails->image!=''){
				$Banner = asset('public/Front/Users/Jobs').'/'.$ViewJobDetails->image;
			}else{ 
				$Banner = asset('public/Front/Design/img/pro_pic.png');
			}

			$ViewJobList['JobID'] 		= $ViewJobDetails->id;
			$ViewJobList['JobType'] 	= $ViewJobDetails->job_type;
			$ViewJobList['JobCategoryID'] = $ViewJobDetails->job_cat;
			$ViewJobList['JobCategory'] = $ViewJobDetails->category;
			$ViewJobList['JobTitle'] 	= $ViewJobDetails->job_title;
			$ViewJobList['ByUserName'] 	= $ViewJobDetails->username;			
			$ViewJobList['WrokSiteContact'] 		= $ViewJobDetails->work_site_contact;
			$ViewJobList['Banner'] 		= $Banner;
			$ViewJobList['JobFavStatus'] 	= 0;
			$ViewJobList['JobApplyStatus'] 	= 0;
			if(!empty($CheckLoginDetails))
			{
				$ViewJobList['JobFavStatus'] 	= $this->ViewJobModel->JobFavStatus($UserID,$JobID);
				$ViewJobList['JobApplyStatus'] 	= $this->ViewJobModel->JobApplyStatus($UserID,$JobID);			
			}

			$JobApplyStatus = $this->ViewJobModel->JobApplyStatus($UserID,$JobID);
			
			$OpeningsCount 				= $this->ViewJobModel->JobOpenings($JobID);
			$ViewJobList['TotalOpenings'] 	= $OpeningsCount->openings;
			$ViewJobList['PostedOn'] 	= $ViewJobDetails->add_date;
			$ViewJobList['Description'] = $ViewJobDetails->job_description;
				
			$Preference 		= $ViewJobDetails->preference;
			$PreferenceList 	= $this->ViewJobModel->GetJobPreference($Preference);
			$JobPreference=array();
			$Gender='';
			if($ViewJobDetails->gender=='1'){
				$Gerder='Male';
			}elseif($ViewJobDetails->gender=='2'){
				$Gerder='Female';
			}elseif($ViewJobDetails->gender=='3'){
				$Gerder='Any';
			}

			$AgeLimit = '';
			if($ViewJobDetails->age_limit!=''){
				$AgeLimit = $ViewJobDetails->age_limit;
			}

			$Parking='N/A';
			if($ViewJobDetails->parking!='')
			{
				if($ViewJobDetails->parking==1){
					$Parking='Free';
				}elseif($ViewJobDetails->parking==2){
					$Parking='Paid';
				}elseif($ViewJobDetails->parking==3){
					$Parking='Not Available';
				}
			}

			$Preference=array();
			if(!empty($PreferenceList))
			{
				foreach($PreferenceList as $p)
				{
					$Preference[]=$p->preference;
				}
			}


			$SubCatOpeningsList = $this->ViewJobModel->GetSubCatOpenings($JobID);
			$SubCatAndOpenings=array();
			if(!empty($SubCatOpeningsList))
			{
				foreach ($SubCatOpeningsList as $sco)
				{

				$Sample=array();
					$Sample['SubCategory'] 	= $sco->position;
					$Sample['Openings'] 	= $sco->openings;
					$Sample['JobFor'] 		= $sco->job_for;
					$Sample['StartDate'] 	= $sco->start_date;
					$Sample['EndDate'] 		= $sco->end_date;
					$Sample['Breaks'] 		= $sco->breaks;
					$Sample['BreakTime'] 	= json_decode($sco->break_time);
					$Sample['BreakPaidUnpaid'] 	= json_decode($sco->break_paid_unpaid);
					$Sample['PayType'] 		= $sco->pay_type;
					$Sample['PayRate'] 		= $sco->pay_rate;
					array_push($SubCatAndOpenings, $Sample);	
				}
			}
			$ViewJobList['SubCatAndOpenings'] 	= $SubCatAndOpenings;

			$LanguageList 					= $this->ViewJobModel->GetJobLanguage($JobID);
			$Language=array();
			if(!empty($LanguageList))
			{				
				foreach ($LanguageList as $l) 
				{
					$Sample=array();
					$Sample['LanguageName'] = $l->language;					
					$Sample['Level'] = $l->level;	
					array_push($Language, $Sample);				
				}
			}
			$ViewJobList['Language'] 		= $Language;
			$ViewJobList['Uniform'] 		= $ViewJobDetails->uniform;

			$Apperance = array();
			
			
			if($ViewJobDetails->no_matter=='1')	
			{
				$Apperance['Gender'] 		= '';
				$Apperance['AgeLimit'] 		= '';
				$Apperance['Height']		= '';	
				$Apperance['HairColor'] 	='';
				$Apperance['EyeColor'] 		= '';
			}
			else
			{
				$HairColor 					= $ViewJobDetails->hair_color;
				$EyeColr 					= $ViewJobDetails->eye_color;
				$Apperance['Gender'] 		= $Gender;
				$Apperance['AgeLimit'] 		= $AgeLimit;
				$Apperance['Height']		= $ViewJobDetails->height;		
				$Apperance['HairColor'] 	= $this->ViewJobModel->GetHairColor($HairColor);
				$Apperance['EyeColor'] 		= $this->ViewJobModel->GetHairColor($EyeColr);
			}
			$Apperance['Parking'] 		= $Parking;
			$ViewJobList['Apperance'] 	= $Apperance;
			$ViewJobList['Address'] 	= $ViewJobDetails->address;
			$ViewJobList['Venue'] 		= $ViewJobDetails->venue;
			$ViewJobList['LocationInstruction'] 		= $ViewJobDetails->location;			
		}
		$Response = ['Status'=>true,
					'Message'=>'Job Details.',
					'ViewJobDetails'=>$ViewJobList
					];
		
	  	return response()->json($Response);
	}

	///////////////////////////////////////
}