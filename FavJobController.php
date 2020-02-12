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
use App\Http\Models\Front\JobPostModel;
use App\Http\Models\Front\ViewProsModel;
use App\Http\Models\Front\ViewJobModel;
use App\Http\Models\Front\FavJobModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class FavJobController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->UserModel = new UserModel();
		$this->JobPostModel = new JobPostModel();
		$this->ViewProsModel = new ViewProsModel();
		$this->ViewJobModel = new ViewJobModel();
		$this->FavJobModel = new FavJobModel();
		$this->Common = new Common();
	}

	public function FavJobs(Request $request)
	{
		$UserID = Session::get('UserID');
		$Data['Title'] 			= 'Favourite Job';
		$Data['Menu'] 			= 'FavJobs';
		

		return View('Front/Pages/User/FavJobs')->with($Data);
	}	

	public function GetFavJobs(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$FavJobs	= $this->FavJobModel->GetUserFavJobs($UserID);

		$UserDetails = $this->UserModel->UserDetails($UserID);
		if(!empty($FavJobs))
		{
			?><h3><?php echo count($FavJobs); ?> Jobs</h3><?php 
			foreach($FavJobs as $j)
			{
				$JobID = $j->id;
				$JobUserID = $j->JobUserID;

				$JobTotalReviews 	= $this->ViewJobModel->JobTotalReviews($JobID);
				$JobReviews 		= $this->ViewJobModel->JobReviews($JobID);

				$Openings 			= $this->ViewJobModel->JobOpenings($JobID);
				$JobFavStatus 		= $this->ViewJobModel->JobFavStatus($UserID,$JobID);
				$JobApplyStatus 	= $this->ViewJobModel->JobApplyStatus($UserID,$JobID);

				$ViewJobsUrl = "'".route('ViewJobDetails',array('JobID'=>base64_encode($j->id)))."'";

				$Address =  $j->address;

				$CompDetails = DB::table('company_info')->where('profile_id',$JobUserID)->select('name')->first();
				$CompanyName = $j->username;
				if($CompDetails){
					$CompanyName = $CompDetails->name;
				}
				$Distance = '';

				$JobLat = $j->latitude;
				$JobLng = $j->longitude;
				if($j->latitude!='' && $j->longitude!='')
				{	
					
					$UserLat = $UserDetails->latitude;
					$UserLng = $UserDetails->longitude;
					if($UserLat!='' && $UserLng!='')
					{
						
						$Dist = $this->Common->DistanceCalculate($JobLat,$JobLng,$UserLat,$UserLng,"M");

						$Distance = 'You are <b>'.number_format($Dist,2).' miles</b> far from this job location.';
					}
				}
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
									<img src="{{asset('public/Front/Design/img/pro_pic.png')}}" alt=""/>
									<?php }  ?>
								</span>
							</div>
							<div class="fav_job_cont">
								<p>
									<a href="<?php echo route('ViewJobDetails',array('JobID'=>base64_encode($j->id))); ?>"><?php echo $j->job_title; ?> </a>
									<span>By <?php echo $CompanyName; ?> </span>
								</p>
								<div class="job_star">
									<div class="rating-form"  name="rating-movie">
										<fieldset class="form-group">
											<legend class="form-legend">Rating:</legend>
											<div class="form-item">
												<?php
												$Rating = ceil($JobTotalReviews/5);
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
											<span>  <?php echo $JobReviews; ?> Reviews</span>
										</fieldset>
									</div>									
								</div>
							</div>
						</div>
						<div class="rhs">
							<?php if($JobApplyStatus==1){?>
							<a class="btn cus_btn2 blue big" href="javascript:void(0);">
								Applied
							</a>
							<?php } else {?>
							<a class="btn cus_btn2 blue big" href="javascript:void(0);" 
								onclick="CheckLoginOrApply(<?php echo $j->id; ?>);">Apply
							</a>
							<?php } ?>

							<?php if($JobFavStatus==1){?>
							<span id='FavHeart_<?php echo $j->id; ?>' class="profile-share">
								<a class="btn cus_btn2 icon grey_bor big " href="javascript:void(0);"
									onclick="CheckLoginOrMakeFav(0,<?php echo $j->id; ?>);" >
									<i class="fa fa-heart gr" aria-hidden="true"></i>
								</a>

								<a class="btn cus_btn2 icon big share" href="javascript:void(0);"  onclick="SocialMedia('<?php echo $j->id; ?>');">
									<i class="fa fa-share-alt" aria-hidden="true"></i>
								</a>
								<ul class="social_product list-inline" id="social_product_<?php echo $j->id;?>">
					              <li><a href="javascript:void(0);" onclick="ShareFacebook(<?php echo $ViewJobsUrl; ?>)"><i class="fa fa-facebook-f"></i></a></li>
					              <li><a href="javascript:void(0);" onclick="ShareTweet(<?php echo $ViewJobsUrl; ?>)"><i class="fa fa-twitter"></i></a></li>
					              <li><a href="javascript:void(0);" onclick="ShareLinkedin(<?php echo $ViewJobsUrl; ?>)"><i class="fa fa-linkedin-square"></i></a></li>
					            </ul>
							</span>
							<?php } else {?>
							<span id='FavHeart_<?php echo $j->id; ?>' class="profile-share">
								<a class="btn cus_btn2 icon grey_bor big " href="javascript:void(0);"
									onclick="CheckLoginOrMakeFav(1,<?php echo $j->id; ?>);" 
									>
									<i class="fa fa-heart" aria-hidden="true"></i>
								</a>
								<a class="btn cus_btn2 icon big share" href="javascript:void(0);"  onclick="SocialMedia('<?php echo $j->id; ?>');">
									<i class="fa fa-share-alt" aria-hidden="true"></i>
								</a>
								<ul class="social_product list-inline" id="social_product_<?php echo $j->id;?>">
					              <li><a href="javascript:void(0);" onclick="ShareFacebook(<?php echo $ViewJobsUrl; ?>)"><i class="fa fa-facebook-f"></i></a></li>
					              <li><a href="javascript:void(0);" onclick="ShareTweet(<?php echo $ViewJobsUrl; ?>)"><i class="fa fa-twitter"></i></a></li>
					              <li><a href="javascript:void(0);" onclick="ShareLinkedin(<?php echo $ViewJobsUrl; ?>)"><i class="fa fa-linkedin-square"></i></a></li>
					            </ul>
							</span>
							<?php } ?>
						</div>
					</div>
					<div class="bt_cont">
						<div class="lhs">
							<span>
							<?php echo $this->GetOpeningsDetails($j->id); ?>
							</span>
							<p>
							<i class="fa fa-calendar" aria-hidden="true"></i>
							<?php echo date('M d, Y',strtotime($j->add_date)); ?>
							</p>
							<span>
							<i class="fa fa-map-marker" aria-hidden="true"></i>
							<?php echo $Address; ?><br>
							<?php echo $Distance; ?>

							</span>
						</div>
						<div class="rhs">
							<p><?php echo $Openings->openings; ?> openings</p>
							<span><?php echo $this->Common->TimeElapsedString($j->add_date, false) ?></span>
						</div>
					</div>
					<p class="job_read_mo">
						<?php
						if(strlen($j->job_description)>=50)
						{
							$pos=strpos($j->job_description, ' ', 50);
							echo $Description = substr($j->job_description,0,$pos ); 
						}
						else
						{
							echo $j->job_description; 												
						}
						?>
						<a href="<?php echo route('ViewJobDetails',array('JobID'=>base64_encode($j->id))); ?>">read more... </a>
					</p>
				</div>
			</div>
			<?php
			}
		}
		else
		{
			?>
			<div class="job_cont">
				No Saved Gigs!
			</div>
			<?php
		}
		exit();	
	}

	public function GetOpeningsDetails($JobID)
	{
		$Details = "";
		$OpeningList = $this->ViewJobModel->OpeningList($JobID);
		

		if(!empty($OpeningList))
		{
			$Details.='<ul class="listing_l">';
			foreach($OpeningList as $ol)
			{
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

				$Details.='<li>Need '.$ol->openings.'
							<strong>'.$SubCatName.'</strong>';
				if($ol->job_for=='2')
				{
				$Details.=' From <strong>'.date('M, d-Y',strtotime($ol->start_date)).'</strong> 
							To <strong>'.date('M, d-Y',strtotime($ol->end_date)).'</strong>'; 
				}
				else
				{
					$Details.=' on <strong>'.date('M, d-Y',strtotime($ol->start_date)).'</strong>>'; 
				}
				$Details.=', will get: <strong>$'.$ol->pay_rate.'/'.$PayRateString.'</strong>  
							</li>';
			}
			$Details.='</ul>';
		}
		return $Details;
	}
}