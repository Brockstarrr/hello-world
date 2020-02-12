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
use App\Http\Models\Front\MyScheduleModel;
use App\Http\Models\Front\UserModel;
use App\Http\Models\Front\ViewJobModel;
use App\Http\Models\Front\JobPostModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Pagination;
use App\Helpers\Common;

class ViewJobController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->Pagination = new Pagination();
		$this->UserModel = new UserModel();
		$this->ViewJobModel = new ViewJobModel();
		$this->JobPostModel = new JobPostModel();
		$this->Common 		= new Common();
		$this->MyScheduleModel 	= new MyScheduleModel();		
	}

	public function ViewJob()
	{
		$Data['Title'] 			= 'View Job';
		$Data['Menu'] 			= 'ViewJob';
		$Data['Category'] 		= $this->ViewJobModel->GetJobCategory();
		$Data['SubCategory'] 	= $this->ViewJobModel->GetJobSubCategory();

		return View('Front/Pages/ViewJob')->with($Data);
	}

	public function GetAllJobs(Request $request)
	{
		$Data 		= $request->all();

		$SearchKeyword 	= $Data['SearchKeyword'];
		$Category 		= $Data['Category'];
		$SubCat 		= $Data['SubCat'];
		$StartDate 		= $Data['StartDate'];
		$EndDate 		= $Data['EndDate'];
		$SortBy 		= $Data['SortBy'];
		$page 			= $Data['page'];
		$numofrecords   = $Data['numofrecords'];

		$PayRangeMin   	= $Data['PayRangeMin'];
		$PayRangeMax   	= $Data['PayRangeMax'];
		$DistanceFrom   = $Data['DistanceFrom'];
		$DistanceTo   	= $Data['DistanceTo'];
		$latitude   		= $Data['latitude'];
		$longitude   		= $Data['longitude'];
		$Distance   		= $Data['Distance'];

		$PayType   		= $Data['PayType'];

		$cur_page 		= $page;

		$Limitpage 		= $page-1;
		$start 			= $Limitpage * $numofrecords;
		$Search['SortBy'] 	= $SortBy;
		$Search['Category'] = $Category;
		$Search['SubCat'] 	= $SubCat;
		$Search['StartDate']= $StartDate;
		$Search['EndDate'] 	= $EndDate;
		$Search['SearchKeyword'] 	= $SearchKeyword;
		if($Data['PayRangeMin']==1 && $Data['PayRangeMax']==5000)
		{
			$Search['PayRangeMin'] 		= '';
			$Search['PayRangeMax'] 		= '';
		}
		else
		{
			$Search['PayRangeMin'] 		= $PayRangeMin;
			$Search['PayRangeMax'] 		= $PayRangeMax;			
		}
		$Search['PayType'] 			= $PayType;
		$Search['Distance'] 		= $Distance;

		$Search['start'] 	= $start;
		$Search['numofrecords'] 	= $numofrecords;

		$AllJobs		= $this->ViewJobModel->AllJobs($start,$numofrecords,$Search);

		$Jobs 			= $AllJobs['Res'];
		$Count 			= $AllJobs['Count'];
		
		$ViewJobs = '';
		$locations = '[';
		$i = 1;
		$LatLng = '';
		$LongLng = '';
		foreach($Jobs as $j)
		{
			$JobID = $j->id;
			$PositionID = $j->sub_cat;
			$UserID = $j->UserID;
			$CompDetails = DB::table('company_info')->where('profile_id',$UserID)->select('name')->first();
			$CompanyName = $j->username;
			if($CompDetails){
				$CompanyName = $CompDetails->name;
			}

			if($j->image!=''){
				$Img='<span><img src="'.asset('public/Front/Users/Jobs').'/'.$j->image.'" alt=""/></span>';
			}else{
				$Img='<span><img src="'.asset('public/Front/Design/img/pro_pic.png').'" alt=""/></span>';
			}

			$ViewJobsLink = "<div class='map_view'><div class='img_wrap'><span><img src='".asset('public/Front/Design/img/pro_pic.png')."'/></span></div><div class='cont_wrap'><a target='_blank' href='".route('ViewJobDetails',array('JobID'=>base64_encode($j->id)))."'>".$j->job_title."</a><span>By ".$CompanyName." </span> Openings:<br>".$this->GetOpeningsList($JobID)."</div></div>";
			$ViewJobsUrl = "'".route('ViewJobDetails',array('JobID'=>base64_encode($j->id)))."'";

			

			$JobTotalReviews = $this->ViewJobModel->JobTotalReviews($JobID);
			$JobReviews = $this->ViewJobModel->JobReviews($JobID);


			if($j->longitude){
				$LatLng = $j->latitude;
				$LongLng = $j->longitude;
			}
			$locations.='["'.$ViewJobsLink.'",'.$j->latitude.','.$j->longitude.','.$i.'],';
			$Distance = 0;
			if($latitude){
				$Distance = $this->Common->DistanceCalculate($latitude,$longitude,$j->latitude,$j->longitude,"M");
				$Distance = explode('.',$Distance);
				$Distance = reset($Distance);
				if($DistanceFrom <= $Distance AND $DistanceTo >= $Distance){
					//$Distance = 'Done';
				}else{
					continue;
				}
			}

			
			$SearchSubCat=array();
			if($Data['SubCat']!=''){
				$SearchSubCat = explode(',', $Data['SubCat']);        
			}

			$Openings = $this->ViewJobModel->JobOpenings($JobID);
			$JobFavStatus = 0;
			$JobApplyStatus = 0;
			$MyJob = 0;
			if(Session::has('UserLogin'))
			{
				$UserID = Session::get('UserID');
				$JobFavStatus = $this->ViewJobModel->JobFavStatus($UserID,$JobID);
				$JobApplyStatus = $this->ViewJobModel->JobApplyStatus($UserID,$JobID);
				if($UserID==$j->UserID)
				{
					$MyJob = 1;
				}
			}
			
			$ViewJobs.='<div class="job_cont">
										<div class="cont_wrap">
											<div class="img_wrap">
												<div class="lhs">
													<div class="pro_pic">
														<span>';
			if($j->image!=''){
				$ViewJobs.='<img src="'.asset('public/Front/Users/Jobs').'/'.$j->image.'" alt=""/>';
			}else{
				$ViewJobs.='<img src="'.asset('public/Front/Design/img/pro_pic.png').'" alt=""/>';
			}		
			$Rating = ceil($JobTotalReviews/5);
			$RatingHtml = '';
			for($i=5; $i>=1;$i--){
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
			$ViewJobs.='</span>
								</div>
								<div>
								<p>
									<a href="'.route('ViewJobDetails',array('JobID'=>base64_encode($j->id))).'">
									'.$j->job_title.'
									<span>By '.$CompanyName.' </span>
									</a>
								</p>
								<div class="job_star">
									<div class="rating-form"  name="rating-movie">
										<fieldset class="form-group">
											<legend class="form-legend">Rating:</legend>
												<div class="form-item">
													'.$RatingHtml.' &nbsp
													'.number_format($Rating,1).'
												</div>
											<span class="rev-job">'.$JobReviews.' Reviews</span>
										</fieldset>
									</div>										
								</div>
							</div>
						</div>';

			$ViewJobs.='<div class="rhs">';			
			if($MyJob==1){

				

				$ViewJobs.='<a class="btn cus_btn2 blue big" href="javascript:void(0);" onclick="MyJob("Apply");">APPLY</a>
								<span class="profile-share">
									<span id="FavHeart_'.$j->id.'">
									<a class="btn cus_btn2 icon grey_bor big share" href="javascript:void(0);"
										onclick="MyJob("Fav");">
										<i class="fa fa-heart" aria-hidden="true"></i>
									</a>
									</span>
									<a class="btn cus_btn2 icon big share" href="javascript:void(0);" onclick="SocialMedia('.$j->id.');">
										<i class="fa fa-share-alt" aria-hidden="true"></i>
									</a>
								</span>
								
								<ul class="social_product list-inline" id="social_product_'.$j->id.'">
					              <li><a href="javascript:void(0);" onclick="ShareFacebook('.$ViewJobsUrl.')"><i class="fa fa-facebook-f"></i></a></li>
					              <li><a href="javascript:void(0);" onclick="ShareTweet('.$ViewJobsUrl.')"><i class="fa fa-twitter"></i></a></li>
					              <li><a href="javascript:void(0);" onclick="ShareLinkedin('.$ViewJobsUrl.')"><i class="fa fa-linkedin-square"></i></a></li>
					            </ul>
								';	
			}else{
				if($JobApplyStatus==1){
					$ViewJobs.='<a class="btn cus_btn2 blue big" href="javascript:void(0);">APPLIED</a>';
				}else{
					$ViewJobs.='<a class="btn cus_btn2 blue big" href="javascript:void(0);" 
									onclick="CheckLoginOrApply('.$j->id.');">Apply
								</a>';
				}
				if($JobFavStatus==1){
					$ViewJobs.='<span class="profile-share">
									<span id="FavHeart_'.$j->id.'">
									<a class="btn cus_btn2 icon grey_bor big" href="javascript:void(0);"
										onclick="CheckLoginOrMakeFav(0,'.$j->id.');" >
										<i class="fa fa-heart gr" aria-hidden="true"></i>
									</a>
									</span>
									<a class="btn cus_btn2 icon big share" onclick="SocialMedia('.$j->id.');" href="javascript:void(0);">
										<i class="fa fa-share-alt" aria-hidden="true"></i>
									</a>
								</span>';
					$ViewJobs.='<ul class="social_product list-inline" id="social_product_'.$j->id.'">
			              <li><a href="javascript:void(0);" onclick="ShareFacebook('.$ViewJobsUrl.')"><i class="fa fa-facebook-f"></i></a></li>
			              <li><a href="javascript:void(0);" onclick="ShareTweet('.$ViewJobsUrl.')"><i class="fa fa-twitter"></i></a></li>
			              <li><a href="javascript:void(0);" onclick="ShareLinkedin('.$ViewJobsUrl.')"><i class="fa fa-linkedin-square"></i></a></li>
			            </ul>';
				}else{
					$ViewJobs.='<span class="profile-share">
										<span id="FavHeart_'.$j->id.'">
										<a class="btn cus_btn2 icon grey_bor big share" href="javascript:void(0);"
											onclick="CheckLoginOrMakeFav(1,'.$j->id.');" 
											>
											<i class="fa fa-heart" aria-hidden="true"></i>
										</a>
										</span>
										<a class="btn cus_btn2 icon big share" onclick="SocialMedia('.$j->id.');" href="javascript:void(0);" style="margin-top:13px !important;">
											<i class="fa fa-share-alt" aria-hidden="true"></i>
										</a>
								</span>';
					$ViewJobs.='<ul class="social_product list-inline" id="social_product_'.$j->id.'">
		              <li><a href="javascript:void(0);" onclick="ShareFacebook('.$ViewJobsUrl.')"><i class="fa fa-facebook-f"></i></a></li>
		              <li><a href="javascript:void(0);" onclick="ShareTweet('.$ViewJobsUrl.')"><i class="fa fa-twitter"></i></a></li>
		              <li><a href="javascript:void(0);" onclick="ShareLinkedin('.$ViewJobsUrl.')"><i class="fa fa-linkedin-square"></i></a></li>
		            </ul>';			
				}
				
			}	
			$JobID = $j->id;
			$ViewJobs.='<h4></h4>
									</div>
									</div>
									<div class="bt_cont">
										<div class="lhs">
											
											<span>
											'.$this->GetOpeningsDetails($JobID).'
											</span>
																						
											<span>
												<i class="fa fa-map-marker" aria-hidden="true"></i>
												'.$j->address.'
											</span><br>

										</div>
										<div class="rhs">
											
										</div>
									</div>
									<p class="job_read_mo">
									<div class="full-dec" id="ReadMore_'.$JobID.'">';		

			if(strlen($j->job_description)>=100){
				$pos=strpos($j->job_description, ' ', 100);
				$ViewJobs.=$Description = substr($j->job_description,0,$pos ); 
			}else{
				$ViewJobs.=$j->job_description; 												
			}
			$ViewJobs.='<a href="javascript:void(0);" onclick="ReadMore('.$JobID.');">Read more... </a>
									</div>
									<div class="full-dec" id="HideMore_'.$JobID.'" style="display:none;">
									'.$j->job_description.'
									<a href="javascript:void(0);"  onclick="HideMore('.$JobID.');">Hide </a>
									</div>
									</p>
									</div>
									</div>';
			$i++;
		}
		$locations.=']';
		if($numofrecords!='')
		{
			$ViewJobs.=$this->Pagination->Links($numofrecords, $Count, $page); 
		}
		$ViewJobsMap='<script type="text/javascript">
							var locations = '.$locations.';
						  var map = new google.maps.Map(document.getElementById("JobListMap"), {
						    zoom: 10,
						    center: new google.maps.LatLng('.$LatLng.', '.$LongLng.'),
						    mapTypeId: google.maps.MapTypeId.ROADMAP
						  });
						  var infowindow = new google.maps.InfoWindow();
						  var marker, i;
						  for (i = 0; i < locations.length; i++) {  
						    marker = new google.maps.Marker({
						      position: new google.maps.LatLng(locations[i][1], locations[i][2]),
						      map: map
						    });
						    google.maps.event.addListener(marker, "click", (function(marker, i) {
						      return function() {
						        infowindow.setContent(locations[i][0]);
						        infowindow.open(map, marker);
						      }
						    })(marker, i));
						  }
							</script>';
		$arr['ViewJobs'] = $ViewJobs;
		$arr['ViewJobsMap'] = $ViewJobsMap;
		echo json_encode($arr);
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
				$TotalHours = $this->CalculateTotalHours($ol->hour_from,$ol->hour_to);
				$Details.='<li><strong> '.$SubCatName;
				$Details.=' <i class="fa fa-calendar" aria-hidden="true"></i>'.date('M d, Y',strtotime($ol->start_date)).' <i class="fa fa-clock-o" aria-hidden="true"></i>'.$ol->hour_from_label.'-'.$ol->hour_to_label.'*'.$TotalHours.'/hrs'; 
				$Details.=', <i class="fa fa-money" aria-hidden="true"></i>$'.$ol->pay_rate.'</strong></li>';
			}
			$Details.='</ul>';
		}
		return $Details;
	}
	public function GetOpeningsList($JobID)
	{
		$Details = "";
		$OpeningList = $this->ViewJobModel->OpeningList($JobID);
		
		if(!empty($OpeningList))
		{
			foreach($OpeningList as $ol)
			{
				$SubCatName = $this->JobPostModel->GetSubCategoryName($ol->sub_cat);
				$TotalHours = $this->CalculateTotalHours($ol->hour_from,$ol->hour_to);
				$Details.=' <span><strong>'.$SubCatName.'</strong></span>';
			}
		}
		return $Details;
	}
	public function ViewJobDetails(Request $request, $JobID)
	{
		$JobID = base64_decode($JobID);

		$IsJobExist 	= $this->ViewJobModel->IsJobExist($JobID);
		if($IsJobExist)
		{

			$Data['Title'] 			= 'View Job';
			$Data['Menu'] 			= 'ViewJob';
			$JobDetails 			= $this->ViewJobModel->GetJobDetail($JobID);
			$Data['JobDetails'] 	= $JobDetails;
			$JobCat 				= $JobDetails->job_cat;
			$Data['Openings'] 		= $this->ViewJobModel->JobOpenings($JobID);
			$Data['Language'] 		= $this->ViewJobModel->GetJobLanguage($JobID);
			$Preference 			= $Data['JobDetails']->preference;
			$Data['Preference']		= '';
			if($Preference!='')
			{
				$Data['Preference'] 	= $this->ViewJobModel->GetJobPreference($Preference);				
			}
			
			$HairColor 				= $Data['JobDetails']->hair_color;
			$EyeColr 				= $Data['JobDetails']->eye_color;
			$Data['HairColor'] 		= $this->ViewJobModel->GetHairColor($HairColor);
			$Data['EyeColor'] 		= $this->ViewJobModel->GetHairColor($EyeColr);
			$Data['SubCatOpenings'] = $this->ViewJobModel->GetSubCatOpenings($JobID);
			$Data['SimilarJobs'] 	= $this->ViewJobModel->GetSimilarJobs($JobID,$JobCat);
			
			
			$JobFavStatus = 0;
			$JobApplyStatus = 0;
			$MyJob = 0;
			if(Session::has('UserLogin'))
			{
				$UserID = Session::get('UserID');
				$JobFavStatus = $this->ViewJobModel->JobFavStatus($UserID,$JobID);
				$JobApplyStatus = $this->ViewJobModel->JobApplyStatus($UserID,$JobID);
				if($UserID==$Data['JobDetails']->profile_id)
				{
					$MyJob = 1;
				}

			}
			$Data['JobFavStatus'] 	= $JobFavStatus;
			$Data['JobApplyStatus'] = $JobApplyStatus;
			$Data['MyJob'] 			= $MyJob;
			$Data['JobTotalReviews'] = $this->ViewJobModel->JobTotalReviews($JobID);
			$Data['JobReviews'] = $this->ViewJobModel->JobReviews($JobID);
			return View('Front/Pages/ViewJobDetails')->with($Data);
		}
		else
		{
			return Redirect::to('404');
		}	
	}
	public function CheckLoginOrMakeFav(Request $request)
	{
		$Data 		= $request->all();
		$UserID		= Session::get('UserID');
		$Status 	= $Data['Status'];
		$JobID 		= $Data['JobID'];
		$ReferURL 	= $Data['ReferURL'];
		if(Session::has('UserLogin') && Session::get('UserLogin')=='1')
		{
			$SetJobFavStatus = $this->ViewJobModel->SetJobFavStatus($UserID,$JobID,$Status);
			if($SetJobFavStatus)
			{
				if($Status=='0')
				{
					
					echo $Response = '<a class="btn cus_btn2 icon grey_bor big" href="javascript:void(0);"
										onclick="CheckLoginOrMakeFav(1,'.$JobID.');" >
										<i class="fa fa-heart" aria-hidden="true"></i>
									</a>';
				}
				else
				{
					echo $Response = '<a class="btn cus_btn2 icon grey_bor big" href="javascript:void(0);"
										onclick="CheckLoginOrMakeFav(0,'.$JobID.');" >
										<i class="fa fa-heart gr" aria-hidden="true"></i>
									</a>';					
				}
			}
			else
			{
				if($Status=='0')
				{
					echo $Response = '<a class="btn cus_btn2 icon grey_bor big" href="javascript:void(0);"
										onclick="CheckLoginOrMakeFav(0,'.$JobID.');" >
										<i class="fa fa-heart gr" aria-hidden="true"></i>
									</a>';	
				}
				else
				{
					echo $Response = '<a class="btn cus_btn2 icon grey_bor big" href="javascript:void(0);"
										onclick="CheckLoginOrMakeFav(1,'.$JobID.');" >
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
	public function CheckLoginOrApply(Request $request)
	{
		$Data 		= $request->all();
		$UserID		= Session::get('UserID');
		$JobID 		= $Data['JobID'];
		$ReferURL 	= $Data['ReferURL'];
		if(Session::has('UserLogin') && Session::get('UserLogin')=='1')
		{
			$JobDetails = $this->ViewJobModel->GetJobDetail($JobID);
			$SubCatOpenings = $this->ViewJobModel->GetSubCatOpenings($JobID);
			?>
			<form action="<?php echo route('JobApply'); ?>" style="width:100%;" id="JobApplyForm" name="JobApplyForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<input type="hidden" name="JobID" id="JobID" value="<?php echo $JobID; ?>">
				<div class="row">
					<div class="col-md-12 mb-2"><h3>Apply Job</h3></div>
					
					<div class="col-md-12">
						<h5><?php echo $JobDetails->job_title; ?></h5>
						<table class="table">
							<htead>
								<tr>
									<th>Job Position</th>
									<th>Openings</th>
									<th>PayRate</th>
									<th>Job Start From</th>
									<th>You Apply For</th>
								</tr>
							</thead>
							<tbody>
								<?php
								
								foreach($SubCatOpenings as $key=>$sco) 
								{ 
									$PayRate = $sco->pay_rate;
									$PayRateString = '';
									if($sco->pay_type==1)
									{
										$PayRateString = '$'.$PayRate;
									}
									else if($sco->pay_type==2)
									{
										$PayRateString = '$'.$PayRate.'/hr';
									}
									?>
									<tr>
										<td><?php echo $sco->position; ?></td>										
										<td><?php echo $sco->openings; ?></td>										
										<td><?php echo $PayRateString; ?></td>										
										<td>
											<?php echo date('M d,Y',strtotime($sco->start_date)).' <strong>'.$sco->hour_from_label.'-'.$sco->hour_to_label.' * '.$this->CalculateTotalHours($sco->hour_from,$sco->hour_to).'/hrs</strong>'; ?>
										</td>										
										<td>
											<div class="custom-control custom-radio">
												<input onclick='Hide("PositionIDErr");' type="radio" value="<?php echo $sco->sub_cat; ?>" 
												class="custom-control-input" id="customRadio_<?php echo $key; ?>" name="PositionID"/>
												<label class="custom-control-label" for="customRadio_<?php echo $key; ?>">Apply</label>
											</div>
										</td>
									</tr>
									<?php 
								}
								?>
							</tbody>
						</table>
						<span id='PositionIDErr'></span>
					</div>
					<div class="col-md-12 text-center">
						<a href="javascript:void(0);" onclick="ApplyJob();" class="btn cus_btn big">Apply</a>
					</div>
					
				</div>
			</form>
			<?php
		}
		else
		{		
			Session::put('ReferURL', $ReferURL);
			Session::save();
			echo 0;
		}
		exit();
	}
	public function CalculateTotalHours($TimeSlotFrom,$TimeSlotTo)
	{
		$From 	= $this->JobPostModel->GetTime($TimeSlotFrom);
		$To 	= $this->JobPostModel->GetTime($TimeSlotTo);
			
		$difference = round(abs(strtotime($To) - strtotime($From)) / 3600,2);
		$FinalTotalHours =  $difference;		
		return  number_format($FinalTotalHours);
	}
	public function JobApply(Request $request)
	{
		$Data 		= $request->all();
		
		$UserID		= Session::get('UserID');

		$Details['job_id'] 		= $Data['JobID'];
		$Details['profile_id'] 	= $UserID;
		$Details['position_id'] = $Data['PositionID'];

		$JobApply = $this->ViewJobModel->JobApply($Details);
		if($JobApply)
		{
			//User Notification
			$JobDetails 		= $this->ViewJobModel->GetJobDetail($Data['JobID']);
			$UserDetails 		= $this->UserModel->UserDetails($UserID);
			$PositionDetails 	= $this->ViewJobModel->GetApplyPositionDetails($Data['PositionID']);
			$Detail['user_id']  = $JobDetails->profile_id;
			$Detail['link'] 	= route('ViewProsDetails',array('ProsID'=>base64_encode($UserDetails->id)));
			$Detail['text'] 	= $UserDetails->first_name.' has applied for '.$PositionDetails->position.' Position';
			$this->Common->AddNotification($Detail);
			//Accept If Auto Fill
			if($JobDetails->job_type=='3')
			{
				$Openings = $PositionDetails->openings;
				$JobAppledCount = $this->MyScheduleModel->GetJobAppledCount($Data['JobID'],$Data['PositionID']);
				if($JobAppledCount<$Openings)
				{
					$Details['status'] = '2';
					$this->MyScheduleModel->AutoAccepted($Data['JobID'],$UserID,$Data['PositionID'],$Details);
				}
			}
			//////////////////////////////
			Session::flash('message', 'Job Applied Successfully.'); 
          	Session::flash('alert-class', 'alert-success'); 
          	return redirect( route('ViewJob' ));
		}
		else
		{
			Session::flash('message', 'Something Wrong Please Try Again.'); 
          	Session::flash('alert-class', 'alert-danger'); 
          	return redirect( route('ViewJob' ));
		}
	}
	public function CheckUserPosition(Request $request)
	{
		$Data 		= $request->all();
		$UserID		= Session::get('UserID');
		$Position 	= $Data['Position'];
		echo $CheckUserPosition = $this->ViewJobModel->CheckUserPosition($UserID,$Position);
		exit();
	}

	

	
}