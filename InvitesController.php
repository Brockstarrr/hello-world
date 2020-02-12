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
use App\Http\Models\Front\ViewJobModel;
use App\Http\Models\Front\ViewProsModel;
use App\Http\Models\Front\InvitesModel;
use App\Http\Models\Front\UserModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class InvitesController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->ViewProsModel = new ViewProsModel();
		$this->ViewJobModel = new ViewJobModel();
		$this->InvitesModel = new InvitesModel();
		$this->UserModel 	= new UserModel();
		$this->Common 		= new Common();
	}

	public function Invites(Request $request)
	{
		$UserID 				= Session::get('UserID');
		$Data['Title'] 			= 'My Invites';
		$Data['Menu'] 			= 'Invites';
		$Data['InvitesISent'] 	= $this->InvitesModel->InvitesISent($UserID);
		$Data['InvitesIGot'] 	= $this->InvitesModel->InvitesIGot($UserID);
		
		return View('Front/Pages/User/MyInvites')->with($Data);
	}

	public function GetInvitesISent(Request $request)
	{
		$UserID 		= Session::get('UserID');
		$InvitesISent 	= $this->InvitesModel->InvitesISent($UserID);
		
		if(!empty($InvitesISent))
		{
			foreach ($InvitesISent as $j) 
			{
				$ProsID 		= $j->id;
				$ProsFavStatus 	= $this->ViewProsModel->ProsFavStatus($UserID,$ProsID);
				$ProsInviteStatus = $this->ViewProsModel->ProsInviteStatus($UserID,$ProsID);
				$JobCategory 	= $this->ViewProsModel->GetUserJobCategory($ProsID);
				$UserTotalReviews = $this->ViewProsModel->UserTotalReviews($j->id);
				?>
				<div class="job_cont pros">
					<div class="cont_wrap">
						<div class="img_wrap title_line">
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
									<p>	<a href="<?php echo route('ViewProsDetails',array('ProsID'=>base64_encode($j->id))); ?>"> 
											<?php echo $j->first_name; ?> 
										</a>
										<span>
											<?php 
											foreach($JobCategory as $jc)
											{
												echo $jc->position.', ';
											}
											?>
										</span>
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
												<span>  <?php echo $UserTotalReviews; ?> Reviews</span>
											</fieldset>
										</div>
									</div>
								</div>
							</div>
							<div class="rhs">
									<!-- <b>Invite For:</b>  -->
									<a href='<?php echo route('ViewJobDetails',array('JobID'=>base64_encode($j->JobID))) ?>'>
										<?php echo $j->job_title; ?>
									</a>
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
						</div>
						<p class="job_read_mo">
							<?php if($j->about!=''){ ?>
							<?php
		                    	echo Common::limitTextWords(html_entity_decode($j->about), 25, true, true);
		                    ?>
							<a href="<?php echo route('ViewProsDetails',array('ProsID'=>base64_encode($j->id))); ?>">Read more..</a></p>
							<?php } ?>
						</p>
						<div class="bk_check">
							<ul>
								<li><img src="<?php echo asset('public/Front/Design/img/bk_check1.png'); ?>" alt="" /> Background :
									<?php  if($j->verified_status==1){ ?>
									<span>Verified</span>
									<?php } else { ?>
									<span>Not Verified</span>
									<?php } ?>							
								</li>
								<li><img src="<?php echo asset('public/Front/Design/img/bk_check2.png'); ?>" alt="" /> Tests completed:<span>Yes</span></li>
							</ul>
						</div>
					</div>
				</div>
				<?php
			}
		}
		else
		{

		}
	}
	public function GetInvitesIGot(Request $request)
	{
		$UserID 		= Session::get('UserID');
		$GetInvitesIGot = $this->InvitesModel->InvitesIGot($UserID);
		
		if(!empty($GetInvitesIGot))
		{
			foreach ($GetInvitesIGot as $j) 
			{
				$JobID = $j->id;
				$Openings = $this->ViewJobModel->JobOpenings($JobID);
				
				
				$JobFavStatus = $this->ViewJobModel->JobFavStatus($UserID,$JobID);
				$JobApplyStatus = $this->ViewJobModel->JobApplyStatus($UserID,$JobID);


				$JobTotalReviews = $this->ViewJobModel->JobTotalReviews($JobID);
				$JobReviews = $this->ViewJobModel->JobReviews($JobID);
				?>
				<div class="job_cont">
					<div class="cont_wrap">
						<div class="img_wrap">
							<div class="lhs">
								<div class="pro_pic">
									<span>
										<?php if($j->image!=''){?>
										<img src="<?php echo asset('public/Front/Users/Jobs').'/'.$j->image; ?>" alt=""/>
										<?php } else{ ?>
										<img src="<?php echo asset('public/Front/Design/img/pro_pic.png'); ?>" alt=""/>
										<?php }  ?>
									</span>
								</div>
								<div>
									<p>
										<a href="<?php echo route('ViewJobDetails',array('JobID'=>base64_encode($j->id))); ?>"><?php echo $j->job_title; ?> </a>
										<span>By <?php echo $j->username; ?> </span>
									</p>
									<div class="job_star">
									<?php 	
										$Rating = ceil($JobTotalReviews/5);
										$RatingHtml = '';
										for($i=5; $i>=1;$i--)
										{
											$checked = '';
											if($i==$Rating){
												$checked = 'checked=""';
											}
											$RatingHtml.= '<input disabled id="rating-'.$i.'" name="rating" type="radio" value="'.$i.'" '.$checked.'>
																		<label for="rating-'.$i.'" data-value="'.$i.'">
																			<span class="rating-star">
																				<i class="fa fa-star grey"></i>
																				<i class="fa fa-star gold"></i>
																			</span>
																		</label>';
										}	

										?>

										<div class="rating-form"  name="rating-movie">
										<fieldset class="form-group">
											<legend class="form-legend">Rating:</legend>
												<div class="form-item">
													<?php echo $RatingHtml.' &nbsp'.number_format($Rating,1); ?>
												</div>
											<span class="rev-job"><?php echo $JobReviews; ?> Reviews</span>
										</fieldset>
									</div>	
									</div>
								</div>
							</div>
							<div class="rhs">
								<?php if($JobFavStatus==1){?>
								<span id='FavHeart_<?php echo $j->id; ?>'>
									<a class="btn cus_btn2 icon grey_bor big" href="javascript:void(0);"
										onclick="CheckLoginOrMakeFav(0,<?php echo $j->id; ?>);" >
										<i class="fa fa-heart gr" aria-hidden="true"></i>
									</a>
								</span>
								<?php } else {?>
								<span id='FavHeart_<?php echo $j->id; ?>'>
									<a class="btn cus_btn2 icon grey_bor big" href="javascript:void(0);"
										onclick="CheckLoginOrMakeFav(1,<?php echo $j->id; ?>);" 
										>
										<i class="fa fa-heart" aria-hidden="true"></i>
									</a>
								</span>
								<?php } ?>
								<a class="btn cus_btn2 icon big" href="javascript:void(0);">
									<i class="fa fa-share-alt" aria-hidden="true"></i>
								</a>
								<?php if($JobApplyStatus==1){?>
								<a class="btn cus_btn2 blue big" href="javascript:void(0);">
									Applied
								</a>
								<?php } else {?>
								<a class="btn cus_btn2 blue big" href="javascript:void(0);" 
									onclick="CheckLoginOrApply(<?php echo $j->id; ?>);">Apply
								</a>
								<?php } ?>
							</div>
						</div>
						<div class="bt_cont">
							<div class="lhs">
								<p>
								<i class="fa fa-calendar" aria-hidden="true"></i>
								<?php echo date('M d, Y',strtotime($j->add_date)); ?>
								</p>
								<span>
								<i class="fa fa-map-marker" aria-hidden="true"></i>
								<?php echo $j->address; ?>
								</span>
							</div>
							<div class="rhs">
								<p><?php echo $Openings->openings; ?> openings</p>
								<span>Posted an hour ago</span>
							</div>
						</div>
						
					</div>
				</div>
				<?php
			}
		}
		else
		{
			
		}
	}

	public function CheckUserPositionAndApply(Request $request)
	{
		$Data 		= $request->all();
		$UserID		= Session::get('UserID');
		$Position 	= $Data['Position'];
		$JobID 		= $Data['JobID'];

		$Details['job_id'] 		= $JobID;
		$Details['profile_id'] 	= $UserID;
		$Details['position_id'] = $Position;

		echo $Apply = $this->InvitesModel->CheckUserPositionAndApply($Details);

		exit();

	}
}