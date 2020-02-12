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
use App\Http\Models\Front\FavProsModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class FavProsController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->FavProsModel = new FavProsModel();
		$this->ViewProsModel = new ViewProsModel();
		$this->ViewJobModel = new ViewJobModel();
		$this->FavProsModel = new FavProsModel();
	}

	public function FavPros(Request $request)
	{
		$UserID = Session::get('UserID');
		$Data['Title'] 			= 'Favourite Pros';
		$Data['Menu'] 			= 'FavPros';

		return View('Front/Pages/User/FavPros')->with($Data);
	}	

	public function GetFavPros(Request $request)
	{
		$UserID = Session::get('UserID');
		$FavPros = $this->FavProsModel->GetUserFavPros($UserID);
		if(!empty($FavPros))
		{
			?><h3><?php echo count($FavPros); ?> Pros</h3><?php 
			foreach($FavPros as $j)
			{
				$ProsID = $j->id;
				$JobCategory = $this->ViewProsModel->GetUserJobCategory($ProsID);

				$ProsFavStatus = $this->ViewProsModel->ProsFavStatus($UserID,$ProsID);
				$ProsInviteStatus = $this->ViewProsModel->ProsInviteStatus($UserID,$ProsID);

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
											<span>  <?php echo $UserReviews; ?> Reviews</span>
										</fieldset>
									</div>
								</div>
							</div>
						</div>
						<div class="rhs">
							<?php if($ProsInviteStatus==1){?>
								<a class="btn cus_btn2 blue big" href="javascript:void(0);">Invited</a>
							<?php } else {?>
								<a class="btn cus_btn2 blue big" href="javascript:void(0);" onclick="CheckLoginOrInvitePros(<?php echo $j->id; ?>);">Invite</a>
							<?php } ?>

							<?php if($ProsFavStatus==1){?>
							<span class="profile-share">
								<span id='FavHeart_<?php echo $j->id; ?>'>
									<a class="btn cus_btn2 icon grey_bor big" href="javascript:void(0);"
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
									<a class="btn cus_btn2 icon grey_bor big" href="javascript:void(0);"
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
						<?php if($j->about!=''){ ?>
						<?php
	                    	echo Common::limitTextWords(html_entity_decode($j->about), 25, true, true);
	                    ?>
						<a href="<?php echo route('ViewProsDetails',array('ProsID'=>base64_encode($j->id))); ?>">Read more..</a></p>
						<?php } ?>
					</p>
					<div class="bk_check">
						<ul>
							<li>
								<?php  if($j->verified_status==1){ ?>
								<img title="Background Verified" src="<?php echo asset('public/Front/Design/img/bk_check1.png'); ?>" alt="" /> 						
								<span></span>	
								<?php } ?>													
							</li>
							<li>
								<?php  if($j->onboarding_quiz_status==1){ ?>
								<img title="Test Completed" src="<?php echo asset('public/Front/Design/img/bk_check2.png'); ?>" alt="" />
								<span></span>
								<?php } ?>
							</li>
							
						</ul>
					</div>
				</div>
			</div>
			<?php
			}
		}
		else
		{
			?>
			<div class="job_cont pros">
				No Favourite Pros.
			</div>
			<?php
		}
	}
}