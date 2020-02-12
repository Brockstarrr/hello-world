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
use App\Http\Models\API\PostAJobModel;
use App\Http\Models\Front\JobPostModel;
use App\Http\Models\API\CommonModel;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Pagination;

class PostAJobController extends Controller 
{
	public function __construct(Request $request)
	{
		$this->JobPostModel = new JobPostModel();
		$this->PostAJobModel = new PostAJobModel();
		$this->CommonModel 	= new CommonModel();
	}
	public function PostAJobAPI(Request $request)
	{
		$Data 			= $request->all();	
		
				
		$Response   	= array();	
		$JobDetails   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];

		$JobType 		= $Data['JobType'];	
		$JobTitle 		= $Data['JobTitle'];	
		$CategoryID		= $Data['CategoryID'];	
		$SubCatAndOpenings		= $Data['SubCatAndOpenings'];	
		$Description 	= $Data['Description'];

		$Address 		= $Data['Address'];	
		$InterviewVenue = $Data['InterviewVenue'];	
		$LocationInstruction 	= $Data['AccessToken'];	
		$Parking 		= $Data['Parking'];	
		$Preference 	= $Data['Preference'];	
		$WorkSiteContact= $Data['WorkSiteContact'];	

		$Language 		= $Data['Language'];

		$NoMatter 		= $Data['NoMatter'];	
		$AgeLimit 		= $Data['AgeLimit'];	
		$HairColor 		= $Data['HairColor'];	
		$EyeColor 		= $Data['EyeColor'];	
		$Height 		= $Data['Height'];	
		$Gender 		= $Data['Gender'];

		$Uniform 		= $Data['Uniform'];	
		
		if($UserID=='')
		{
			$Response = ['Status'=>false,'Message'=>'UserID Missing.'];	
			return response()->json($Response);	
		}
		if($AccessToken=='')
		{
			$Response = ['Status'=>false,'Message'=>'AccessToken Missing.'];	
			return response()->json($Response);	
		}
		if($JobType=='')
		{
			$Response = ['Status'=>false,'Message'=>'JobType Missing.'];	
			return response()->json($Response);	
		}
		if($JobTitle=='')
		{
			$Response = ['Status'=>false,'Message'=>'JobTitle Missing.'];	
			return response()->json($Response);	
		}
		if($CategoryID=='')
		{
			$Response = ['Status'=>false,'Message'=>'Category Missing.'];	
			return response()->json($Response);	
		}
		if($Description=='')
		{
			$Response = ['Status'=>false,'Message'=>'Description Missing.'];	
			return response()->json($Response);	
		}
		if($Address=='')
		{
			$Response = ['Status'=>false,'Message'=>'Address Missing.'];	
			return response()->json($Response);	
		}
		if($InterviewVenue=='')
		{
			$Response = ['Status'=>false,'Message'=>'Interview Venue Missing.'];	
			return response()->json($Response);	
		}
		if($LocationInstruction=='')
		{
			$Response = ['Status'=>false,'Message'=>'Location Instruction Missing.'];	
			return response()->json($Response);	
		}
		if($Parking=='')
		{
			$Response = ['Status'=>false,'Message'=>'Parking Missing.'];	
			return response()->json($Response);	
		}		
		if($NoMatter=='')
		{
			$Response = ['Status'=>false,'Message'=>'AgeLimit Missing.'];	
			return response()->json($Response);	
		}
		
		if(empty($SubCatAndOpenings))
		{
			$Response = ['Status'=>false,'Message'=>'Sub Cat And Openings Missing.'];	
			return response()->json($Response);
		}

		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);	
		if(!empty($CheckLoginDetails))
		{
			$JobDetails['profile_id'] 	= $UserID;
			$JobDetails['job_type'] 	= $JobType;
			$JobDetails['job_title'] 	= $JobTitle;
			$JobDetails['job_cat'] 		= $CategoryID;
			$JobDetails['job_description'] = $Description;
			$JobDetails['address'] 		= $Address;
			$JobDetails['venue'] 		= $InterviewVenue;
			$JobDetails['location'] 	= $LocationInstruction;
			$JobDetails['parking'] 		= $Parking;			
			$JobDetails['work_site_contact'] 		= $WorkSiteContact;
			if($NoMatter=='1')	
			{	
				$JobDetails['age_limit'] 	= $AgeLimit;
				$JobDetails['hair_color'] 	= $HairColor;
				$JobDetails['eye_color'] 	= $EyeColor;
				$JobDetails['height'] 		= $Height;
				$JobDetails['gender'] 		= $Gender;
			}
			$JobDetails['uniform'] 		= $Uniform;
			$JobDetails['preference'] 	= $Preference;
			$File = $request->file('Image');
		    if(!empty($File)){
			    $Path = 'public/Front/Users/Jobs';
		        $BannerName = str_replace(' ', '_', $File->getClientOriginalName());
		        $Upload = $File->move($Path, $BannerName);
		        $JobDetails['image'] 		= $BannerName;
	        }
	       	
	       	$LanguageArray = array();
        	if(!empty($Language))
        	{
        		$Sample = array();
        		$Array2 = json_decode($Language);
		        foreach($Array2 as $l)
		        {
		        	$Sample['language'] = $l->Language;
			        $Sample['level'] 	= $l->Level;
		        	array_push($LanguageArray, $Sample);
		        }
        	}

	        $SubCatAndOpeningArray = array();
	        if(!empty($SubCatAndOpenings))
	        {
	        	$Array1 = json_decode($SubCatAndOpenings);
		        foreach($Array1 as $a)
		        {
		        	$Sample = array();		        	
		    		$Sample['sub_cat'] 		= $a->SubCategoryID;   	        		        		
		    		$Sample['openings']	 	= $a->Openings;	        		        		
		    		$Sample['job_for'] 		= $a->JobFor; 	        		        		
		    		$Sample['start_date'] 	= $a->StartDate; 	        		        		
		    		$Sample['end_date'] 	= $a->EndDate;    	        		        		
		    		$Sample['hour_from'] 	= $a->HourTimeFrom;    	        		        		
		    		$Sample['hour_from_label'] 	= $this->JobPostModel->GetTimeSlot($a->HourTimeFrom);    	        		        		
		    		$Sample['hour_to'] 		= $a->HourTimeTo;    	        		        		
		    		$Sample['hour_to_label'] 	= $this->JobPostModel->GetTimeSlot($a->HourTimeTo);    	        		        		
		    		$Sample['breaks'] 		= $a->Breaks; 	        		        		
		    		$Sample['break_time'] 			= json_encode($a->BreakTimes); 	        		        		
		    		$Sample['break_paid_unpaid'] 	= json_encode($a->BreakPaidUnpaids); 		        		        		
		    		$Sample['pay_type'] 	= $a->PayRateType; 	        		        		
		    		$Sample['pay_rate'] 	= $a->PayRate;  	        		        		
		    		$Sample['total_hour'] 	= $a->TotalHour;	        	        	        		
		        	array_push($SubCatAndOpeningArray, $Sample);
		        }
	        }
    	

	        $SaveJobDetails = $this->JobPostModel->SaveJobDetails($JobDetails,$LanguageArray,$SubCatAndOpeningArray);
			if($SaveJobDetails)
			{
	          	$Response = ['Status'=>true,'Message'=>'Job Details Saved Successfully.'];
			}
			else
			{
				$Response = ['Status'=>true,'Message'=>'OOPS! Something Wrong. PLease Try Again.'];
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];		
		}
	  	return response()->json($Response);			
	}

	public function GetTotalHoursAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$JobFor 		= $Data['JobFor'];
		$TimeSlotFrom 	= $Data['TimeSlotFrom'];
		$TimeSlotTo 	= $Data['TimeSlotTo'];
		if($UserID=='')
		{
			$Response = ['Status'=>false,'Message'=>'UserID Missing.'];	
			return response()->json($Response);	
		}
		if($AccessToken=='')
		{
			$Response = ['Status'=>false,'Message'=>'AccessToken Missing.'];	
			return response()->json($Response);	
		}
		if($JobFor=='' || $TimeSlotFrom=='' || $TimeSlotTo=='')
		{
			$Response = ['Status'=>false,'Message'=>'Parameter Missing.'];	
			return response()->json($Response);	
		}

		if($JobFor=='2')
		{
			$DateFrom 		= $Data['DateFrom'];
			$DateTo 		= $Data['DateTo'];

			$datediff 		= strtotime($DateTo) - strtotime($DateFrom);

			$TotalDay 		= round($datediff / (60 * 60 * 24));

		}
		else
		{
			$DateFrom 		= $Data['DateFrom'];
			$DateTo 		= '';
			$TotalDay		= 1; 
		}	

		$From 	= $this->JobPostModel->GetTime($TimeSlotFrom);
		$To 	= $this->JobPostModel->GetTime($TimeSlotTo);			

		$difference = round(abs(strtotime($To) - strtotime($From)) / 3600,2);
		$TotalHours =  $difference*$TotalDay;

		$Response = ['Status'=>true,'TotalHours'=>$TotalHours];
		return response()->json($Response);	
	}
}