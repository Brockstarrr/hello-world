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
use App\Http\Models\Front\ViewProsModel;
use App\Http\Models\Front\UserModel;
use App\Http\Models\API\ProsModel;
use App\Http\Models\API\CommonModel;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Pagination;
use App\Helpers\Common;

class ProsController extends Controller 
{
	public function __construct(Request $request)
	{
		$this->UserModel 		= new UserModel();
		$this->ViewProsModel	= new ViewProsModel();
		$this->ProsModel 		= new ProsModel();
		$this->CommonModel 		= new CommonModel();
		$this->Common 			= new Common();
	}

	public function ViewProsListAPI(Request $request)
	{
		$Data 			= $request->all();

		$ViewProsList  	= array();
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		
		$Category 		= $Data['CategoryID'];
		$SubCat 		= $Data['SubCat'];
		$SortBy 		= $Data['SortBy'];
		$PayRangeMin   	= $Data['PayRangeMin'];
		$PayRangeMax   	= $Data['PayRangeMax'];
		$DistanceFrom   = 0;
		$DistanceTo   	= 20;
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
		$Verified  		= $Data['Verified'];
		$Background  	= $Data['Background'];

		$Search['SortBy'] 		= $SortBy;
		$Search['Category'] 	= $Category;
		$Search['SubCat'] 		= $SubCat;
		$Search['SearchKeyword']= $SearchKeyword;
		$Search['PayRangeMin'] 	= $PayRangeMin;
		$Search['PayRangeMax'] 	= $PayRangeMax;
		$Search['Verified'] 	= $Verified;	
		$Search['Background'] 	= $Background;

		//$ViewPros 		= $this->ProsModel->ViewProsList($CategoryID);
		$ViewPros 		= $this->ProsModel->ViewProsList($Search);
		if(!empty($ViewPros))
		{
			foreach ($ViewPros as $p) 
			{
				$Distance = 0;
				if($latitude){		
					$Distance = $this->Common->DistanceCalculate($latitude,$longitude,$p->latitude,$p->longitude,"K");
					$Distance = explode('.',$Distance);
					$Distance = reset($Distance);
					if($DistanceFrom <= $Distance AND $DistanceTo >= $Distance){
						//$Distance = 'Done';
						if($p->image!=''){
							$Image = asset('public/Front/Users/Profile').'/'.$p->image;
						}else{ 
							$Image = asset('public/Front/Design/img/pro_pic.png');
						}
						$ProsID 				= $p->id;
						$Sample['ProsID'] 		= $ProsID;
						$Sample['Image'] 		= $Image;
						$Sample['FirstName'] 	= $p->first_name;
						$Sample['LastName'] 	= $p->last_name;
						$Sample['Location'] 	= $p->location;
						$Sample['Favourite'] 	= 0;
						$Sample['Invited'] 		= 0;
						$CheckLoginDetails  	= $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
						if(!empty($CheckLoginDetails))
						{
							$Sample['Favourite'] 	= $this->ViewProsModel->ProsFavStatus($UserID,$ProsID);
							$Sample['Invited'] 		= $this->ViewProsModel->ProsInviteStatus($UserID,$ProsID);
						}
						array_push($ViewProsList, $Sample);	
					}else{
						continue;
					}
				}
				else
				{
					if($p->image!=''){
					$Image = asset('public/Front/Users/Profile').'/'.$p->image;
					}else{ 
						$Image = asset('public/Front/Design/img/pro_pic.png');
					}
					$ProsID 				= $p->id;
					$Sample['ProsID'] 		= $ProsID;
					$Sample['Image'] 		= $Image;
					$Sample['FirstName'] 	= $p->first_name;
					$Sample['LastName'] 	= $p->last_name;
					$Sample['Location'] 	= $p->location;
					$Sample['Favourite'] 	= 0;
					$Sample['Invited'] 		= 0;
					$CheckLoginDetails  	= $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
					if(!empty($CheckLoginDetails))
					{
						$Sample['Favourite'] 	= $this->ViewProsModel->ProsFavStatus($UserID,$ProsID);
						$Sample['Invited'] 		= $this->ViewProsModel->ProsInviteStatus($UserID,$ProsID);
					}
					array_push($ViewProsList, $Sample);	
				}

				

			}
			$Response = ['Status'=>true,
						'Message'=>'Pros List.',
						'ViewProsList'=>$ViewProsList
						];
		}
		else
		{
			$Response = ['Status'=>false,
						'Message'=>'Pros List Not Available.'
						];
		}	
		
	  	return response()->json($Response);
	}

	public function ViewProsDetailsAPI(Request $request)
	{
		$Data 			= $request->all();	
		$ProsDetails  	= array();
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$ProsID 		= $Data['ProsID'];
		
		$IsProsExist 	= $this->ViewProsModel->IsProsExist($ProsID);
		if($IsProsExist)
		{
			$UserTotalReviews  = $this->ViewProsModel->UserTotalReviews($ProsID);


			$JobPreferences = array();
			$ExperienceDetails = array();
			$EducationDetails = array();
			$LanguageDetails = array();
			$AppearanceDetails = array();
			$AccreditationsDetails = array();
			$CertificationsDetails = array();
			$UserDetails 	= $this->UserModel->UserDetails($ProsID);
			$Experience		= $this->UserModel->GetUserExperience($ProsID);
			$Preference 	= $this->UserModel->GetUserPreference($ProsID);
			$Education		= $this->UserModel->GetUserEducation($ProsID);
			$Language		= $this->UserModel->GetUserLanguage($ProsID);
			$Appearance		= $this->UserModel->GetUserAppearance($ProsID);
			$Accreditations = $this->UserModel->GetUserAccreditations($ProsID);
			$Certifications	= $this->UserModel->GetUserCertifications($ProsID);

			$ProsDetails['ProsID'] 		= $UserDetails->id;
			$UserDetails = $this->UserModel->UserDetails($ProsID);
			if($UserDetails->image=='')
			{
				$Image = asset('public/Front/Design/img/pro_pic.png');				
			}
			else 
			{
				$Image = asset('public/Front/Users/Profile').'/'.$UserDetails->image;				
			}
			$ProsDetails['FirstName'] 	= $UserDetails->first_name;
			$ProsDetails['LastName'] 	= $UserDetails->last_name;
			$ProsDetails['Image'] 		= $Image;
			$ProsDetails['About'] 		= $UserDetails->about;
			$ProsDetails['DOB'] 		= $UserDetails->dob;
			$ProsDetails['Location'] 	= $UserDetails->location;
			$ProsDetails['Rating'] 		= 0;
			if($UserTotalReviews!=0)
			{
				$ProsDetails['Rating'] 		= ceil($UserTotalReviews/5);				
			}
			$ProsDetails['BackgroundCheck'] 	= $UserDetails->background;
			$ProsDetails['OnboardingTest'] 		= $UserDetails->onboarding_quiz_status;
			
			if(!empty($Experience))
			{
				$Sample =array();
				foreach($Experience as $e)
				{
					$Sample['JobTitle'] = $e->job_title;
					$Sample['Company'] 	= $e->company;
					$Sample['From'] 	= $e->from;
					$Sample['To'] 		= $e->to;
					$Sample['Still'] 	= $e->still;
					$Sample['Location'] = $e->location;
					array_push($ExperienceDetails, $Sample);
				}
			}
			if(!empty($Preference))
			{
				$Sample =array();
				foreach($Preference as $p)
				{
					$Sample['Categoy'] 		= $p->job_category;
					$Sample['SubCategoy'] 	= $p->job_sub_category;
					$Sample['PayRate'] 		= '$'.$p->pay_rate.'/hr';
					array_push($JobPreferences, $Sample);
				}
			}
			if(!empty($Education))
			{
				$Sample =array();
				foreach($Education as $e)
				{
					$Sample['Institute'] = $e->institute;
					$Sample['Degree'] 	= $e->degree;
					$Sample['From'] 	= $e->from;
					$Sample['To'] 		= $e->to;
					$Sample['Persuing'] = $e->persuing;
					array_push($EducationDetails, $Sample);
				}
			}			
			if(!empty($Language))
			{
				$Sample =array();
				foreach($Language as $p)
				{
					$Sample['Language'] = $p->language;
					if($p->level=='1'){ $Sample['Level'] 	= "Beginner"; }
					if($p->level=='2'){ $Sample['Level'] 	= "Intermediate"; }
					if($p->level=='3'){ $Sample['Level'] 	= "Proficient"; }
					
					array_push($LanguageDetails, $Sample);
				}
			}

			

			if(!empty($Appearance))
			{
				$MyHairColor = $Appearance->my_hair_color;
				$MyEyeColor = $Appearance->my_eye_color;
				$MyHeight 	= $Appearance->height;
				$MyWeight 	= $Appearance->weight;
				
				if($UserDetails->transportation=='0')
				{
					$Transportation = 'No';
				} 
				elseif($UserDetails->transportation=='1')
				{
					$Transportation = 'Yes';
				}
				elseif($UserDetails->transportation=='')
				{
					$Transportation = 'N/A';
				}
				$Gender = '';
				if($UserDetails->gender=='0' || $UserDetails->gender=='')
				{
					$Gender = 'Not mentioned';
				} 
				elseif($UserDetails->gender=='1')
				{
					$Gender = 'Male';
				}
				elseif($UserDetails->gender=='')
				{
					$Gender = 'Female';
				}

				$AppearanceDetails['HairColor'] = $MyHairColor;
				$AppearanceDetails['EyeColor']  = $MyEyeColor;
				$AppearanceDetails['Height']  	= $MyHeight;
				$AppearanceDetails['Weight']  	= $MyWeight;
				$AppearanceDetails['Gender']  	= $Gender;
				$AppearanceDetails['Transportation']  	= $Transportation;
			}
			else
			{
				$AppearanceDetails['HairColor'] = "";
				$AppearanceDetails['EyeColor']  = "";
				$AppearanceDetails['Height']  	= "";
				$AppearanceDetails['Weight']  	= "";
				$AppearanceDetails['Gender']  	= "";
				$AppearanceDetails['Transportation']  	= "";
			}
			if(!empty($Accreditations))
			{
				$Sample =array();
				foreach($Accreditations as $e)
				{
					$Sample['Accreditations'] 	= $e->accreditations;
					$Sample['Image'] 			= asset('public/Front/Users/Accreditations').'/'.$e->image;
					$Sample['ExpiredOn'] 		= $e->exp_date;
					array_push($AccreditationsDetails, $Sample);
				}
			}
			else
			{
				$Sample =array();
				$Sample['Accreditations'] = "";
				$Sample['Image']  = "";
				$Sample['ExpiredOn']  	= "";
				array_push($AccreditationsDetails, $Sample);
			}
			if(!empty($Certifications))
			{
				$Sample =array();
				foreach($Certifications as $e)
				{
					$Sample['Certifications'] 	= $e->name;
					$Sample['Image'] 			= asset('public/Front/Users/Certification').'/'.$e->image;
					$Sample['Description'] 		= $e->description;
					array_push($CertificationsDetails, $Sample);
				}
			}
			else
			{
				$Sample =array();
				$Sample['Certifications'] = "";
				$Sample['Image']  = "";
				$Sample['Description']  	= "";
				array_push($CertificationsDetails, $Sample);
			}


			$ProsList 	 = $this->ViewProsModel->GetSimilarPros($ProsID);
			$SimilarPros = array();
			if(!empty($ProsList))
			{
				$Sample = array();
				foreach($ProsList as $p)
				{ 
					$ProsID 				= $p->id;
					$Sample['ProsID'] 		= $ProsID;
					$Sample['Image'] 		= $Image;
					$Sample['FirstName'] 	= $p->first_name;
					$Sample['LastName'] 	= $p->last_name;
					$Sample['Location'] 	= $p->location;
					$Sample['Favourite'] 	= 0;
					$Sample['Invited'] 		= 0;
					$CheckLoginDetails  	= $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
					if(!empty($CheckLoginDetails))
					{
						$Sample['Favourite'] 	= $this->ViewProsModel->ProsFavStatus($UserID,$ProsID);
						$Sample['Invited'] 		= $this->ViewProsModel->ProsInviteStatus($UserID,$ProsID);
					}
					array_push($SimilarPros, $Sample);
				}
			}
			$Response = ['Status' 				=> true,
						'Message' 				=> 'Pros Details.',
						'ProsDetails' 			=> $ProsDetails,
						'ExperienceDetails'		=> $ExperienceDetails,
						'JobPreferences' 		=> $JobPreferences,
						'EducationDetails' 		=> $EducationDetails,
						'LanguageDetails' 		=> $LanguageDetails,
						'AppearanceDetails' 	=> $AppearanceDetails,
						'AccreditationsDetails' => $AccreditationsDetails,
						'CertificationsDetails' => $CertificationsDetails,
						'SimilarPros'		 	=> $SimilarPros
						];			
		}
		else
		{
			$Response = ['Status'=>false,
						'Message'=>'Pros Does Not Exist.'
						];
		}
		
		
	  	return response()->json($Response);
	}

	public function CheckAvailabilityAPI(Request $request)
	{
		$Data 			= $request->all();	
		$ProsID 		= $Data['ProsID'];
		$Response   	= array();	
		$AvailabilityArray   = array();	
		$UserDetails 	= $this->UserModel->UserDetails($ProsID);
		$Availability = array();
		if($UserDetails->availability!='')
		{
			$Availability = json_decode($UserDetails->availability);
		}
			
		$TimeSlots 		= $this->UserModel->GetTimeSlots();

		if(empty($Availability))
		{
			$Response = ['Status'=>false,
						'Message'=>'Still Not Added Availablity.'
						];
		}
		else
		{
			$Sample = array();
			$TimeStamp = strtotime('next Monday');
			for($i = 0; $i < 7; $i++) 
			{ 
				$Day= strftime('%A', $TimeStamp);
				
				$Sample['Day'] 		= $Day;

				$From=""; 
				$To="";
				$FromVal=""; 
				$ToVal="";

				if(!empty($Availability))
				{
					if($Availability[$i]->NotAvailable==0)
					{
						$FromVal 	= $this->GetSlot($Availability[$i]->From);
						$ToVal 		= $this->GetSlot($Availability[$i]->To);
					}
					else
					{
						$FromVal 	= 'Not Available.';
						$ToVal 		= 'Not Available.';
					}
				}
				else
				{
					$FromVal 	= 'Not Available.';
					$ToVal 		= 'Not Available.';
				}

				$Sample['From'] 	= $FromVal;
				$Sample['To']	 	= $ToVal;

				array_push($AvailabilityArray, $Sample);
			 
			}

			$Response = ['Status'=>true,
						'Message'=>'Check Availability.',
						'Availability'=>$AvailabilityArray
						];
		}
		return response()->json($Response);
	}

	public function GetSlot($SlotID)
	{
		$Slot = "";
		$TimeSlots 		= $this->UserModel->GetTimeSlots();

		foreach($TimeSlots as $ts)
		{ 													
			if($SlotID == $ts->id)
			{ 
				$Slot = $ts->time_slot;
			}													
		} 
		return $Slot;
	}
}