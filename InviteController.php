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
use App\Http\Models\Front\InvitesModel;
use App\Http\Models\Front\UserModel;
use App\Http\Models\Front\ViewJobModel;
use App\Http\Models\Front\ViewProsModel;
use App\Http\Models\API\InviteModel;
use App\Http\Models\API\CommonModel;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Pagination;

class InviteController extends Controller 
{
	public function __construct(Request $request)
	{
		$this->ViewJobModel = new ViewJobModel();
		$this->ViewProsModel = new ViewProsModel();
		$this->InviteModel 	= new InviteModel();
		$this->InvitesModel = new InvitesModel();
		$this->CommonModel 	= new CommonModel();
		$this->UserModel = new UserModel();
	}
	public function InvitesAPI(Request $request)
	{
		$Data 			= $request->all();	
		$InviteISent  	= array();
		$InviteIGot  	= array();
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
			$InviteISentList = $this->InvitesModel->InvitesISent($UserID);
			if(!empty($InviteISentList))
			{
				foreach ($InviteISentList as $is) 
				{
					$JobPrefrence=array();
					$ProsID = $is->id;
					$JobPrefrenceList 	= $this->ViewProsModel->GetUserJobCategory($ProsID);
					if(!empty($JobPrefrenceList))
					{
						foreach ($JobPrefrenceList as $jp)
						{ 
							$JobPrefrence[] = $jp->position;
						}
					}
					$UserTotalReviews = $this->ViewProsModel->UserTotalReviews($ProsID);
					$Rating = ceil($UserTotalReviews/5);
					$TotalReviews = $this->ViewProsModel->UserReviews($ProsID);

					$Sent=array();
					$Sent['ProsID'] 	= $ProsID;
					$Sent['ProsName'] 	= $is->first_name.' '.$is->last_name;
					if($is->image=='')
					{
						$Image = asset('public/Front/Design/img/pro_pic.png');
					} 
					else 
					{
						$Image = asset('public/Front/Users/Profile').'/'.$is->image;
					}
					$Sent['ProsImage'] = $Image;
					$Sent['JobPrefrence'] = $JobPrefrence;
					$Sent['TotalReviews'] = $TotalReviews;
					$Sent['Rating'] 		= $Rating;
					$Sent['Location'] 	= $is->location;
					$Sent['InviteOn'] 	= date('M d, Y',strtotime($is->invited_at));
					array_push($InviteISent, $Sent);
				}
			}
			else
			{
				$Sent=array();
				$Sent['ProsID'] 	= '';
				array_push($InviteISent, $Sent);
			}
			$InviteIGotList = $this->InvitesModel->InvitesIGot($UserID);
			if(!empty($InviteIGotList))
			{
				foreach ($InviteIGotList as $is) 
				{
					$JobID = $is->id;

					$JobTotalReviews= $this->ViewJobModel->JobTotalReviews($JobID);
					$JobReviews 	= $this->ViewJobModel->JobReviews($JobID);

					$JobFavStatus = $this->ViewJobModel->JobFavStatus($UserID,$JobID);
					$JobApplyStatus = $this->ViewJobModel->JobApplyStatus($UserID,$JobID);
					$Got=array();

					$Got['JobID'] 		= $JobID;
					$Got['JobTitle'] 	= $is->job_title;
					$Got['PostedBy'] 	= $is->username;
					if($is->image=='')
					{
						$Image = asset('public/Front/Design/img/pro_pic.png');
					} 
					else 
					{
						$Image = asset('public/Front/Users/Jobs').'/'.$is->image;
					}
					$Got['Image'] 		= $Image;
					$Got['Address'] 	= $is->address;
					$Got['PostedOn'] 	= date('M d, Y',strtotime($is->add_date));
					$Got['Favourite'] 	= $JobFavStatus;
					$Got['TotalReviews'] = $JobReviews;
					$Got['Rating'] 	= ceil($JobTotalReviews/5);
					$Got['Apply'] 		= $JobApplyStatus;
					array_push($InviteIGot, $Got);
				}
			}
			else
			{
				$Got=array();
				$Got['JobID'] 	= '';
				array_push($InviteIGot, $Got);
			}
			$Response = ['Status'=>true,
						'Message'=>'Invites.',
						'InviteISent'=>$InviteISent,
						'InviteIGot'=>$InviteIGot
						];
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];		
		}
	  	return response()->json($Response);			
	}

	public function GetJobListForInvitesAPI(Request $request)
	{
		$Data 			= $request->all();	
		$JobList  		= array();
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
			$GetJobList = $this->InviteModel->GetJobList($UserID);
			if(!empty($GetJobList))
			{
				foreach ($GetJobList as $is) 
				{
					$JobID = $is->id;					
					$List=array();
					$List['JobID'] 		= $JobID;
					$List['JobTitle'] 	= $is->job_title;
					array_push($JobList, $List);
				}
			}
			
			$Response = ['Status'=>true,
						'Message'=>'Job List For Invites.',
						'JobList'=>$JobList
						];
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];		
		}
	  	return response()->json($Response);			
	}

	public function SendInvitesAPI(Request $request)
	{
		$Data 			= $request->all();	
		$JobList  		= array();
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$ProsID 		= $Data['ProsID'];	
		$JobIDs 		= $Data['JobIDs'];	
				
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
		if($JobIDs=='')
		{
			$Response = ['Status'=>false,'Message'=>'JobIDs Array Empty.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);	
		if(!empty($CheckLoginDetails))
		{
			$SendInvites = $this->InviteModel->SendInvites($UserID,$ProsID,$JobIDs);
			if($JobIDs!='')
			{
				$Response = ['Status'=>true,'Message'=>'Invitation Sent Successfully.'];				
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'Something Wrong Please Try Again.'];
			}
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];		
		}
	  	return response()->json($Response);			
	}
}