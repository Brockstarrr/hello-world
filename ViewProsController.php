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
use App\Http\Models\Front\ViewProsModel;
use App\Http\Models\Front\UserModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Pagination;
use App\Helpers\Common;

class ViewProsController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->Pagination = new Pagination();
		$this->ViewProsModel = new ViewProsModel();
		$this->UserModel = new UserModel();
		$this->Common = new Common();
	}

	public function ViewPros()
	{
		
		$Data['Title'] 			= 'View Pros';
		$Data['Menu'] 			= 'ViewPros';
		$Data['Category'] 		= $this->ViewProsModel->GetJobCategory();
		$Data['SubCategory'] 	= $this->ViewProsModel->GetJobSubCategory();
		$Data['IAmAPro']		= 0;
		if(Session::has('UserLogin'))
		{
			$UserID 			= Session::get('UserID');
			$IAmAPro			= $this->ViewProsModel->IAmAPro($UserID);
			$Data['IAmAPro']	= $IAmAPro->type;
		}

		return View('Front/Pages/ViewPros')->with($Data);
	}

	public function GetAllPros(Request $request)
	{
		$Data 			= $request->all();
		
		$SearchKeyword 	= $Data['SearchKeyword'];
		$Category 		= $Data['Category'];
		$SubCat 		= $Data['SubCat'];
		$SortBy 		= $Data['SortBy'];
		$page 			= $Data['page'];
		$numofrecords   = $Data['numofrecords'];

		$PayRangeMin   	= $Data['PayRangeMin'];
		$PayRangeMax  	= $Data['PayRangeMax'];
		$DistanceFrom   = $Data['DistanceFrom'];
		$DistanceTo   	= $Data['DistanceTo'];
		$latitude   		= $Data['latitude'];
		$longitude   		= $Data['longitude'];
		$Background   	= $Data['Background'];
		$Driving   	= $Data['Driving'];
		$cur_page 			= $page;

		$Limitpage 		= $page-1;
		$start 			= $Limitpage * $numofrecords;
		$Search['SortBy'] 	= $SortBy;
		$Search['Category'] = $Category;
		$Search['SubCat'] = $SubCat;
		$Search['SearchKeyword'] = $SearchKeyword;

		$Search['PayRangeMin'] = $PayRangeMin;
		$Search['PayRangeMax'] = $PayRangeMax;
		$Search['Background'] = $Background;
		$Search['Driving'] = $Driving;

		$AllPros		= $this->ViewProsModel->AllPros($start,$numofrecords,$Search);
		$Pros 			= $AllPros['Res'];
		$Count 			= $AllPros['Count'];
		
		if(!empty($Pros))
		{
			foreach($Pros as $j)
			{
				if($latitude){
					$Distance = $this->Common->DistanceCalculate($latitude,$longitude,$j->latitude,$j->longitude,"K");
					$Distance = explode('.',$Distance);
					$Distance = reset($Distance);
					if($DistanceFrom <= $Distance AND $DistanceTo >= $Distance){
						//$Distance = 'Done';
					}else{
						continue;
					}
				}

				$ProsID = $j->id;
				$JobCategory = $this->ViewProsModel->GetUserJobCategory($ProsID);

				$ProsFavStatus = 0;
				$ProsInviteStatus = 0;
				if(Session::has('UserLogin'))
				{
					$UserID = Session::get('UserID');
					$ProsFavStatus = $this->ViewProsModel->ProsFavStatus($UserID,$ProsID);
					$ProsInviteStatus = $this->ViewProsModel->ProsInviteStatus($UserID,$ProsID);
				}

				$UserTotalReviews = $this->ViewProsModel->UserTotalReviews($j->id);
				$UserReviews = $this->ViewProsModel->UserReviews($j->id);
			?>		
			<div class="job_cont pros">
				<div class="cont_wrap">
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
							<div class="vi_pro_cont">
								<p>	<a href="<?php echo route('ViewProsDetails',array('ProsID'=>base64_encode($j->id))); ?>"> 
										<?php echo $j->first_name; ?> 
									</a>
									<div id="Only3_<?php echo $j->id; ?>">
										<?php 
										$i=1;
										foreach($JobCategory as $jc)
										{
											if($i<=3)
											{
												?>
												<ul class="profile-list">
													<li><span class='pos'><?php echo $jc->position;?></span>
													<span class='rate'><?php echo "$".$jc->pay_rate.'/hr';?></span></li>
												</ul>
												<?php
											}
											$i++;
										}

										if(count($JobCategory)>3)
										{
											?>
											<a href="javascript:void(0);" onclick='ShowAllCat(<?php echo $j->id; ?>);'>Show More</a>
											<?php
										}
										?>
									</div>
									<div id="All_<?php echo $j->id; ?>" style="display:none;">
										<?php 
										foreach($JobCategory as $jc)
										{
											?>
											<ul class="profile-list">
												<li><span class='pos'><?php echo $jc->position;?></span>
												<span class='rate'><?php echo "$".$jc->pay_rate.'/hr';?></span></li>
											</ul>
											<?php											
										}
										?>
										<a href="javascript:void(0);" onclick='HideAllCat(<?php echo $j->id; ?>);'>Shwo Less</a>
									</div>
								</p>
								<div class="job_star pros-star">
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
						<div class="rhs">
							<?php if($ProsInviteStatus==1){?>
								<a class="btn cus_btn2 blue big share" href="javascript:void(0);">Invited</a>
							<?php } else {?>
								<a class="btn cus_btn2 blue big share" href="javascript:void(0);" onclick="CheckLoginOrInvitePros(<?php echo $j->id; ?>);">Invite</a>
							<?php } ?>
							<?php if($ProsFavStatus==1){?>
							<span class="profile-share">
								<span id='FavHeart_<?php echo $j->id; ?>'>
								<a class="btn cus_btn2 icon grey_bor big share " href="javascript:void(0);"
									onclick="CheckLoginOrMakeFavPros(0,<?php echo $j->id; ?>);" >
									<i class="fa fa-heart gr" aria-hidden="true"></i>
								</a>
								</span>
								<a class="btn cus_btn2 icon big share share-menu share-top" onclick="SocialMedia(<?=$j->id?>);" href="javascript:void(0)">
									<i class="fa fa-share-alt" aria-hidden="true"></i>
								</a>
							</span>
							<?php } else {?>
							<span class="profile-share">
								<span id='FavHeart_<?php echo $j->id; ?>'>
								<a class="btn cus_btn2 icon grey_bor big share" href="javascript:void(0);"
									onclick="CheckLoginOrMakeFavPros(1,<?php echo $j->id; ?>);" 
									>
									<i class="fa fa-heart" aria-hidden="true"></i>
								</a>
								</span>
								<a class="btn cus_btn2 icon big share share-menu share-top" onclick="SocialMedia(<?=$j->id?>);" href="javascript:void(0)">
									<i class="fa fa-share-alt" aria-hidden="true"></i>
								</a>
							</span>
							<?php } ?>
							
						<ul class="social_product list-inline" id="social_product_<?=$j->id?>">
			              <li><a href="javascript:void(0);" onclick="ShareFacebook('<?=route('ViewProsDetails',array('ProsID'=>base64_encode($j->id)))?>')"><i class="fa fa-facebook-f"></i></a></li>
			              <li><a href="javascript:void(0);" onclick="ShareTweet('<?=route('ViewProsDetails',array('ProsID'=>base64_encode($j->id)))?>')"><i class="fa fa-twitter"></i></a></li>
			              <li><a href="javascript:void(0);" onclick="ShareLinkedin('<?=route('ViewProsDetails',array('ProsID'=>base64_encode($j->id)))?>')"><i class="fa fa-linkedin-square"></i></a></li>
			            </ul>
	            
						</div>
					</div>
					<div class="bt_cont">
						<div class="lhs">
							<span>						
							<?php  
							if($j->location!='')
							{
								?>
								<svg 
								xmlns="http://www.w3.org/2000/svg"
								xmlns:xlink="http://www.w3.org/1999/xlink"
								width="10px" height="14px">
								<path fill-rule="evenodd"  fill="rgb(34, 34, 34)"
								d="M4.736,6.643 C4.144,6.643 3.639,6.434 3.221,6.015 C2.802,5.597 2.593,5.092 2.593,4.500 C2.593,3.909 2.802,3.403 3.221,2.985 C3.639,2.566 4.144,2.357 4.736,2.357 C5.327,2.357 5.832,2.566 6.251,2.985 C6.669,3.403 6.879,3.909 6.879,4.500 C6.879,5.092 6.669,5.597 6.251,6.015 C5.832,6.434 5.327,6.643 4.736,6.643 ZM7.766,1.470 C6.929,0.633 5.919,0.214 4.736,0.214 C3.553,0.214 2.543,0.633 1.706,1.470 C0.868,2.307 0.450,3.317 0.450,4.500 C0.450,5.108 0.542,5.608 0.726,5.998 L3.781,12.477 C3.865,12.661 3.995,12.806 4.171,12.912 C4.346,13.018 4.535,13.071 4.736,13.071 C4.937,13.071 5.125,13.018 5.301,12.912 C5.476,12.806 5.609,12.661 5.698,12.477 L8.745,5.998 C8.929,5.608 9.021,5.108 9.021,4.500 C9.021,3.317 8.603,2.307 7.766,1.470 Z"/>
								</svg>
								<?php
							 	echo $j->location;
							}
							?>
							</span>
						</div>
						<!-- <div class="distance_cont">12 Miles</div> -->
					</div>
					<p class="job_read_mo">

						<div class="full-dec" id="ReadMore_<?php echo $ProsID; ?>">
						<p class="pros_descriptoin">
							<?php
							if($j->about!='')
							{
								if(strlen($j->about)>=90)
								{
									$pos=strpos($j->about, ' ', 90);
									echo $Description = substr($j->about,0,$pos ); 
								}
								else
								{
									echo $j->about; 												
								}
								?>
								<a href="javascript:void(0);" onclick="ReadMore(<?php echo $ProsID; ?>);">Read more... </a>
								<?php 
							}
							?>
						</p>
						</div>
						<div class="full-dec" id="HideMore_<?php echo $ProsID; ?>" style="display:none;">
							<?php echo $j->about; ?>
							<a href="javascript:void(0);"  onclick="HideMore(<?php echo $ProsID; ?>);">Hide more... </a>
						</div>
					</p>
					<div class="bk_check">
						<ul>
							<li>
								<?php  if($j->background==1){ ?>
								<!-- <img title="Background Status Verified" src="<?php //echo asset('public/Front/Design/img/bk_check1.png'); ?>" alt="" /> --> 						
								<i class="fa fa-graduation-cap" style="#e5a449" aria-hidden="true"></i>
								<span></span>	
								<?php } ?>													
							</li>
							<li>
								<?php  if($j->driving_status==1){ ?>
								<!-- <img title="Driving Status Verified" src="<?php //echo asset('public/Front/Design/img/bk_check3.png'); ?>" alt="" /> --> 
								<i class='fa fa-star'></i>						
								<span></span>	
								<?php } ?>													
							</li>
							<li>
								<?php  if($j->onboarding_quiz_status==1){ ?>
								<img title="OnBoarding Quiz Completed" src="<?php echo asset('public/Front/Design/img/bk_check2.png'); ?>" alt="" />
								<span></span>
								<?php } ?>
							</li>
							
						</ul>
					</div>
				</div>
			</div>       
			<?php
			}
			if($numofrecords!='')
			{
				echo $this->Pagination->Links($numofrecords, $Count, $page); 
			}
		}
		else
		{
			?>
			<div class="no-found">
				<img src="<?php echo asset('public/Front/Design/img/page-icon.png'); ?>">
				<h3>No Data Found</h3>
			</div>
			<?php
		}
		?>
		<?php
		exit();
	}

	public function ViewProsDetails(Request $request, $ProsID)
	{
		$ProsID = base64_decode($ProsID);

		$IsProsExist 	= $this->ViewProsModel->IsProsExist($ProsID);
		if($IsProsExist)
		{
			$Data['Title'] 				= 'View Pros';
			$Data['Menu'] 				= 'ViewPros';
			$UserDetails 				= $this->UserModel->UserDetails($ProsID);
			$Data['ProsID'] 			= $ProsID;
			$Data['UserDetails'] 		= $UserDetails;
			$Data['Experience'] 		= $this->UserModel->GetUserExperience($ProsID);
			$Data['Preference'] 		= $this->UserModel->GetUserPreference($ProsID);
			$Data['Education'] 			= $this->UserModel->GetUserEducation($ProsID);
			$Data['Language'] 			= $this->UserModel->GetUserLanguage($ProsID);
			$Data['Appearance'] 		= $this->UserModel->GetUserAppearance($ProsID);
			$Data['Accreditations'] 	= $this->UserModel->GetUserAccreditations($ProsID);
			$Data['Certifications'] 	= $this->UserModel->GetUserCertifications($ProsID);
			$Data['SimilarPros'] 		= $this->ViewProsModel->GetSimilarPros($ProsID);
			$Data['UserTotalReviews']  	= $this->ViewProsModel->UserTotalReviews($ProsID);
			$Data['UserReviews'] 		= $this->ViewProsModel->UserReviews($ProsID);
			$Data['ProsFavStatus'] 		= 0;
			$Data['ProsInviteStatus'] 	= 0;
			$Data['AppliedAnyOfYourJob'] 	= 0;

			$Availability 	= array();
			if($UserDetails->availability!='')
			{
				$Availability = json_decode($UserDetails->availability);
			}
			
			$TimeSlots 		= $this->UserModel->GetTimeSlots();
			$Data['Availability']	= $Availability;
			$Data['TimeSlots']		= $TimeSlots;
			if(Session::has('UserLogin'))
			{
				$UserID = Session::get('UserID');
				$Data['ProsFavStatus'] 	= $this->ViewProsModel->ProsFavStatus($UserID,$ProsID);
				$Data['ProsInviteStatus'] = $this->ViewProsModel->ProsInviteStatus($UserID,$ProsID);
				$Data['AppliedAnyOfYourJob'] = $this->ViewProsModel->AppliedAnyOfYourJob($UserID,$ProsID);
			}
			
			return View('Front/Pages/ViewProsDetails')->with($Data);
		}
		else
		{
			return Redirect::to('404');
		}
		
	}

	public function CheckLoginOrMakeFavPros(Request $request)
	{
		$Data 		= $request->all();
		$UserID		= Session::get('UserID');
		$Status 	= $Data['Status'];
		$ProsID 	= $Data['ProsID'];
		$ReferURL 	= $Data['ReferURL'];
		if(Session::has('UserLogin') && Session::get('UserLogin')=='1')
		{
			$SetProsFavStatus = $this->ViewProsModel->SetProsFavStatus($UserID,$ProsID,$Status);
			if($SetProsFavStatus)
			{
				if($Status=='0')
				{
					
					echo $Response = '<a class="btn cus_btn2 icon grey_bor big share" href="javascript:void(0);"
										onclick="CheckLoginOrMakeFavPros(1,'.$ProsID.');" >
										<i class="fa fa-heart" aria-hidden="true"></i>
									</a>';
				}
				else
				{
					echo $Response = '<a class="btn cus_btn2 icon grey_bor big share" href="javascript:void(0);"
										onclick="CheckLoginOrMakeFavPros(0,'.$ProsID.');" >
										<i class="fa fa-heart gr" aria-hidden="true"></i>
									</a>';					
				}
			}
			else
			{
				if($Status=='0')
				{
					echo $Response = '<a class="btn cus_btn2 icon grey_bor big share" href="javascript:void(0);"
										onclick="CheckLoginOrMakeFavPros(0,'.$ProsID.');" >
										<i class="fa fa-heart gr" aria-hidden="true"></i>
									</a>';	
				}
				else
				{
					echo $Response = '<a class="btn cus_btn2 icon grey_bor big share" href="javascript:void(0);"
										onclick="CheckLoginOrMakeFavPros(1,'.$ProsID.');" >
										<i class="fa fa-heart" aria-hidden="true"></i>
									</a>';					
				}
			}
		}
		else
		{		
			Session::put('ReferURL', $ReferURL);
			Session::save();
			echo 0;
		}
		exit();
	}


	public function CheckLoginOrInvitePros(Request $request)
	{
		$Data 		= $request->all();
		$UserID		= Session::get('UserID');
		$ProsID 	= $Data['ProsID'];
		$ReferURL 	= $Data['ReferURL'];
		if(Session::has('UserLogin') && Session::get('UserLogin')=='1')
		{
			$JobList 			= $this->ViewProsModel->GetJobList($UserID,$ProsID);	
			$PorsDetails 		= $this->ViewProsModel->PorsDetails($ProsID);	
			if(!empty($JobList))
			{		
				?>
				<form action="<?php echo route('InvitePros'); ?>" style="width:100%;" id="InviteProsForm" name="InviteProsForm" method="POST" enctype='multipart/form-data'>
					<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
					<input type="hidden" name="ProsID" id="ProsID" value="<?php echo $ProsID; ?>">
					<div class="row">
						<div class="col-md-12 mb-2"><h3>Send Invitation</h3></div>
						
						<div class="col-md-12">
							<table class="table">
								<htead>
									<tr>
										<th>Job Title</th>
										<th>Invite</th>
									</tr>
								</thead>
								<tbody>
									<?php
									
									foreach($JobList as $key=>$jl) 
									{ 
										?>
										<tr>
											<td><?php echo $jl['job_title']; ?></td>								
											<td>
												<div class="custom-control custom-checkbox">
													<input onclick='Hide("InviteIDErr");' type="checkbox" value="<?php echo $jl['job_id']; ?>" 
													class="custom-control-input" id="customRadio_<?php echo $key; ?>" name="InviteID[]"/>
													<label class="custom-control-label" for="customRadio_<?php echo $key; ?>">Invite</label>
												</div>
											</td>
										</tr>
										<?php
									}
									?>
								</tbody>
							</table>
							<span id='InviteIDErr'></span>
						</div>
						<div class="col-md-12 text-center">
							<a href="javascript:void(0);" onclick="SendInvite();" class="btn cus_btn big">Send Invite</a>
						</div>
						
					</div>
				</form>
				<?php
			}
			else
			{
				?>
				<form style="width:100%;">
					<div class="row">
						<div class="col-md-12 mb-2"><h3>Sorry!</h3></div>
						
						<div class="col-md-12 text-center">
							<p>You Don't Have Job For <?php echo $PorsDetails->first_name; ?> </p>
						</div>
						<div class="col-md-12  text-center">
							<a href="javascript:void(0);" class="btn cus_btn big" data-dismiss="modal">Close</a>
						</div>
						
					</div>
				</form>
				<?php
			}
		}
		else
		{		
			Session::put('ReferURL', $ReferURL);
			Session::save();
			echo 0;
		}
		exit();
	}

	public function InvitePros(Request $request)
	{
		$Data 		= $request->all();
		$UserID		= Session::get('UserID');
		$ProsID 	= $Data['ProsID'];
		$InviteIDs 	= $Data['InviteID'];
		$InvitePros = $this->ViewProsModel->InvitePros($UserID,$ProsID,$InviteIDs);
		if($InvitePros)
		{
			Session::flash('message', 'Invitation Sent Successfully.'); 
			Session::flash('alert-class', 'alert-success'); 
			return Redirect::route('Invites');
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
			return Redirect::route('ViewPros');
      	}
	}

	//////////////////////////
	public function GetUserAvailability(Request $request)
	{
		$Data 			= $request->all();
		$UserID 		= $Data['UserID'];
		$UserDetails 	= $this->UserModel->UserDetails($UserID);
		$Availability 	= array();
		if($UserDetails->availability!='')
		{
			$Availability = json_decode($UserDetails->availability);
		}
			
		$TimeSlots 		= $this->UserModel->GetTimeSlots();
		
		?>
			<div class="col-md-12 mb-2"><h3>User Availabilty</h3></div>
			<?php if(empty($Availability)){?>
			Still Not Added Availablity.
			<?php } else { ?>
			<form style="width:100%;">
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<div class="row">
					<div class="col-md-12">
						<table class="table">
							<htead>
								<tr>
									<th>Day</th>
									<th>From</th>
									<th>To</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$TimeStamp = strtotime('next Monday');
								for($i = 0; $i < 7; $i++) 
								{ 
									$Day= strftime('%A', $TimeStamp);
    								$TimeStamp = strtotime('+1 day', $TimeStamp);
									
									$Checked=""; 
									$From=""; 
									$To="";
									$FromVal=""; 
									$ToVal=""; 
									if(!empty($Availability))
									{
										if($Availability[$i]->NotAvailable==1)
										{
											$Checked="checked"; 
											$From="disabled"; 
											$To="disabled"; 
										}
										else
										{
											$FromVal=$Availability[$i]->FromSlotID;
											$ToVal=$Availability[$i]->ToSlotID;
										}
									} 
									?>

									<tr>
										<td><?php echo $Day; ?></td>
										<?php if($Checked==''){ ?>										
										<td>
											<?php
											foreach($TimeSlots as $ts)
											{ 													
												if($FromVal==$ts->id){ echo $ts->time_slot;}													
											} 
											?>
										</td>										
										<td>
											<?php
											foreach($TimeSlots as $ts)
											{ 													
												if($ToVal==$ts->id){ echo $ts->time_slot;}													
											} 
											?>
										</td>
										<?php } else { ?>
										<td colspan="2">
											Not Available.
										</td>
										<?php } ?>
									</tr>
									<?php 
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</form>
			<?php } ?>

		<?php
	}

	public function ProfileReviews(Request $request){
		$Data = $request->all();
		$UserID 				= $Data['ProfileID'];
		$sort 					= $Data['sort'];
		$OnClick 				= $Data['OnClick'];
		$page 					= $Data['page'];

		$numofrecords   = 10;
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;
		$Result				= $this->UserModel->UserReviews($start,$numofrecords,$UserID,$sort);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];
		
		$Review = '';
		foreach($Result_arr as $row){

			$profile_id = $row->profile_id;
			
			$preference = DB::table('job_preference as preference');
      $preference->where('preference.profile_id',$profile_id);
      $preference->join('job_position','job_position.id','=','preference.job_sub_cat');
      $preference->select('job_position.position');
      $PreferenceData = $preference->get();

      $PreferenceList = '';
      foreach ($PreferenceData as $Pre) {
      	$PreferenceList.=$Pre->position.', ';
      }
      //echo '<pre>';print_r($PreferenceData);

			$ProfileImage = asset('public/Front/Design/img/pro_pic.png');
			if($row->image!=''){
				$ProfileImage = asset('public/Front/Users/Profile/'.$row->image);
			}

			$Rating = '';
			for($i=5; $i>=1;$i--){
				$checked = '';
				if($i==$row->rating){
					$checked = 'checked=""';
				}
				$Rating.= '<input disabled id="rating-'.$i.'" name="rating" type="radio" value="'.$i.'" '.$checked.'>
										<label for="rating-'.$i.'" data-value="'.$i.'">
											<span class="rating-star">
												<i class="fa fa-star grey"></i>
												<i class="fa fa-star gold"></i>
											</span>
										</label>';
			}

			$Review.='<li>
								<div class="lhs">
									<div class="img_wrap"><img src="'.$ProfileImage.'" alt=""></div>
									<div class="cont_wrap">
										<div class="review_dt">
											<a href="javascript:void(0)">'.$row->first_name.' '.$row->last_name.'</a>
											<span>'.date('Y-m-d',strtotime($row->add_date)).'</span>
										</div>
										<span>'.rtrim($PreferenceList, ', ').'</span>
										<form class="rating-form" action="#" method="post" name="rating-movie">
											<fieldset class="form-group">
											<legend class="form-legend">Rating:</legend>
												<div class="form-item">'.$Rating.'</div>
											</fieldset>
										</form>
										<p class="star-no">'.$row->rating.'</p>
										<p>'.$row->review.'</p>
									</div>
								</div>
							</li>';
		}
		$Pagination=Common::DynamicPagination($numofrecords, $Count, $page,$OnClick);
		$Response['Review'] 		= $Review;
		$Response['Pagination'] = $Pagination;
		return json_encode($Response);
	}

	public function MyProfileReviews(Request $request)
	{
		$Data = $request->all();
		$UserID 				= $Data['ProfileID'];
		$sort 					= $Data['sort'];
		$OnClick 				= $Data['OnClick'];
		$page 					= $Data['page'];

		$numofrecords   = 10;
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;
		$Result				= $this->UserModel->MyUserReviews($start,$numofrecords,$UserID,$sort);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];
		
		$Review = '';
		foreach($Result_arr as $row){

			$profile_id = $row->profile_id;
			
      $ProfileImage = asset('public/Front/Design/img/pro_pic.png');
			if($row->image!=''){
				$ProfileImage = asset('public/Front/Users/Profile/'.$row->image);
			}

			$Rating = '';
			for($i=5; $i>=1;$i--){
				$checked = '';
				if($i==$row->rating){
					$checked = 'checked=""';
				}
				$Rating.= '<input disabled id="rating-'.$i.'" name="rating" type="radio" value="'.$i.'" '.$checked.'>
										<label for="rating-'.$i.'" data-value="'.$i.'">
											<span class="rating-star">
												<i class="fa fa-star grey"></i>
												<i class="fa fa-star gold"></i>
											</span>
										</label>';
			}

			$Review.='<li>
								<div class="lhs">
									<div class="img_wrap"><img src="'.$ProfileImage.'" alt=""></div>
									<div class="cont_wrap">
										<div class="review_dt">
											<a href="javascript:void(0)">'.$row->first_name.' '.$row->last_name.'</a>
											<span>'.date('Y-m-d',strtotime($row->add_date)).'</span>
										</div>
										<form class="rating-form" action="#" method="post" name="rating-movie">
											<fieldset class="form-group">
											<legend class="form-legend">Rating:</legend>
												<div class="form-item">'.$Rating.'</div>
											</fieldset>
										</form>
										<p class="star-no">'.$row->rating.'</p>
										<p>'.$row->review.'</p>
									</div>
								</div>
							</li>';
		}
		$Pagination=Common::DynamicPagination($numofrecords, $Count, $page,$OnClick);
		$Response['Review'] 		= $Review;
		$Response['Pagination'] = $Pagination;
		return json_encode($Response);
	}
	public function SaveReviewRating(Request $request){
		$Data = $request->all();
		if(Session::has('UserID')){
			$Save['profile_id'] = Session::get('UserID');
			$count = DB::table('pros_rating_review')->where('pros_id',$Data['ProfileID'])->where('profile_id',$Save['profile_id'])->count();
			if($count==0){
				$Save['pros_id'] 		= $Data['ProfileID'];
				$Save['rating'] 		= $Data['rating'];
				$Save['review'] 		= $Data['comment'];
				$Save['add_date'] 	= date('Y-m-d H:i:s');
				DB::table('pros_rating_review')->insert($Save);
				$msg = Common::AlertErrorMsg('Success','Thank for review.');
			}else{
				$msg = Common::AlertErrorMsg('Danger','You have already review this profile.');
			}
			return $msg;
		}
	}
}
