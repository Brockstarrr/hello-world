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
use App\Http\Models\API\FavouriteModel;
use App\Http\Models\Front\UserModel;
use App\Http\Models\Front\ViewJobModel;
use App\Http\Models\Front\ViewProsModel;
use App\Http\Models\API\InviteModel;
use App\Http\Models\API\CommonModel;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Pagination;
use App\Http\Models\Front\JobPostModel;
class FavouriteController extends Controller 
{
	public function __construct(Request $request)
	{
		$this->JobPostModel 	= new JobPostModel();
		$this->FavouriteModel 	= new FavouriteModel();
		$this->ViewJobModel 	= new ViewJobModel();
		$this->ViewProsModel 	= new ViewProsModel();
		$this->CommonModel 		= new CommonModel();
		$this->UserModel 		= new UserModel();
	}	
	public function FavouriteJobAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$JobID 			= $Data['JobID'];	
		$Status 		= $Data['Status'];	

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
		if($JobID=='')
		{
			$Response = ['Status'=>false,'Message'=>'JobID Missing.'];	
			return response()->json($Response);	
		}
		if($Status=='')
		{
			$Response = ['Status'=>false,'Message'=>'Status Missing.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);	
		if(!empty($CheckLoginDetails))
		{		
			if($Status==1)
			{	
				$IsFavouriteAlready  = $this->FavouriteModel->IsFavouriteAlready($UserID, $JobID);
				if($IsFavouriteAlready>0)
				{
					$Response = ['Status'=>false,'Message'=>'Already In Favourite List.'];	
				}
				else
				{
					$Details['profile_id'] 	= $UserID;
					$Details['job_id'] 		= $JobID;
					$Add  = $this->FavouriteModel->AddToFavouriteJob($Details);
					if($Add)
					{
						$Response = ['Status'=>true,'Message'=>'Successfully Added In Favourite List.'];					
					}
					else
					{
						$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];	
					}
				}
			}
			else
			{
				$IsFavouriteAlready  = $this->FavouriteModel->IsFavouriteAlready($UserID, $JobID);
				if($IsFavouriteAlready<=0)
				{
					$Response = ['Status'=>false,'Message'=>'Not In Favourite List.'];	
				}
				else
				{
					$Delete  = $this->FavouriteModel->DeleteToFavouriteJob($UserID, $JobID);
					if($Delete)
					{
						$Response = ['Status'=>true,'Message'=>'Successfully Deleted In Favourite List.'];					
					}
					else
					{
						$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];	
					}
				}
			}
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token.'];		
		}
	  	return response()->json($Response);			
	}
	public function FavouriteJobListAPI(Request $request)
	{
		$Data 			= $request->all();	
		$FavouriteJobList   	= array();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];		

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
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);	
		if(!empty($CheckLoginDetails))
		{			
			$List  = $this->FavouriteModel->FavouriteJobList($UserID);
			if(!empty($List))
			{
				foreach ($List as $hjl) 
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
					$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
					if(!empty($CheckLoginDetails))
					{
						$Sample['JobFavStatus'] 	= $this->ViewJobModel->JobFavStatus($UserID,$JobID);
						$Sample['JobApplyStatus'] 	= $this->ViewJobModel->JobApplyStatus($UserID,$JobID);
					}
					$Sample['Openings'] = $this->GetOpeningsDetails($JobID);
					array_push($FavouriteJobList, $Sample);
				}
			}
			$Response = ['Status'=>true,
						'Message'=>'My Favourite Job List.',
						'FavouriteJobs'=>$FavouriteJobList];
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token.'];		
		}
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
	public function FavouriteProsAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$ProsID 			= $Data['ProsID'];	
		$Status 			= $Data['Status'];	


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
		if($ProsID=='')
		{
			$Response = ['Status'=>false,'Message'=>'ProsID Missing.'];	
			return response()->json($Response);	
		}
		if($Status=='')
		{
			$Response = ['Status'=>false,'Message'=>'Status Missing.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);	
		if(!empty($CheckLoginDetails))
		{	
			if($Status==1)
			{
				$IsFavouriteAlready  = $this->FavouriteModel->IsFavouriteProsAlready($UserID, $ProsID);
				if($IsFavouriteAlready>0)
				{
					$Response = ['Status'=>false,'Message'=>'Already In Favourite List.'];	
					
				}
				else
				{
					$Details['profile_id'] 	= $UserID;
					$Details['pros_id'] 		= $ProsID;
					$Add  = $this->FavouriteModel->AddToFavouritePros($Details);
					if($Add)
					{
						$Response = ['Status'=>true,'Message'=>'Successfully Added In Favourite List.'];					
					}
					else
					{
						$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];	
					}
				}		
			}
			else
			{
				$IsFavouriteAlready  = $this->FavouriteModel->IsFavouriteProsAlready($UserID, $ProsID);
				if($IsFavouriteAlready<=0)
				{
					$Response = ['Status'=>false,'Message'=>'Not In Favourite List.'];	
				}
				else
				{
					$Delete  = $this->FavouriteModel->DeleteToFavouritePros($UserID, $ProsID);
					if($Delete)
					{
						$Response = ['Status'=>true,'Message'=>'Successfully Deleted In Favourite List.'];					
					}
					else
					{
						$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];	
					}
				}
			}
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token.'];		
		}
	  	return response()->json($Response);			
	}
	public function FavouriteProsListAPI(Request $request)
	{
		$Data 			= $request->all();	
		$FavouriteProsList   	= array();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];		

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
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);	
		if(!empty($CheckLoginDetails))
		{			
			$List  = $this->FavouriteModel->FavouriteProsList($UserID);
			if(!empty($List))
			{
				foreach ($List as $hjl) 
				{

					if($hjl->image!=''){
						$Image = asset('public/Front/Users/Profile').'/'.$hjl->image;
					}else{ 
						$Image = asset('public/Front/Design/img/pro_pic.png');
					}
					$ProsID 				= $hjl->id;

					$UserTotalReviews = $this->ViewProsModel->UserTotalReviews($ProsID);
					$Rating = ceil($UserTotalReviews/5);
					$TotalReviews = $this->ViewProsModel->UserReviews($ProsID);


					$Sample['ProsID'] 		= $ProsID;
					$Sample['Name'] 		= $hjl->first_name.' '.$hjl->last_name;					
					$Sample['Image'] 		= $Image;
					$Sample['Favourite'] 	= 0;
					$Sample['Invited'] 		= 0;
					$Sample['TotalReviews'] = $TotalReviews;
					$Sample['Rating'] 		= $Rating;

					$CheckLoginDetails  	= $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
					if(!empty($CheckLoginDetails))
					{
						$Sample['Favourite']= $this->ViewProsModel->ProsFavStatus($UserID,$ProsID);
						$Sample['Invited'] = $this->ViewProsModel->ProsInviteStatus($UserID,$ProsID);
					}
					$JobCategory 			= $this->ViewProsModel->GetUserJobCategory($ProsID);
					$JobCategoryArray = array();
					foreach($JobCategory as $jc)
					{
						$JobCategoryArray[] = $jc->position;
					}
					$Sample['JobCategory'] 	= $JobCategoryArray;
					array_push($FavouriteProsList, $Sample);
				}
			}
			else
			{
				$Sample['ProsID'] 		= "";
				$Sample['Name'] 		= "";					
				$Sample['Image'] 		= "";
				$Sample['Favourite'] 	= "";
				$Sample['Favourite']	= "";
				$Sample['JobCategory']  = "";
				array_push($FavouriteProsList, $Sample);
			}
			$Response = ['Status'=>true,
						'Message'=>'My Favourite Pros List.',
						'FavouritePros'=>$FavouriteProsList];
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token.'];		
		}
	  	return response()->json($Response);			
	}

}