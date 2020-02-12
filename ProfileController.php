<?php
namespace App\Http\Controllers\API;
use Route;
use Mail;
use Auth, Hash;
use Validator;
use Session;
use Redirect;
use DB;
use URL;
use Crypt;
use Illuminate\Http\Request;
use App\Http\Models\Front\UserModel;
use App\Http\Models\API\ProfileModel;
use App\Http\Models\API\CommonModel;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Pagination;

class ProfileController extends Controller 
{
	public function __construct(Request $request)
	{
		$this->UserModel = new UserModel();
		$this->ProfileModel = new ProfileModel();
		$this->CommonModel 	= new CommonModel();
	}	
	public function ProfileDetailAPI(Request $request)
	{
		$Data 			= $request->all();	
		$ExperienceInfo = array();	
		$PreferenceInfo = array();	
		$EducationInfo 	= array();	
		$LanguageInfo 	= array();	
		$AppearanceInfo = array();	
		$AccreditationsInfo = array();	
		$CertificationsInfo = array();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			if($CheckLoginDetails->image==''){
				$Image = asset('public/Front/Design/img/pro_pic.png');
			}else{
				$Image = asset('public/Front/Users/Profile').'/'.$CheckLoginDetails->image;
			}
			
			$Type='3';
			if($CheckLoginDetails->type=='1'){
				$Type = 'Employee';
			} else if($CheckLoginDetails->type=='2'){
				$Type = 'Company';
			} else if($CheckLoginDetails->type=='3'){
				$Type = 'Both';
			}	

			$GeneralInfo['FirstName'] 	= $CheckLoginDetails->first_name;
			$GeneralInfo['LastName'] 	= $CheckLoginDetails->last_name;
			$GeneralInfo['Gender'] 		= $CheckLoginDetails->gender;
			$GeneralInfo['DOB'] 		= $CheckLoginDetails->dob;
			$GeneralInfo['Address'] 	= $CheckLoginDetails->location;
			$GeneralInfo['Image'] 		= $Image;
			$GeneralInfo['AccountTypeID'] 		= $CheckLoginDetails->type;
			$GeneralInfo['AccountType'] 		= $Type;

			$Experience  = $this->ProfileModel->GetUserExperience($UserID);
			if(!empty($Experience))
			{
				$Sample=array();
				foreach($Experience as $e)
				{
					$Sample['ExperienceID']	= $e->id;
					$Sample['JobTitle']		= $e->job_title;
					$Sample['CompanyName']	= $e->company;
					$Sample['Location']		= $e->location;
					$Sample['From']			= $e->from;
					$Sample['To']			= $e->to;
					$Sample['Still']		= $e->still;
					array_push($ExperienceInfo, $Sample);
				}
			}
			else
			{
				$Sample=array();
				$Sample['ExperienceID'] = '';
				array_push($ExperienceInfo, $Sample);
			}
			$Preference  = $this->ProfileModel->GetUserPreference($UserID);
			if(!empty($Preference))
			{
				$Sample=array();
				foreach($Preference as $p)
				{
					$Sample['JobPrefID']		= $p->id;
					$Sample['JobCategory']		= $p->job_category;
					$Sample['JobSubCategory']	= $p->job_sub_category;
					$Sample['PreferredPayRate']	= $p->pay_rate;
					array_push($PreferenceInfo, $Sample);
				}
			}
			else
			{
				$Sample=array();
				$Sample['JobPrefID'] = '';
				array_push($PreferenceInfo, $Sample);
			}
			$Education   = $this->ProfileModel->GetUserEducation($UserID);
			if(!empty($Education))
			{
				$Sample=array();
				foreach($Education as $eu)
				{
					$Sample['EducationID']	= $eu->id;
					$Sample['Institute']	= $eu->institute;
					$Sample['Degree']		= $eu->degree;
					$Sample['From']			= $eu->from;
					$Sample['To']			= $eu->to;
					$Sample['Persuing']		= $eu->persuing;
					array_push($EducationInfo, $Sample);
				}
			}
			else
			{
				$Sample=array();
				$Sample['EducationID'] = '';
				array_push($EducationInfo, $Sample);
			}
			$Language   = $this->ProfileModel->GetUserLanguage($UserID);
			if(!empty($Language))
			{
				$Sample=array();
				foreach($Language as $l)
				{
					$Level='';
					if($l->level=='1'){
						$Level = 'Beginner';
					} else if($l->level=='2'){
						$Level = 'Intermediate';
					} else if($l->level=='3'){
						$Level = 'Intermediate';
					}	
					$Sample['LanguageID']	= $l->id;
					$Sample['Language']		= $l->language;
					$Sample['Level']		= $l->level;
					array_push($LanguageInfo, $Sample);
				}
			}
			else
			{
				$Sample=array();
				$Sample['Language'] = '';
				array_push($LanguageInfo, $Sample);
			}
			$Appearance   = $this->ProfileModel->GetUserAppearance($UserID);
			if(!empty($Appearance))
			{
				$HairColorID 	= $Appearance->hair_color;
				$EyeColorID 	= $Appearance->eye_color;

				$AppearanceInfo['HairColorID']	= $HairColorID;
				$AppearanceInfo['HairColor']	= $this->ProfileModel->GetHairColor($HairColorID);
				$AppearanceInfo['EyeColorID']	= $EyeColorID;
				$AppearanceInfo['EyeColor']		= $this->ProfileModel->GetEyeColor($EyeColorID);

				$MyHeightSting 	= $Appearance->height;
				$MyHeightArray 	= explode('#',$Appearance->height);

				$MyHeight 	= "";
				$MyWeight 	= "";
				if(!empty($MyHeightArray))
				{
					$MyHeight 	= $MyHeightArray[0]."'".$MyHeightArray[1];
					$MyWeight 	= $Appearance->weight.'lbs';
				}			

				$AppearanceInfo['Height']					= $MyHeight;
				$AppearanceInfo['Weight']					= $MyWeight;

				if($CheckLoginDetails->transportation=='0')
				{
					$Transportation = 'No';
				} 
				elseif($CheckLoginDetails->transportation=='1')
				{
					$Transportation = 'Yes';
				}
				elseif($CheckLoginDetails->transportation=='')
				{
					$Transportation = 'N/A';
				}

				$AppearanceInfo['OwnReliableTransportation']= $Transportation;
			}
			else
			{
				$AppearanceInfo['HairColorID'] = '';
				$AppearanceInfo['HairColor'] = '';
				$AppearanceInfo['EyeColorID'] = '';
				$AppearanceInfo['EyeColor'] = '';
				$AppearanceInfo['Height'] = '';
				$AppearanceInfo['Weight'] = '';
				$AppearanceInfo['OwnReliableTransportation'] = '';
			}
			$Accreditations   = $this->ProfileModel->GetUserAccreditations($UserID);
			if(!empty($Accreditations))
			{
				$Sample=array();
				foreach($Accreditations as $a)
				{
					$Sample['AccreditationID']			= $a->id;
					$Sample['Title']	= $a->accreditations;
					$Sample['Image']	= asset('public/Front/Users/Accreditations').'/'.$a->image;
					$Sample['ExpDate']	= $a->exp_date;
				array_push($AccreditationsInfo, $Sample);
				}
			}
			else
			{
				$Sample=array();
				$Sample['Title'] = '';
				array_push($AccreditationsInfo, $Sample);
			}
			$Certifications   = $this->ProfileModel->GetUserCertifications($UserID);
			if(!empty($Certifications))
			{
				$Sample=array();
				foreach($Certifications as $c)
				{
					$Sample['CertificationID']			= $c->id;
					$Sample['Name']			= $c->name;
					$Sample['Image']		= asset('public/Front/Users/Certification').'/'.$c->image;
					$Sample['Description']	= $c->description;
					array_push($CertificationsInfo, $Sample);
				}
			}
			else
			{
				$Sample=array();
				$Sample['Name'] = '';
				array_push($CertificationsInfo, $Sample);
			}
			$Response = ['Status'			=> true,
						'Message'			=> 'Profile Details.',
						'GeneralInfo'		=> $GeneralInfo,
						'AboutMe'			=> $CheckLoginDetails->about,
						'ExperienceInfo'	=> $ExperienceInfo,
						'PreferenceInfo'	=> $PreferenceInfo,
						'EducationInfo'		=> $EducationInfo,
						'LanguageInfo'		=> $LanguageInfo,
						'AppearanceInfo'	=> $AppearanceInfo,
						'AccreditationsInfo'=> $AccreditationsInfo,
						'CertificationsInfo'=> $CertificationsInfo
						];			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];			
		}
		
	  	return response()->json($Response);
	}
	public function EditGeneralInfo(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$FirstName 		= $Data['FirstName'];	
		$LastName 		= $Data['LastName'];	
		$Gender 		= $Data['Gender'];	
		$DOB 			= $Data['DOB'];		
		$Address 		= $Data['Address'];	
		if($FirstName=='')
		{
			$Response = ['Status'=>false,'Message'=>'FirstName Missing.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$File 		= $request->file('Image');
		    if(!empty($File)){
			    $Path = 'public/Front/Users/Profile';
		        $ImageName = $File->getClientOriginalName();
		        $Upload = $File->move($Path, $ImageName);
		        $Details['image'] 		= $ImageName;
	        }
			$Details['first_name'] 	= $FirstName;
			$Details['last_name'] 	= $LastName;
			$Details['gender']		= $Gender;
			$Details['dob'] 		= $DOB;
			$Details['updated_at'] 	= date('Y-m-d H:i:s');

			$EditGeneralInfo  = $this->ProfileModel->EditGeneralInfo($UserID, $Details);
			if($EditGeneralInfo)
			{
				$Response = ['Status'=>true,'Message'=>'General-Info Updated Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
		
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	public function EditAddressInfo(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$Address 		= $Data['Address'];	
		$Latitude 		= $Data['Latitude'];	
		$Longitude 		= $Data['Longitude'];	
		if($Address=='')
		{
			$Response = ['Status'=>false,'Message'=>'Address Missing.'];	
			return response()->json($Response);	
		}
		if($Latitude=='')
		{
			$Response = ['Status'=>false,'Message'=>'Latitude Missing.'];	
			return response()->json($Response);	
		}
		if($Longitude=='')
		{
			$Response = ['Status'=>false,'Message'=>'Longitude Missing.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			
			$Details['location'] 	= $Address;
			$Details['latitude'] 	= $Latitude;
			$Details['longitude']	= $Longitude;

			$EditGeneralInfo  = $this->ProfileModel->EditGeneralInfo($UserID, $Details);
			if($EditGeneralInfo)
			{
				$Response = ['Status'=>true,'Message'=>'Address-Info Updated Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
		
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	public function EditAboutInfo(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$About 			= $Data['About'];
		if($About=='')
		{
			$Response = ['Status'=>false,'Message'=>'About Parameter Missing.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{			
			$Details['about'] 		= $About;
			$Details['updated_at'] 	= date('Y-m-d H:i:s');

			$EditGeneralInfo  = $this->ProfileModel->EditGeneralInfo($UserID, $Details);
			if($EditGeneralInfo)
			{
				$Response = ['Status'=>true,'Message'=>'About-Info Updated Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	//////////////////////////////////////////////////
	public function AddJobPreferenceAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$CatID			= $Data['CatID'];	
		$SubCatID 		= $Data['SubCatID'];	
		$PayRate 		= $Data['PayRate'];	
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
		if($CatID=='')
		{
			$Response = ['Status'=>false,'Message'=>'CatID Missing.'];	
			return response()->json($Response);	
		}
		if($SubCatID=='')
		{
			$Response = ['Status'=>false,'Message'=>'SubCatID Missing.'];	
			return response()->json($Response);	
		}
		if($PayRate=='')
		{
			$Response = ['Status'=>false,'Message'=>'PayRate Missing.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{			
			$Details['profile_id'] 	= $UserID;
			$Details['job_cat'] 	= $CatID;
			$Details['job_sub_cat'] = $SubCatID;
			$Details['pay_rate']	= $PayRate;

			$AddJobPreference  = $this->ProfileModel->AddJobPreference($Details);
			if($AddJobPreference)
			{
				$Response = ['Status'=>true,'Message'=>'Job Preference Added Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	public function EditJobPreferenceAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$JobPrefID		= $Data['JobPrefID'];	
		$CatID			= $Data['CatID'];	
		$SubCatID 		= $Data['SubCatID'];	
		$PayRate 		= $Data['PayRate'];
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
		if($JobPrefID=='')
		{
			$Response = ['Status'=>false,'Message'=>'Job Pref ID Missing.'];	
			return response()->json($Response);	
		}	
		if($CatID=='')
		{
			$Response = ['Status'=>false,'Message'=>'CatID Missing.'];	
			return response()->json($Response);	
		}
		if($SubCatID=='')
		{
			$Response = ['Status'=>false,'Message'=>'SubCatID Missing.'];	
			return response()->json($Response);	
		}
		if($PayRate=='')
		{
			$Response = ['Status'=>false,'Message'=>'PayRate Missing.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{			
			$Details['job_cat'] 	= $CatID;
			$Details['job_sub_cat'] = $SubCatID;
			$Details['pay_rate']	= $PayRate;

			$EditJobPreference  = $this->ProfileModel->EditJobPreference($JobPrefID,$UserID,$Details);
			if($EditJobPreference)
			{
				$Response = ['Status'=>true,'Message'=>'Job Preference Updated Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	public function DeleteJobPreferenceAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$JobPrefID		= $Data['JobPrefID'];
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
		if($JobPrefID=='')
		{
			$Response = ['Status'=>false,'Message'=>'Job Pref ID Missing.'];	
			return response()->json($Response);	
		}	
		
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{		
			$DeleteJobPreference  = $this->ProfileModel->DeleteJobPreference($JobPrefID,$UserID);
			if($DeleteJobPreference)
			{
				$Response = ['Status'=>true,'Message'=>'Job Preference Deleted Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	//////////////////////////////////////////////////
	//////////////////////////////////////////////////
	public function AddExperienceAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$JobTitle		= $Data['JobTitle'];	
		$Company 		= $Data['Company'];	
		$Location 		= $Data['Location'];	
		$From 			= $Data['From'];	
		$To 			= $Data['To'];	
		$Still 			= $Data['Still'];	
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
		if($JobTitle=='')
		{
			$Response = ['Status'=>false,'Message'=>'JobTitle Missing.'];	
			return response()->json($Response);	
		}
		if($Company=='')
		{
			$Response = ['Status'=>false,'Message'=>'Company Missing.'];	
			return response()->json($Response);	
		}
		if($Location=='')
		{
			$Response = ['Status'=>false,'Message'=>'Location Missing.'];	
			return response()->json($Response);	

		}

		if($From=='')

		{

			$Response = ['Status'=>false,'Message'=>'From Date Missing.'];	

			return response()->json($Response);	

		}

		if($Still==0 || $Still=='')

		{

			if($To=='')

			{

				$Response = ['Status'=>false,'Message'=>'To Date Missing.'];	

				return response()->json($Response);	

			}

		}

		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);

		if(!empty($CheckLoginDetails))

		{			

			$Details['profile_id'] 	= $UserID;

			$Details['job_title'] 	= $JobTitle;

			$Details['company'] 	= $Company;

			$Details['location']	= $Location;

			$Details['from']		= $From;

			$Details['to']			= '';

			$Details['still']		= '';

			if($Still==1)

			{

				$Details['to']			= $To;

				$Details['still']		= $Still;				

			}

			



			$AddExperience  = $this->ProfileModel->AddExperience($Details);

			if($AddExperience)

			{

				$Response = ['Status'=>true,'Message'=>'Job Experience Added Successfully!'];

				return response()->json($Response);

			}

			else

			{

				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];

				return response()->json($Response);

			}

			

		}

		else

		{

			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	

			return response()->json($Response);		

		}

	}

	public function EditExperienceAPI(Request $request)
	{

		$Data 			= $request->all();	

		$Response   	= array();	



		$UserID 		= $Data['UserID'];	

		$AccessToken 	= $Data['AccessToken'];	



		$ExperienceID	= $Data['ExperienceID'];	

		$JobTitle		= $Data['JobTitle'];	

		$Company 		= $Data['Company'];	

		$Location 		= $Data['Location'];	

		$From 			= $Data['From'];	

		$To 			= $Data['To'];	

		$Still 			= $Data['Still'];	

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

		if($JobTitle=='')

		{

			$Response = ['Status'=>false,'Message'=>'JobTitle Missing.'];	

			return response()->json($Response);	

		}

		if($Company=='')

		{

			$Response = ['Status'=>false,'Message'=>'Company Missing.'];	

			return response()->json($Response);	

		}

		if($Location=='')

		{

			$Response = ['Status'=>false,'Message'=>'Location Missing.'];	

			return response()->json($Response);	

		}

		if($From=='')

		{

			$Response = ['Status'=>false,'Message'=>'From Date Missing.'];	

			return response()->json($Response);	

		}

		if($Still==0 || $Still=='')

		{

			if($To=='')

			{

				$Response = ['Status'=>false,'Message'=>'To Date Missing.'];	

				return response()->json($Response);	

			}

		}

		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);

		if(!empty($CheckLoginDetails))

		{			

			$Details['job_title'] 	= $JobTitle;

			$Details['company'] 	= $Company;

			$Details['location']	= $Location;

			$Details['from']		= $From;

			$Details['to']			= '';

			$Details['still']		= '';

			if($Still==1)

			{

				$Details['to']			= $To;

				$Details['still']		= $Still;				

			}



			$EditExperience  = $this->ProfileModel->EditExperience($ExperienceID,$UserID,$Details);

			if($EditExperience)

			{

				$Response = ['Status'=>true,'Message'=>'Job Experience Updated Successfully!'];

				return response()->json($Response);

			}

			else

			{

				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];

				return response()->json($Response);

			}

			

		}

		else

		{

			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	

			return response()->json($Response);		

		}
	}
	public function DeleteExperienceAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$ExperienceID		= $Data['ExperienceID'];
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
		if($ExperienceID=='')
		{
			$Response = ['Status'=>false,'Message'=>'Job ExperienceID ID Missing.'];	
			return response()->json($Response);	
		}	
		
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{		
			$DeleteExperience  = $this->ProfileModel->DeleteExperience($ExperienceID,$UserID);
			if($DeleteExperience)
			{
				$Response = ['Status'=>true,'Message'=>'Job Experience Deleted Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	//////////////////////////////////////////////////
	//////////////////////////////////////////////////
	public function AddEducationAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$Institute		= $Data['Institute'];	
		$Degree 		= $Data['Degree'];		
		$From 			= $Data['From'];	
		$To 			= $Data['To'];	
		$Persuing 		= $Data['Persuing'];	
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
		if($Institute=='')
		{
			$Response = ['Status'=>false,'Message'=>'Institute Missing.'];	
			return response()->json($Response);	
		}
		if($Degree=='')
		{
			$Response = ['Status'=>false,'Message'=>'Degree Missing.'];	
			return response()->json($Response);	
		}
		if($From=='')
		{
			$Response = ['Status'=>false,'Message'=>'From Date Missing.'];	
			return response()->json($Response);	
		}
		if($Persuing==0 || $Persuing=='')
		{
			if($To=='')
			{
				$Response = ['Status'=>false,'Message'=>'To Date Missing.'];	
				return response()->json($Response);	
			}
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{			
			$Details['profile_id'] 	= $UserID;
			$Details['institute'] 	= $Institute;
			$Details['degree'] 		= $Degree;
			$Details['from']		= $From;
			$Details['to']			= '';
			$Details['persuing']		= '';
			if($Persuing==1)
			{
				$Details['to']			= $To;
				$Details['persuing']		= $Persuing;				
			}
			

			$AddEducation  = $this->ProfileModel->AddEducation($Details);
			if($AddEducation)
			{
				$Response = ['Status'=>true,'Message'=>'Job Education Added Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	public function EditEducationAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$EducationID	= $Data['EducationID'];	
		$Institute		= $Data['Institute'];	
		$Degree 		= $Data['Degree'];		
		$From 			= $Data['From'];	
		$To 			= $Data['To'];	
		$Persuing 		= $Data['Persuing'];	
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
		if($EducationID=='')
		{
			$Response = ['Status'=>false,'Message'=>'EducationID Missing.'];	
			return response()->json($Response);	
		}
		if($Institute=='')
		{
			$Response = ['Status'=>false,'Message'=>'Institute Missing.'];	
			return response()->json($Response);	
		}
		if($Degree=='')
		{
			$Response = ['Status'=>false,'Message'=>'Degree Missing.'];	
			return response()->json($Response);	
		}
		if($From=='')
		{
			$Response = ['Status'=>false,'Message'=>'From Date Missing.'];	
			return response()->json($Response);	
		}
		if($Persuing==0 || $Persuing=='')
		{
			if($To=='')
			{
				$Response = ['Status'=>false,'Message'=>'To Date Missing.'];	
				return response()->json($Response);	
			}
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{			
			$Details['profile_id'] 	= $UserID;
			$Details['institute'] 	= $Institute;
			$Details['degree'] 		= $Degree;
			$Details['from']		= $From;
			$Details['to']			= '';
			$Details['persuing']	= '';
			if($Persuing==1)
			{
				$Details['to']			= $To;
				$Details['persuing']	= $Persuing;				
			}

			$EditEducation  = $this->ProfileModel->EditEducation($EducationID,$UserID,$Details);
			if($EditEducation)
			{
				$Response = ['Status'=>true,'Message'=>'Education Updated Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	public function DeleteEducationAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$EducationID		= $Data['EducationID'];
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
		if($EducationID=='')
		{
			$Response = ['Status'=>false,'Message'=>'Education ID Missing.'];	
			return response()->json($Response);	
		}	
		
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{		
			$DeleteEducation  = $this->ProfileModel->DeleteEducation($EducationID,$UserID);
			if($DeleteEducation)
			{
				$Response = ['Status'=>true,'Message'=>'Education Deleted Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	//////////////////////////////////////////////////
	public function HairColorAPI(Request $request)
	{
		$Data 			= $request->all();	
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
			$HairColor  = $this->ProfileModel->HairColor();
			$Response = ['Status'=>true,'Message'=>'Hair Color List','HairColor'=>$HairColor];
			return response()->json($Response);
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	public function EyeColorAPI(Request $request)
	{
		$Data 			= $request->all();	
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
			$EyeColor  = $this->ProfileModel->EyeColor();
			$Response = ['Status'=>true,'Message'=>'Eye Color List','EyeColor'=>$EyeColor];
			return response()->json($Response);
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	
	public function GetOtherInfoAPI(Request $request)
	{
		$Data 			= $request->all();	
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
			$OtherInfo=array();
			$GetUserAppearance  = $this->ProfileModel->GetUserAppearance($UserID);
			$HairColorID 	= '';
			$HairColor		= '';
			$EyeColorID		= '';
			$EyeColor		= '';
			$Height			= '';
			$Weight			= '';
			if(!empty($GetUserAppearance))
			{
				$HairColorID 	= $GetUserAppearance->hair_color;
				$HairColor		= $GetUserAppearance->my_hair_color;
				$EyeColorID		= $GetUserAppearance->eye_color;
				$EyeColor		= $GetUserAppearance->my_eye_color;
				$Height			= $GetUserAppearance->height;
				$Weight			= $GetUserAppearance->weight;
			}

			$OtherInfo['HairColorID'] 	= $HairColorID;
			$OtherInfo['HairColor']		= $HairColor;
			$OtherInfo['EyeColorID']	= $EyeColorID;
			$OtherInfo['EyeColor']		= $EyeColor;
			$OtherInfo['Height']		= $Height;
			$OtherInfo['Weight']		= $Weight;

			if($CheckLoginDetails->transportation=='0')
			{
				$Transportation = 'No';
			} 
			elseif($CheckLoginDetails->transportation=='1')
			{
				$Transportation = 'Yes';
			}
			elseif($CheckLoginDetails->transportation=='')
			{
				$Transportation = 'N/A';
			}
			$OtherInfo['Transportation']		= $Transportation;

			$Response = ['Status'=>true,'Message'=>'Other Info.','OtherInfo'=>$OtherInfo];
			return response()->json($Response);
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	public function EditOtherInfoAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$HairColor		= $Data['HairColor'];	
		$EyeColor 		= $Data['EyeColor'];		
		$Height 		= $Data['Height'];	
		$Weight 		= $Data['Weight'];	
		$Transportation = $Data['Transportation'];	
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
			$Appearance['profile_id'] 	= $UserID;
			$Appearance['hair_color'] 	= $HairColor;
			$Appearance['eye_color'] 	= $EyeColor;
			if($Height!='')
			{
				$Appearance['height']		= str_replace("'", '#', $Height);				
			}
			$Appearance['weight']		= $Weight;

			$ProfileInfo['transportation']		= $Transportation;
			

			$EditOtherInfo  = $this->ProfileModel->EditOtherInfo($UserID,$Appearance);
			if($EditOtherInfo)
			{
				$this->ProfileModel->EditGeneralInfo($UserID, $ProfileInfo);
				$Response = ['Status'=>true,'Message'=>'Other Info Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	//////////////////////////////////////////////////

	public function AddAccreditationAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$Accreditations	= $Data['Accreditations'];		
		$ExpDate 		= $Data['ExpDate'];
		$File 			= $request->file('Image');
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
		if($Accreditations=='')
		{
			$Response = ['Status'=>false,'Message'=>'Accreditations Missing.'];	
			return response()->json($Response);	
		}
		if($ExpDate=='')
		{
			$Response = ['Status'=>false,'Message'=>'ExpDate Missing.'];	
			return response()->json($Response);	
		}
		if($File=='')
		{
			$Response = ['Status'=>false,'Message'=>'Image Missing.'];	
			return response()->json($Response);	
		}   
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{		
		 	if(!empty($File)){
			    $Path = 'public/Front/Users/Accreditations';
		        $ImageName = $File->getClientOriginalName();
		        $Upload = $File->move($Path, $ImageName);
		        $Details['image'] 		= $ImageName;
	        }
	        $Details['profile_id'] 		= $UserID;	
			$Details['accreditations'] 	= $Accreditations;
			$Details['exp_date'] 		= $ExpDate;
			

			$AddAccreditation  = $this->ProfileModel->AddAccreditation($Details);
			if($AddAccreditation)
			{
				$Response = ['Status'=>true,'Message'=>'Accreditation Added Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	public function EditAccreditationAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$AccID			= $Data['AccID'];
		$Accreditations	= $Data['Accreditations'];		
		$ExpDate 		= $Data['ExpDate'];
		$File 			= $request->file('Image');	
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
		if($AccID=='')
		{
			$Response = ['Status'=>false,'Message'=>'Accreditation ID Missing.'];	
			return response()->json($Response);	
		}	
		if($Accreditations=='')
		{
			$Response = ['Status'=>false,'Message'=>'Accreditations Missing.'];	
			return response()->json($Response);	
		}
		if($ExpDate=='')
		{
			$Response = ['Status'=>false,'Message'=>'ExpDate Missing.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{			
			if(!empty($File)){
			    $Path = 'public/Front/Users/Accreditations';
		        $ImageName = $File->getClientOriginalName();
		        $Upload = $File->move($Path, $ImageName);
		        $Details['image'] 		= $ImageName;
	        }
			$Details['profile_id'] 		= $UserID;
			$Details['accreditations'] 	= $Accreditations;
			$Details['exp_date'] 		= $ExpDate;
			$EditAccreditation  = $this->ProfileModel->EditAccreditation($AccID,$UserID,$Details);
			if($EditAccreditation)
			{
				$Response = ['Status'=>true,'Message'=>'Accreditation Updated Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	public function DeleteAccreditationAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$AccID			= $Data['AccID'];
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
		if($AccID=='')
		{
			$Response = ['Status'=>false,'Message'=>'Accreditation ID Missing.'];	
			return response()->json($Response);	
		}	
		
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{		
			$DeleteAccreditation  = $this->ProfileModel->DeleteAccreditation($AccID,$UserID);
			if($DeleteAccreditation)
			{
				$Response = ['Status'=>true,'Message'=>'Accreditation Deleted Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	//////////////////////////////////////////////////

	public function AddCertificationAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$Name			= $Data['Name'];		
		$Description 	= $Data['Description'];
		$File 			= $request->file('Image');
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
		if($Name=='')
		{
			$Response = ['Status'=>false,'Message'=>'Name Missing.'];	
			return response()->json($Response);	
		}
		if($Description=='')
		{
			$Response = ['Status'=>false,'Message'=>'Description Missing.'];	
			return response()->json($Response);	
		}
		if($File=='')
		{
			$Response = ['Status'=>false,'Message'=>'Image Missing.'];	
			return response()->json($Response);	
		}   
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{		
		 	if(!empty($File)){
			    $Path = 'public/Front/Users/Certification';
		        $ImageName = $File->getClientOriginalName();
		        $Upload = $File->move($Path, $ImageName);
		        $Details['image'] 		= $ImageName;
	        }
	        $Details['profile_id'] 		= $UserID;	
			$Details['name'] 			= $Name;
			$Details['description'] 	= $Description;			

			$AddCertification  = $this->ProfileModel->AddCertification($Details);
			if($AddCertification)
			{
				$Response = ['Status'=>true,'Message'=>'Certification Added Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	public function EditCertificationAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$CertID			= $Data['CertID'];
		$Name			= $Data['Name'];		
		$Description 	= $Data['Description'];
		$File 			= $request->file('Image');	
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
		if($CertID=='')
		{
			$Response = ['Status'=>false,'Message'=>'Certification ID Missing.'];	
			return response()->json($Response);	
		}	
		if($Name=='')
		{
			$Response = ['Status'=>false,'Message'=>'Name Missing.'];	
			return response()->json($Response);	
		}
		if($Description=='')
		{
			$Response = ['Status'=>false,'Message'=>'Description Missing.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{			
			if(!empty($File)){
			    $Path = 'public/Front/Users/Certification';
		        $ImageName = $File->getClientOriginalName();
		        $Upload = $File->move($Path, $ImageName);
		        $Details['image'] 		= $ImageName;
	        }
	        $Details['profile_id'] 		= $UserID;	
			$Details['name'] 			= $Name;
			$Details['description'] 	= $Description;
			$EditCertification  = $this->ProfileModel->EditCertification($CertID,$UserID,$Details);
			if($EditCertification)
			{
				$Response = ['Status'=>true,'Message'=>'Certification Updated Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	public function DeleteCertificationAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$CertID			= $Data['CertID'];
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
		if($CertID=='')
		{
			$Response = ['Status'=>false,'Message'=>'Certification ID Missing.'];	
			return response()->json($Response);	
		}	
		
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{		
			$DeleteCertification  = $this->ProfileModel->DeleteCertification($CertID,$UserID);
			if($DeleteCertification)
			{
				$Response = ['Status'=>true,'Message'=>'Certification Deleted Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
//////////////////////////////////////////////////
	public function UpdateAccountTypeAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$AccountType	= $Data['AccountType'];
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
		if($AccountType=='')
		{
			$Response = ['Status'=>false,'Message'=>'Account Type Missing.'];	
			return response()->json($Response);	
		}	
		if($AccountType<=0 || $AccountType>3)
		{
			$Response = ['Status'=>false,'Message'=>'Invalid Account Type.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{		
			$Details['type'] = $AccountType;
			$Details['updated_at'] = date('Y-m-d H:i:s',time());
			$UpdateAccountType  = $this->ProfileModel->EditGeneralInfo($UserID,$Details);
			if($UpdateAccountType)
			{
				$Response = ['Status'=>true,'Message'=>'Account Type Updated Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
		return response()->json($Response);		

		}

	}
	public function UpdateCompanyDetailsAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$CompanyName	= $Data['CompanyName'];
		$Website		= $Data['Website'];
		$AboutCompany	= $Data['AboutCompany'];
		$File 			= $request->file('Logo');
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
		if($CompanyName=='')
		{
			$Response = ['Status'=>false,'Message'=>'Company Name Missing.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{		
			if(!empty($File)){
			    $Path = 'public/Front/Users/Profile/Company';
		        $ImageName = $File->getClientOriginalName();
		        $Upload = $File->move($Path, $ImageName);
		        $Details['logo'] 		= $ImageName;
	        }
			$Details['profile_id'] 	= $UserID;
			$Details['name'] 	= $CompanyName;
			$Details['web']		= $Website;
			$Details['about'] 	= $AboutCompany;
			$Update 			= $this->ProfileModel->UpdateCompanyDetails($UserID,$Details);
			if($Update)
			{
				$Response = ['Status'=>true,'Message'=>'Company Details Updated Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	/////////////////////Language////////
	public function AddLanguageAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$Language		= $Data['Language'];	
		$Level 			= $Data['Level'];		
			
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
		if($Language=='')
		{
			$Response = ['Status'=>false,'Message'=>'Language Missing.'];	
			return response()->json($Response);	
		}
		if($Level=='')
		{
			$Response = ['Status'=>false,'Message'=>'Level Missing.'];	
			return response()->json($Response);	
		}
		
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{			
			$Details['profile_id'] 	= $UserID;
			$Details['language'] 	= $Language;
			$Details['level'] 		= $Level;

			$AddLanguage  = $this->ProfileModel->AddLanguage($Details);
			if($AddLanguage)
			{
				$Response = ['Status'=>true,'Message'=>'Language Added Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	public function EditLanguageAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$LanguageID		= $Data['LanguageID'];	
		$Language		= $Data['Language'];	
		$Level 			= $Data['Level'];		
			
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
		if($LanguageID=='')
		{
			$Response = ['Status'=>false,'Message'=>'LanguageID Missing.'];	
			return response()->json($Response);	
		}
		if($Language=='')
		{
			$Response = ['Status'=>false,'Message'=>'Language Missing.'];	
			return response()->json($Response);	
		}
		if($Level=='')
		{
			$Response = ['Status'=>false,'Message'=>'Level Missing.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{			
			$Details['language'] 	= $Language;
			$Details['level'] 		= $Level;

			$EditLanguage  = $this->ProfileModel->EditLanguage($LanguageID,$UserID,$Details);
			if($EditLanguage)
			{
				$Response = ['Status'=>true,'Message'=>'Language Updated Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	public function DeleteLanguageAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$LanguageID		= $Data['LanguageID'];
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
		if($LanguageID=='')
		{
			$Response = ['Status'=>false,'Message'=>'Language ID Missing.'];	
			return response()->json($Response);	
		}	
		
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{		
			$DeleteLanguage  = $this->ProfileModel->DeleteLanguage($LanguageID,$UserID);
			if($DeleteLanguage)
			{
				$Response = ['Status'=>true,'Message'=>'Language Deleted Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	///////////////////////////////////
	public function GotReferLinkAPI(Request $request)
	{
		$Data 			= $request->all();	
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
			$ReferLink = URL::to('/').'/sign-up'.'/'.$CheckLoginDetails->code;
			$Response = ['Status'=>true,
						'Message'=>' Refer Link.',
						'ReferLink'=>$ReferLink];
			return response()->json($Response);		
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
	public function ReferAFriendAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$EmailMobile	= $Data['EmailMobile'];
		$Type			= $Data['Type'];
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
		if($EmailMobile=='')
		{
			$Response = ['Status'=>false,'Message'=>'Email OR Mobile Missing.'];	
			return response()->json($Response);	
		}
		if($Type=='')
		{
			$Response = ['Status'=>false,'Message'=>'Type Missing.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{		
			$Datas['Link']	= route('SignupViewForRefer',array('code'=>$CheckLoginDetails->code));
			$EmailMessage = View('Front/EmailTemplates/SendInvites')->with($Datas);
			$SMSMessage = "";
			if($Type=='Email')
			{			
				$Send = $this->SendInviteMail($EmailMobile, $EmailMessage);
				if($Send)
				{
					$Response = ['Status'=>true,'Message'=>'Invitation Sent Sucessfully.'];
					return response()->json($Response);
				}
				else
				{
					$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
					return response()->json($Response);
				}
			}
			else if($Type=="Mobile")
			{
				$Send = $this->SendInviteSMS($EmailMobile, $SMSMessage);
				if($Send)
				{
					$Response = ['Status'=>true,'Message'=>'Invitation Sent Sucessfully.'];
					return response()->json($Response);
				}
				else
				{
					$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
					return response()->json($Response);
				}
			}	
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}

	public function SendInviteSMS($Mobile,$Message)
	{
		return true;
	}

	public function SendInviteMail($Email,$Message) 
	{ 
	    $New_Line = "\n";
	    $Headers = "MIME-Version: 1.0" .$New_Line;
	    $Headers .= "Content-type: text/html; charset=iso-8859-1" .$New_Line;
	    $Headers .= "Content-Transfer-Encode: 7bit " .$New_Line;

	    $Headers .= "X-Mailer: PHP " .$New_Line;  
	    $Subject = "Invites";       
	    $mail_sent = mail($Email, $Subject, $Message, $Headers);
	    return $mail_sent;
	}
	//////////////////////////////////////////////////
	public function GetAvailabilityAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$AvailabilityArray   = array();	

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
			$UserDetails 	= $this->UserModel->UserDetails($UserID);
			$Availability = array();
			if($UserDetails->availability!='')
			{
				$Availability = json_decode($UserDetails->availability);
			}
				
			$TimeSlots 		= $this->UserModel->GetTimeSlots();

			$Sample = array();
			$TimeStamp = strtotime('next Monday');
			for($i = 0; $i < 7; $i++) 
			{ 
				if($i==0)
				{
					$Day= strftime('%A', $TimeStamp);					
				}
				else
				{
					$Day= date('l', strtotime('+'.$i.' day',  $TimeStamp ));	
				}
				
				$Sample['Day'] 		= $Day;

				$From=""; 
				$To="";
				$FromVal=""; 
				$ToVal="";

				if(!empty($Availability))
				{
					if($Availability[$i]->NotAvailable==0)
					{
						$FromSlotID = $Availability[$i]->FromSlotID;
						$FromVal 	= $this->GetSlot($Availability[$i]->FromSlotID);
						$ToSlotID 	= $Availability[$i]->ToSlotID;
						$ToVal 		= $this->GetSlot($Availability[$i]->ToSlotID);
					}
					else
					{
						$FromSlotID 	= '';
						$FromVal 		= '';
						$ToSlotID 		= '';
						$ToVal 			= '';
					}
				}
				else
				{
					$FromSlotID 	= '';
					$FromVal 		= '';
					$ToSlotID 		= '';
					$ToVal 			= '';
				}

				$Sample['FromSlotID'] 	= $FromSlotID;
				$Sample['From'] 		= $FromVal;
				$Sample['ToSlotID']	 	= $ToSlotID;
				$Sample['To']	 		= $ToVal;

				array_push($AvailabilityArray, $Sample);
			 
			}

			$Response = ['Status'=>true,
						'Message'=>'Check Availability.',
						'Availability'=>$AvailabilityArray
						];	
			return response()->json($Response);			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}	
	}
	public function GetSlot($SlotID)
	{
		$Slot = "";
		$TimeSlots 		= $this->UserModel->GetTimeSlots();

		foreach($TimeSlots as $ts)
		{ 													
			if($SlotID == $ts->id)
			{ 
				$Slot = $ts->time_slot;
			}													
		} 
		return $Slot;
	}
	public function GetTimeSlotAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$TimeSlotArray  = array();	
		$TimeSlots 	= $this->UserModel->GetTimeSlots();

		foreach($TimeSlots as $ts)
		{ 													
			$Sample=array();													
			$Sample['SlotID'] 	= $ts->id;
			$Sample['Slot'] 	= $ts->time_slot;
			array_push($TimeSlotArray, $Sample);
		} 
		$Response = ['Status'=>true,
					'Message'=>'Time Slots',
					'TimeSlots'=>$TimeSlotArray];
		return response()->json($Response);		
	}
	public function GetDayListAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$DayListArray  = array();	
		

		$TimeStamp = strtotime('next Monday');
		for($i = 0; $i < 7; $i++) 
		{ 
			$Sample=array();	
			$Day= strftime('%A', $TimeStamp);
			$TimeStamp = strtotime('+1 day', $TimeStamp);	
			$Sample[] 	= $Day;
			array_push($DayListArray, $Sample);
		} 
		$Response = ['Status'=>true,
					'Message'=>'Day List',
					'DayList'=>$DayListArray];
		return response()->json($Response);		
	}

	public function SaveAvailabilityAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	
		$Availability 	= $Data['Availability'];	
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
		if($Availability=='')
		{
			$Response = ['Status'=>false,'Message'=>'Availability Missing.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{	
			$Details['availability'] = $Availability;
			$Save = $this->UserModel->UpdateProfileDetails($UserID,$Details);
	        if($Save)
			{
				$Response = ['Status'=>true,'Message'=>'Availability Details Has been Updated.'];	
				return response()->json($Response);
			}
			else
	      	{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];	
				return response()->json($Response);	
	      	}		
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}		
	}


	//////////////////////////////////////////////
	public function UpdateAddressAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	

		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];	

		$Location 		= $Data['Location'];	
		$Latitude 		= $Data['Latitude'];	
		$Longitude 		= $Data['Longitude'];	
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
		if($Location=='')
		{
			$Response = ['Status'=>false,'Message'=>'Location Missing.'];	
			return response()->json($Response);	
		}
		if($Latitude=='')
		{
			$Response = ['Status'=>false,'Message'=>'Latitude Missing.'];	
			return response()->json($Response);	
		}
		if($Longitude=='')
		{
			$Response = ['Status'=>false,'Message'=>'Longitude Missing.'];	
			return response()->json($Response);	
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			
			$Details['location'] 	= $Location;
			$Details['latitude'] 	= $Latitude;
			$Details['longitude'] 	= $Longitude;

			$EditGeneralInfo  = $this->ProfileModel->EditGeneralInfo($UserID, $Details);
			if($EditGeneralInfo)
			{
				$Response = ['Status'=>true,'Message'=>'Adrdess-Info Updated Successfully!'];
				return response()->json($Response);
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'OOPS! Something Wrong Please Try Again.'];
				return response()->json($Response);
			}
		
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];	
			return response()->json($Response);		
		}
	}
}