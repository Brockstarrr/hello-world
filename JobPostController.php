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
use App\Http\Models\Front\JobPostModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;
use DateTime;

class JobPostController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->JobPostModel = new JobPostModel();
	}

	public function PostAJob()
	{
		$Data['Title'] 			= 'Post A Job';
		$Data['Menu'] 			= 'PostAJob';
		$Data['JobCategory'] 	= $this->JobPostModel->GetJobCategory();
		$Data['HairColor'] 		= $this->JobPostModel->HairColor();
		$Data['EyeColor'] 		= $this->JobPostModel->EyeColor();
		$Data['LanguageList'] 		= $this->JobPostModel->LanguageList();
		
		return View('Front/Pages/User/PostAJob')->with($Data);
	}

	public function GetJobSubCategory(Request $request)
	{
		$Data 		= $request->all(); 
		$CategoryID = $Data['CategoryID']; 
		$JobSubCategory = $this->JobPostModel->GetJobSubCategory($CategoryID);
		$Preference = $this->JobPostModel->GetPreference($CategoryID);
		
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

	public function GetSubCatAndOpenings(Request $request)
	{
		$Data 		= $request->all(); 
		$CategoryID = $Data['CategoryID']; 
		$JobSubCategory = $this->JobPostModel->GetJobSubCategory($CategoryID);
		$Number = rand(1,100);
		?>
		<div class="row" id="Div_<?php echo $Number; ?>">
			<div class="col-md-6">
				<div class="form-group">
					<label class="input_label2">Sub-Category</label>
					<select class="form-control inputfield2" id="SubCategoryID" name="SubCategoryID[]"
						onchange="Hide('SubCategoryIDErr');" >
						<option value=''>Select Sub-Category</option>
						<?php foreach($JobSubCategory as $p) 
						{
							?>
							<option value='<?php echo  $p->id; ?>'><?php echo  $p->position; ?></option>			
							<?php 
						} 
						?>
					</select>
				</div>
				<span id="SubCategoryIDErr"></span>
			</div>
			<div class="col-md-6 add_cat_icon">
				<div class="form-group">
					<label class="input_label2">No. of Openings</label>
					<input id="Openings" name="Openings[]" type="text" class="form-control inputfield2" 
						placeholder="No. of Openings." onkeypress="Hide('OpeningsErr');">
					<span id="OpeningsErr"></span>
				</div>
				<a href="javascript:void(0);" onclick="Remove('<?php echo $Number; ?>');">
					<i class="fa fa-minus-circle" aria-hidden="true"></i>
				</a>
			</div>
		</div>
		<?php
		exit();
	}

	public function AddMoreLanguage(Request $request)
	{
		$Data 			= $request->all(); 
		$LanguageList 	= $this->JobPostModel->LanguageList();
		$RandomNo = rand(5,15).time();
		?>
		<div class="row" id='GetLanguage_<?php echo $RandomNo; ?>'>
			<div class="col-md-4">
				<div class="form-group">
					<select id="Language" name="Language[]" class="form-control inputfield2">
						<option value="">Select Language</option>
						<?php  foreach($LanguageList as $ll) { ?>
						<option value="<?php echo $ll->language; ?>"><?php echo $ll->language; ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="col-md-4">
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
						<button type="button" class="inputfield2" onclick="RemoveLanguage(<?php echo $RandomNo; ?>);">
							Remove
						</button>												
					</div>
				</div>
			</div>
		</div>
		<?php
		exit();
	}

	public function PositionAndOpeningModal(Request $request)
	{
	  	$Data 			= $request->all(); 
	  	
		$CategoryID 	= $Data['CategoryID']; 
		$NextCount 		= $Data['NextCount'];
		$JobSubCategory = $this->JobPostModel->GetJobSubCategory($CategoryID);
		$TimeSlots = $this->JobPostModel->GetTimeSlots();
		?>
		<div class="modal-header">
         	<h5>Position And Openings</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="row">
               <div class="col-md-7">
                  <div class="form-group">
                    <label class="input_label2">Position<span>*</span></label>
                    <select class="form-control inputfield2" id="SubCategoryID" 
								onchange="Hide('SubCategoryIDErr');">
                        <option value=''>Select Position</option>
                        <?php 
                        if(!empty($JobSubCategory))
						{
							foreach($JobSubCategory as $jsc)
							{	
								?>		
								<option value='<?php echo $jsc->id; ?>'><?php echo $jsc->position; ?></option>";			
								<?php
							}
						}
						?>
                    </select>
                  	<span id="SubCategoryIDErr"></span>
                  </div>
               </div>
               <div class="col-md-5 ">
                  <div class="form-group">
					<label class="input_label2">No. of Openings<span>*</span></label>
					<input id="Openings" type="text" class="form-control inputfield2" 
						placeholder="No. of Openings." onkeypress="Hide('OpeningsErr');">
					<span id="OpeningsErr"></span>
				</div>
               </div>
            </div>
            <div class="row">
            	<div class="col-md-6">
					 <div class="form-group">
					 	<div class="cus_radio post_radio">
					        <label class="input_label2">Job For</label>
					        <div class="custom-control custom-radio custom-control-inline">
				            	<input onclick='ShowHideEndDate(),GetTotalHours();' id="customRadio11" class="custom-control-input" type="radio" value="2" name='job_for' checked/>
				        		<label class="custom-control-label" for="customRadio11">Multiple Day</label>
				        	</div>
				        	<div class="custom-control custom-radio custom-control-inline">
				            	<input onclick='ShowHideEndDate(),GetTotalHours();' id="customRadio12"  class="custom-control-input" type="radio" value="1" name='job_for'/>
				            	<label class="custom-control-label" for="customRadio12">One Day</label>
				            </div>
				        </div>
				    </div>
				</div>
                <div class="col-md-3">
					<div class="form-group">
						<label class="input_label2" id="JobForLabel">Job From<span>*</span></label>
						<input id='StartDate'  type="text" class="form-control inputfield2" 
						placeholder="Select Date" onclick="Hide('StartDateErr'),GetTotalHours();" >
						<span id="StartDateErr"></span>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group" id='EndDateDiv'>
						<label class="input_label2">Job To<span>*</span></label>
						<input id='EndDate'  type="text" class="form-control inputfield2" 
						placeholder="Select Date" onclick="Hide('EndDateErr'),GetTotalHours();">
						<span id="EndDateErr"></span>
					</div>
				</div>
            </div>
            <div class="row">
            	<div class="col-md-3">
					<div class="form-group">
						<label class="input_label2">Job Time From<span>*</span></label>
						<select id='HourTimeFrom'  type="text" class="form-control inputfield2" 
							onchange="Hide('HourTimeFromErr'),GetTotalHours(),CloseIfOpenBreaks();">
							<option value=''>Select Time</option>
							<?php foreach($TimeSlots as $ts){ ?>
								<option value='<?php echo $ts->id; ?>'><?php echo $ts->time_slot; ?></option>
							<?php } ?>
						</select>
						<span id="HourTimeFromErr"></span>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label class="input_label2">Job Time To<span>*</span></label>
						<select id='HourTimeTo'  type="text" class="form-control inputfield2" 
							onchange="Hide('HourTimeToErr'),GetTotalHours(),CloseIfOpenBreaks();">
							<option value=''>Select Time</option>
							<?php foreach($TimeSlots as $ts){ ?>
								<option value='<?php echo $ts->id; ?>'><?php echo $ts->time_slot; ?></option>
							<?php } ?>
						</select>
						<span id="HourTimeToErr"></span>
					</div>
				</div>
				<div class="col-md-3">
                  <div class="form-group">
						<label class="input_label2">Total Hours (Day*Hour)</label>
						<input readonly id="TotalHour"  onkeypress="Hide('TotalHourErr');" type="text" class="form-control inputfield2" placeholder="Total Hours">
						<span id="TotalHourErr"></span>
					</div>
               </div>
               <div class="col-md-3">
					<div class="form-group">
						<label class="input_label2">No of Breaks<span>*</span></label>
						<select id='Breaks' name='Breaks' type="text" class="form-control inputfield2" 
							onchange="Hide('BreaksErr'),GetBreakList();">
							<option value=''>Select</option>
							<?php for($B=1;$B<=5;$B++){ ?>
								<option value='<?php echo $B; ?>'><?php echo $B; ?></option>
							<?php } ?>
						</select>
						<span id="BreaksErr"></span>
					</div>
				</div>
            </div>
            <div class="row" id="BreakList">
				
            </div>
            <div class="row">
               <div class="col-md-4">
                  <div class="form-group">
                     <div class="cus_radio post_radio">
						<label class="input_label2">Pay Rate Type<span>*</span></label>
						<div class="custom-control custom-radio custom-control-inline">
							<input onclick="ShowHideTotalHour();" checked type="radio" class="custom-control-input" value="1" id="customRadio1" name="PayRateType">
							<label class="custom-control-label" for="customRadio1">Fixed</label>
						</div>
						<div class="custom-control custom-radio custom-control-inline">
							<input onclick="ShowHideTotalHour();" type="radio" class="custom-control-input" value="2"  id="customRadio2" name="PayRateType">
							<label class="custom-control-label" for="customRadio2">Hourly</label>
						</div>
					</div>
                  </div>
               </div>
               
               <div class="col-md-3">
                  <div class="form-group">
						<label class="input_label2">Pay Rate<span>*</span></label>
						<input id="PayRate"  onkeypress="Hide('PayRateErr');" type="text" class="form-control inputfield2" placeholder="Enter Rate" maxlength="5">
						<span id="PayRateErr"></span>
					</div>
               </div>
            </div>            
         </div>
        <div class="modal-footer">
            <button type="button" onclick="AddPositionAndOpenings();" class="btn pop-btn">Add</button>
        </div>
        
        <script type="text/javascript">
        	$('#PayRate').keypress(function(event) {
			  if((event.which != 46 || $(this).val().indexOf('.') != -1) && ((event.which < 48 || event.which > 57) && (event.which != 0 && event.which != 8))) {
			    event.preventDefault();
			  }
			  var text = $(this).val();
			  if((text.indexOf('.') != -1) && (text.substring(text.indexOf('.')).length > 2) && (event.which != 0 && event.which != 8) && ($(this)[0].selectionStart >= text.length - 2)) {
			    event.preventDefault();
			  }
			});
			$('#StartDate').Zebra_DatePicker({
		        direction: 1,
		        pair: $('#EndDate')
		    });

		    $('#EndDate').Zebra_DatePicker({
		        direction: 0
		    });
        </script>

        <?php
		exit();
	}

	public function PostAJobDetails(Request $request)
	{
		$Data 		= $request->all(); 
		
		$JobDetails['profile_id'] = Session::get('UserID');
		$JobDetails['job_type'] = $Data['JobType'];
		$JobDetails['job_title'] = $Data['JobTitle'];
		$JobDetails['job_cat'] = $Data['CategoryID'];
		$JobDetails['job_description'] = $Data['Description'];
		$JobDetails['address'] = $Data['Address'];
		$JobDetails['venue'] = $Data['Venue'];
		$JobDetails['location'] = $Data['Location'];
		$JobDetails['work_site_contact'] = $Data['WorkSiteContact'];
		$JobDetails['parking'] = $Data['Parking'];
		$JobDetails['latitude'] = $Data['latitude'];
		$JobDetails['longitude'] = $Data['longitude'];
		if(!isset($Data['NoMatter']))
		{
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
			$JobDetails['gender'] = $Data['Gender'];
			$JobDetails['no_matter'] = '1';
		}

		$JobDetails['uniform'] = $Data['Uniform'];
		/*if(isset($Data['Preference']))
		{
			$JobDetails['preference'] = json_encode($Data['Preference']);			
		}*/
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
	        	array_push($LanguageArray, $Sample);
        	}
        }

        $SubCatAndOpeningArray = array();  
        foreach($Data['PositionAndOpenings'] as $POS)
        {
        	$a = json_decode($POS);
        	$Sample = array();
        	
    		$Sample['sub_cat'] 		= $a->SubCategoryID;   	        		        		
    		$Sample['openings']	 	= $a->Openings;	        		        		
    		$Sample['job_for'] 		= $a->JobFor; 	        		        		
    		$Sample['start_date'] 	= $a->StartDate; 	        		        		
    		$Sample['end_date'] 	= $a->EndDate;    	        		        		
    		$Sample['hour_from'] 	= $a->HourTimeFrom;    	        		        		
    		$Sample['hour_from_label'] 	= $this->JobPostModel->GetTimeSlot($a->HourTimeFrom);    	        		        		
    		$Sample['hour_to'] 		= $a->HourTimeTo;    	        		        		
    		$Sample['hour_to_label'] 	= $this->JobPostModel->GetTimeSlot($a->HourTimeTo);    	        		        		
    		$Sample['breaks'] 		= $a->Breaks; 	        		        		
    		$Sample['break_time'] 			= json_encode($a->BreakTimes); 	        		        		
    		$Sample['break_paid_unpaid'] 	= json_encode($a->BreakPaidUnpaids); 		        		        		
    		$Sample['pay_type'] 	= $a->PayRateType; 	        		        		
    		$Sample['pay_rate'] 	= $a->PayRate;  	        		        		
    		$Sample['total_hour'] 	= $a->TotalHour;  	        		        		
        	
        	array_push($SubCatAndOpeningArray, $Sample);
        }
        
		$SaveJobDetails = $this->JobPostModel->SaveJobDetails($JobDetails,$LanguageArray,$SubCatAndOpeningArray);
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

	public function GetTotalHours(Request $request)
	{
		$Data 				= $request->all(); 
		$Response 			= array(); 
		$FinalTotalHours 	= 0; 
		$JobFor 			= $Data['JobFor'];
		$Breaks 			= $Data['Breaks'];
		
		$TimeSlotFrom 	= $Data['TimeSlotFrom'];
		$TimeSlotTo 	= $Data['TimeSlotTo'];

		if($TimeSlotFrom>=$TimeSlotTo)
		{
			$Response['Status'] 		= 0;
			$Response['TotalHours']		= '';
		}
		else
		{
			if($JobFor=='2')
			{
				$DateFrom 		= $Data['DateFrom'];
				$DateTo 		= $Data['DateTo'];
				$datediff 		= strtotime($DateTo) - strtotime($DateFrom);
				$TotalDay 		= round($datediff / (60 * 60 * 24));
			}
			else
			{
				$DateFrom 		= $Data['DateFrom'];
				$DateTo 		= '';
				$TotalDay		= 1; 
			}	

			$From 	= $this->JobPostModel->GetTime($TimeSlotFrom);
			$To 	= $this->JobPostModel->GetTime($TimeSlotTo);
				
			$difference = round(abs(strtotime($To) - strtotime($From)) / 3600,2);
			$FinalTotalHours =  $difference*$TotalDay;
			$Count = 0.00;
			if($Breaks!='')
			{
				$BreakTimes 		= $Data['BreakTimes'];
				$BreakPaidUnpaids	= $Data['BreakPaidUnpaids'];
				for($i=0;$i<$Breaks;$i++)
				{
					if($BreakPaidUnpaids[$i]=='Unpaid')
					{
						if($BreakTimes[$i]=='30')
						{
							$Count = $Count + 0.50;
						}
						else if($BreakTimes[$i]=='60')
						{
							$Count = $Count + 1.00;
						}
					}
				}
			}
			$Response['Status'] 		= 1;
			$Response['TotalHours']		= number_format(($FinalTotalHours-$Count),1);
		}
		echo json_encode($Response);
		exit();
	}

	public function GetBreakList(Request $request)
	{
		$Data 			= $request->all(); 
		$Breaks 		= $Data['Breaks'];

		for($b=1;$b<=$Breaks;$b++) 
		{
			?>
			<div class="col-md-2">
				<div class="form-group">
					<label class="input_label2">Break <?php echo $b; ?><span>*</span></label>
					<select onchange="GetTotalHours();"  id='BreakTime_<?php echo $b; ?>' name='BreakTime[]' type="text" class="form-control inputfield2">
						<?php for($BreakTime=30;$BreakTime<=60;$BreakTime+=30){ ?>
							<option value='<?php echo $BreakTime; ?>'><?php echo $BreakTime; ?> Min.</option>
						<?php } ?>
					</select>
					<select id='PaidUnpaid_<?php echo $b; ?>' onchange="GetTotalHours();" name='BreakPaidUnpaid[]' type="text" class="form-control inputfield2">						
						<option value='Paid'>Paid</option>
						<option value='Unpaid'>Unpaid</option>						
					</select>
				</div>
			</div>
			<?php
		}
		exit();
	}

	public function MakePositionList(Request $request)
	{
		$Response = array();
		$Result = array();
		$Data 				= $request->all();
		$NextCount 			= $Data['NextCount'];
		$SubCategoryID 		= $Data['SubCategoryID'];
		$Openings 			= $Data['Openings'];
		$JobFor 			= $Data['JobFor'];
		$StartDate 			= $Data['StartDate'];
		$EndDate 			= $Data['EndDate'];
		$HourTimeFrom 		= $Data['HourTimeFrom'];
		$HourTimeTo 		= $Data['HourTimeTo'];
		$Breaks 			= $Data['Breaks'];
		$BreakTimes 		= $Data['BreakTimes'];
		$BreakPaidUnpaids 	= $Data['BreakPaidUnpaids'];
		$TotalHour 			= $Data['TotalHour'];
		$PayRateType 		= $Data['PayRateType'];
		$PayRate 			= $Data['PayRate'];

		$GetSubCategoryName = $this->JobPostModel->GetSubCategoryName($SubCategoryID);

		$Result['NextCount'] 		= $NextCount;
		$Result['SubCategoryID'] 	= $SubCategoryID;
		$Result['Openings'] 		= $Openings;
		$Result['JobFor'] 			= $JobFor;
		$Result['StartDate'] 		= $StartDate;
		$Result['EndDate'] 			= $EndDate;
		$Result['HourTimeFrom'] 	= $HourTimeFrom;
		$Result['HourTimeTo'] 		= $HourTimeTo;
		$Result['Breaks'] 			= $Breaks;
		$Result['BreakTimes'] 		= $BreakTimes;
		$Result['BreakPaidUnpaids'] = $BreakPaidUnpaids;
		$Result['TotalHour'] 		= $TotalHour;
		$Result['PayRateType'] 		= $PayRateType;
		$Result['PayRate'] 			= $PayRate;
		$Result['SubCategoryName'] 			= $GetSubCategoryName;
		if($NextCount==0)
		{
		  $Heading ="<thead>
						<tr>
							<th>Position</th>
							<th>Openings</th>
							<th>Pay Type</th>
							<th>Pay Rate</th>
							<th>Action</th>
						</tr>
					</thead>";			
		}
		else
		{
			$Heading = '';
		}
		
		if($PayRateType=='1')
		{
			$PayRateTypeText = 'Fixed';
		}
		else if($PayRateType=='2')
		{
			$PayRateTypeText = 'Hourly';
		}
			$RandomNumber = rand(1,10000);
			$Result['RandomNumber'] 			= $RandomNumber;
		 	$Content = "<tbody>
					<tr id='PositionBreak_".$RandomNumber."'>
						<td>".$GetSubCategoryName."</td>
						<td>".$Openings."</td>
						<td>".$PayRateTypeText."</td>
						<td>$".number_format($PayRate,2)."
						<input type='hidden' id='PositionAndOpenings_".$RandomNumber."' name='PositionAndOpenings[]' value='".json_encode($Result)."'>
						</td>
						<td>
							<i class='fa fa-trash btn btn-danger btn-sm' onclick='RemoveTableRow(".$RandomNumber.")''></i>
							<i class='fa fa-edit btn btn-primary btn-sm' onclick='EditTableRow(".$RandomNumber.")''></i>
						</td>
					</tr>
				</tbody>";
		
		$Response['List'] 			= $Heading.$Content;
		$Response['Total'] 			= '';
		$Response['Count'] 			= $NextCount+1;
		$Response['PriceBreakDown'] = $this->GetPriceBreakDown($Result,'0');
		echo json_encode($Response);
		exit();
	}

	public function GetPriceBreakDown($Result,$State)
	{
		$NextCount 			= $Result['NextCount'];
		$SubCategoryID 		= $Result['SubCategoryID'];
		$Openings 			= $Result['Openings'];
		$JobFor 			= $Result['JobFor'];
		$StartDate 			= $Result['StartDate'];
		$EndDate 			= $Result['EndDate'];
		$HourTimeFrom 		= $Result['HourTimeFrom'];
		$HourTimeTo 		= $Result['HourTimeTo'];
		$Breaks 			= $Result['Breaks'];
		$BreakTimes 		= $Result['BreakTimes'];
		$BreakPaidUnpaids 	= $Result['BreakPaidUnpaids'];
		$TotalHour 			= $Result['TotalHour'];
		$PayRateType 		= $Result['PayRateType'];
		$PayRate 			= $Result['PayRate'];
		$RandomNumber 		= $Result['RandomNumber'];
		$SubCategoryName 	= $Result['SubCategoryName'];
		$GetCategoryDetails = $this->JobPostModel->GetCategoryDetails($SubCategoryID);
		if($NextCount==0)
		{
		  $Heading ="<thead>
						<tr>
							<th>Position</th>
							<th>Job Amount</th>
							<th>Gig Assist Commission</th>
							<th>Total Paid Amount</th>
						</tr>
					</thead>";			
		}
		else
		{
			$Heading = '';
		}
		$ComissionRate 		= $GetCategoryDetails->commission_rate;
		$TotalAmount 		= 0;
		$JobAmount 		= 0;
		if($PayRateType=='1')
		{
			$JobAmount 		= $PayRate;
		}
		else if($PayRateType=='2')
		{
			$JobAmount 		= $TotalHour*$PayRate;
		}
		
		$GigAssistComission = $JobAmount*$ComissionRate/100;
		$TotalAmount 	= $JobAmount+$GigAssistComission;

		if($State=='0')
		{
			$Content = "<tbody>
					<tr id='PriceBreak_".$RandomNumber."'>
						<td>".$SubCategoryName."</td>
						<td>$".number_format($JobAmount,2)."</td>
						<td>$".number_format($GigAssistComission,2)."</td>
						<td>$".number_format($TotalAmount,2)."</td>
					</tr>
				</tbody>";
		}
		else if($State=='1')
		{
			$Content = "<td>".$SubCategoryName."</td>
						<td>$".number_format($JobAmount,2)."</td>
						<td>$".number_format($GigAssistComission,2)."</td>
						<td>$".number_format($TotalAmount,2)."</td>";
		}
		return $Heading.$Content;
	}

	////////////////////
	public function EditPositionAndOpeningModal(Request $request)
	{
	  	$Data 			= $request->all(); 
	  	
		$DataDetails 	= json_decode($Data['DataDetails']); 
		$RandomID 		= $Data['RandomID'];
		$CategoryID 	= $Data['CategoryID'];

	  	$SubCategoryID 	= $DataDetails->SubCategoryID;
	  	$Openings 		= $DataDetails->Openings;
	  	$JobFor 		= $DataDetails->JobFor;
	  	$StartDate 		= $DataDetails->StartDate;
	  	$EndDate 		= $DataDetails->EndDate;
	  	$HourTimeFrom 	= $DataDetails->HourTimeFrom;
	  	$HourTimeTo 	= $DataDetails->HourTimeTo;
	  	$Breaks 		= $DataDetails->Breaks;
	  	$BreakTimes 	= $DataDetails->BreakTimes;
	  	$BreakPaidUnpaids 		= $DataDetails->BreakPaidUnpaids;
	  	$TotalHour 		= $DataDetails->TotalHour;
	  	$PayRateType 	= $DataDetails->PayRateType;
	  	$PayRate 		= $DataDetails->PayRate;

		$JobSubCategory = $this->JobPostModel->GetJobSubCategory($CategoryID);
		$TimeSlots = $this->JobPostModel->GetTimeSlots();


		?>
		<div class="modal-header">
         	<h5>Position And Openings</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="row">
               <div class="col-md-7">
                  <div class="form-group">
                    <label class="input_label2">Position<span>*</span></label>
                    <select class="form-control inputfield2" id="SubCategoryID" 
								onchange="Hide('SubCategoryIDErr');">
                        <option value=''>Select Position</option>
                        <?php 
                        if(!empty($JobSubCategory))
						{
							foreach($JobSubCategory as $jsc)
							{	
								?>		
								<option value='<?php echo $jsc->id; ?>'
									<?php if($SubCategoryID==$jsc->id){ echo "selected";} ?>><?php echo $jsc->position; ?></option>";			
								<?php
							}
						}
						?>
                    </select>
                  	<span id="SubCategoryIDErr"></span>
                  </div>
               </div>
               <div class="col-md-5 ">
                  <div class="form-group">
					<label class="input_label2">No. of Openings<span>*</span></label>
					<input id="Openings" type="text" class="form-control inputfield2" 
						value='<?php echo $Openings; ?>' placeholder="No. of Openings." onkeypress="Hide('OpeningsErr');">
					<span id="OpeningsErr"></span>
				</div>
               </div>
            </div>
            <div class="row">
            	<div class="col-md-6">
					 <div class="form-group">
					 	<div class="cus_radio post_radio">
					        <label class="input_label2">Job For</label>
					        <div class="custom-control custom-radio custom-control-inline">
				            	<input onclick='ShowHideEndDate();' id="customRadio11" class="custom-control-input" type="radio" value="2" name='job_for' checked/>
				        		<label class="custom-control-label" for="customRadio11">Multiple Day</label>
				        	</div>
				        	<div class="custom-control custom-radio custom-control-inline">
				            	<input onclick='ShowHideEndDate();' id="customRadio12"  class="custom-control-input" type="radio" value="1" name='job_for'/>
				            	<label class="custom-control-label" for="customRadio12">One Day</label>
				            </div>
				        </div>
				    </div>
				</div>
                <div class="col-md-3">
					<div class="form-group">
						<label class="input_label2" id="JobForLabel">Job From<span>*</span></label>
						<input id='StartDate'  type="text" class="form-control inputfield2" 
						value='<?php echo $StartDate; ?>' placeholder="Select Date" onclick="Hide('StartDateErr'),GetTotalHours();" >
						<span id="StartDateErr"></span>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group" id='EndDateDiv'>
						<label class="input_label2">Job To<span>*</span></label>
						<input id='EndDate'  type="text" class="form-control inputfield2" 
						value='<?php echo $EndDate; ?>'  placeholder="Select Date" onclick="Hide('EndDateErr'),GetTotalHours();">
						<span id="EndDateErr"></span>
					</div>
				</div>
            </div>
            <div class="row">
            	<div class="col-md-3">
					<div class="form-group">
						<label class="input_label2">Job Time From<span>*</span></label>
						<select id='HourTimeFrom'  type="text" class="form-control inputfield2" 
							onchange="Hide('HourTimeFromErr'),GetTotalHours();">
							<option value=''>Select Time<option>
							<?php foreach($TimeSlots as $ts){ ?>
								<option value='<?php echo $ts->id; ?>'
									<?php if($HourTimeFrom==$ts->id){ echo "selected"; } ?>><?php echo $ts->time_slot; ?></option>
							<?php } ?>
						</select>
						<span id="HourTimeFromErr"></span>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label class="input_label2">Job Time To<span>*</span></label>
						<select id='HourTimeTo'  type="text" class="form-control inputfield2" 
							onchange="Hide('HourTimeToErr'),GetTotalHours();">
							<option value=''>Select Time<option>
							<?php foreach($TimeSlots as $ts){ ?>
								<option value='<?php echo $ts->id; ?>'
									<?php if($HourTimeTo==$ts->id){ echo "selected"; } ?>><?php echo $ts->time_slot; ?></option>
							<?php } ?>
						</select>
						<span id="HourTimeToErr"></span>
					</div>
				</div>
				<div class="col-md-3">
                  <div class="form-group">
						<label class="input_label2">Total Hours (Day*Hour)</label>
						<input value='<?php echo $TotalHour; ?>' readonly id="TotalHour"  onkeypress="Hide('TotalHourErr');" type="text" class="form-control inputfield2" placeholder="Total Hours">
						<span id="TotalHourErr"></span>
					</div>
               </div>
               <div class="col-md-3">
					<div class="form-group">
						<label class="input_label2">No of Breaks<span>*</span></label>
						<select id='Breaks' name='Breaks' type="text" class="form-control inputfield2" 
							onchange="Hide('BreaksErr'),GetBreakList();">
							<option value=''>Select</option>
							<?php for($B=1;$B<=5;$B++){ ?>
								<option value='<?php echo $B; ?>'
									<?php if($Breaks==$B){ echo "selected"; } ?>><?php echo $B; ?></option>
							<?php } ?>
						</select>
						<span id="BreaksErr"></span>
					</div>
				</div>
            </div>
            <div class="row" id="BreakList">
				<?php
				for($b=1;$b<=$Breaks;$b++) 
				{
					?>
					<div class="col-md-2">
						<div class="form-group">
							<label class="input_label2">Break <?php echo $b; ?><span>*</span></label>
							<select name='BreakTime[]' type="text" class="form-control inputfield2">
								<?php for($BreakTime=30;$BreakTime<=60;$BreakTime+=30){ ?>
									<option value='<?php echo $BreakTime; ?>'
										<?php if($BreakTimes[$b-1]==$BreakTime){ echo "selected";} ?>><?php echo $BreakTime; ?> Min.</option>
								<?php } ?>
							</select>
							<select name='BreakPaidUnpaid[]' type="text" class="form-control inputfield2">						
								<option value='Paid' <?php if($BreakPaidUnpaids[$b-1]=='Paid'){ echo "selected";} ?>>Paid</option>
								<option value='Unpaid' <?php if($BreakPaidUnpaids[$b-1]=='Unpaid'){ echo "selected";} ?>>Unpaid</option>						
							</select>
						</div>
					</div>
					<?php
				}
				?>
            </div>
            <div class="row">
               <div class="col-md-4">
                  <div class="form-group">
                     <div class="cus_radio post_radio">
						<label class="input_label2">Pay Rate Type<span>*</span></label>
						<div class="custom-control custom-radio custom-control-inline">
							<input onclick="ShowHideTotalHour();" checked type="radio" class="custom-control-input" value="1" id="customRadio1" name="PayRateType">
							<label class="custom-control-label" for="customRadio1">Fixed</label>
						</div>
						<div class="custom-control custom-radio custom-control-inline">
							<input onclick="ShowHideTotalHour();" type="radio" class="custom-control-input" value="2"  id="customRadio2" name="PayRateType">
							<label class="custom-control-label" for="customRadio2">Hourly</label>
						</div>
					</div>
                  </div>
               </div>
               
               <div class="col-md-3">
                  <div class="form-group">
						<label class="input_label2">Pay Rate<span>*</span></label>
						<input value='<?php echo $PayRate; ?>' id="PayRate"  onkeypress="Hide('PayRateErr');" type="text" class="form-control inputfield2" placeholder="Enter Rate">
						<span id="PayRateErr"></span>
					</div>
               </div>
            </div>            
         </div>
        <div class="modal-footer">
        	<input type='hidden' name='RandomID' id='RandomID' value='<?php echo $RandomID; ?>'>
            <button type="button" onclick="EditPositionAndOpenings();" class="btn pop-btn">Update</button>
        </div>
        <script type="text/javascript" src="<?php echo asset('public/Front/Design/DatePicker/core.js'); ?>"></script>
        <?php
		exit();
	}
	public function EditPositionList(Request $request)
	{
		$Response = array();
		$Result = array();
		$Data 				= $request->all();
		$NextCount 			= $Data['NextCount'];
		$SubCategoryID 		= $Data['SubCategoryID'];
		$Openings 			= $Data['Openings'];
		$JobFor 			= $Data['JobFor'];
		$StartDate 			= $Data['StartDate'];
		$EndDate 			= $Data['EndDate'];
		$HourTimeFrom 		= $Data['HourTimeFrom'];
		$HourTimeTo 		= $Data['HourTimeTo'];
		$Breaks 			= $Data['Breaks'];
		$BreakTimes 		= $Data['BreakTimes'];
		$BreakPaidUnpaids 	= $Data['BreakPaidUnpaids'];
		$TotalHour 			= $Data['TotalHour'];
		$PayRateType 		= $Data['PayRateType'];
		$PayRate 			= $Data['PayRate'];

		$GetSubCategoryName = $this->JobPostModel->GetSubCategoryName($SubCategoryID);

		$Result['NextCount'] 		= $NextCount;
		$Result['SubCategoryID'] 	= $SubCategoryID;
		$Result['Openings'] 		= $Openings;
		$Result['JobFor'] 			= $JobFor;
		$Result['StartDate'] 		= $StartDate;
		$Result['EndDate'] 			= $EndDate;
		$Result['HourTimeFrom'] 	= $HourTimeFrom;
		$Result['HourTimeTo'] 		= $HourTimeTo;
		$Result['Breaks'] 			= $Breaks;
		$Result['BreakTimes'] 		= $BreakTimes;
		$Result['BreakPaidUnpaids'] = $BreakPaidUnpaids;
		$Result['TotalHour'] 		= $TotalHour;
		$Result['PayRateType'] 		= $PayRateType;
		$Result['PayRate'] 			= $PayRate;
		$Result['SubCategoryName'] 			= $GetSubCategoryName;
		if($NextCount==0)
		{
		  $Heading ="<thead>
						<tr>
							<th>Position</th>
							<th>Openings</th>
							<th>Pay Type</th>
							<th>Pay Rate</th>
							<th>Action</th>
						</tr>
					</thead>";			
		}
		else
		{
			$Heading = '';
		}
		
		if($PayRateType=='1')
		{
			$PayRateTypeText = 'Fixed';
		}
		else if($PayRateType=='2')
		{
			$PayRateTypeText = 'Hourly';
		}
			$RandomNumber = $NextCount;
			$Result['RandomNumber'] 			= $RandomNumber;
		 	$Content = "<td>".$GetSubCategoryName."</td>
						<td>".$Openings."</td>
						<td>".$PayRateTypeText."</td>
						<td>$".number_format($PayRate,2)."
						<input type='hidden' id='PositionAndOpenings_".$RandomNumber."' name='PositionAndOpenings[]' value='".json_encode($Result)."'>
						</td>
						<td>
							<i class='fa fa-trash btn btn-danger btn-sm' onclick='RemoveTableRow(".$RandomNumber.")''></i>
							<i class='fa fa-edit btn btn-primary btn-sm' onclick='EditTableRow(".$RandomNumber.")''></i>
						</td>";
		
		$Response['List'] 			= $Heading.$Content;
		$Response['RandomID'] 		= $NextCount;
		$Response['PriceBreakDown'] = $this->GetPriceBreakDown($Result,'1');
		echo json_encode($Response);
		exit();
	}
}