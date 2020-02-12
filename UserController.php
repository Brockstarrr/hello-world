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
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class UserController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->UserModel = new UserModel();
	}
	
	public function UserDashboard(Request $request)
	{
		$UserID = Session::get('UserID');
		$UserDetails = $this->UserModel->UserDetails($UserID);
		$OnBoardinQuizStatus = $UserDetails->onboarding_quiz_status;
		if($OnBoardinQuizStatus==0)
		{
			return redirect('onboarding-quiz-start');
		}
		else
		{
			$Data['Title'] 				= 'User Dashboard';
			$Data['Menu'] 				= 'Profile';
			$Data['UserDetails'] 		= $UserDetails;
			$Data['Experience'] 		= $this->UserModel->GetUserExperience($UserID);
			$Data['Preference'] 		= $this->UserModel->GetUserPreference($UserID);
			$Data['Education'] 			= $this->UserModel->GetUserEducation($UserID);
			$Data['Language'] 			= $this->UserModel->GetUserLanguage($UserID);
			$Data['Appearance'] 		= $this->UserModel->GetUserAppearance($UserID);
			$Data['Accreditations'] 	= $this->UserModel->GetUserAccreditations($UserID);
			$Data['Certifications'] 	= $this->UserModel->GetUserCertifications($UserID);
			$Data['CompanyDetails'] 	= $this->UserModel->CompanyDetails($UserID);
			$Data['UserTotalReviews'] 	= $this->UserModel->UserTotalReviews($UserID);
			$Data['CountUserReviews']   = $this->UserModel->CountUserReviews($UserID);
			return View('Front/Pages/User/UserDashboard')->with($Data);
		}		
	}

	public function OnBoardingQuizQuestion()
	{
		$Data['Title'] 				= 'OnBoarding Quiz Question';
		$Data['Menu'] 				= 'OnBoardingQuizQuestion';
		return View('Front/Pages/OnBoardingQuizQuestion')->with($Data);
	}

	public function OnBoardingQuizStart()
	{
		$UserID = Session::get('UserID');
		$OnBoardingQuizQuestion = $this->UserModel->OnBoardingQuizQuestion($UserID);
		if($OnBoardingQuizQuestion==0)
		{
			$Data['Title'] 				= 'OnBoarding Quiz Start';
			$Data['Menu'] 				= 'OnBoardingQuizStart';
			$Data['Content'] 			= $this->UserModel->OnBoardingQuizStart();
			return View('Front/Pages/OnBoardingQuizStart')->with($Data);
		}
		else
		{
			return redirect('onboarding-quiz-question');
		}		
	}

	public function OnBoardingQuizContent()
	{
		$Data['Title'] 				= 'OnBoarding Quiz Content';
		$Data['Menu'] 				= 'OnBoardingQuizContent';
		$Data['Content'] 			= $this->UserModel->OnBoardingQuizContent();
		return View('Front/Pages/OnBoardingQuizContent')->with($Data);
	}

	public function GetOnBoardingQuizQuestion(Request $request)
	{
		$UserID = Session::get('UserID');
		$GetOnBoardingQuizQuestion = $this->UserModel->GetOnBoardingQuizQuestion($UserID);
		if($GetOnBoardingQuizQuestion['Status']==1)
		{
			return redirect('user-dashboard');
		}
		else
		{
			$GetTotaldQuestion = $this->UserModel->GetTotaldQuestion();
			$QuestionNo = $this->UserModel->GetTotalAttempedQuestion($UserID)+1;

			$GetQuestion = $GetOnBoardingQuizQuestion['GetQuestion'];

			$Choice = json_decode($GetQuestion->choice);

			$Bar = $QuestionNo * (100/$GetTotaldQuestion);
			?>
			<input type="hidden" value="<?php echo $GetQuestion->id; ?>" id="QuestionID">
			<div class="cus_prog">
				<div class="container">
					<div class="cus_prog_cont">
						<div class="progress">
							<div class="progress-bar" role="progressbar" 
								style="width:<?php echo round($Bar); ?>%;" aria-valuenow="<?php echo round($Bar); ?>" aria-valuemin="0" 
								aria-valuemax="100">
								<span><?php echo round($Bar); ?>%</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="on_board_cont read">
				<div class="container">
					<div class="quiz_cont">
						<h3><?php echo $QuestionNo.'. '.$GetQuestion->question; ?></h3>
						<div class="quiz_ques cus_radio">
						 	<?php foreach($Choice as $key=>$value){ ?>
							<div class="custom-control custom-radio">
								<input onclick='Hide("Err");' type="radio" value="<?php echo $key; ?>" class="custom-control-input" id="customRadio_<?php echo $key; ?>" name="exampleRadios"/>
								<label class="custom-control-label" for="customRadio_<?php echo $key; ?>"><?php echo $value; ?></label>
							</div>
							<?php } ?>
						</div>
					</div>
					<center><span id='Err'></span></center>
				</div>
			</div>
			<div class="board_footer text-center">
				<div class="container">
					<a href="javascript:void(0);" onclick="CheckQuestion();" class="btn cus_btn big">Check</a>
				</div>
			</div>
			<?php
		}

		exit();
	}

	public function GetOnBoardingQuizResult(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();

		$QuestionID		= $Data['QuestionID'];
		$Selected 		= $Data['Selected'];

		$SaveResult  	= $this->UserModel->SaveResult($QuestionID,$Selected,$UserID);

		$GetQuestion 	= $this->UserModel->GetQuestion($QuestionID);

		$GetTotaldQuestion = $this->UserModel->GetTotaldQuestion();
		$QuestionNo 	= $this->UserModel->GetTotalAttempedQuestion($UserID);	

		$Choice 		= json_decode($GetQuestion->choice);
		$RightAnswer 	= $GetQuestion->answer;

		$Bar = $QuestionNo * (100/$GetTotaldQuestion);
		?>
			<div class="cus_prog">
				<div class="container">
					<div class="cus_prog_cont">
						<div class="progress">
							<div class="progress-bar" role="progressbar" 
								style="width:<?php echo round($Bar); ?>%;" aria-valuenow="<?php echo round($Bar); ?>" aria-valuemin="0" 
								aria-valuemax="100">
								<span><?php echo round($Bar); ?>%</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="on_board_cont read">
				<div class="container">
					<div class="quiz_cont">
						<h3><?php echo $QuestionNo.'. '.$GetQuestion->question; ?></h3>
						<div class="quiz_ques cus_radio">
						 	<?php foreach($Choice as $key=>$value){ ?>
							<div class="custom-control custom-radio <?php if($RightAnswer==$key){ echo "right"; } else { echo "wrong"; }?>">
								<input disabled <?php if($Selected==$key){ echo "checked"; }?> onclick='Hide("Err");' type="radio" value="<?php echo $key; ?>" class="custom-control-input" id="customRadio_<?php echo $key; ?>" name="exampleRadios"/>
								<label class="custom-control-label" for="customRadio_<?php echo $key; ?>"><?php echo $value; ?></label>
							</div>
							<?php } ?>
						</div>
						<?php if($RightAnswer==$Selected){ ?>
						<div class="correct">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z"/></svg>
							<span>You are correct</span>
						</div>
						<?php } else {?>
						<div class="correct wr">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm121.6 313.1c4.7 4.7 4.7 12.3 0 17L338 377.6c-4.7 4.7-12.3 4.7-17 0L256 312l-65.1 65.6c-4.7 4.7-12.3 4.7-17 0L134.4 338c-4.7-4.7-4.7-12.3 0-17l65.6-65-65.6-65.1c-4.7-4.7-4.7-12.3 0-17l39.6-39.6c4.7-4.7 12.3-4.7 17 0l65 65.7 65.1-65.6c4.7-4.7 12.3-4.7 17 0l39.6 39.6c4.7 4.7 4.7 12.3 0 17L312 256l65.6 65.1z"/></svg>
							<span>You are wrong</span>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="board_footer text-center">
				<div class="container">
					<?php if($QuestionNo == $GetTotaldQuestion){?>
					<a href="<?php echo route('UserDashboard'); ?>" class="btn cus_btn big">Complete Your Quiz</a>
					<?php } else {?>
					<a href="javascript:void(0);" onclick="ContinueQuestion();" class="btn cus_btn big">Continue</a>
					<?php }?>
				</div>
			</div> 
			
			<?php
		exit();
	}

	//////////////////////////////////////////////////////////////

	public function GetProfileDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$UserDetails = $this->UserModel->UserDetails($UserID);
		?>
			<div class="col-md-12 mb-2"><h3>Edit Profile</h3></div>
			<form action="<?php echo route('UpdateProfileDetails'); ?>" id="ProfileEditForm" name="ProfileEditForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<input type='hidden' id='latitude' name='latitude' value=''>
				<input type='hidden' id='longitude' name='longitude' value=''>

				<div class="col-md-12">
					<div class="edit_profile_cont">
						<div class="lhs">
							<div class="pro_img">
								<div class="edit_pik">
									<img src="<?php echo asset('public/Front/Design/img/camera.png'); ?>" alt="" />
									<input type="file" name="Image" id="Image" accept="image/*" />
								</div>
								<?php if($UserDetails->image==''){?>
								<img id="imagePrev" src="<?php echo asset('public/Front/Design/img/pro_pic.png'); ?>" alt="" />
								<?php } else {?>
								<img id="imagePrev" src="<?php echo asset('public/Front/Users/Profile').'/'.$UserDetails->image; ?>" alt="" />
								<?php }?>								
							</div>
						</div>
						<div class="rhs pro_img_right">
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="input_label2">First Name<span>*</span></label>
										<input name="FirstName" id="FirstName" onkeypress="Hide('FirstNameErr');" 
										type="text" class="form-control inputfield2" 
										placeholder="Enter First Name" value="<?php echo $UserDetails->first_name; ?>">
									</div>
									<span id="FirstNameErr"></span>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="input_label2">Last Name</label>
										<input name="LastName" id="LastName" onkeypress="Hide('LastNameErr');"  
										type="text" class="form-control inputfield2" 
										placeholder="Enter Last Name" value="<?php echo $UserDetails->last_name; ?>">
									</div>
									<span id="LastNameErr"></span>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="input_label2">Gender</label>
										<select name="Gender" id="Gender" onchange="Hide('GenderErr');" class="form-control inputfield2" >
											<option value="0" <?php if($UserDetails->gender=='0'){ echo "selected";}?>>Select Gender</option>
											<option value="1" <?php if($UserDetails->gender=='1'){ echo "selected";}?>>Male</option>
											<option value="2" <?php if($UserDetails->gender=='2'){ echo "selected";}?>>Female</option>
										</select>
									</div>
									<span id="GenderErr"></span>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="input_label2">Date of Birth</label>
										<input name="DOB" id="DOB" onchange="Hide('DOBErr');" type="text" 
										class="form-control inputfield2" 
										placeholder="DD-MM-YYYY" value="<?php echo $UserDetails->dob; ?>">
									</div>
									<span id="DOBErr"></span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12 text-center">
					<a href="javascript:void(0);" onclick="UpdateProfile();" class="btn cus_btn big">Update Profile</a>
				</div>
			</form>
			<script type="text/javascript">
				$('#DOB').Zebra_DatePicker({
				  direction: ['<?=date('Y-m-d', strtotime('-70 years'));?>', '<?=date('Y-m-d', strtotime('-10 years'))?>']
				});
				$("#Image").change(function() {
				  readURL(this);
				})
			</script>
		<?php
	}
	public function UpdateProfileDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$File 		= $request->file('Image');
	    if(!empty($File)){
		    $Path = 'public/Front/Users/Profile';
	        $ImageName = $File->getClientOriginalName();
	        $Upload = $File->move($Path, $ImageName);
	        $Details['image'] 		= $ImageName;
        }
        $Details['first_name'] 	= $Data['FirstName'];
        $Details['last_name'] 	= $Data['LastName'];
        $Details['gender'] 		= $Data['Gender'];
        $Details['dob'] 		= $Data['DOB'];
        
       
        $Save = $this->UserModel->UpdateProfileDetails($UserID,$Details);
        if($Save)
		{
			Session::flash('message', 'Profile Details Has been Updated.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}

	public function UpdateLocation(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
	    $Details['location'] 	= $Data['Location'];
	    $Details['latitude'] 	= $Data['latitude'];
	    $Details['longitude'] 	= $Data['longitude'];
	    $this->UserModel->UpdateProfileDetails($UserID,$Details);
	}

	public function GetAboutMeDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$UserDetails = $this->UserModel->UserDetails($UserID);
		?>
			<form action="<?php echo route('UpdateAboutMeDetails'); ?>" id="AboutMeEditForm" name="AboutMeEditForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<div class="row">
					<div class="col-md-12 mb-2"><h3>Edit About Profile</h3></div>
					
					<div class="col-md-12">
						<div class="form-group">
							<label class="input_label2">About Me<span>*</span></label>
							<textarea name="AboutMe" id="AboutMe" onkeypress="Hide('AboutMeErr');"  
							type="text" class="form-control inputfield2" 
							placeholder="Enter About Me" ><?php echo $UserDetails->about; ?></textarea>
						</div>
						<span id="AboutMeErr"></span>
					</div>
					<div class="col-md-12 text-center">
						<a href="javascript:void(0);" onclick="UpdateAboutMe();" class="btn cus_btn big">Update</a>
					</div>
					
				</div>
			</form>
		<?php
	}
	public function UpdateAboutMeDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
        $Details['about'] 	= $Data['AboutMe'];
       
        $Save = $this->UserModel->UpdateProfileDetails($UserID,$Details);
        if($Save)
		{
			Session::flash('message', 'About Your Profile Has been Updated.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}

	public function GetExperienceAddModal(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$UserDetails = $this->UserModel->UserDetails($UserID);
		?>
			<div class="col-md-12 mb-2"><h3>Add Experience</h3></div>
			<form action="<?php echo route('AddExperienceDetails'); ?>" id="AddExperienceForm" name="AddExperienceForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="input_label2">Job Title<span>*</span></label>
								<input name="JobTitle" id="JobTitle" onkeypress="Hide('JobTitleErr');" type="text" class="form-control inputfield2" placeholder="Job Title">
								<span id="JobTitleErr"></span>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="input_label2">Company Name<span>*</span></label>
								<input name="CompanyName" id="CompanyName" onkeypress="Hide('CompanyNameErr');" type="text" class="form-control inputfield2" placeholder="Company Name">
								<span id="CompanyNameErr"></span>
							</div>
						</div>
						<div class="col-md-5">
							<div class="form-group">
								<label class="input_label2">Start Working From<span>*</span></label>
								<input name="From" id="From" onkeypress="Hide('FromErr');" type="text" class="form-control inputfield2">
								<span id="FromErr"></span>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label class="input_label2">Still Working</label>
								<input name="Still" id="Still" onclick="ValidateDate();" type="checkbox" class="form-control inputfield2" placeholder="Enter Company Name">
								<span id="StillErr"></span>
							</div>
						</div>
						<div class="col-md-5">
							<div class="form-group">
								<label class="input_label2">Worked Till<span>*</span></label>
								<input name="To" id="To" onkeypress="Hide('ToErr');" type="text" class="form-control inputfield2">
								<span id="ToErr"></span>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="input_label2">Location</label>
								<input name="Location" id="Location" onkeypress="Hide('LocationErr');" type="text" class="form-control inputfield2" placeholder="Location">
								<span id="LocationErr"></span>
							</div>
						</div>						
					</div>	
				</div>
				<div class="col-md-12 text-center">
					<a href="javascript:void(0);" onclick="AddExperience();" class="btn cus_btn big">Add Experience</a>
				</div>
			</form>
			<script type="text/javascript">
				$('#From').Zebra_DatePicker({
				  direction: ['<?=date('Y-m-d', strtotime('-20 years'));?>', '<?=date('Y-m-d')?>'],
				  pair: $('#To')
				});
				$('#To').Zebra_DatePicker({
				  direction: 1
				});
			</script>
		<?php
	}
	public function AddExperienceDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
        $Details['profile_id'] 	= $UserID;
        $Details['job_title'] 	= $Data['JobTitle'];
        $Details['company']		= $Data['CompanyName'];
        $Details['from'] 		= $Data['From'];
        if(isset($Data['Still']))
        {
        	$Details['still'] 		= 1;
        	$Details['to'] 			= '';
        }
        else
        {
        	$Details['still'] 		= 0;
        	$Details['to'] 			= $Data['To'];        	
        }
        $Details['location'] 	= $Data['Location'];
       
        $Save = $this->UserModel->AddExperienceDetails($Details);
        if($Save)
		{
			Session::flash('message', 'Experience Details Has been Added.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}

	public function GetExperienceDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$ExpID		= $Data['ExpID'];
		$Experience = $this->UserModel->GetExperienceDetails($UserID,$ExpID);
		?>
			<div class="col-md-12 mb-2"><h3>Edit Experience</h3></div>
			<form action="<?php echo route('UpdateExperienceDetails'); ?>" id="AddExperienceForm" name="AddExperienceForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<input type="hidden" name="ExpID" id="ExpID" value="<?php echo $ExpID; ?>">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="input_label2">Job Title<span>*</span></label>
								<input name="JobTitle" id="JobTitle" onkeypress="Hide('JobTitleErr');" 
								type="text" class="form-control inputfield2"
								placeholder="Job Title" value="<?php echo $Experience->job_title; ?>">
								<span id="JobTitleErr"></span>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="input_label2">Company Name<span>*</span></label>
								<input name="CompanyName" id="CompanyName" onkeypress="Hide('CompanyNameErr');" 
								type="text" class="form-control inputfield2" 
								placeholder="Company Name" value="<?php echo $Experience->company; ?>">
								<span id="CompanyNameErr"></span>
							</div>
						</div>
						<div class="col-md-5">
							<div class="form-group">
								<label class="input_label2">Start Working From<span>*</span></label>
								<input name="From" id="From" onkeypress="Hide('FromErr');" 
								type="text" class="form-control inputfield2" value="<?php echo $Experience->from; ?>">
								<span id="FromErr"></span>
							</div>
						</div>
						<?php 
						$Checked="";
						$Disabled="";
						if($Experience->still=='1')
						{
							$Checked="checked";							
							$Disabled="disabled";							
						} 
						?>
						<div class="col-md-2">
							<div class="form-group">
								<label class="input_label2">Still Working</label>
								<input name="Still" id="Still" onclick="ValidateDate();" 
								type="checkbox" class="form-control inputfield2" 
								placeholder="Enter Company Name" <?php echo $Checked; ?>>
								<span id="StillErr"></span>
							</div>
						</div>
						<div class="col-md-5">
							<div class="form-group">
								<label class="input_label2">Worked Till<span>*</span></label>
								<input name="To" id="To" onkeypress="Hide('ToErr');" 
								type="text" class="form-control inputfield2" <?php echo $Disabled; ?>
								placeholder="Choose Availability" value="<?php echo $Experience->to; ?>">
								<span id="ToErr"></span>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="input_label2">Location</label>
								<input name="Location" id="Location" onkeypress="Hide('LocationErr');" 
								type="text" class="form-control inputfield2" 
								placeholder="Location" value="<?php echo $Experience->location; ?>">
								<span id="LocationErr"></span>
							</div>
						</div>						
					</div>	
				</div>
				<div class="col-md-12 text-center">
					<a href="javascript:void(0);" onclick="AddExperience();" class="btn cus_btn big">Update Experience</a>
				</div>
			</form>
			<script type="text/javascript">
				$('#From').Zebra_DatePicker({
				  direction: true,
				  pair: $('#To')
				});
				$('#To').Zebra_DatePicker({
				  direction: 1
				});
			</script>
		<?php
	}
	public function UpdateExperienceDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
        $ExpID 					= $Data['ExpID'];
        $Details['profile_id'] 	= $UserID;
        $Details['job_title'] 	= $Data['JobTitle'];
        $Details['company']		= $Data['CompanyName'];
        $Details['from'] 		= $Data['From'];
        if(isset($Data['Still']))
        {
        	$Details['still'] 		= 1;
        	$Details['to'] 			= '';
        }
        else
        {
        	$Details['still'] 		= 0;
        	$Details['to'] 			= $Data['To'];        	
        }
        $Details['location'] 	= $Data['Location'];
       
        $Save = $this->UserModel->UpdateExperienceDetails($ExpID,$Details);
        if($Save)
		{
			Session::flash('message', 'Experience Details Has been Updated.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}

	public function GetPreferencesAddModal(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$JobCategory = $this->UserModel->GetJobCategory();
		?>
			<div class="col-md-12 mb-2"><h3>Add Preferences</h3></div>
				<form action="<?php echo route('AddPreferencesDetails'); ?>" id="AddPreferencesForm" name="AddPreferencesForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-5">
							<div class="form-group">
								<label class="input_label2">Job Category<span>*</span></label>
								<select name="JobCategory" id="JobCategory" onchange="Hide('JobCategoryErr'),GetSubCategory();" 
									type="text" class="form-control inputfield2">
									<option value="">Select Sub Category<option>
								<?php foreach($JobCategory as $jc) { ?>
									<option value="<?php echo $jc->id; ?>"><?php echo $jc->category; ?><option>
								<?php } ?>
								</select>
								<span id="JobCategoryErr"></span>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="input_label2">Sub Category<span>*</span></label>
								<select name="SubCategory" id="SubCategory" onchange="Hide('SubCategoryErr');" 
									type="text" class="form-control inputfield2" >
									<option value="">Select Sub Category<option>
								</select>
								<span id="SubCategoryErr"></span>
							</div>
						</div>	
						<div class="col-md-3">
							<div class="form-group">
								<label class="input_label2">Pay Rate($/Hr)<span>*</span></label>
								<input name="PayRate" id="PayRate" onkeypress="Hide('PayRateErr'),isNumber(event);" 
								type="text" class="form-control inputfield2"
								placeholder="Pay Rate" maxlength="4">
								<span id="PayRateErr"></span>
							</div>
						</div>										
					</div>	
				</div>
				<div class="col-md-12 text-center">
					<a href="javascript:void(0);" onclick="AddPreferences();" class="btn cus_btn big">Add Preferences</a>
				</div>
				</div>
			</form>
			<script type="text/javascript">
				$(document).ready(function(){
			    $('#PayRate').keypress(function(event) {
			    if((event.which != 46 || $(this).val().indexOf('.') != -1) && ((event.which < 48 || event.which > 57) && (event.which != 0 && event.which != 8))) {
			        event.preventDefault();
			        }
			    var text = $(this).val();
			    if((text.indexOf('.') != -1) && (text.substring(text.indexOf('.')).length > 2) && (event.which != 0 && event.which != 8) && ($(this)[0].selectionStart >= text.length - 2)) {
			        event.preventDefault();
			        }
			    });
				});
			</script>
		<?php
	}
	public function GetSubCategory(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$CategoryID = $Data['Category'];
		$SubCategory = $this->UserModel->GetSubCategory($CategoryID);
		?>
		<option value="">Select Sub Category</option>
		<?php
		foreach($SubCategory as $sc)
		{
			?>
			<option value="<?php echo $sc->id; ?>"><?php echo $sc->position; ?></option>
			<?php
		}
		exit();
	}
	public function CheckSubCategoryForAdd(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
        $JobCategory	= $Data['JobCategory'];
        $SubCategory	= $Data['SubCategory'];
        
        echo $this->UserModel->CheckSubCategoryForAdd($UserID,$JobCategory,$SubCategory);        
      	exit();
	}
	public function AddPreferencesDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
        $Details['profile_id'] 	= $UserID;
        $Details['job_cat'] 	= $Data['JobCategory'];
        $Details['job_sub_cat']	= $Data['SubCategory'];
        $Details['pay_rate'] 	= $Data['PayRate'];
        
        $Save = $this->UserModel->AddPreferencesDetails($Details);
        if($Save)
		{
			Session::flash('message', 'Preferences Details Has been Added.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}
	public function GetPreferenceDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$PrefID 	= $Data['PrefID'];
		$Preference 	= $this->UserModel->GetPreferenceDetails($UserID,$PrefID);
		$CatID 			= $Preference->job_cat;
		$JobCategory 	= $this->UserModel->GetJobCategory();
		$JobSubCategory = $this->UserModel->GetSubCategory($CatID);

		?>
			<div class="col-md-12 mb-2"><h3>Edit Preferences</h3></div>
				<form action="<?php echo route('UpdatePreferencesDetails'); ?>" id="EditPreferencesForm" name="EditPreferencesForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<input type="hidden" name="PrefID" id="PrefID" value="<?php echo $Preference->id; ?>">
				<input type="hidden" name="OldSubCat" id="OldSubCat" value="<?php echo $Preference->job_sub_cat; ?>">
				<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-5">
							<div class="form-group">
								<label class="input_label2">Job Category<span>*</span></label>
								<select name="JobCategory" id="JobCategory" onchange="Hide('JobCategoryErr'),GetSubCategory();" 
									type="text" class="form-control inputfield2">
									<option value="">Select Sub Category<option>
									<?php foreach($JobCategory as $jc) { ?>
										<option value="<?php echo $jc->id; ?>" 
											<?php if($jc->id==$Preference->job_cat){ echo "selected";} ?>><?php echo $jc->category; ?><option>
									<?php } ?>
								</select>
								<span id="JobCategoryErr"></span>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="input_label2">Sub Category<span>*</span></label>
								<select name="SubCategory" id="SubCategory" onchange="Hide('SubCategoryErr');" 
									type="text" class="form-control inputfield2" >
									<option value="">Select Sub Category<option>
									<?php foreach($JobSubCategory as $jsc) { ?>
										<option value="<?php echo $jsc->id; ?>"
											<?php if($jsc->id==$Preference->job_sub_cat){ echo "selected";} ?>><?php echo $jsc->position; ?><option>
									<?php } ?>
								</select>
								<span id="SubCategoryErr"></span>
							</div>
						</div>	
						<div class="col-md-3">
							<div class="form-group">
								<label class="input_label2">Pay Rate($/Hr)<span>*</span></label>
								<input name="PayRate" id="PayRate" onkeypress="Hide('PayRateErr'),isNumber(event);" 
								type="text" class="form-control inputfield2"
								placeholder="Pay Rate" value="<?php echo $Preference->pay_rate; ?>" maxlength="4">
								<span id="PayRateErr"></span>
							</div>
						</div>										
					</div>	
				</div>
				<div class="col-md-12 text-center">
					<a href="javascript:void(0);" onclick="UpdatePreferences();" class="btn cus_btn big">Update Preferences</a>
				</div>
				</div>
			</form>
			<script type="text/javascript">
				$(document).ready(function(){
			    $('#PayRate').keypress(function(event) {
			    if((event.which != 46 || $(this).val().indexOf('.') != -1) && ((event.which < 48 || event.which > 57) && (event.which != 0 && event.which != 8))) {
			        event.preventDefault();
			        }
			    var text = $(this).val();
			    if((text.indexOf('.') != -1) && (text.substring(text.indexOf('.')).length > 2) && (event.which != 0 && event.which != 8) && ($(this)[0].selectionStart >= text.length - 2)) {
			        event.preventDefault();
			        }
			    });
				});
			</script>
		<?php
	}
	public function CheckSubCategoryForEdit(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
        $JobCategory	= $Data['JobCategory'];
        $SubCategory	= $Data['SubCategory'];
        $OldSubCat		= $Data['OldSubCat'];
        
        echo $this->UserModel->CheckSubCategoryForEdit($UserID,$JobCategory,$SubCategory,$OldSubCat);        
      	exit();
	}
	public function UpdatePreferencesDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
        $PrefID		= $Data['PrefID'];
        $Details['profile_id'] 	= $UserID;
        $Details['job_cat'] 	= $Data['JobCategory'];
        $Details['job_sub_cat']	= $Data['SubCategory'];
        $Details['pay_rate'] 	= $Data['PayRate'];
        
        $Save = $this->UserModel->UpdatePreferencesDetails($PrefID,$Details);
        if($Save)
		{
			Session::flash('message', 'Preferences Details Has been Updated.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}

	public function GetEducationAddModal(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$UserDetails = $this->UserModel->UserDetails($UserID);
		?>
			<div class="col-md-12 mb-2"><h3>Add Education</h3></div>
			<form action="<?php echo route('AddEducationDetails'); ?>" id="AddEducationForm" name="AddEducationForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="input_label2">School/College/Institute<span>*</span></label>
								<input name="Institute" id="Institute" onkeypress="Hide('InstituteErr');" type="text" class="form-control inputfield2" placeholder="School/College/Institute">
								<span id="InstituteErr"></span>
							</div>
						</div>	
						<div class="col-md-6">
							<div class="form-group">
								<label class="input_label2">Degree<span>*</span></label>
								<input name="Degree" id="Degree" onkeypress="Hide('DegreeErr');" type="text" class="form-control inputfield2" placeholder="Degree Name">
								<span id="DegreeErr"></span>
							</div>
						</div>					
						<div class="col-md-5">
							<div class="form-group">
								<label class="input_label2">Start Year<span>*</span></label>
								<select name="From" id="From" onchange="Hide('FromErr');" type="date" class="form-control inputfield2">
									<option value="">Select Start Year</option>
									<?php for($i=1980;$i<=date('Y');$i++){?>
									<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
									<?php }?>
								</select>
								<span id="FromErr"></span>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label class="input_label2">Persuing</label>
								<input name="Persuing" id="Persuing" onclick="ValidateDate1();" type="checkbox" class="form-control inputfield2">
								<span id="PersuingErr"></span>
							</div>
						</div>
						<div class="col-md-5">
							<div class="form-group">
								<label class="input_label2">Graduation Year<span>*</span></label>
								<select name="To" id="To" onchange="Hide('ToErr');" type="date" class="form-control inputfield2">
									<option value="">Select Passout Year</option>
									<?php for($i=1980;$i<=date('Y');$i++){?>
									<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
									<?php }?>
								</select>
								<span id="ToErr"></span>
							</div>
						</div>						
					</div>	
				</div>
				<div class="col-md-12 text-center">
					<a href="javascript:void(0);" onclick="AddEducation();" class="btn cus_btn big">Add Education</a>
				</div>
			</form>
		<?php
	}
	public function AddEducationDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
        $Details['profile_id'] 	= $UserID;
        $Details['institute'] 	= $Data['Institute'];
        $Details['degree']		= $Data['Degree'];
        $Details['from'] 		= $Data['From'];
        if(isset($Data['Persuing']))
        {
        	$Details['persuing'] 		= 1;
        	$Details['to'] 			= '';
        }
        else
        {
        	$Details['persuing'] 		= 0;
        	$Details['to'] 			= $Data['To'];        	
        }
        $Save = $this->UserModel->AddEducationDetails($Details);
        if($Save)
		{
			Session::flash('message', 'Education Details Has been Added.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}
	public function GetEducationDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$EduID 		= $Data['EduID'];
		$Education = $this->UserModel->GetEducationDetails($UserID,$EduID);
		?>
			<div class="col-md-12 mb-2"><h3>Add Education</h3></div>
			<form action="<?php echo route('UpdateEducationDetails'); ?>" id="AddEducationForm" name="AddEducationForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<input type="hidden" name="EduID" id="EduID" value="<?php echo $Education->id; ?>">
				<div class="col-md-12">
					<div class="row">
						
						<div class="col-md-6">
							<div class="form-group">
								<label class="input_label2">School/College/Institute<span>*</span></label>
								<input name="Institute" id="Institute" onkeypress="Hide('InstituteErr');" 
								type="text" class="form-control inputfield2" 
								placeholder="School/College/Institute" value="<?php echo $Education->institute; ?>">
								<span id="InstituteErr"></span>
							</div>
						</div>	
						<div class="col-md-6">
							<div class="form-group">
								<label class="input_label2">Degree<span>*</span></label>
								<input name="Degree" id="Degree" onkeypress="Hide('DegreeErr');" 
								type="text" class="form-control inputfield2" 
								placeholder="Degree Name" value="<?php echo $Education->degree; ?>">
								<span id="DegreeErr"></span>
							</div>
						</div>					
						<div class="col-md-5">
							<div class="form-group">
								<label class="input_label2">Start Year<span>*</span></label>
								<select name="From" id="From" onchange="Hide('FromErr');" type="date" class="form-control inputfield2">
									<option value="">Select Start Year</option>
									<?php for($i=1980;$i<=date('Y');$i++){?>
									<option value="<?php echo $i; ?>"
										<?php if($i==$Education->from){ echo "selected";} ?>><?php echo $i; ?></option>
									<?php }?>
								</select>
								<span id="FromErr"></span>
							</div>
						</div>
						<?php 
						$Checked="";
						$Disabled="";
						if($Education->persuing=='1')
						{
							$Checked="checked";							
							$Disabled="disabled";							
						} 
						?>
						<div class="col-md-2">
							<div class="form-group">
								<label class="input_label2">Persuing</label>
								<input name="Persuing" id="Persuing" onclick="ValidateDate1();" 
								type="checkbox" class="form-control inputfield2" <?php echo $Checked; ?>>
								<span id="PersuingErr"></span>
							</div>
						</div>
						<div class="col-md-5">
							<div class="form-group">
								<label class="input_label2">Graduation Year<span>*</span></label>
								<select name="To" id="To" onchange="Hide('ToErr');" type="date" 
								class="form-control inputfield2" <?php echo $Disabled; ?>>
									<option value="">Select Passout Year</option>
									<?php for($i=1980;$i<=date('Y');$i++){?>
									<option value="<?php echo $i; ?>"
										<?php if($i==$Education->to){ echo "selected";} ?>><?php echo $i; ?></option>
									<?php }?>
								</select>
								<span id="ToErr"></span>
							</div>
						</div>						
					</div>	
				</div>
				<div class="col-md-12 text-center">
					<a href="javascript:void(0);" onclick="AddEducation();" class="btn cus_btn big">Update Education</a>
				</div>
			</form>
		<?php
	}
	public function UpdateEducationDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
        $EduID 	= $Data['EduID'];
        $Details['profile_id'] 	= $UserID;
        $Details['institute'] 	= $Data['Institute'];
        $Details['degree']		= $Data['Degree'];
        $Details['from'] 		= $Data['From'];
        if(isset($Data['Persuing']))
        {
        	$Details['persuing'] 		= 1;
        	$Details['to'] 			= '';
        }
        else
        {
        	$Details['persuing'] 		= 0;
        	$Details['to'] 			= $Data['To'];        	
        }
        $Save = $this->UserModel->UpdateEducationDetails($EduID,$Details);
        if($Save)
		{
			Session::flash('message', 'Education Details Has been Updated.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}

	public function GetLanguageAddModal(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$UserDetails = $this->UserModel->UserDetails($UserID);
		$LanguageList = $this->UserModel->LanguageList();
		?>
			<div class="col-md-12 mb-2"><h3>Add Language</h3></div>
			<form action="<?php echo route('AddLanguageDetails'); ?>" id="AddLanguageForm" name="AddLanguageForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label class="input_label2">Language<span>*</span></label>
									<select name="Language" id="Language" onchange="Hide('LanguageErr');" class="form-control inputfield2">
										<option value="" selected="">Select Language</option>
										<?php
										foreach ($LanguageList as $lang) {
											echo '<option value="'.$lang->language.'">'.$lang->language.'</option>';
										}
										?>
									</select>
									<span id="LanguageErr"></span>
								</div>
							</div>						
							<div class="col-md-6">
								<div class="form-group">
									<label class="input_label2">Proficiency<span>*</span></label>
									<select name="Proficiency" id="Proficiency" onchange="Hide('ProficiencyErr');" type="date" class="form-control inputfield2">
										<option value="">Select Proficiency</option>
										<option value="1">Beginner</option>
										<option value="2">Intermediate</option>
										<option value="3">Proficient</option>
										
									</select>
									<span id="ProficiencyErr"></span>
								</div>
							</div>												
						</div>	
					</div>
					<div class="col-md-12 text-center">
						<a href="javascript:void(0);" onclick="AddLanguage();" class="btn cus_btn big">Add Language</a>
					</div>
				</div>
			</form>
		<?php
	}
	public function AddLanguageDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
        $Details['profile_id'] 	= $UserID;
        $Details['language'] 	= $Data['Language'];
        $Details['level']		= $Data['Proficiency'];       
        $Save = $this->UserModel->AddLanguageDetails($Details);
        if($Save)
		{
			Session::flash('message', 'Language Details Has been Added.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}
	public function GetLanguageDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$LangID 	= $Data['LangID'];
		$Language 	= $this->UserModel->GetLanguageDetails($UserID,$LangID);
		$LanguageList 	= $this->UserModel->LanguageList();
		?>
			<div class="col-md-12 mb-2"><h3>Edit Language</h3></div>
			<form action="<?php echo route('UpdateLanguageDetails'); ?>" id="AddLanguageForm" name="AddLanguageForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<input type="hidden" name="LangID" id="LangID" value="<?php echo $Language->id; ?>">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label class="input_label2">Language<span>*</span></label>

									<select name="Language" id="Language" onchange="Hide('LanguageErr');" class="form-control inputfield2">
										<option value="" selected="">Select Language</option>
										<?php
										foreach ($LanguageList as $lang) {
											$selected = '';
											if($lang->language==$Language->language){
												$selected = 'selected';
											}
											echo '<option value="'.$lang->language.'" '.$selected.'>'.$lang->language.'</option>';
										}
										?>
									</select>	
									<span id="LanguageErr"></span>
								</div>
							</div>						
							<div class="col-md-6">
								<div class="form-group">
									<label class="input_label2">Proficiency<span>*</span></label>
									<select name="Proficiency" id="Proficiency" onchange="Hide('ProficiencyErr');" type="date" class="form-control inputfield2">
										<option value="">Select Proficiency</option>
										<option value="1" <?php if($Language->level=='1'){ echo "selected";} ?>>Beginner</option>
										<option value="2" <?php if($Language->level=='2'){ echo "selected";} ?>>Intermediate</option>
										<option value="3" <?php if($Language->level=='3'){ echo "selected";} ?>>Proficient</option>
										
									</select>
									<span id="ProficiencyErr"></span>
								</div>
							</div>												
						</div>	
					</div>
					<div class="col-md-12 text-center">
						<a href="javascript:void(0);" onclick="AddLanguage();" class="btn cus_btn big">Update Language</a>
					</div>
				</div>
			</form>
		<?php
	}
	public function UpdateLanguageDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
        $LangID 	= $Data['LangID'];
       	$Details['profile_id'] 	= $UserID;
        $Details['language'] 	= $Data['Language'];
        $Details['level']		= $Data['Proficiency']; 
        $Save = $this->UserModel->UpdateLanguageDetails($LangID,$Details);
        if($Save)
		{
			Session::flash('message', 'Language Details Has been Updated.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}

	public function GetAppearanceDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$HairColor 	= $this->UserModel->GetHairColor();
		$EyeColor 	= $this->UserModel->GetEyeColor($UserID);
		$Appearance = $this->UserModel->GetAppearanceDetails($UserID);
		$UserDetails = $this->UserModel->UserDetails($UserID);
		$MyHairColor = '';
		$MyEyeColor = '';
		$MyHeight 	= '';
		$MyWeight 	= '';
		if(!empty($Appearance))
		{
			$MyHairColor = $Appearance->hair_color;
			$MyEyeColor = $Appearance->eye_color;
			$MyHeight 	= $Appearance->height;
			$MyWeight 	= $Appearance->weight;
		}
		?>
			<div class="col-md-12 mb-2"><h3>Edit Physical Information</h3></div>
			<form action="<?php echo route('UpdateAppearanceDetails'); ?>" id="AddAppearanceForm" name="AddAppearanceForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label class="input_label2">Hair Color</label>
							<select id="HairColor" name="HairColor" onchange="Hide('HairColorErr');" class="form-control inputfield2">
								<option value="">Select Hair Color</option>
								<?php foreach($HairColor as $hc){?>
								<option value="<?php echo $hc->id; ?>"
									<?php if($MyHairColor==$hc->id ){ echo "selected"; } ?>><?php echo $hc->color; ?></option>
								<?php } ?>
							</select>
							<span id="HairColorErr"></sapn>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="input_label2">Eye Color</label>
							<select id="EyeColor" name="EyeColor" onchange="Hide('EyeColorErr');" class="form-control inputfield2">
								<option value="">Select Eyes Color</option>
								<?php foreach($EyeColor as $ec){?>
								<option value="<?php echo $ec->id; ?>"
									<?php if($MyEyeColor==$ec->id ){ echo "selected"; } ?>><?php echo $ec->color; ?></option>
								<?php } ?>
							</select>
							<span id="EyeColorErr"></sapn>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="input_label2">Height</label>
							<select id="Height" name="Height" onchange="Hide('HeightErr');"  class="form-control inputfield2">
								<option value="">Select Height</option>
								<?php
								for ($i=100; $i <= 250  ; $i++) {
									$Selected = '';
									if($MyHeight==$i){ $Selected = "selected"; } 
									echo '<option value="'.$i.'" '.$Selected.' >'.$i.'</option>';
								}
								?>
							</select>
							<span id="HeightErr"></sapn>
						</div>
					</div>					
					<div class="col-md-4">
						<div class="form-group">
							<label class="input_label2">Gender</label>
							<select name="Gender" id="Gender" onchange="Hide('GenderErr');" class="form-control inputfield2" >
								<option value="0" <?php if($UserDetails->gender=='0'){ echo "selected";}?>>Select Gender</option>
								<option value="1" <?php if($UserDetails->gender=='1'){ echo "selected";}?>>Male</option>
								<option value="2" <?php if($UserDetails->gender=='2'){ echo "selected";}?>>Female</option>
							</select>
						</div>
						<span id="GenderErr"></span>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="input_label2">Date of Birth</label>
							<input name="DOB" id="DOB" onchange="Hide('DOBErr');" type="text" 
							class="form-control inputfield2" 
							placeholder="DD-MM-YYYY" value="<?php echo $UserDetails->dob; ?>">
						</div>
						<span id="DOBErr"></span>
					</div>
					<div class="col-md-12 text-center">
						<a href="javascript:void(0);" onclick="AddAppearance();" class="btn cus_btn big">Update Appearance</a>
					</div>
				</div>
			</form>
			<script type="text/javascript">
				$('#DOB').Zebra_DatePicker({
				  direction: ['<?=date('Y-m-d', strtotime('-70 years'));?>', '<?=date('Y-m-d', strtotime('-10 years'))?>']
				});
			</script>
		<?php
	}
	public function UpdateAppearanceDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
       	$Details['profile_id'] 	= $UserID;
        $Details['hair_color'] 	= $Data['HairColor'];
        $Details['eye_color']	= $Data['EyeColor']; 
        $Details['height']		= $Data['Height']; 
        $Other['gender']		= $Data['Gender']; 
        $Other['dob']			= $Data['DOB']; 
        $Save = $this->UserModel->UpdateAppearanceDetails($UserID,$Details);
        if($Save)
		{
			$Save = $this->UserModel->UpdateProfileDetails($UserID,$Other);
			Session::flash('message', 'Physical Informations Details Has been Updated.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}

	public function GetAccreditationsAddModal(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$UserDetails = $this->UserModel->UserDetails($UserID);
		?>
			<div class="col-md-12 mb-2"><h3>Add Accreditations</h3></div>
			<form action="<?php echo route('AddAccreditationsDetails'); ?>" id="AddAccreditationsForm" name="AddAccreditationsForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label class="input_label2">Accreditations Title<span>*</span></label>
									<input id="Accreditations" name="Accreditations" onkeypress="Hide('AccreditationsErr');" type="text" class="form-control inputfield2" placeholder="Enter">
								</div>
								<span id="AccreditationsErr"></span>
							</div>
							<div class="col-md-6">
								<div class="form-group add_job_pic">
									<label class="input_label2">Upload Image</label>
									<div class="custom-file mb-3">
										<input  id="Image" name="Image" onchange="Hide('ImageErr');" type="file" class="custom-file-input inputfield2" >
										<label class="custom-file-label inputfield2" for="Image">Upload File</label>
									</div>
								</div>
								<span id="ImageErr"></span>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="input_label2">Expiration Date<span>*</span></label>
									<input id="ExpDate" name="ExpDate" onchange="Hide('ExpDateErr');" type="date" class="form-control inputfield2" placeholder="Enter">
								</div>
								<span id="ExpDateErr"></span>
							</div>
						</div>
					</div>
					<div class="col-md-12 text-center">
						<a href="javascript:void(0);" onclick="AddAccreditations();" class="btn cus_btn big">Add Accreditations</a>
					</div>
				</div>
			</form>
		<?php
	}
	public function AddAccreditationsDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
        $Details['profile_id'] 	= $UserID;
        $Details['accreditations'] 	= $Data['Accreditations'];
        $Details['exp_date']		= $Data['ExpDate']; 
        $File 		= $request->file('Image');
	    if(!empty($File)){
		    $Path = 'public/Front/Users/Accreditations';
	        $ImageName = $File->getClientOriginalName();
	        $Upload = $File->move($Path, $ImageName);
	        $Details['image'] 		= $ImageName;
        }      
        $Save = $this->UserModel->AddAccreditationsDetails($Details);
        if($Save)
		{
			Session::flash('message', 'Accreditations Details Has been Added.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}
	public function GetAccreditationsDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$AccID 	= $Data['AccID'];
		$Accreditations 	= $this->UserModel->GetAccreditationsDetails($UserID,$AccID);
		?>
			<div class="col-md-12 mb-2"><h3>Edit Accreditations</h3></div>
			<form action="<?php echo route('UpdateAccreditationsDetails'); ?>" id="EditAccreditationsForm" name="EditAccreditationsForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<input type="hidden" name="AccID" id="AccID" value="<?php echo $Accreditations->id; ?>">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label class="input_label2">Accreditations Title<span>*</span></label>
									<input id="Accreditations" name="Accreditations" 
									onkeypress="Hide('AccreditationsErr');" type="text" 
									class="form-control inputfield2" placeholder="Enter"
									value="<?php echo $Accreditations->accreditations; ?>">
								</div>
								<span id="AccreditationsErr"></span>
							</div>
							<div class="col-md-6">
								<div class="form-group add_job_pic">
									<label class="input_label2">Upload Image</label>
									<div class="custom-file mb-3">
										<input  id="Image" name="Image" onchange="Hide('ImageErr');" type="file" class="custom-file-input inputfield2" >
										<label class="custom-file-label inputfield2" for="Image">Upload File</label>
									</div>
								</div>
								<span id="ImageErr"></span>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="input_label2">Expiration Date<span>*</span></label>
									<input id="ExpDate" name="ExpDate" onchange="Hide('ExpDateErr');" 
									type="date" class="form-control inputfield2" placeholder="Enter"
									value="<?php echo $Accreditations->exp_date; ?>">
								</div>
								<span id="ExpDateErr"></span>
							</div>
						</div>
					</div>
					<div class="col-md-12 text-center">
						<a href="javascript:void(0);" onclick="UpdateAccreditations();" class="btn cus_btn big">Update Accreditations</a>
					</div>
				</div>
			</form>
		<?php
	}
	public function UpdateAccreditationsDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
        $AccID 	= $Data['AccID'];
       	$Details['profile_id'] 	= $UserID;
        $Details['accreditations'] 	= $Data['Accreditations'];
        $Details['exp_date']		= $Data['ExpDate']; 
        $File 		= $request->file('Image');
	    if(!empty($File)){
		    $Path = 'public/Front/Users/Accreditations';
	        $ImageName = $File->getClientOriginalName();
	        $Upload = $File->move($Path, $ImageName);
	        $Details['image'] 		= $ImageName;
        }    
        $Save = $this->UserModel->UpdateAccreditationsDetails($AccID,$Details);
        if($Save)
		{
			Session::flash('message', 'Accreditations Details Has been Updated.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}

	public function GetCertificationsAddModal(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$UserDetails = $this->UserModel->UserDetails($UserID);
		?>
			<div class="col-md-12 mb-2"><h3>Add Certifications</h3></div>
			<form action="<?php echo route('AddCertificationsDetails'); ?>" id="AddCertificationsForm" name="AddCertificationsForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label class="input_label2">Certifications Title<span>*</span></label>
									<input id="Certifications" name="Certifications" onkeypress="Hide('CertificationsErr');" type="text" class="form-control inputfield2" placeholder="Enter">
								</div>
								<span id="CertificationsErr"></span>
							</div>
							<div class="col-md-6">
								<div class="form-group add_job_pic">
									<label class="input_label2">Upload Image</label>
									<div class="custom-file mb-3">
										<input  id="Image" name="Image" onchange="Hide('ImageErr');" type="file" class="custom-file-input inputfield2" >
										<label class="custom-file-label inputfield2" for="Image">Upload File</label>
									</div>
								</div>
								<span id="ImageErr"></span>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<label class="input_label2">Certifications Description<span>*</span></label>
									<textarea id="Description" name="Description"
									 onkeypress="Hide('DescriptionErr');" type="text" 
									 class="form-control inputfield2" 
									 placeholder="Enter Description"></textarea>
								</div>
								<span id="DescriptionErr"></span>
							</div>
						</div>
					</div>
					<div class="col-md-12 text-center">
						<a href="javascript:void(0);" onclick="AddCertifications();" class="btn cus_btn big">Add Certifications</a>
					</div>
				</div>
			</form>
		<?php
	}
	public function AddCertificationsDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
        $Details['profile_id'] 	= $UserID;
        $Details['name'] 	= $Data['Certifications'];
        $Details['description']		= $Data['Description']; ; 
        $File 		= $request->file('Image');
	    if(!empty($File)){
		    $Path = 'public/Front/Users/Certification';
	        $ImageName = $File->getClientOriginalName();
	        $Upload = $File->move($Path, $ImageName);
	        $Details['image'] 		= $ImageName;
        }      
        $Save = $this->UserModel->AddCertificationsDetails($Details);
        if($Save)
		{
			Session::flash('message', 'Certification Details Has been Added.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}
	public function GetCertificationsDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$CertID 	= $Data['CertID'];
		$Certifications 	= $this->UserModel->GetCertificationsDetails($UserID,$CertID);
		?>
			<div class="col-md-12 mb-2"><h3>Edit Certifications</h3></div>
			<form action="<?php echo route('UpdateCertificationsDetails'); ?>" id="EditCertificationsForm" name="EditCertificationsForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<input type="hidden" name="AccID" id="AccID" value="<?php echo $Accreditations->id; ?>">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label class="input_label2">Certifications Title<span>*</span></label>
									<input id="Certifications" name="Certifications" onkeypress="Hide('CertificationsErr');" type="text" class="form-control inputfield2" placeholder="Enter">
								</div>
								<span id="CertificationsErr"></span>
							</div>
							<div class="col-md-6">
								<div class="form-group add_job_pic">
									<label class="input_label2">Upload Image</label>
									<div class="custom-file mb-3">
										<input  id="Image" name="Image" onchange="Hide('ImageErr');" type="file" class="custom-file-input inputfield2" >
										<label class="custom-file-label inputfield2" for="Image">Upload File</label>
									</div>
								</div>
								<span id="ImageErr"></span>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<label class="input_label2">Certifications Description<span>*</span></label>
									<textarea id="Description" name="Description"
									 onkeypress="Hide('DescriptionErr');" type="text" 
									 class="form-control inputfield2" 
									 placeholder="Enter Description"></textarea>
								</div>
								<span id="DescriptionErr"></span>
							</div>
						</div>
					</div>
					<div class="col-md-12 text-center">
						<a href="javascript:void(0);" onclick="UpdateCertifications();" class="btn cus_btn big">Update Certifications</a>
					</div>
				</div>
			</form>
		<?php
	}
	public function UpdateCertificationsDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		
        $CertID 	= $Data['CertID'];
       	$Details['profile_id'] 	= $UserID;
        $Details['name'] 	= $Data['Certifications'];
        $Details['description']		= $Data['Description']; 
        $File 		= $request->file('Image');
	    if(!empty($File)){
		    $Path = 'public/Front/Users/Certification';
	        $ImageName = $File->getClientOriginalName();
	        $Upload = $File->move($Path, $ImageName);
	        $Details['image'] 		= $ImageName;
        }    
        $Save = $this->UserModel->UpdateCertificationsDetails($CertID,$Details);
        if($Save)
		{
			Session::flash('message', 'Certification Details Has been Updated.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}

	public function GetSetAvailabilty(Request $request)
	{
		$UserID 		= Session::get('UserID');
		$Data 			= $request->all();
		$UserDetails 	= $this->UserModel->UserDetails($UserID);
		$Availability = array();
		if($UserDetails->availability!='')
		{
			$Availability = json_decode($UserDetails->availability);
		}
			
		$TimeSlots 		= $this->UserModel->GetTimeSlots();
		
		?>
			<div class="col-md-12 mb-2"><h3>Set Availability</h3></div>
			<form action="<?php echo route('SaveAvailabilty'); ?>" id="SetAvailabiltyForm" name="SetAvailabiltyForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<div class="row">
					<div class="col-md-12">
					<div class="table-responsive">
						<table class="table">
							<htead>
								<tr>
									<th>Day</th>
									<th>From</th>
									<th>To</th>
									<th>Not Available</th>
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
										<td>
											<select <?php echo $From; ?> id="From_<?php echo $i; ?>" name="From[<?php echo $Day; ?>]" onchange="GetToTimeSlot('<?php echo $i; ?>'),Hide('FromErr_<?php echo $i; ?>');">
												<option value="">Select Time</option>
												<?php foreach($TimeSlots as $ts){ ?>
												<option value="<?php echo $ts->id; ?>"
													<?php if($FromVal==$ts->id){ echo "selected";}?>><?php echo $ts->time_slot; ?></option>
												<?php } ?>
											</select>
											<span id="FromErr_<?php echo $i; ?>"></span>
										</td>										
										<td>
											<select <?php echo $To; ?> id="To_<?php echo $i; ?>" name="To[<?php echo $Day; ?>]">
												<option value="">Select Time</option>
												<?php
												if(!empty($Availability))
												{
													foreach($TimeSlots as $ts)
													{ 
														?>
														<option value="<?php echo $ts->id; ?>"
															<?php if($ToVal==$ts->id){ echo "selected";}?>><?php echo $ts->time_slot; ?></option>
														<?php 
													} 
												}
												?>
											</select>
											<span id="ToErr_<?php echo $i; ?>"></span>
										</td>

										<td><input <?php echo $Checked; ?> type="checkbox" onclick="SetNotAvailable('<?php echo $i; ?>');" id="NotAvailable_<?php echo $i; ?>" name="NotAvailable[]" value="<?php echo $Day; ?>"></td>
									</tr>
									<?php 
								}
								?>
							</tbody>
						</table>
					</div>
					</div>
					<div class="col-md-12 text-center">
						<a href="javascript:void(0);" onclick="SetAvailabilty();" class="btn cus_btn big">Set Availability</a>
					</div>
				</div>
			</form>
		<?php
	}
	public function GetToTimeSlot(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$From 		= $Data['From'];
		$TimeSlots 	= $this->UserModel->GetTimeSlotsTo($From);
		?>
			<option value="">Select Time</option>
		<?php foreach($TimeSlots as $ts){ ?>
			<option value="<?php echo $ts->id; ?>"><?php echo $ts->time_slot; ?></option>
		<?php } ?>
		<?php
		exit();
	}
	public function SaveAvailabilty(Request $request)
	{
		$UserID 		= Session::get('UserID');
		$Data 			= $request->all();
		
		$NotAvailable 	= $Data['NotAvailable'];
		$From=array();
		$To=array();
		if(isset($Data['From']))
		{
			$From 			= $Data['From'];			
		}
		if(isset($Data['To']))
		{
			$To 			= $Data['To'];			
		}
		$TimeStamp = strtotime('next Monday');
		$DaysArray=array();
		for($i = 0; $i < 7; $i++) 
		{ 
			$Day= strftime('%A', $TimeStamp);
			$TimeStamp = strtotime('+1 day', $TimeStamp);

			$Sample['Day'] = $Day;

			if (in_array($Day, $NotAvailable))
			{
				$Sample['NotAvailable'] = 1;
				$Sample['FromSlotID'] = '';
				$Sample['ToSlotID'] = '';
			}
			else
			{
				$Sample['NotAvailable'] = 0;
				$Sample['FromSlotID'] = $From[$Day];
				$Sample['ToSlotID'] = $To[$Day];
			}
			array_push($DaysArray, $Sample);
		}
		$Details['availability'] 	= json_encode($DaysArray);

    	$Save = $this->UserModel->UpdateProfileDetails($UserID,$Details);
    	if($Save)
		{
			Session::flash('message', 'Profile Details Has been Updated.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}

	public function DeleteDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();

		$Title 		= $Data['Title'];
        $TableName 	= $Data['TableName'];
        $ID			= $Data['ID']; 

		$Del = $this->UserModel->DeleteDetails($TableName,$ID);
        if($Del)
		{
			Session::flash('message', $Title.' Details Has been Deleted.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}

	/////////////////////////////////////////////////////
	public function Settings(Request $request)
	{
		$Data['Title'] 			= 'User Dashboard';
		$Data['Menu'] 			= 'Settings';
		$UserID 				= Session::get('UserID');
		$UserDetails 			= $this->UserModel->UserDetails($UserID);
		$Data['UserDetails'] 	= $UserDetails;
		return View('Front/Pages/User/Settings')->with($Data);
	}
	public function CheckCurrentPassword(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();

		$CurrentPass= $Data['CurrentPass'];
		$Save = $this->UserModel->CheckCurrentPassword($UserID,$CurrentPass);
        if($Save)
		{
			echo 1;
		}
		else
      	{
			echo 0;
      	}
      	exit();
        
	}
	public function ChangePassword(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();

		$CurrentPass= $Data['CurrentPass'];
        $NewPass 	= $Data['NewPass'];

        $Details['password'] 	= $NewPass;
       
        $Save = $this->UserModel->UpdateProfileDetails($UserID,$Details);
        if($Save)
		{
			Session::flash('message', 'Profile Password Has been Updated.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('Settings');

	}

	public function SetPushNotification(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();

		$Details['push_notification']= $Data['PushNotification'];
		$Save = $this->UserModel->UpdateProfileDetails($UserID,$Details);
        if($Save)
		{
			echo 1;
		}
		else
      	{
			echo 0;
      	}
      	exit();
        
	}
	public function SetEmailNotification(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();

		$Details['email_notification']= $Data['EmailNotification'];
		$Save = $this->UserModel->UpdateProfileDetails($UserID,$Details);
        if($Save)
		{
			echo 1;
		}
		else
      	{
			echo 0;
      	}
      	exit();
        
	}
	public function SetTextNotification(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();

		$Details['text_notification']= $Data['TextNotification'];
		$Save = $this->UserModel->UpdateProfileDetails($UserID,$Details);
        if($Save)
		{
			echo 1;
		}
		else
      	{
			echo 0;
      	}
      	exit();
        
	}
	///////////////////////////////////////////////////////////
	public function SetTypeVal(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();

		$Details['type']= $Data['Type'];
		$Save = $this->UserModel->UpdateProfileDetails($UserID,$Details);
        if($Save)
		{
			echo 1;
		}
		else
      	{
			echo 0;
      	}
      	exit();
	}
	public function GetCompanyDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$CompanyDetails = $this->UserModel->CompanyDetails($UserID);
		$CompanyLogo 	= "";
		$CompanyName 	= "";
		$Web 			= "";
		$AboutCompany 	= "";
		if(!empty($CompanyDetails))
		{
			$CompanyLogo 	= $CompanyDetails->logo;
			$CompanyName 	= $CompanyDetails->name;
			$Web 			= $CompanyDetails->web;
			$AboutCompany 	= $CompanyDetails->about;
		}
		?>
			<div class="col-md-12 mb-2"><h3>Company Details</h3></div>
			<form action="<?php echo route('UpdateCompanyDetails'); ?>" id="CompanyDetailsForm" name="CompanyDetailsForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<div class="col-md-12">
					<div class="edit_profile_cont">
						<div class="lhs">
							<div class="pro_img">
								<div class="edit_pik">
									<img src="<?php echo asset('public/Front/Design/img/camera.png'); ?>" alt="" />
									<input type="file" name="Image" id="Image" />
								</div>
								<?php if($CompanyLogo==''){?>
								<img id="imagePrev" src="<?php echo asset('public/Front/Design/img/pro_pic.png'); ?>" alt="" />
								<?php } else {?>
								<img id="imagePrev" src="<?php echo asset('public/Front/Users/Profile/Company').'/'.$CompanyLogo; ?>" alt="" />
								<?php }?>								
							</div>
						</div>
						<div class="rhs pro_img_right">
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<label class="input_label2">Company Name<span>*</span></label>
										<input name="CompanyName" id="CompanyName" onkeypress="Hide('CompanyNameErr');" 
										type="text" class="form-control inputfield2" 
										placeholder="Enter Company Name" value="<?php echo $CompanyName; ?>">
									</div>
									<span id="CompanyNameErr"></span>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<label class="input_label2">Web Site URL(If Any)</label>
										<input name="Web" id="Web" onkeypress="Hide('WebErr');"  
										type="text" class="form-control inputfield2" 
										placeholder="Enter Location" value="<?php echo $Web; ?>">
									</div>
									<span id="WebErr"></span>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<label class="input_label2">About Company</label>
										<textarea name="AboutCompany" id="AboutCompany" onkeypress="Hide('AboutCompanyErr');"  
										class="form-control inputfield2" 
										placeholder="Enter About Company"><?php echo $AboutCompany; ?></textarea>
									</div>
									<span id="AboutCompanyErr"></span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12 text-center">
					<a href="javascript:void(0);" onclick="UpdateCompanyDetails();" class="btn cus_btn big">Update Company Details</a>
				</div>
			</form>
		<?php
	}
	public function UpdateCompanyDetails(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$File 		= $request->file('Image');
	    if(!empty($File)){
		    $Path = 'public/Front/Users/Profile/Company';
	        $ImageName = $File->getClientOriginalName();
	        $Upload = $File->move($Path, $ImageName);
	        $Details['logo'] 		= $ImageName;
        }
        $Details['name'] 	= $Data['CompanyName'];
        $Details['about'] 	= $Data['AboutCompany'];
        $Details['web'] 	= $Data['Web'];
        $Details['profile_id'] 	= $UserID;
        
       
        $Save = $this->UserModel->UpdateCompanyDetails($UserID,$Details);
        if($Save)
		{
			Session::flash('message', 'Cpmpany Details Has been Updated.'); 
			Session::flash('alert-class', 'alert-success'); 
		}
		else
      	{
			Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
      	}
      	return Redirect::route('UserDashboard');
	}

	public function UserReviews(Request $request)
	{
		$Data = $request->all();
		$UserID 				= Session::get('UserID');
		$page 					= $Data['page'];
		$sort 					= $Data['sort'];

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
										<p class="star-no">'.number_format($row->rating,2).'</p>
										<p>'.$row->review.'</p>
									</div>
								</div>
							</li>';
		}
		$Pagination=Common::Pagination($numofrecords, $Count, $page);
		$Response['Review'] 		= $Review;
		$Response['Pagination'] = $Pagination;
		return json_encode($Response);
	}

	////////////////////////////////////////////////////
	public function OpenBgVerificationModal(Request $request)
	{
		$UserID 	= Session::get('UserID');
		$Data 		= $request->all();
		$UserDetails = $this->UserModel->UserDetails($UserID);
		?>
			<div class="col-md-12 mb-2"><h3>Add Background Verification Details</h3></div>
			<form action="<?php echo route('BgVerificationProcess'); ?>" id="BgVerificationForm" name="BgVerificationForm" method="POST" enctype='multipart/form-data'>
				<input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
				<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label class="input_label2">First Name<span>*</span></label>
							<input name="FirstName" id="FirstName" onkeypress="Hide('FirstNameErr');" 
							type="text" class="form-control inputfield2" 
							placeholder="Enter First Name" value="<?php echo $UserDetails->first_name; ?>">
						</div>
						<span id="FirstNameErr"></span>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="input_label2">Last Name<span>*</span></label>
							<input name="LastName" id="LastName" onkeypress="Hide('LastNameErr');"  
							type="text" class="form-control inputfield2" 
							placeholder="Enter Last Name" value="<?php echo $UserDetails->last_name; ?>">
						</div>
						<span id="LastNameErr"></span>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="input_label2">Email<span>*</span></label>
							<input name="Email" id="Email" onkeypress="Hide('EmailErr');" 
							type="text" class="form-control inputfield2" 
							placeholder="Enter Email" value="<?php echo $UserDetails->email; ?>">
						</div>
						<span id="FirstNameErr"></span>
					</div>					
					<div class="col-md-3">
						<div class="form-group">
							<label class="input_label2">DOB<span>*</span></label>
							<input name="DOB" id="DOB" onkeypress="Hide('DOBErr');"  
							type="text" class="form-control inputfield2"
							placeholder="Enter DOB" value="<?php echo $UserDetails->dob; ?>">
						</div>
						<span id="DOBErr"></span>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="input_label2">Phone</label>
							<input name="Phone" id="Phone" onkeypress="Hide('PhoneErr');"  
							type="text" class="form-control inputfield2" 
							placeholder="Enter Phone Number" value="<?php echo $UserDetails->phone; ?>">
						</div>
						<span id="LastNameErr"></span>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="input_label2">SSN</label>
							<input name="SSN" id="SSN" onkeypress="Hide('SSNErr');"  
							type="text" class="form-control inputfield2" 
							placeholder="Enter SSN" value="">
						</div>
						<span id="SSNErr"></span>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="input_label2">Address</label>
							<input name="Address" id="Address" onkeypress="Hide('AddressErr');"  
							type="text" class="form-control inputfield2" 
							placeholder="Enter Address" value="<?php echo $UserDetails->location; ?>">
						</div>
						<span id="AddressErr"></span>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="input_label2">City</label>
							<input name="City" id="City" onkeypress="Hide('CityErr');"  
							type="text" class="form-control inputfield2" 
							placeholder="Enter City" value="">
						</div>
						<span id="CityErr"></span>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="input_label2">Country</label>
							<input name="Country" id="Country" onkeypress="Hide('CountryErr');"  
							type="text" class="form-control inputfield2" 
							placeholder="Enter Country" value="">
						</div>
						<span id="CountryErr"></span>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="input_label2">Region</label>
							<input name="Region" id="Region" onkeypress="Hide('RegionErr');"  
							type="text" class="form-control inputfield2" 
							placeholder="Enter Region" value="">
						</div>
						<span id="RegionErr"></span>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="input_label2">Postal</label>
							<input name="Postal" id="Postal" onkeypress="Hide('PostalErr');"  
							type="text" class="form-control inputfield2" 
							placeholder="Enter Postal" value="">
						</div>
						<span id="PostalErr"></span>
					</div>
					<div class="col-md-12 text-center">
						<a href="javascript:void(0);" onclick="BgVerificationProcess();" class="btn cus_btn big">Send Verification sRequest</a>
					</div>
				</div>
			</form>
			<script type="text/javascript">
				$('#DOB').Zebra_DatePicker({
				  direction: ['<?=date('Y-m-d', strtotime('-70 years'));?>', '<?=date('Y-m-d', strtotime('-10 years'))?>']
				});
			</script>
		<?php
	}

	public function BgVerificationProcess(Request $request)
	{
    	$Data = $request->all();
    
   	 	$BgData['firstName'] = $Data['FirstName']; /***/
		$BgData['lastName'] = $Data['LastName'];  /***/
		$BgData['middleName'] = '';
		$BgData['suffix'] = '';
		$BgData['dateOfBirth'] = $Data['DOB']; /***/
		$BgData['ssn'] = $Data['SSN'];
		$BgData['email'] = $Data['Email']; /***/
		$BgData['phone'] = $Data['Phone'];
		$BgData['address'] = $Data['Address'];
		$BgData['city'] = $Data['City'];
		$BgData['region'] = $Data['Region'];
		$BgData['country'] = $Data['Country'];
		$BgData['postalCode'] = $Data['Postal'];

		$CreateCandidate = $this->CreateCandidate($BgData);
		if(isset($CreateCandidate->errors)){
			//echo json_encode($CreateCandidate->errors[0]);
			Session::flash('message', $CreateCandidate->errors[0]->message); 
			Session::flash('alert-class', 'alert-danger');
			return Redirect::route('UserDashboard');
		}else{
			$CandidateId = $CreateCandidate->id;

			$BPlaceData['candidateId'] = $CandidateId;
			$BPlaceData['packageType'] = 'PKG_EMPTY';
			$BPlaceData['workflow'] = 'EXPRESS';
			$BPlaceData['jobLocation.country'] = $BgData['country'];
			$BPlaceData['jobLocation.region'] = $BgData['region'];
			$BPlaceData['jobLocation.city'] = $BgData['city'];
			$BPlaceData['jobLocation.postalCode'] = $BgData['postalCode'];

			$PlaceOrder = $this->PlaceOrder($BPlaceData);
			if(isset($PlaceOrder->errors)){
				//echo json_encode($PlaceOrder->errors[0]); 
				Session::flash('message', $PlaceOrder->errors[0]->message); 
				Session::flash('alert-class', 'alert-danger');
				return Redirect::route('UserDashboard');
			}else{
				$OrderId = $PlaceOrder->id;
				$CheckStatus = $this->CheckOrderStatus($OrderId);
				$Status = $CheckStatus->status;
				if($Status=='PENDING'){
					$background = '0';
				}else{
					$background = '1';
				}
				$UserID 	= Session::get('UserID');
				$UpdateData = DB::table('profile')->where('id',$UserID)->update(['background'=>$background,'background_id'=>$OrderId,'background_response'=>json_encode($CheckStatus)]);

				Session::flash('message', 'Background Check Has been Updated.'); 
				Session::flash('alert-class', 'alert-success'); 
				return Redirect::route('UserDashboard');

			}
		}

	}

	public function VerificationRequest(Request $request)
	{
		$Response       = array();
		$UserID 		= Session::get('UserID');
		$UserDetails 	= $this->UserModel->UserDetails($UserID);
    	$OrderId 		= $UserDetails->background_id;
		$CheckStatus 	= $this->CheckOrderStatus($OrderId);
		$Status 		= $CheckStatus->status;
		if($Status=='PENDING')
		{
			$background = '0';
			$UpdateData = DB::table('profile')->where('id',$UserID)->update(['background'=>$background,'background_id'=>$OrderId,'background_response'=>json_encode($CheckStatus)]);
			$Response['Status'] = $background;
			$Response['ButtonDiv'] = '';
		}
		else
		{
			$background = '1';
			$UpdateData = DB::table('profile')->where('id',$UserID)->update(['background'=>$background,'background_id'=>$OrderId,'background_response'=>json_encode($CheckStatus)]);
			$Response['Status'] = $background;
			$Response['ButtonDiv'] = "<button class='btn btn-warning' title='Background Verification Done.'>Verified</button>";
		} 
		echo json_encode($Response);
		exit();
	}
	public function CreateCandidate($params){
		$ClientID = env('ClientID');
		$ClientSecret = env('ClientSecret');
		$BaseUrl = env('BaseUrl');
		$header = array(
		    'Accept: application/json',
		    'Content-Type: application/x-www-form-urlencoded',
		    'Authorization: Basic '. base64_encode($ClientID.':'.$ClientSecret)
		);
		$request = $BaseUrl.'candidate';
		$session = curl_init($request);
		curl_setopt($session, CURLOPT_POST, true);
		curl_setopt($session, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($session, CURLOPT_HEADER, false);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session, CURLOPT_HTTPHEADER, $header);
		$response = curl_exec($session);
		$response = json_decode($response);
		curl_close($session);
		return $response;
	}
	public function PlaceOrder($params){
		$ClientID = env('ClientID');
		$ClientSecret = env('ClientSecret');
		$BaseUrl = env('BaseUrl');

		$header = array(
		    'Accept: application/json',
		    'Content-Type: application/x-www-form-urlencoded',
		    'Authorization: Basic '. base64_encode($ClientID.':'.$ClientSecret)
			);

		$request = $BaseUrl.'order';
		$session = curl_init($request);
		curl_setopt($session, CURLOPT_POST, true);
		curl_setopt($session, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($session, CURLOPT_HEADER, false);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session, CURLOPT_HTTPHEADER, $header);
		$response = curl_exec($session);
		$response = json_decode($response);
		curl_close($session);
		return $response;	
	}
	public function CheckOrderStatus($OrderId){
		$ClientID = env('ClientID');
		$ClientSecret = env('ClientSecret');
		$BaseUrl = env('BaseUrl');
		
		$header = array(
		    'Accept: application/json',
		    'Content-Type: application/x-www-form-urlencoded',
		    'Authorization: Basic '. base64_encode($ClientID.':'.$ClientSecret)
		);

		$request = $BaseUrl.'order/'.$OrderId;
		$session = curl_init($request);
		curl_setopt($session, CURLOPT_POST, false);
		curl_setopt($session, CURLOPT_HEADER, false);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session, CURLOPT_HTTPHEADER, $header);
		$response = curl_exec($session);
		$response = json_decode($response);
		curl_close($session);
		return $response;	
	}
	


}
