<?php
namespace App\Http\Controllers\Front;
use Route;
use Mail;
use Auth, Hash;
use Validator;
use Session;
use Redirect;
use DB;
use Crypt;
use Illuminate\Http\Request;
use App\Http\Models\Front\UserModel;
use App\Http\Models\Front\ViewProsModel;
use App\Http\Models\Front\ViewJobModel;
use App\Http\Models\Front\MyScheduleModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;
use DateTime;

class MyScheduleController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->Common 		= new Common();
		$this->UserModel 		= new UserModel();
		$this->ViewProsModel 	= new ViewProsModel();
		$this->ViewJobModel 	= new ViewJobModel();
		$this->MyScheduleModel 	= new MyScheduleModel();
	}

	public function MySchedule(Request $request)
	{
		$UserID = Session::get('UserID');
		$Data['Title'] 			= 'My Schedule ';
		$Data['Menu'] 			= 'MySchedule';
		$Data['Applied'] 		= $this->MyScheduleModel->GetMyScheduleApplied($UserID);
		$Data['Posted'] 		= $this->MyScheduleModel->GetMySchedulePosted($UserID);
		
		return View('Front/Pages/User/MySchedule')->with($Data);
	}	

	public function MyJobApplicant(Request $request, $JobID)
	{
		$JobID = base64_decode($JobID);
		$UserID = Session::get('UserID');
		$Data['Title'] 			= 'My Job Applicant ';
		$Data['Menu'] 			= 'MySchedule';
		$Data['JobDetails'] 	= $this->ViewJobModel->GetJobDetail($JobID);
		$Data['JobPosition'] 	= $this->ViewJobModel->GetSubCatOpenings($JobID);
		$Data['JobTotalReviews']= $this->ViewJobModel->JobTotalReviews($JobID);
		$Data['JobReviews'] 	= $this->ViewJobModel->JobReviews($JobID);
		return View('Front/Pages/User/MyJobApplicant')->with($Data);
	}

	public function GetJobApplicants(Request $request)
	{
		$Data 		= $request->all();		
		$JobID 		= $Data['JobID'];
		$PositionID = $Data['PositionID']; 
		$JobDetails = $this->MyScheduleModel->JobDetails($JobID);
		$GetPositoinDetails = $this->MyScheduleModel->GetPositoinDetails($JobID,$PositionID);
		$GetJobApplicantsByPositoin = $this->MyScheduleModel->GetJobApplicantsByPositoin($JobID,$PositionID);
		
		if(!empty($GetJobApplicantsByPositoin))
		{
			
			foreach($GetJobApplicantsByPositoin as $key=>$j)
			{
				$ProfileID 	= $j->id;
				$ApplyID 	= $j->apply_id;
				//$GetPayRate = $this->MyScheduleModel->GetPayRate($ProfileID,$PositionID);
				$GetPayRate = '';
				if($GetPositoinDetails->pay_type=='1')
				{
					$GetPayRate = $GetPositoinDetails->pay_rate.'/Fixed';
				}
				else if($GetPositoinDetails->pay_type=='2')
				{
					$GetPayRate = $GetPositoinDetails->pay_rate.'/Hr';
				}
				$TimeFrom = $this->MyScheduleModel->GetTimeBySlotID($GetPositoinDetails->hour_from);
				$TimeTo = $this->MyScheduleModel->GetTimeBySlotID($GetPositoinDetails->hour_to);
				$DateAndTime = 'Shift Time <b>'.$TimeFrom.' to '.$TimeTo.'</b>';
					
				$UserTotalReviews = $this->ViewProsModel->UserTotalReviews($j->id);
				$UserReviews = $this->ViewProsModel->UserReviews($j->id);

				
				$ApplyStatus = $j->apply_status;
				if($ApplyStatus=='1' || $ApplyStatus=='')
				{
					$Reject = '';
					$Backup = '';
					$Accept = '';
					$RejectDisabled = '';
					$BackupDisabled = '';
					$AcceptDisabled = '';
				}
				else if($ApplyStatus=='2')
				{
					$Reject = '';
					$Backup = '';
					$Accept = 'checked';
					$RejectDisabled = '';
					$BackupDisabled = '';
					$AcceptDisabled = 'disabled';
				}
				else if($ApplyStatus=='3')
				{
					$Reject = 'checked';
					$Backup = '';
					$Accept = '';
					$RejectDisabled = 'disabled';
					$BackupDisabled = '';
					$AcceptDisabled = '';

				}
				else if($ApplyStatus=='4')
				{
					$Reject = '';
					$Backup = 'checked';
					$Accept = '';
					$RejectDisabled = '';
					$BackupDisabled = 'disabled';
					$AcceptDisabled = '';
				}


			?>
			
			<?php 
			if($ApplyStatus=='2')
			{
				if($j->position_status=='4')
				{
					?>
					<div class="col-md-12">
						<div class="confm-btn" style="text-align: center !important;">
							<a class="btn cus_btn2 icon" href="javascript:void(0);">Job Completed</a>
							<a class="btn cus_btn2 icon" href="<?php echo route('ViewCheckInCheckOut',array('ApplyID'=>base64_encode($ApplyID)));?>">Check Details</a>
						</div>
					</div>
					<?php
				}
				if($j->position_status=='2')
				{
					?>
					<div class="col-md-12">
						<div class="confm-btn" style="text-align: center !important;">
							<a class="btn cus_btn2 icon" href="javascript:void(0);">Job Completed</a>
							<a class="btn cus_btn2 icon" href="<?php echo route('ViewCheckInCheckOut',array('ApplyID'=>base64_encode($ApplyID)));?>">Approve CheckIn/CheckOut</a>
						</div>
					</div>
					<?php
				}
				if($j->position_status=='1')
				{
					?>
					<div class="col-md-12">
						<div class="confm-btn" style="text-align: center !important;">
							<a class="btn cus_btn2 icon" href="javascript:void(0);">Job Confirmed</a>
							<a class="btn cus_btn2 icon" href="<?php echo route('ViewCheckInCheckOut',array('ApplyID'=>base64_encode($ApplyID)));?>">View CheckIn/CheckOut</a>
						</div>
					</div>
					<?php
				}
				else if($j->position_status=='0')
				{
					?>
					<div class="col-md-12">
						<div class="confm-btn" style="text-align: center !important;">
							<a class="btn cus_btn2 icon" href="javascript:void(0);"
								onclick="ConfirmJobModal(<?php echo $ApplyID; ?>);">Confirm Job</a>
						</div>
					</div>
					<?php
				}
			}
			?>
			<div class="job_cont">
				<div class="cont_wrap">
					<div id="ErrMsg_<?php echo $ApplyID; ?>"></div>
					<div class="img_wrap">
						<div class="lhs">
							<div class="pro_pic">
								<span>
									<?php if($j->image==''){?>
									<img src="<?php echo asset('public/Front/Design/img/pro_pic.png'); ?>" alt="" />
									<?php } else {?>
									<img src="<?php echo asset('public/Front/Users/Profile').'/'.$j->image; ?>" alt="" />
									<?php }?>
								</span>
							</div>
							<div>
								<p>
									<a href="<?php echo route('ViewProsDetails',array('ProsID'=>base64_encode($j->id))); ?>"> 
										<?php echo $j->username; ?> 
									</a>
								</p>
								
								<div class="job_star">
									<div class="rating-form"  name="rating-movie">
										<fieldset class="form-group">
											<legend class="form-legend">Rating:</legend>
											<div class="form-item">
												<?php
												$Rating = ceil($UserTotalReviews/5);
												for($i=5; $i>=1;$i--){
													$checked = '';
													if($i==$Rating){
														$checked = 'checked=""';
													}
													echo '<input disabled id="rating-'.$i.'" name="rating" type="radio" value="'.$i.'" '.$checked.'>
															<label for="rating-'.$i.'" data-value="'.$i.'">
																<span class="rating-star">
																	<i class="fa fa-star grey"></i>
																	<i class="fa fa-star gold"></i>
																</span>
															</label>';
												}
												echo "&nbsp";
												echo number_format($Rating,1);
												?>

											</div>
											<span>  <?php echo $UserReviews; ?> Reviews</span>
										</fieldset>
									</div>
								</div>
							</div>
						</div>
						<div class="rhs schedulerhs">
							<?php if($j->position_status!=1){ ?>
							
							<div class="select_applicant">
								<input <?php echo $Accept; ?> <?php echo $AcceptDisabled; ?> onclick="SetApplyStatusAccept(<?php echo $ApplyID; ?>);" 
									type="radio" id="Accept_<?php echo $key; ?>" name="Accept" value="" />
								<label for="Accept_<?php echo $key; ?>">Accept</label>										
							</div>
							<div class="select_applicant">
								<input <?php echo $Backup; ?> <?php echo $BackupDisabled; ?> onclick="SetApplyStatusBackup(<?php echo $ApplyID; ?>);" 
									type="checkbox" id="Backup_<?php echo $key; ?>" name="Backup" value=""/>
								<label for="Backup_<?php echo $key; ?>">Backup</label>										
							</div>
							<div class="select_applicant reject">
								<input <?php echo $Reject; ?> <?php echo $RejectDisabled; ?>  onclick="SetApplyStatusReject(<?php echo $ApplyID; ?>);" 
										type="checkbox" id="Reject_<?php echo $key; ?>" name="Reject" value=""/>
								<label for="Reject_<?php echo $key; ?>">Reject</label>										
							</div>
							<h4>$<?php echo $GetPayRate;?></h4>

							<?php } else{ 
								if($ApplyStatus=='2')
								{
									?>
									<div class="select_applicant">
										<input checked type="radio" id="Accept_<?php echo $key; ?>" />
										<label for="Accept_<?php echo $key; ?>">Accepted</label>
									</div>
									<?php
								}
								else if($ApplyStatus=='3')
								{
									?>
									<div class="select_applicant">
										<input checked type="radio" id="Reject_<?php echo $key; ?>" />
										<label for="Reject_<?php echo $key; ?>">Rejected</label>
									</div>
									<?php
								}
								else if($ApplyStatus=='4')
								{
									?>
									<div class="select_applicant">
										<input checked type="radio" id="Backup_<?php echo $key; ?>" />		
										<label for="Backup_<?php echo $key; ?>">Backup</label>
									</div>
									<?php
								}
							} ?>
						</div>
					</div>
					<div class="bt_cont">
						<div class="lhs">
							<span>
								<?php echo $DateAndTime; ?>
							</span>
							<p>
							<i class="fa fa-calendar" aria-hidden="true"></i>
							Applied At: <?php echo date('M d, Y',strtotime($j->applied_at)); ?>
							</p>
							<span>
							<i class="fa fa-map-marker" aria-hidden="true"></i>
							<?php echo $j->location; ?>
							</span>
						</div>
					</div>
					<p class="job_read_mo">
					<?php if($j->about!=''){ ?>
					<?php
                    	//echo Common::limitTextWords(html_entity_decode($j->about), 25, true, true);
                    ?>
					<!-- <a href="<?php //echo route('ViewProsDetails',array('ProsID'=>base64_encode($j->id))); ?>">read more...</a></p> -->
					<?php } ?>
				</div>
			</div>
			<?php
			}
		}
		else
		{
			?>
			No One Appllied For This job.
			<?php
		}
		exit();
	}

	public function SetApplyStatus(Request $request)
	{
		$Data 		= $request->all();		
		$JobID 		= $Data['JobID'];
		$ApplyID 	= $Data['ApplyID'];
		$Status 	= $Data['Status'];
		$Response 	= array();
		$SetApplyStatus = $this->MyScheduleModel->SetApplyStatus($JobID,$ApplyID,$Status);
		if($SetApplyStatus)
		{
			$UserID				= Session::get('UserID');
			$JobDetails 		= $this->ViewJobModel->GetJobDetail($JobID);
			$JobApplyDetails 	= $this->ViewJobModel->GetJobApplyDetails($ApplyID);
			$PositionDetails 	= $this->ViewJobModel->GetApplyPositionDetails($JobApplyDetails->position_id);
			if($Status=='2')
			{
				$ApplyStatus ="Your application has been accepted, for ".$PositionDetails->position;
			}
			else if($Status=='3')
			{
				$ApplyStatus ="Your application has been rejected, for ".$PositionDetails->position;
			}
			else if($Status=='4')
			{
				$ApplyStatus ="Your are in backup list, for ".$PositionDetails->position;
			}
			
			
			$Detail['user_id']  = $JobApplyDetails->profile_id;
			$Detail['link'] 	= route('ViewJobDetails',array('JobID'=>base64_encode($JobID)));
			$Detail['text'] 	= $ApplyStatus;
			$this->Common->AddNotification($Detail);

			$Response['Status'] = 1;
			$Response['Message']= Common::AlertErrorMsg('Success','Successfully!');
		}
		else
		{
			$Response['Status'] = 0;
			$Response['Message']= Common::AlertErrorMsg('Danger','Something Wrong!, Please Try Again.');
		}
		echo json_encode($Response);
		exit();
	}
	

	public function ConfirmJobModal(Request $request)
	{
		$Data 				= $request->all();		
		$ApplyID 			= $Data['ApplyID'];		

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
		
		
		if(Session::has('CouponCode') && Session::get('CouponCode')!='')
		{
			if(Session::get('CouponType')==1)
			{
				$DiscountOnPromo 	= Session::get('CouponAmount');
			}
			else
			{
				$DiscountOnPromo 	= ($JobAmount*$CategoryDetails->commission_rate/100)*Session::get('CouponAmount')/100;
			}			
		}

		$CommissionRate 		= $JobAmount*$CategoryDetails->commission_rate/100;
		$AdminCommissionRate 	= $CommissionRate-$DiscountOnPromo;

		$AmountWillBeEscrow 	= $JobAmount+$AdminCommissionRate;
		?>
		<h3>Confirm Job</h3>
		<div class="confirm_job_coupon" id="CouponCodeDiv">
			<?php if(Session::has('CouponCode') && Session::get('CouponCode')!=''){ ?>
			<div class="form-group">
				<input type="text" class="form-control inputfield2" 
					id="CouponCode" disabled placeholder="Enter Coupon Code" 
					onkeypress="Hide('CouponCodeErr');" value="<?php  echo Session::get('CouponCode'); ?>">
					<span id="CouponCodeErr"></span>
			</div>
			<a class="btn cus_btn2" href="javascript:void();" onclick="RemoveCouponCode();">Remove Coupon</a>			
			<?php } else{ ?>
			<div class="form-group">
				<input type="text" class="form-control inputfield2" 
					id="CouponCode" placeholder="Enter Coupon Code" onkeypress="Hide('CouponCodeErr');">
					<span id="CouponCodeErr"></span>
			</div>
			<a class="btn cus_btn2" href="javascript:void();" onclick="ApplyCouponCode();">Apply</a>
			<?php } ?>
		</div>
		<ul class="pro_bart job_coupon_details">
			<li>
				<p><b>Job Title: </b><?php echo $JobDetails->job_title;?></p>
			</li>
			<li>
				<p>Position</p>
				<strong><?php echo $JobPositionDetails->position;?></strong>
			</li>
			<li>
				<p>Pay Type</p>
				<strong>
					<?php 
					if($JobPositionDetails->pay_type=='1')
					{
						echo "Fixed";
					}	
					else
					{
						echo "Hourly";
					}
					?>
				</strong>
			</li>
			<li>
				<p>Pay Rate</p>
				<strong>$<?php echo number_format($JobAmount,2);?></strong>
			</li>
			<li>
				<p>Comission : <?php echo number_format($CategoryDetails->commission_rate,2).'%';?></p>
				<strong>$<?php echo number_format($CommissionRate,2);?></strong>
			</li>
			<?php if(Session::has('CouponCode') && Session::get('CouponCode')!=''){ ?>
			<li>
				<p>Discount On Comission(<?php echo Session::get('CouponCode'); ?>)</p>
				<strong>$<?php echo number_format($DiscountOnPromo,2);?></strong>
			</li>
			<?php }?>
			<li>
				<p>Employee Will Get</p>
				<strong>$<?php echo number_format($EmployeeWillGet,2); ?></strong>
			</li>
			<li>
				<p>Amount Will Be Escrow From Your Wallet Is</p>
				<strong>$<?php echo number_format($AmountWillBeEscrow,2); ?></strong>
			</li>
		</ul>
		<div id="ConfirmError">
		</div>
		<div class="confm-btn mb-0 text-center mt-3" style="width:100%;">
			<input type='hidden' value="<?php echo $ApplyID; ?>" id="ApplyID">
			<input type='hidden' value="<?php echo $PositionID; ?>" id="PositionID">
			<input type='hidden' value="<?php echo $JobCatID; ?>" id="JobCatID">
			<input type='hidden' value="<?php echo $JobID; ?>" id="JobID">
			<input type='hidden' value="<?php echo $AmountWillBeEscrow; ?>" id="AmountWillBeEscrow">
			<input type='hidden' value="<?php echo $AdminCommissionRate; ?>" id="AdminCommissionRate">
			<a class="btn cus_btn2" href="javascript:void(0);" onclick="ConfirmJob();">Confirm</a>
		</div>
		<?php
		exit();
	}

	public function ApplyCouponCode(Request $request)
	{
		$Data 		= $request->all();		
		$JobCatID 	= $Data['JobCatID'];
		$CouponCode = $Data['CouponCode'];
		$CheckCouponCode = $this->MyScheduleModel->GetCouponDetails($CouponCode);
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
				Session::put('CouponCode',$CouponCode);
				Session::put('CouponType',$CouponType);
				Session::put('CouponAmount',$Amount);
				Session::save();
				echo 2;
			}
			else
			{
				echo 1;
			}
		}
		else
		{
			echo 0;
		}
		exit();
	}

	public function RemoveCouponCode(Request $request)
	{
		$Data 		= $request->all();		
		$JobCatID 	= $Data['JobCatID'];
		Session::put('CouponCode','');
		Session::put('CouponType','');
		Session::put('Amount','');
		Session::save();
		if(Session::has('CouponCode') && Session::get('CouponCode')=='')
		{
			echo 1;
		}
		else
		{
			echo 0;
		}
		exit();
	}

	public function ConfirmJob(Request $request)
	{
		$Data 				= $request->all();		
		$ApplyID 			= $Data['ApplyID'];
		$JobID 				= $Data['JobID'];
		$JobCatID 			= $Data['JobCatID'];
		$PositionID 		= $Data['PositionID'];
		$AmountWillBeEscrow = $Data['AmountWillBeEscrow'];
		$AdminCommissionRate = $Data['AdminCommissionRate'];
		$UserID 			= Session::get('UserID');

		$HaveBalance = $this->MyScheduleModel->HaveBalance($UserID);
		if($HaveBalance==0)
		{
			echo Common::AlertErrorMsg('Danger','Your Wallet Balance Is Zero.' );
		}
		else if($HaveBalance<$AmountWillBeEscrow)
		{
			echo Common::AlertErrorMsg('Danger','You Dont Have Balance Sufficent Amount In Your Wallet' );;
		}
		else 
		{
			$EmployeeID =  $this->MyScheduleModel->GetEmployeeID($JobID);
			if($EmployeeID!=0)
			{
				$Balance = $HaveBalance-$AmountWillBeEscrow;
				$Response['employee_id'] 		= $EmployeeID;
				$Response['employer_id'] 		= $UserID;
				$Response['job_id'] 			= $JobID;
				$Response['job_cat_id'] 		= $JobCatID;
				$Response['position_id'] 		= $PositionID;
				$Response['applied_id'] 		= $ApplyID;

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
	       		$Details['response'] 			= json_encode($Response);
	       		
	       		$AdminCommission['applied_id'] 		= $ApplyID;
	       		$AdminCommission['job_id'] 			= $JobID;
	       		$AdminCommission['position_id'] 	= $PositionID;
	       		$AdminCommission['commission'] 		= $AdminCommissionRate;
	       		$AdminCommission['coupon_code'] 	= Session::get('CouponCode');
	       		$AdminCommission['coupon_amount'] 	= Session::get('Amount');
	       		$AdminCommission['employee'] 		= $EmployeeID;

	       		$ConfirmJob =  $this->MyScheduleModel->ConfirmJob($ApplyID,$Details,$AdminCommission,$JobID,$PositionID);
	       		if($ConfirmJob)
	       		{
	       			Session::put('CouponCode','');
					Session::put('CouponType','');
					Session::put('Amount','');
					Session::save();

	       			echo 1;
	       		}
	       		else
	       		{
	       			echo Common::AlertErrorMsg('Danger','Something Wrong Please Try Again.' );;
	       		}
			}
			else
			{
				echo Common::AlertErrorMsg('Danger','Something Wrong Please Try Again.' );;
			}
			
		}
		exit();
	}

	public function ViewCheckInCheckOut(Request $request, $ApplyID)
	{		
		$UserID 				= Session::get('UserID');
		$ApplyID 				= base64_decode($ApplyID);
		
		$Data['Title'] 			= 'View Check-In Check-Out ';
		$Data['Menu'] 			= 'MySchedule';
		$JobAppliedDetails  	= $this->MyScheduleModel->JobAppliedDetails($ApplyID);

		$JobID 					= $JobAppliedDetails->job_id;
		$Data['JobDetails'] 	= $this->MyScheduleModel->JobDetails($JobID);
		$Data['EmployeeDetails'] 			= $this->MyScheduleModel->EmployeeDetails($JobID);
		$Data['CheckInCheckOutDetails'] 	= $this->MyScheduleModel->CheckInCheckOutDetailsForEmployer($JobID,$UserID);
		$JobUserID 							= $Data['EmployeeDetails']->id;
		$Data['JobPositionDetails']			= $this->MyScheduleModel->GetAppliedPositoinDetails($JobID,$JobUserID);
		$Data['ProsRatingReview']			= $this->MyScheduleModel->GetProsRatingReview($JobID,$UserID,$JobUserID);
		
		return View('Front/Pages/User/ViewCheckInCheckOut')->with($Data);
	}

	public function AddCheckInCheckOut(Request $request, $ApplyID)
	{
		$UserID 				= Session::get('UserID');
		$ApplyID 				= base64_decode($ApplyID);

		$Data['Title'] 			= 'Add Check-In Check-Out ';
		$Data['Menu'] 			= 'MySchedule';
		$JobAppliedDetails  	= $this->MyScheduleModel->JobAppliedDetails($ApplyID);

		$JobID 					= $JobAppliedDetails->job_id;
		$Data['JobDetails'] 	= $this->MyScheduleModel->JobDetails($JobID);
		$Data['EmployeeDetails'] 			= $this->MyScheduleModel->EmployeeDetails($JobID);
		$Data['JobPositionDetails']			= $this->MyScheduleModel->GetAppliedPositoinDetails($JobID,$UserID);
		$Data['CheckInCheckOutDetails'] 	= $this->MyScheduleModel->CheckInCheckOutDetailsForEmployee($JobID,$UserID);
		$Data['JobRatingReview']			= $this->MyScheduleModel->GetJobRatingReview($JobID,$UserID);

		return View('Front/Pages/User/AddCheckInCheckOut')->with($Data);
	}

	public function AddCheckInTime(Request $request)
	{
		$Data 			= $request->all();	
		$UserID 		= Session::get('UserID');	
		$CurrentDateTime	= date('Y-m-d H:i A',time());
		$JobID 			= $Data['JobID'];

		$Details['job_id']		= $JobID;
		$Details['employee_id']	= $UserID;
		$Details['date']		= date('Y-m-d',strtotime($CurrentDateTime));
		$Details['start']		= date('H:i A' ,strtotime($CurrentDateTime));
		
		$Details['add_date'] 	= date('Y-m-d H:i:s',time());

		$AddCheckInTime 		= $this->MyScheduleModel->AddCheckInTime($Details);
		if($AddCheckInTime)
		{
			echo 1;
		}
		else
		{
			echo 0;
		}
		exit();
	}

	public function AddCheckOutTime(Request $request)
	{
		$Data 			= $request->all();	
		$UserID 		= Session::get('UserID');	
		$CurrentDateTime= $Data['CurrentDateTime'];
		$RowID 			= $Data['RowID'];
		
		$Details['end']			= date('H:i A' ,strtotime($CurrentDateTime));		
		$Details['add_date'] 	= date('Y-m-d H:i:s',time());
		$AddCheckOutTime 		= $this->MyScheduleModel->AddCheckOutTime($RowID,$Details);
		if($AddCheckOutTime)
		{
			echo 1;
		}
		else
		{
			echo 0;
		}
		exit();
	}

	public function CompleteJob(Request $request)
	{
		$Data 			= $request->all();	
		$UserID 		= Session::get('UserID');	
		$ApplyID 		= $Data['RowID'];
		
		$Details['position_status']	= '2';		
		$CompleteJob 		= $this->MyScheduleModel->CompleteJob($ApplyID,$Details);
		if($CompleteJob)
		{
			echo 1;
		}
		else
		{
			echo 0;
		}
		exit();
	}

	public function ApprovePaymentModal(Request $request)
	{
		$Data 			= $request->all();	
		$UserID 		= Session::get('UserID');	
		$ApplyID 		= $Data['ApplyID'];

		$JobAppliedDetails  = $this->MyScheduleModel->JobAppliedDetails($ApplyID);

		$JobID 				= $JobAppliedDetails->job_id;
		$JobPositionID 		= $JobAppliedDetails->position_id;

		$JobDetails 		= $this->MyScheduleModel->JobDetails($JobID);
		$JobCatID			= $JobDetails->job_cat;
		$CategoryDetails 	= $this->MyScheduleModel->GetCategoryDetails($JobCatID);
		$JobPositionDetails = $this->MyScheduleModel->GetJobPositionDetails($JobID,$JobPositionID);

		$CheckInCheckOutDetails = $this->MyScheduleModel->CheckInCheckOutDetailsForEmployer($JobID,$UserID);
		if($JobPositionDetails->pay_type=='1')
		{
			$JobAmount 			= $JobPositionDetails->pay_rate;
			$EmployerWillGet 	= $JobAmount;
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
				$EmployerWillGet 	= $JobAmount;
				$RevertBack 		= 0;
			}
			else
			{
				$EmployerWillGet 	= $TotalHoursRateCalculate;
				$RevertBack 		= ($JobAmount-($CommissionRate))-$TotalHoursRateCalculate;
			}			
		}

		?>
		<h3>Approve Payment</h3>		
		<ul class="pro_bart job_coupon_details">
			<li>
				<p>Pay Rate</p>
				<strong>$<?php echo number_format($JobAmount,2);?></strong>
			</li>
			<li>
				<p>Comission : <?php echo number_format($CategoryDetails->commission_rate,2).'%';?></p>
				<strong>$<?php echo number_format($CommissionRate,2);?></strong>
			</li>
			<li>
				<p>Employee Will Get</p>
				<strong>$<?php echo number_format($EmployerWillGet,2); ?></strong>
			</li>
			<li>
				<p>Revert Back to You</p>
				<strong>$<?php echo number_format($RevertBack,2); ?></strong>
			</li>
		</ul>
		<div id="ConfirmError">
		</div>
		<div class="confm-btn mb-0 text-center mt-3" style="width:100%;">
			<a class="btn cus_btn2" href="javascript:void(0);" id='ApprovePaymentBtn'
				onclick="ApprovePayment('<?php echo $ApplyID; ?>');">Approve</a>
			<a class="btn cus_btn2 btn-warning" href="javascript:void(0);" id='DisputeBtn' 
				onclick="DisputePayment('<?php echo $ApplyID; ?>');">Dispute</a>
		</div>
		<?php
		exit();
	}
	
	public function ApprovePayment(Request $request)
	{
		$Data 			= $request->all();	
		$UserID 		= Session::get('UserID');	
		$ApplyID 		= $Data['ApplyID'];

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
			$EmployerWillGet 	= $JobAmount;
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
				$EmployerWillGet 	= $JobAmount;
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
			echo 1;
		}
		else
		{
			echo 0;
		}
		exit();
	}

	public function DisputePayment(Request $request)
	{
		$Data 			= $request->all();	
		$UserID 		= Session::get('UserID');	
		$JobID 			= $Data['JobID'];
		$JobPositionID 	= $Data['JobPositionID'];
		$ApprovePayment = $this->MyScheduleModel->DisputePayment($UserID,$JobID,$JobPositionID);
		if($ApprovePayment )
		{
			echo 1;
		}
		else
		{
			echo 0;
		}
		exit();
	}

	public function DisputeHistory(Request $request)
	{
		$Data 			= $request->all();	
		$ApplyID 		= $Data['ApplyID'];

		$JobAppliedDetails  = $this->MyScheduleModel->JobAppliedDetails($ApplyID);

		$JobID 				= $JobAppliedDetails->job_id;
		$JobPositionID 		= $JobAppliedDetails->position_id;
		$DisputeHistory_arr = $this->MyScheduleModel->DisputeHistory($JobID,$JobPositionID);

		foreach ($DisputeHistory_arr as $row) 
		{
			if($row->employee_id!=0){
				$User = DB::table('profile')->select('first_name','last_name')->where('id',$row->employee_id)->first();
				$Name = $User->first_name.' '.$User->last_name;
			}elseif ($row->employer_id!=0) {
				$User = DB::table('profile')->select('first_name','last_name')->where('id',$row->employer_id)->first();
				$Name = $User->first_name.' '.$User->last_name;
			}elseif ($row->admin!=0) {
				$Name = 'Administrator';
			}else{
				$Name = 'N/A';
			}
			?>
				<li class="sn">
		          <div class="chating_cont">
		            <p><?=$row->message?></p>
		            <span><?=$Name?></span>
		          </div>
		        </li>
			<?php	
		}	
	}

	public function SendDisputeChat(Request $request)
	{
		$Data 			= $request->all();	
		$ChatType 		= $Data['ChatType'];
		
		$Insert['job_id'] 		= $Data['JobID'];
		$Insert['position_id'] 	= $Data['JobPositionID'];
		$Insert['message'] 		= $Data['message'];
		$Insert[$ChatType] 		= Session::get('UserID');
		$this->MyScheduleModel->InsertDisputeChat($Insert);
		return 1; 
	}

	//////////////////////////////////////////////////
	public function SaveProsReviewRating(Request $request)
	{
		$Data 			= $request->all();	
		
		$Insert['profile_id'] 	= Session::get('UserID');
		$Insert['pros_id'] 		= $Data['ProsID'];
		$Insert['job_id'] 		= $Data['JobID'];
		$Insert['review'] 		= $Data['Review'];
		$Insert['rating'] 		= $Data['Rating'];

		echo $this->MyScheduleModel->SaveProsReviewRating($Insert);
		
		exit();
	}
	public function SaveJobReviewRating(Request $request)
	{
		$Data 			= $request->all();	
		
		$Insert['profile_id'] 	= Session::get('UserID');
		$Insert['job_id'] 		= $Data['JobID'];
		$Insert['review'] 		= $Data['Review'];
		$Insert['rating'] 		= $Data['Rating'];

		echo $this->MyScheduleModel->SaveJobReviewRating($Insert);
		
		exit();
	}
}