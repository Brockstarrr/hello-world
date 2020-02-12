<?php
namespace App\Http\Controllers\Admin;
use Route;
use Mail;
use Auth, Hash;
use Validator;
use Session;
use Redirect;
use DB;
use Crypt;
use Illuminate\Http\Request;
use App\Http\Models\Admin\JobsModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class JobsController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->JobsModel = new JobsModel();
	}


	public function AddJob()
	{
		$Data['Title'] 			= 'Add Job';
		$Data['Menu'] 			= 'Jobs';
		$Data['SubMenu'] 		= '';
		$Data['JobCategory'] 	= $this->JobsModel->GetJobCategory();
		$Data['HairColor'] 		= $this->JobsModel->HairColor();
		$Data['EyeColor'] 		= $this->JobsModel->EyeColor();
		$Data['LanguageList'] 	= $this->JobsModel->LanguageList();
		$Data['UserList'] 	    = $this->JobsModel->UserTypeList();
		return View('Admin/Jobs/Add')->with($Data);
	}

	public function List()
	{
		$Data['Title'] 				= 'Jobs List';
		$Data['Menu'] 				= 'Jobs';
		$Data['SubMenu'] 			= '';
		$Data['UserList'] 		= $this->JobsModel->UserList();
		$Data['CategoryList'] = $this->JobsModel->CategoryList();
		return View('Admin/Jobs/List')->with($Data);
	}
	
	public function JobListing(Request $request){
		$Data = $request->all();
		$Search['user_id']= $Data['user_id'];
		$Search['cat_id'] = $Data['cat_id'];
		$Search['job_type'] = $Data['job_type'];
		$Search['name'] 	= $Data['name'];
		$Search['status']	= $Data['status'];
		$page 				= $Data['page'];

		$numofrecords   = Session::get('no_of_page');
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;
		$Result				= $this->JobsModel->JobListing($start,$numofrecords,$Search);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];
		foreach($Result_arr as $row){

			$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',1)"><span class="badge badge-danger m-1">De-active</span></a>';
			if($row->status==1){
				$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',0)"><span class="badge badge-success m-1">Active</span></a>';
			}
			$job_type = 'Close Job';
			if($row->job_type==1){
				$job_type = 'Open Job';
			}
		?>
			<tr>
				<td><a href="<?=route('UserDetails',array('ID'=>base64_encode($row->profile_id)) )?>"><?=$row->first_name.' '.$row->last_name?></a></td>
        <td><?=$job_type?></td>
        <td><?=$row->category?></td>
        <td><?=$row->job_title?></td>
        <td><?=$Status?></td>
        <td>
        	
        	<a href="<?=route('ViewJobDetails',array('JobID'=>base64_encode($row->id)) )?>" target="_blank">
        		<button class="btn btn-primary"> <i class="fa fa-eye"></i> </button>
        	</a>
        </td>
      </tr>
		<?php	
		}
		echo '<tr><td colspan="10">'.Common::Pagination($numofrecords, $Count, $page).'</td></tr>';
	}
	public function JobChangeStatus(Request $request){
		$Data 	= $request->all();
		$id 	= $Data['id'];
		$status = $Data['status'];
		$this->JobsModel->UpdateData($id,['status'=>$status]);
	}


	public function AddMoreLanguage(Request $request)
	{
		$Data 			= $request->all(); 
		$LanguageList 	= $this->JobsModel->LanguageList();
		$RandomNo = rand(5,15).time();
		?>
		<div class="row">
		<div class="col-md-12">
		<div class="row" id='GetLanguage_<?php echo $RandomNo; ?>'>
			<div class="col-md-5">
				<div class="form-group">
					<select id="Language" name="Language[]" class="form-control inputfield2">
						<option value="">Select Language</option>
						<?php  foreach($LanguageList as $ll) { ?>
						<option value="<?php echo $ll->language; ?>"><?php echo $ll->language; ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="col-md-5">
				<div class="form-group">
					<select id="Proficiency" name="Proficiency[]" class="form-control inputfield2">
						<option value="">Select Proficiency</option>
						<option value="Beginner">Beginner</option>
						<option value="Intermediate">Intermediate</option>
						<option value="Proficient">Proficient</option>									
					</select>
				</div>
			</div>
			<div class="col-md-2">
				<div class="form-group">
					<div class="pop_up-btn">
						<button type="button" class="inputfield2 btn btn-success" onclick="RemoveLanguage(<?php echo $RandomNo; ?>);">
							Remove
						</button>												
					</div>
				</div>
			</div>
		</div>
	</div>
		</div>
		<?php
		exit();
	}


	public function CategorySubCategory(Request $request)
	{
		$Data 		= $request->all(); 
		$CategoryID = $Data['CategoryID']; 
		$JobSubCategory = $this->JobsModel->GetJobSubCategory($CategoryID);
		$Preference = $this->JobsModel->GetPreference($CategoryID);
		
		$SubCategoryList = "<option value=''>Select Sub-Category</option>";
		$PreferenceList = "<option value=''>Select Preference</option>";
		
		if(!empty($JobSubCategory))
		{
			foreach($JobSubCategory as $jsc)
			{			
				$SubCategoryList.= "<option value='".$jsc->id."'>".$jsc->position."</option>";			
			}
		}

		if(!empty($Preference))
		{
			foreach($Preference as $p)
			{			
				$PreferenceList.= "<option value='".$p->id."'>".$p->preference."</option>";			
			}
		}

		$Response['SubCategory'] = $SubCategoryList;
		$Response['Preference'] = $PreferenceList;
		echo json_encode($Response);
		exit();
	}



	public function AddJobDetails(Request $request)
	{
		$Data 		= $request->all(); 	
		
		$JobDetails['profile_id'] = $Data['User'];
		$JobDetails['job_type'] = $Data['JobType'];
		$JobDetails['job_title'] = $Data['JobTitle'];
		$JobDetails['job_cat'] = $Data['CategoryID'];
		$JobDetails['job_description'] = $Data['Description'];
		$JobDetails['address'] = $Data['Address'];
		$JobDetails['venue'] = $Data['Venue'];
		$JobDetails['location'] = $Data['Location'];
		$JobDetails['parking'] = $Data['Parking'];
		$JobDetails['latitude'] = $Data['latitude'];
		$JobDetails['longitude'] = $Data['longitude'];
		/*$JobDetails['pay_type'] = $Data['PayRateType'];
		$JobDetails['pay_rate'] = $Data['PayRate'];
		$JobDetails['start_date'] = $Data['StartDate'];
		$JobDetails['end_date'] = $Data['EndDate'];*/
		/*if(!isset($Data['MultipleDay']))
		{
			$JobDetails['start_date'] = $Data['StartDate'];
			$JobDetails['end_date'] = $Data['EndDate'];			
		}
		else
		{
			$JobDetails['multiple_time'] = $Data['MultipleDay'];
		}*/
		/*if(isset($Data['BreakTime']))
		{
			$JobDetails['break_time'] = $Data['BreakTime'];			
		}*/
		$JobDetails['age_limit'] = $Data['AgeLimitMin'].'-'.$Data['AgeLimitMax'];
		$JobDetails['hair_color'] = '';		
		if(!empty($Data['HairColor']))
		{
			$JobDetails['hair_color'] = json_encode($Data['HairColor']);		
		}
		$JobDetails['eye_color'] = '';		
		if(!empty($Data['EyesColor']))
		{
			$JobDetails['eye_color'] = json_encode($Data['EyesColor']);		
		}
		$JobDetails['height'] = $Data['HeightMin'].'-'.$Data['HeightMax'];
		$JobDetails['weight'] = $Data['WeightMin'].'-'.$Data['WeightMax'];
		$JobDetails['gender'] = $Data['Gender'];
		$JobDetails['uniform'] = $Data['Uniform'];
		$JobDetails['preference'] = json_encode($Data['Preference']);
		$File = $request->file('Picture');
	    if(!empty($File)){
		    $Path = 'public/Front/Users/Jobs';
	        $BannerName = str_replace(' ', '_', $File->getClientOriginalName());
	        $Upload = $File->move($Path, $BannerName);
	        $JobDetails['image'] 		= $BannerName;
        }

        $LanguageArray = array();
        foreach($Data['Language'] as $Key=>$Value)
        {
        	if($Value!='')
        	{        		
	        	$Sample['language'] = $Value;
	        	$Sample['level'] = $Data['Proficiency'][$Key];
	        	/*$String = 'LanguageStatus'.($Key+1);
	        	$Value = 'Not Mention';
	        	if(isset($Data[$String]))
	        	{
	        		$Value = implode(",",$Data[$String]); 
	        	}
	        	$Sample['level'] 	= $Value;*/
	        	array_push($LanguageArray, $Sample);
        	}
        }

        $SubCatAndOpeningArray = array();        
        foreach($Data['PositionAndOpenings'] as $v)
        {
        	/*$Sample['sub_cat'] = $Value;
        	$Openings = $Data['Openings'][0];
        	if(isset($Data['SubCategoryID'][$Key]) && $Data['Openings'][$Key]!='')
        	{
        		$Openings = $Data['Openings'][$Key];
        	}
        	$Sample['openings'] = $Openings;*/ 
        	$Sample = array();
        	$Array = json_decode($v);
        	foreach ($Array as $a) 
        	{
        		$Sample['sub_cat'] 		= $a->SubCategory;   	        		        		
        		$Sample['openings']	 	= $a->Openings;	        		        		
        		$Sample['pay_type'] 	= $a->PayRateType; 	        		        		
        		$Sample['pay_rate'] 	= $a->PayRate;  	        		        		
        		$Sample['total_hour'] 	= $a->TotalHour;  	        		        		
        		$Sample['start_date'] 	= $a->StartDate; 	        		        		
        		$Sample['end_date'] 	= $a->EndDate;  	        		        		
        	}  
        	array_push($SubCatAndOpeningArray, $Sample);
        }
		$SaveJobDetails = $this->JobsModel->SaveJobDetails($JobDetails,$LanguageArray,$SubCatAndOpeningArray);
		if($SaveJobDetails)
		{
			Session::flash('message', 'Job Details Saved Successfully.'); 
          	Session::flash('alert-class', 'alert-success'); 
          	return redirect( route('PostAJob' ));
		}
		else
		{
			Session::flash('message', 'OOPS! Something Wrong. PLease Try Again.'); 
          	Session::flash('alert-class', 'alert-danger'); 
          	return redirect( route('PostAJob' ));
		}

	}

	public function EditJobDetails($ID){
		$id = base64_decode($ID);
		$Data['Title'] 			            = 'Edit Job';
		$Data['Menu'] 			            = 'Jobs';
		$Data['SubMenu'] 		            = '';
		$Data['JobCategory'] 	            = $this->JobsModel->GetJobCategory();
		$Data['HairColor'] 		            = $this->JobsModel->HairColor();
		$Data['EyeColor'] 		            = $this->JobsModel->EyeColor();
		$Data['LanguageList'] 	            = $this->JobsModel->LanguageList();
		$Data['UserList'] 	                = $this->JobsModel->UserTypeList();
		$Data['PostAJob']                   = $this->JobsModel->PostAJob($id);
		$Data['EditPositionAndOpenings']    = $this->JobsModel->EditPositionAndOpenings($id);
		$Data['LanguageOptions']            = $this->JobsModel->LanguageOptions($id);
		$Data['HairColor']                  = $this->JobsModel->HairColorOptions($id);
		return View('Admin/Jobs/Edit')->with($Data);

	}

	public function DeletepositionRow($id)
	{
	   $Data 	= $request->all();
	   $id 	    = $Data['id'];
       $Result  = $this->JobsModel->DeletepositionRow($id);
       return true;
	}
}