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
use App\Http\Models\Front\ViewJobModel;
use App\Http\Models\Front\MyScheduleModel;
use App\Http\Models\API\JobApplyModel;
use App\Http\Models\API\JobModel;
use App\Http\Models\API\CommonModel;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Pagination;

class JobApplyController extends Controller 
{
	public function __construct(Request $request)
	{
		$this->MyScheduleModel = new MyScheduleModel();
		$this->ViewJobModel = new ViewJobModel();
		$this->JobApplyModel= new JobApplyModel();
		$this->JobModel 	= new JobModel();
		$this->CommonModel 	= new CommonModel();
	}
	public function JobDetailsForJobApplyAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$JobID 			= $Data['JobID'];	
		$JobDetails 			= array();
		$JobPositionDetails 	= array();
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}		
		if($JobID=='')
		{
			$Response = ['Status' => false,'Message' => 'JobID Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$Job 			= $this->ViewJobModel->GetJobDetail($JobID);
			if(!empty($Job))
			{
				$JobDetails['JobTitle'] 			= $Job->job_title;	
				$Positions 			= $this->ViewJobModel->GetSubCatOpenings($JobID);
				if(!empty($Positions))
				{
					$Sample = array();
					foreach($Positions as $Position)
					{
						$PayRate = $Position->pay_rate;
						$PayRateString = '';
						if($Position->pay_type==1)
						{
							$PayRateString = '$'.$PayRate;
						}
						else if($Position->pay_type==2)
						{
							$PayRateString = '$'.$PayRate.'/hr';
						}

						$Sample['PositionID'] 	= $Position->sub_cat;			
						$Sample['PositionName'] = $Position->position;			
						$Sample['Openings'] 	= $Position->openings;			
						$Sample['PayRate'] 		= $PayRateString;			
						$Sample['JobStartFrom'] = $Position->start_date;
						array_push($JobPositionDetails, $Sample);
					}			
				}
			}
			$Response = ['Status'	=> true,
						'Message' 	=> 'Job List.',
						'JobDetails'=> $JobDetails,
						'JobPositionDetails'=> $JobPositionDetails
						];
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function JobPositionApplyAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$JobID 			= $Data['JobID'];	
		$PositionID 	= $Data['PositionID'];	
		
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}		
		if($JobID=='')
		{
			$Response = ['Status' => false,'Message' => 'JobID Parameter Missing.'];
			return response()->json($Response);
		}
		if($PositionID=='')
		{
			$Response = ['Status' => false,'Message' => 'PositionID Parameter Missing.'];
			return response()->json($Response);
		}
		$JobDetails 			= array();
		$JobPositionDetails 	= array();

		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$Job 			= $this->ViewJobModel->GetJobDetail($JobID);
			if($Job->profile_id==$UserID)
			{
				$Response = ['Status'	=> false,
							'Message' 	=> 'You Can not Apply Your Own Job. Sorry!.'
							];
			}
			else
			{
				$CheckUserPosition = $this->ViewJobModel->CheckUserPosition($UserID,$PositionID);
				if($CheckUserPosition==1)
				{
					$Details['job_id'] 		= $JobID;
					$Details['profile_id'] 	= $UserID;
					$Details['position_id'] = $PositionID;
					$JobApply = $this->ViewJobModel->JobApply($Details);
					if($JobApply)
					{
						$Response = ['Status'	=> true,
								'Message' 	=> 'Job Applied.'
								];
					}
					else
					{
						$Response = ['Status'	=> false,
									'Message' 	=> 'JSomething Wrong Please Try Again.'
									];
					}					
				}
				else
				{
					$Response = ['Status'	=> false,
								'Message' 	=> 'You Are Not Eligible For This Position. Please Add Preference First.'
								];
				}
			}			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	///////////////////////////////////////////////////
	public function JobPositionAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$JobID 			= $Data['JobID'];	
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}		
		if($JobID=='')
		{
			$Response = ['Status' => false,'Message' => 'JobID Parameter Missing.'];
			return response()->json($Response);
		}
		$JobDetails 			= array();
		$JobPositionDetails 	= array();

		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$JobPosition = $this->ViewJobModel->GetSubCatOpenings($JobID);
			$Positions['PositionID'] 	= '';
			$Positions['PositionName'] 	= '';
			if(!empty($JobPosition))
			{
				$Positions 	= array();
				$Sample 	= array();
				foreach($JobPosition as $jp)
				{
					$Sample['PositionID'] 	= $jp->sub_cat;
					$Sample['PositionName'] = $jp->position;
					array_push($Positions, $Sample);
				}
				$Response = ['Status'	=> true,
							'Message' 	=> 'Job Position List.',
							'Positions' => $Positions
							];					
			}
			else
			{
				$Response = ['Status'	=> false,
							'Message' 	=> 'Something Wrong, Please Try Again.',
							'Positions' => $Positions
							];
			}		
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function JobApplicantAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Applicant   	= array();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$JobID 			= $Data['JobID'];	
		$PositionID 	= $Data['PositionID'];	
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}		
		if($JobID=='')
		{
			$Response = ['Status' => false,'Message' => 'JobID Parameter Missing.'];
			return response()->json($Response);
		}
		if($PositionID=='')
		{
			$Response = ['Status' => false,'Message' => 'PositionID Parameter Missing.'];
			return response()->json($Response);
		}
		$JobDetails 			= array();
		$JobPositionDetails 	= array();

		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{			
			$JobApplicants = $this->MyScheduleModel->GetJobApplicantsByPositoin($JobID,$PositionID);
			if(!empty($JobApplicants))
			{
				$Sample=array();
				foreach($JobApplicants as $j)
				{
					if($j->image==''){
					$Image = asset('public/Front/Design/img/pro_pic.png');
					} else {
					$Image = asset('public/Front/Users/Profile').'/'.$j->image;
					}

					$Sample['ApplyID']  = $j->apply_id;
					$Sample['Name']		= $j->username;
					$Sample['Image']	= $Image;
					$Sample['Location']	= $j->location;
					$Sample['AppliedAt']= date('M d, Y',strtotime($j->applied_at));
					$ApplyStatus 		= $j->apply_status;
					if($ApplyStatus=='1' || $ApplyStatus=='')
					{
						$Reject = 0;
						$Backup = 0;
						$Accept = 0;
						$JobConfirm = 0;
					}
					else if($ApplyStatus=='2')
					{
						$Reject = 0;
						$Backup = 0;
						$Accept = 1;
						$JobConfirm = 1;
					}
					else if($ApplyStatus=='3')
					{
						$Reject = 1;
						$Backup = 0;
						$Accept = 0;
						$JobConfirm = 0;
					}
					else if($ApplyStatus=='4')
					{
						$Reject = 0;
						$Backup = 1;
						$Accept = 0;
						$JobConfirm = 0;
					}
					$Sample['AcceptStatus'] = $Accept;
					$Sample['BackupStatus'] = $Backup;
					$Sample['RejectStatus'] = $Reject;
					$Sample['JobConfirmStatus'] = $JobConfirm;
					array_push($Applicant, $Sample);
				}				
				$Response = ['Status'=>true,
							'Message'=>'Job Applicant List',
							'Applicant'=>$Applicant
							];
			}
			else
			{
				$Applicant['ApplyID']  	= "";
				$Applicant['Name']		= "";
				$Applicant['Image']		= "";
				$Applicant['Location']	= "";
				$Applicant['AppliedAt']	= "";
				$Applicant['AcceptStatus'] = "";
				$Applicant['BackupStatus'] = "";
				$Applicant['RejectStatus'] = "";
				$Applicant['JobConfirmStatus'] = "";
				$Response = ['Status'=>true,
							'Message'=>'Job Applicant List',
							'Applicant'=>$Applicant
							];
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function SetJobStatusAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$JobID 			= $Data['JobID'];	
		$ApplyID 		= $Data['ApplyID'];	
		$Status 		= $Data['Status'];	
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}		
		if($JobID=='')
		{
			$Response = ['Status' => false,'Message' => 'JobID Parameter Missing.'];
			return response()->json($Response);
		}
		if($ApplyID=='')
		{
			$Response = ['Status' => false,'Message' => 'ApplyID Parameter Missing.'];
			return response()->json($Response);
		}
		if($Status=='')
		{
			$Response = ['Status' => false,'Message' => 'Status Parameter Missing.'];
			return response()->json($Response);
		}
		$JobDetails 			= array();
		$JobPositionDetails 	= array();

		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$ApplyIDExist = $this->JobApplyModel->ApplyIDExist($ApplyID);
			if($ApplyIDExist==1)
			{
				$SetApplyStatus = $this->MyScheduleModel->SetApplyStatus($JobID,$ApplyID,$Status);
				if($SetApplyStatus)
				{
					$Response['Status'] = true;
					$Response['Message']= 'Status Changed Successfully!';
				}
				else
				{
					$Response['Status'] = false;
					$Response['Message']= 'Something Wrong!, Please Try Again.';
				}
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'Wrong Apply-ID.'];
			}		
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function JobConfirmDetailsAPI(Request $request)
	{
		$Data 			= $request->all();	
		$ConfirmDetails = array();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$ApplyID 		= $Data['ApplyID'];	
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}
		if($ApplyID=='')
		{
			$Response = ['Status' => false,'Message' => 'ApplyID Parameter Missing.'];
			return response()->json($Response);
		}
		$JobDetails 			= array();
		$JobPositionDetails 	= array();

		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$ApplyIDExist = $this->JobApplyModel->ApplyIDExist($ApplyID);
			if($ApplyIDExist==1)
			{
				$JobAppliedDetails  = $this->MyScheduleModel->JobAppliedDetails($ApplyID);
				if(!empty($JobAppliedDetails))
				{
					$JobID 				= $JobAppliedDetails->job_id;
					$PositionID 		= $JobAppliedDetails->position_id;

					$JobDetails 		= $this->ViewJobModel->GetJobDetail($JobID);
					$JobCatID			= $JobDetails->job_cat;
					$CategoryDetails 	= $this->MyScheduleModel->GetCategoryDetails($JobCatID);
					$JobPositionDetails	= $this->MyScheduleModel->GetPositoinDetails($JobID,$PositionID);

					if($JobPositionDetails->pay_type=='1')
					{
						$PayType = "Fixed";
					}	
					else
					{
						$PayType = "Hourly";
					}

					$DiscountOnPromo 	= 0;
					$JobAmount 			= $JobPositionDetails->pay_rate;
					if($JobPositionDetails->pay_type=='2')
					{
						$JobAmount 		= $JobAmount*$JobPositionDetails->total_hour;
					}
					$EmployeeWillGet 	= $JobAmount;
					$AmountWillBeEscrow = $JobAmount+($JobAmount*$CategoryDetails->commission_rate/100);
					$CheckCouponApplied = $this->JobApplyModel->CheckCouponApplied($ApplyID);
					if(!empty($CheckCouponApplied))
					{
						if($CheckCouponApplied->coupon_type==1)
						{
							$DiscountOnPromo 	= $CheckCouponApplied->coupon_amount;
						}
						else
						{
							$DiscountOnPromo 	= ($JobAmount*$CategoryDetails->commission_rate/100)*$CheckCouponApplied->coupon_amount/100;
						}			
					}

					$CommissionRate 		= $JobAmount*$CategoryDetails->commission_rate/100;
					$AdminCommissionRate 	= $CommissionRate-$DiscountOnPromo;

					$ConfirmDetails['ApplyID'] 		= $ApplyID;
					$ConfirmDetails['JobID'] 		= $JobID;
					$ConfirmDetails['JobTitle'] 	= $JobDetails->job_title;
					$ConfirmDetails['PositionID'] 	= $PositionID;
					$ConfirmDetails['Position'] 	= $JobPositionDetails->position;
					$ConfirmDetails['PayType'] 		= $PayType;
					$ConfirmDetails['PayRate'] 		= "$".number_format($JobAmount,2);
					$ConfirmDetails['Comission '] 	=  number_format($CategoryDetails->commission_rate,2).'%';
					$ConfirmDetails['DiscountOnPromo '] 	= "$".number_format($DiscountOnPromo,2);
					$ConfirmDetails['EmployeeWillGet'] 		= "$".number_format($EmployeeWillGet,2);
					$ConfirmDetails['AmountWillBeEscrow'] 	= "$".number_format($AmountWillBeEscrow,2);


					$Response['Status'] 			= true;
					$Response['Message'] 			= 'Job Confirm Details';		
					$Response['ConfirmDetails'] 	= $ConfirmDetails;
				}
				else
				{
					$ConfirmDetails['ApplyID'] 		= "";
					$ConfirmDetails['JobID'] 		= "";
					$ConfirmDetails['JobTitle'] 	= "";
					$ConfirmDetails['PositionID'] 	= "";
					$ConfirmDetails['Position'] 	= "";
					$ConfirmDetails['PayType'] 		= "";
					$ConfirmDetails['PayRate'] 		= "";
					$ConfirmDetails['Comission '] 	= "";
					$ConfirmDetails['DiscountOnPromo '] 	= "";
					$ConfirmDetails['EmployeeWillGet'] 		= "";
					$ConfirmDetails['AmountWillBeEscrow'] 	= "";

					$Response['Status'] 			= false;
					$Response['Message'] 			= 'Job Details Not Found';		
					$Response['ConfirmDetails'] 	= $ConfirmDetails;
				}
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'Wrong Apply-ID.'];
			}		
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function ApplyCouponCodeAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$ApplyID 		= $Data['ApplyID'];	
		$CouponCode 	= $Data['CouponCode'];	
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}		
		if($ApplyID=='')
		{
			$Response = ['Status' => false,'Message' => 'ApplyID Parameter Missing.'];
			return response()->json($Response);
		}
		if($CouponCode=='')
		{
			$Response = ['Status' => false,'Message' => 'CouponCode Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$ApplyIDExist = $this->JobApplyModel->ApplyIDExist($ApplyID);
			if($ApplyIDExist==1)
			{
				$CheckCouponApplied = $this->JobApplyModel->CheckCouponApplied($ApplyID);
				if(empty($CheckCouponApplied))
				{
					$JobAppliedDetails  = $this->MyScheduleModel->JobAppliedDetails($ApplyID);
					$JobID 				= $JobAppliedDetails->job_id;
					$PositionID 		= $JobAppliedDetails->position_id;
					$CheckCouponCode 	= $this->MyScheduleModel->GetCouponDetails($CouponCode);
					if(!empty($CheckCouponCode))
					{
						$StartDate 		= strtotime($CheckCouponCode->start_date);
						$EndDate 		= strtotime($CheckCouponCode->end_date);
						$CurrentDate 	= strtotime(date('Y-m-d'));
						if($CurrentDate>=$StartDate && $CurrentDate<=$EndDate)
						{
							$CouponCode = $CheckCouponCode->code;
							$CouponType = $CheckCouponCode->coupon_type;
							$Amount 	= $CheckCouponCode->amount;
							$SaveDetails['profile_id'] 		= $UserID;
							$SaveDetails['apply_id'] 		= $ApplyID;
							$SaveDetails['coupon_code'] 	= $CouponCode;
							$SaveDetails['coupon_type'] 	= $CouponType;
							$SaveDetails['coupon_amount'] 	= $Amount;
							$SaveDetails 	= $this->JobApplyModel->SaveDetails($SaveDetails);
							if($SaveDetails)
							{
								$Response = ['Status'=>true,'Message'=>'Coupon Code Applied.'];
							}
							else
							{
								$Response = ['Status'=>false,'Message'=>'Something Wrong Please try Again.'];
							}
						}
						else
						{
							$Response = ['Status'=>false,'Message'=>'Coupon Code Expired.'];
						}
					}
					else
					{
						$Response = ['Status'=>false,'Message'=>'Coupon Code Does Not Exit.'];
					}
				}
				else
				{
					$Response = ['Status'=>false,'Message'=>'Coupon Code Already Applied.'];
				}	
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'Wrong Apply-ID.'];
			}	
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function RemoveCouponCodeAPI(Request $request)
	{
		$Data 			= $request->all();		
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$ApplyID 		= $Data['ApplyID'];	
		$CouponCode 	= $Data['CouponCode'];	
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}		
		if($ApplyID=='')
		{
			$Response = ['Status' => false,'Message' => 'ApplyID Parameter Missing.'];
			return response()->json($Response);
		}
		$ApplyIDExist = $this->JobApplyModel->ApplyIDExist($ApplyID);
		if($ApplyIDExist==1)
		{
			$RemoveCoupon 	= $this->JobApplyModel->RemoveCouponDetails($ApplyID);
			if($RemoveCoupon)
			{
				$Response = ['Status'=>true,'Message'=>'Coupon Removed Successfully.'];
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'Something Wrong Please try Again.'];
			}
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Wrong Apply-ID.'];
		}
		return response()->json($Response);
	}
	public function JobConfirmAPI(Request $request)
	{
		$Data 			= $request->all();	
		$ConfirmDetails = array();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$ApplyID 		= $Data['ApplyID'];	
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}
		if($ApplyID=='')
		{
			$Response = ['Status' => false,'Message' => 'ApplyID Parameter Missing.'];
			return response()->json($Response);
		}
		$JobDetails 			= array();
		$JobPositionDetails 	= array();

		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$ApplyIDExist = $this->JobApplyModel->ApplyIDExist($ApplyID);
			if($ApplyIDExist==1)
			{
				$JobAppliedDetails  = $this->MyScheduleModel->JobAppliedDetails($ApplyID);

				$JobID 				= $JobAppliedDetails->job_id;
				$PositionID 		= $JobAppliedDetails->position_id;

				$JobDetails 		= $this->ViewJobModel->GetJobDetail($JobID);
				$JobCatID			= $JobDetails->job_cat;
				$CategoryDetails 	= $this->MyScheduleModel->GetCategoryDetails($JobCatID);
				$JobPositionDetails	= $this->MyScheduleModel->GetPositoinDetails($JobID,$PositionID);

				$DiscountOnPromo 	= 0;
				$JobAmount 			= $JobPositionDetails->pay_rate;
				if($JobPositionDetails->pay_type=='2')
				{
					$JobAmount 		= $JobAmount*$JobPositionDetails->total_hour;
				}

				$EmployeeWillGet 	= $JobAmount;
				
				$CheckCouponApplied = $this->JobApplyModel->CheckCouponApplied($ApplyID);
				$AdminCommissionCouponCode = '';
				$AdminCommissionCouponAmount = 0;
				if(!empty($CheckCouponApplied))
				{
					if($CheckCouponApplied->coupon_type==1)
					{
						$DiscountOnPromo 	= $CheckCouponApplied->coupon_amount;
					}
					else
					{
						$DiscountOnPromo 	= ($JobAmount*$CategoryDetails->commission_rate/100)*$CheckCouponApplied->coupon_amount/100;
					}
					$AdminCommissionCouponCode = $CheckCouponApplied->coupon_code;
					$AdminCommissionCouponAmount = $DiscountOnPromo;
				}
				$AmountWillBeEscrow 	= $JobAmount+(($JobAmount*$CategoryDetails->commission_rate/100)-$DiscountOnPromo);
				$CommissionRate 		= $JobAmount*$CategoryDetails->commission_rate/100;
				$AdminCommissionRate 	= $CommissionRate-$DiscountOnPromo;

				$HaveBalance = $this->MyScheduleModel->HaveBalance($UserID);
				if($HaveBalance==0)
				{
					$Response['Status'] 			= false;
					$Response['Message'] 			= 'Your Wallet Balance Is Zero';	
				}
				else if($HaveBalance<$AmountWillBeEscrow)
				{
					$Response['Status'] 			= false;
					$Response['Message'] 			= 'You Dont Have Balance Sufficent Amount In Your Wallet';	
				}
				else 
				{		
					$EmployeeID =  $this->MyScheduleModel->GetEmployeeID($JobID);

					$Balance = $HaveBalance-$AmountWillBeEscrow;

					$JsonResponse['employee_id'] 		= $EmployeeID;
					$JsonResponse['job_id'] 			= $JobID;
					$JsonResponse['job_cat_id'] 		= $JobCatID;
					$JsonResponse['position_id'] 		= $PositionID;
					$JsonResponse['applied_id'] 		= $ApplyID;

					$Details['profile_id'] 			= $UserID;
		       		$Details['transaction_type'] 	= 3;
		       		$Details['transaction_id'] 		= '';
		       		$Details['amount'] 				= $AmountWillBeEscrow;
		       		$Details['balance'] 			= $Balance;
		       		$Details['added_at'] 			= date('Y-m-d H:i:s A',time());
		       		$Details['receipt_url'] 		= '';
		       		$Details['job_id'] 				= $JobID;
		       		$Details['job_position_id'] 		= $PositionID;
		       		$Details['status'] 				= 1;
		       		$Details['response'] 			= json_encode($JsonResponse);
		       		
		       		$AdminCommission['applied_id'] 		= $ApplyID;
		       		$AdminCommission['job_id'] 			= $JobID;
		       		$AdminCommission['position_id'] 	= $PositionID;
		       		$AdminCommission['commission'] 		= $AdminCommissionRate;
		       		$AdminCommission['coupon_code'] 	= $AdminCommissionCouponCode;
		       		$AdminCommission['coupon_amount'] 	= $AdminCommissionCouponAmount;
		       		$AdminCommission['employee'] 		= $EmployeeID;
		       		
		       		$ConfirmJob =  $this->MyScheduleModel->ConfirmJob($ApplyID,$Details,$AdminCommission,$JobID,$PositionID);
		       		if($ConfirmJob)
		       		{
		       			$this->JobApplyModel->RemoveCouponDetails($ApplyID);

		       			$Response['Status'] 				= true;
						$Response['Message'] 				= 'Job Confirmed Sucessfully';
		       		}
		       		else
		       		{
		       			$Response['Status'] 				= false;
						$Response['Message'] 				= 'Something Wrong Please Try Again';
		       		}						
				}
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'Wrong Apply-ID'];
			}
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}

	public function GetCheckInCheckOutAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$ApplyID 		= $Data['ApplyID'];		
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}		
		if($ApplyID=='')
		{
			$Response = ['Status' => false,'Message' => 'ApplyID Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$ApplyIDExist = $this->JobApplyModel->ApplyIDExist($ApplyID);
			if($ApplyIDExist==1)
			{
				$JobAppliedDetails  	= $this->MyScheduleModel->JobAppliedDetails($ApplyID);
				if(!empty($JobAppliedDetails))
				{
					$JobID 					= $JobAppliedDetails->job_id;
					$PositionStatus 		= $JobAppliedDetails->position_status;
					$JobPositionDetails 	= $this->MyScheduleModel->GetAppliedPositoinDetails($JobID,$UserID);
					if(!empty($JobPositionDetails))
					{
						$StartDate 				= $JobPositionDetails->start_date;
						$EndDate 				= $JobPositionDetails->end_date;
						$CheckInCheckOutDetails	= $this->MyScheduleModel->CheckInCheckOutDetailsForEmployee($JobID,$UserID);
						$CheckInCheckOut 		= array();
						$CheckInCheckOut['RowID']   = "";
						$CheckInCheckOut['Date']   	= "";
						$CheckInCheckOut['Start']	= "";
						$CheckInCheckOut['End']		= "";
						$CheckInCheckOut['Total']	= "";
						if(!empty($CheckInCheckOutDetails))
						{
							$Sample = array();
							foreach($CheckInCheckOutDetails as $cico)
							{
								$Sample['RowID']   	= $cico->id;
								$Sample['Date']   	= $cico->date;
								$Sample['Start']	= $cico->start;
								$Sample['End']		= $cico->end;
								$Sample['Total']	= $cico->total;
								array_push($CheckInCheckOut, $Sample);
							}
						}

						$Response = ['Status'			=> true,
									'Message'			=> 'View Check-In Check-Out',
									'JobID'				=> $JobID,
									'ApplyID'			=> $ApplyID,
									'PositionStatus'	=> $PositionStatus,
									'StartDate'			=> $StartDate,
									'EndDate'			=> $EndDate,
									'CheckInCheckOut'	=> $CheckInCheckOut];	
					}
					else
					{
						$Response = ['Status'=>false,'Message'=>'Job Position Details Not Found.'];
					}
				}
				else
				{
					$Response = ['Status'=>false,'Message'=>'Job Details Not Found.'];
				}
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'Wrong Apply-ID.'];
			}	
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function AddCheckInTimeAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$JobID 			= $Data['JobID'];		
		$CurrentDateTime= $Data['CurrentDateTime'];		
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}		
		if($JobID=='')
		{
			$Response = ['Status' => false,'Message' => 'JobID Parameter Missing.'];
			return response()->json($Response);
		}
		if($CurrentDateTime=='')
		{
			$Response = ['Status' => false,'Message' => 'DateTime Parameter Missing.'];
			return response()->json($Response);
		}

		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$Details['job_id']		= $JobID;
			$Details['employee_id']	= $UserID;
			$Details['date']		= date('Y-m-d',strtotime($CurrentDateTime));
			$Details['start']		= date('H:i A' ,strtotime($CurrentDateTime));
			
			$Details['add_date'] 	= date('Y-m-d H:i:s',time());

			$AddCheckInTime 		= $this->MyScheduleModel->AddCheckInTime($Details);
			if($AddCheckInTime)
			{
				$Response = ['Status'	=> true,'Message'	=> 'Check-In Time Added'];
			}
			else
			{
				$Response = ['Status'	=> false,'Message'	=> 'Something Wrong Please Try Again.'];
			}
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function AddCheckOutTimeAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$RowID 			= $Data['RowID'];		
		$CurrentDateTime= $Data['CurrentDateTime'];		
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}		
		if($RowID=='')
		{
			$Response = ['Status' => false,'Message' => 'RowID Parameter Missing.'];
			return response()->json($Response);
		}
		if($CurrentDateTime=='')
		{
			$Response = ['Status' => false,'Message' => 'DateTime Parameter Missing.'];
			return response()->json($Response);
		}

		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$Details['end']			= date('H:i A' ,strtotime($CurrentDateTime));		
			$Details['add_date'] 	= date('Y-m-d H:i:s',time());
			$AddCheckOutTime 		= $this->MyScheduleModel->AddCheckOutTime($RowID,$Details);
			if($AddCheckOutTime)
			{
				$Response = ['Status'	=> true,'Message'	=> 'Check-Out Time Added'];
			}
			else
			{
				$Response = ['Status'	=> false,'Message'	=> 'Something Wrong Please Try Again.'];
			}
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function CompleteJobAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$ApplyID 		= $Data['ApplyID'];			
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}		
		if($ApplyID=='')
		{
			$Response = ['Status' => false,'Message' => 'ApplyID Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$ApplyIDExist = $this->JobApplyModel->ApplyIDExist($ApplyID);
			if($ApplyIDExist==1)
			{
				$Details['position_status']	= '2';		
				$CompleteJob 				= $this->MyScheduleModel->CompleteJob($ApplyID,$Details);
				if($CompleteJob)
				{
					$Response = ['Status'	=> true,'Message'	=> 'Job Completed Successfully.'];
				}
				else
				{
					$Response = ['Status'	=> false,'Message'	=> 'Something Wrong Please Try Again.'];
				}
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'Wrong Apply-ID.'];
			}	
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}

	public function ApprovePaymentDetailsAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$ApplyID 		= $Data['ApplyID'];			
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}		
		if($ApplyID=='')
		{
			$Response = ['Status' => false,'Message' => 'ApplyID Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$ApplyIDExist = $this->JobApplyModel->ApplyIDExist($ApplyID);
			if($ApplyIDExist==1)
			{
				$JobAppliedDetails  = $this->MyScheduleModel->JobAppliedDetails($ApplyID);
				if(!empty($JobAppliedDetails ))
				{
					$JobID 				= $JobAppliedDetails->job_id;
					$JobPositionID 		= $JobAppliedDetails->position_id;

					$JobDetails 		= $this->MyScheduleModel->JobDetails($JobID);
					$JobCatID			= $JobDetails->job_cat;
					$CategoryDetails 	= $this->MyScheduleModel->GetCategoryDetails($JobCatID);
					$JobPositionDetails = $this->MyScheduleModel->GetJobPositionDetails($JobID,$JobPositionID);
					
					if($JobAppliedDetails->position_status==2)
					{
						$CheckInCheckOutDetails = $this->MyScheduleModel->CheckInCheckOutDetailsForEmployer($JobID,$UserID);
						if($JobPositionDetails->pay_type=='1')
						{
							$JobAmount 			= $JobPositionDetails->pay_rate;
							$EmployerWillGet 	= $JobAmount-($JobAmount*$CategoryDetails->commission_rate/100);
							$CommissionRate 	= $JobAmount*$CategoryDetails->commission_rate/100;
							$RevertBack 		= 0;
						}	
						else
						{
							$TotalWorkHours		= $JobPositionDetails->total_hour;  
							$TotalHours			= 0;  
							$HourlyPayRate		= $JobPositionDetails->pay_rate; 
							foreach($CheckInCheckOutDetails as $cico)
							{
								if($cico->start!='' && $cico->end!='')
								{
									$FromDate	= $cico->date.' '.$cico->start;
									$ToDate		= $cico->date.' '.$cico->end;
									$Hours 		= Common::GetTotalHours($FromDate,$ToDate); 

									$TotalHours = $TotalHours+$Hours;
								}
							}
							$JobAmount 			= $JobPositionDetails->pay_rate*$JobPositionDetails->total_hour;			
							$CommissionRate 	= $JobAmount*$CategoryDetails->commission_rate/100;	

							$TotalHoursRateCalculate = $HourlyPayRate*$TotalHours;
							if($TotalHoursRateCalculate>$JobAmount)
							{
								$EmployerWillGet 	= $JobAmount-($CommissionRate);
								$RevertBack 		= 0;
							}
							else
							{
								$EmployerWillGet 	= $TotalHoursRateCalculate;
								$RevertBack 		= ($JobAmount-($CommissionRate))-$TotalHoursRateCalculate;
							}			
						}


						$Response = ['Status'	=> true,
									'Message'	=> 'Approve Payment Details',
									'PayRate'	=> number_format($JobAmount,2),
									'Commission'		=> number_format($CategoryDetails->commission_rate,2).'%',
									'CommissionRate'	=> number_format($CommissionRate,2),
									'EmployerWillGet'	=> number_format($EmployerWillGet,2),
									'RevertBack'		=> number_format($RevertBack,2)
									];
					}
					else
					{
						$Response = ['Status'=>false,'Message'=>'Job Not Completed. Befor The Job Completed You Can Not Approve The Payment.'];
					}
				}
				else
				{
					$Response = ['Status'=>false,'Message'=>'Job Details Not Found.'];
				}
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'Wrong Apply-ID.'];
			}
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function ApprovePaymentAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$ApplyID 		= $Data['ApplyID'];			
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}		
		if($ApplyID=='')
		{
			$Response = ['Status' => false,'Message' => 'ApplyID Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$ApplyIDExist = $this->JobApplyModel->ApplyIDExist($ApplyID);
			if($ApplyIDExist==1)
			{

				$JobAppliedDetails  = $this->MyScheduleModel->JobAppliedDetails($ApplyID);

				$JobID 				= $JobAppliedDetails->job_id;
				$JobPositionID 		= $JobAppliedDetails->position_id;
				
				$EscrowDetails = $this->MyScheduleModel->GetEscrowDetails($JobID,$JobPositionID);

				$JobDetails 		= $this->MyScheduleModel->JobDetails($JobID);
				$JobCatID			= $JobDetails->job_cat;
				$CategoryDetails 	= $this->MyScheduleModel->GetCategoryDetails($JobCatID);
				$JobPositionDetails = $this->MyScheduleModel->GetJobPositionDetails($JobID,$JobPositionID);

				$CheckInCheckOutDetails = $this->MyScheduleModel->CheckInCheckOutDetailsForEmployer($JobID,$UserID);
				if($JobPositionDetails->pay_type=='1')
				{
					$JobAmount 			= $JobPositionDetails->pay_rate;
					$EmployerWillGet 	= $JobAmount-($JobAmount*$CategoryDetails->commission_rate/100);
					$CommissionRate 	= $JobAmount*$CategoryDetails->commission_rate/100;
					$RevertBack 		= 0;
				}	
				else
				{
					$TotalWorkHours		= $JobPositionDetails->total_hour;  
					$TotalHours			= 0;  
					$HourlyPayRate		= $JobPositionDetails->pay_rate; 
					foreach($CheckInCheckOutDetails as $cico)
					{
						if($cico->start!='' && $cico->end!='')
						{
							$FromDate	= $cico->date.' '.$cico->start;
							$ToDate		= $cico->date.' '.$cico->end;
							$Hours 		= Common::GetTotalHours($FromDate,$ToDate); 

							$TotalHours = $TotalHours+$Hours;
						}
					}
					$JobAmount 			= $JobPositionDetails->pay_rate*$JobPositionDetails->total_hour;			
					$CommissionRate 	= $JobAmount*$CategoryDetails->commission_rate/100;	

					$TotalHoursRateCalculate = $HourlyPayRate*$TotalHours;
					if($TotalHoursRateCalculate>$JobAmount)
					{
						$EmployerWillGet 	= $JobAmount-($CommissionRate);
						$RevertBack 		= 0;
					}
					else
					{
						$EmployerWillGet 	= $TotalHoursRateCalculate;
						$RevertBack 		= ($JobAmount-($CommissionRate))-$TotalHoursRateCalculate;
					}			
				}
				$ResponseArray 	= json_decode($EscrowDetails->response);
				$ProfileID 		= $ResponseArray->employee_id;
				$Balance 		= $this->MyScheduleModel->HaveBalance($ProfileID);
				$TransferToEmployee['profile_id'] 		= $ProfileID;
				$TransferToEmployee['transaction_type'] = '1';
				$TransferToEmployee['amount'] 			= $EmployerWillGet;
				$TransferToEmployee['balance'] 			= $Balance+$EmployerWillGet;
				$TransferToEmployee['status'] 			= '1';
				$TransferToEmployee['job_id'] 			= $JobID;
				$TransferToEmployee['job_position_id'] 	= $JobPositionID;

				$RevertToEmployer = array();
				if($RevertBack>0)
				{
					$Balance 		= $this->MyScheduleModel->HaveBalance($UserID);
					$RevertToEmployer['profile_id'] 		= $UserID;
					$RevertToEmployer['transaction_type'] 	= '5';
					$RevertToEmployer['amount'] 			= $RevertBack;
					$RevertToEmployer['balance'] 			= $Balance+$RevertBack;
					$RevertToEmployer['status'] 			= '1';
					$RevertToEmployer['job_id'] 			= $JobID;
					$RevertToEmployer['job_position_id'] 	= $JobPositionID;
				}

				$ApprovePayment = $this->MyScheduleModel->ApprovePayment($ApplyID,$TransferToEmployee,$RevertToEmployer,$RevertBack,$UserID,$JobID,$JobPositionID);
				if($ApprovePayment )
				{
					$Response = ['Status'=>true,'Message'=>'Payment Approved.'];
				}
				else
				{
					$Response = ['Status'=>false,'Message'=>'Something Wrong Pease Try Again.'];
				}
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'Wrong Apply-ID.'];
			}	
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function DisputePaymentAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$ApplyID 		= $Data['ApplyID'];			
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}		
		if($ApplyID=='')
		{
			$Response = ['Status' => false,'Message' => 'ApplyID Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$ApplyIDExist = $this->JobApplyModel->ApplyIDExist($ApplyID);
			if($ApplyIDExist==1)
			{
				$JobAppliedDetails  = $this->MyScheduleModel->JobAppliedDetails($ApplyID);

				$JobID 				= $JobAppliedDetails->job_id;
				$JobPositionID 		= $JobAppliedDetails->position_id;
				
				$ApprovePayment = $this->MyScheduleModel->DisputePayment($UserID,$JobID,$JobPositionID);
				if($ApprovePayment)
				{
					$Response = ['Status'=>true,'Message'=>'Payment Approved.'];
				}
				else
				{
					$Response = ['Status'=>false,'Message'=>'Something Wrong Pease Try Again.'];
				}
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'Wrong Apply-ID.'];
			}
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}


	//////////////////////////////////////////////////
	public function RateAProsAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$ProsID 		= $Data['ProsID'];			
		$Rating 		= $Data['Rating'];			
		$Review 		= $Data['Review'];			
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}		
		if($ProsID=='')
		{
			$Response = ['Status' => false,'Message' => 'ProsID Parameter Missing.'];
			return response()->json($Response);
		}
		if($Rating=='')
		{
			$Response = ['Status' => false,'Message' => 'Rating Parameter Missing.'];
			return response()->json($Response);
		}
		if($Review=='')
		{
			$Response = ['Status' => false,'Message' => 'Review Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$RatingExist = $this->JobApplyModel->RatingExist($ProsID,$UserID);
			if($RatingExist)
			{
				$Response = ['Status'=>false,'Message'=>'You have already given review and rating to this profile.'];
			}
			else
			{
				$Details['pros_id'] = $ProsID;
				$Details['job_id'] 	= '';
				$Details['review'] 	= $Review;
				$Details['rating'] 	= $Rating;
				$Details['profile_id'] 	= $UserID;
				$RateAPros = $this->MyScheduleModel->SaveProsReviewRating($Details);
				if($RateAPros)
				{
					$Response = ['Status'=>true,'Message'=>'Pros Review Rating Successfully.'];
				}
				else
				{
					$Response = ['Status'=>false,'Message'=>'Something Wrong Pease Try Again.'];
				}
			}
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
}