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
use App\Http\Models\Admin\LocationModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class LocationController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->LocationModel = new LocationModel();
	}

	/*Country List*/
	public function Country(Request $request)
	{
		$Data['Title'] 				= 'Country';
		$Data['Menu'] 				= 'Location';
		$Data['SubMenu'] 			= 'Country';
		return View('Admin/Location/Country')->with($Data);
	}
	public function CountryList(Request $request){
		$Data = $request->all();
		$Search['name'] 	= $Data['name'];
		$Search['code']		= $Data['code'];
		$Search['sort']		= $Data['sort'];
		$Search['status']	= $Data['status'];
		$page 						= $Data['page'];

		$numofrecords   = Session::get('no_of_page');
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;
		$Result				= $this->LocationModel->CountryList($start,$numofrecords,$Search);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];
		foreach($Result_arr as $row){
			$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',1)"><span class="badge badge-danger m-1">De-active</span></a>';
			if($row->status==1){
				$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',0)"><span class="badge badge-success m-1">Active</span></a>';
			}
		?>
			<tr>
        <td><?=$row->country_code?></td>
        <td><?=$row->country_name?></td>
        <td><?=$row->sort?></td>
        <td><?=$Status?></td>
        <td>
        	<a href="<?=route('EditCountry',array('ID'=>base64_encode($row->id)) )?>">
        		<button class="btn btn-success"> <i class="fa fa-edit"></i> </button>
        	</a>
        </td>
      </tr>
		<?php	
		}
		echo '<tr><td colspan="10">'.Common::Pagination($numofrecords, $Count, $page).'</td></tr>';
	}
	public function CountryChangeStatus(Request $request){
		$Data 	= $request->all();
		$id 		= $Data['id'];
		$status = $Data['status'];
		$this->LocationModel->UpdateCountry($id,['status'=>$status]);
	}
	public function AddCountry(){
		$Data['Title'] 				= 'Add Country';
		$Data['Menu'] 				= 'Location';
		$Data['SubMenu'] 			= 'Country';
		return View('Admin/Location/AddCountry')->with($Data);
	}
	public function EditCountry($ID){
		$id = base64_decode($ID);
		$CheckID = $this->LocationModel->CheckCountryName(['id'=>$id]);
		if($CheckID==0){
			return Redirect()->back();
		}
		$Result 					= $this->LocationModel->GetCountryDetails($id);
		
		$Data['Result'] 	= $Result;
		$Data['Title'] 		= 'Edit Country';
		$Data['Menu'] 		= 'Location';
		$Data['SubMenu'] 	= 'Country';
		return View('Admin/Location/EditCountry')->with($Data);
	}
	public function InsertCountry(Request $request){
		$Data = $request->all();

		$Save['country_name'] 		= $Data['name'];
		$Save['country_code'] 		= $Data['code'];
		$Save['sort'] 						= $Data['sort'];
		$Save['status'] 					= $Data['status'];
		$Save['update_date'] 			= date('Y-m-d H:i:s');

		$CheckName = $this->LocationModel->CheckCountryName(['country_name'=>$Save['country_name']]);
		if($CheckName > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','Country name already exists.');
			echo json_encode($arr);
			exit();
		}
		$CheckCode = $this->LocationModel->CheckCountryName(['country_code'=>$Save['country_code']]);
		if($CheckCode > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','Country code already exists.');
			echo json_encode($arr);
			exit();
		}
		$Result = $this->LocationModel->AddCountry($Save);
		if($Result){
			$arr['status'] 	= 1;
			$arr['msg'] 		= Common::AlertErrorMsg('Success','Country Details Has been Added.');
		}else{
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','OOPS! Something Wrong Please Try Again.');
		}
		echo json_encode($arr);
		exit();
	}
	public function SaveCountry(Request $request){
		$Data = $request->all();

		$EditID							 			= $Data['edit_id'];
		
		$Save['country_name'] 		= $Data['name'];
		$Save['country_code'] 		= $Data['code'];
		$Save['sort'] 						= $Data['sort'];
		$Save['status'] 					= $Data['status'];
		$Save['update_date'] 			= date('Y-m-d H:i:s');

		$CheckName = $this->LocationModel->CheckCountryEditName(['country_name'=>$Save['country_name']],$EditID);
		if($CheckName > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','Country name already exists.');
			echo json_encode($arr);
			exit();
		}
		$CheckCode = $this->LocationModel->CheckCountryEditName(['country_code'=>$Save['country_code']],$EditID);
		if($CheckCode > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','Country code already exists.');
			echo json_encode($arr);
			exit();
		}

		$Result = $this->LocationModel->UpdateCountry($EditID,$Save);
		if($Result){
			$arr['status'] 	= 1;
			$arr['msg'] 		= Common::AlertErrorMsg('Success','Country Details Has been Saved.');
		}else{
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','OOPS! Something Wrong Please Try Again.');
		}
		echo json_encode($arr);
		exit();
	}

	/*State List*/
	public function State(){
		$CountryList = $this->LocationModel->GetCountryList();
		$Data['Title'] 				= 'State List';
		$Data['Menu'] 				= 'Location';
		$Data['SubMenu'] 			= 'State';
		$Data['CountryList'] 	= $CountryList;
		return View('Admin/Location/State')->with($Data);
	}
	public function StateList(Request $request){
		$Data = $request->all();

		$Search['country_id'] = $Data['country_id'];
		$Search['name'] 	= $Data['name'];
		$Search['code']		= $Data['code'];
		$Search['sort']		= $Data['sort'];
		$Search['status']	= $Data['status'];
		$page 						= $Data['page'];

		$numofrecords   = Session::get('no_of_page');
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;

		$Result				= $this->LocationModel->StateList($start,$numofrecords,$Search);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];

		foreach ($Result_arr as $row) {
			
			$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',1)"><span class="badge badge-danger m-1">De-active</span></a>';
			if($row->status==1){
				$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',0)"><span class="badge badge-success m-1">Active</span></a>';
			}
		?>
			<tr>
				<td><?=$row->country_name?></td>
				<td><?=$row->state_code?></td>
				<td><?=$row->state_name?></td>
				<td><?=$row->sort?></td>
				<td><?=$Status?></td>
        <td>
        	<a href="<?=route('EditState',array('ID'=>base64_encode($row->id)) )?>">
        		<button class="btn btn-success"> <i class="fa fa-edit"></i> </button>
        	</a>
        </td>
			</tr>		
		<?php	
		}
		echo '<tr><td colspan="10">'.Common::Pagination($numofrecords, $Count, $page).'</td></tr>';
	}
	public function StateChangeStatus(Request $request){
		$Data 	= $request->all();
		$id 		= $Data['id'];
		$status = $Data['status'];
		$this->LocationModel->UpdateState($id,['status'=>$status]);
	}
	public function AddState(){
		$CountryList = $this->LocationModel->GetCountryList();
		$Data['Title'] 				= 'Add State';
		$Data['Menu'] 				= 'Location';
		$Data['SubMenu'] 			= 'State';
		$Data['CountryList'] 	= $CountryList;
		return View('Admin/Location/AddState')->with($Data);
	}
	public function EditState($ID){
		$id = base64_decode($ID);
		$CheckID = $this->LocationModel->CheckStateName(['id'=>$id]);
		if($CheckID==0){
			return Redirect()->back();
		}
		$Result 					= $this->LocationModel->GetStateDetails($id);
		$CountryList = $this->LocationModel->GetCountryList();

		$Data['Result'] 			= $Result;
		$Data['CountryList'] 	= $CountryList;
		$Data['Title'] 				= 'Edit State';
		$Data['Menu'] 				= 'Location';
		$Data['SubMenu'] 			= 'State';
		return View('Admin/Location/EditState')->with($Data);
	}
	public function InsertState(Request $request){
		$Data = $request->all();

		$Save['country_id'] 			= $Data['country_id'];
		$Save['state_name'] 			= $Data['name'];
		$Save['state_code'] 			= $Data['code'];
		$Save['sort'] 						= $Data['sort'];
		$Save['status'] 					= $Data['status'];
		$Save['update_date'] 			= date('Y-m-d H:i:s');

		$CheckName = $this->LocationModel->CheckStateName(['country_id'=>$Save['country_id'],'state_name'=>$Save['state_name'] ]);
		if($CheckName > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','State name already exists.');
			echo json_encode($arr);
			exit();
		}
		$CheckCode = $this->LocationModel->CheckCountryName(['country_id'=>$Save['country_id'],'state_code'=>$Save['state_code']]);
		if($CheckCode > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','State code already exists.');
			echo json_encode($arr);
			exit();
		}
		$Result = $this->LocationModel->AddState($Save);
		if($Result){
			$arr['status'] 	= 1;
			$arr['msg'] 		= Common::AlertErrorMsg('Success','State Details Has been Added.');
		}else{
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','OOPS! Something Wrong Please Try Again.');
		}
		echo json_encode($arr);
		exit();
	}
	public function SaveState(Request $request){
		$Data = $request->all();

		$EditID							 			= $Data['edit_id'];
		
		$Save['country_id'] 			= $Data['country_id'];
		$Save['state_name'] 			= $Data['name'];
		$Save['state_code'] 			= $Data['code'];
		$Save['sort'] 						= $Data['sort'];
		$Save['status'] 					= $Data['status'];
		$Save['update_date'] 			= date('Y-m-d H:i:s');

		$CheckName = $this->LocationModel->CheckStateEditName(['state_name'=>$Save['state_name']],$EditID);
		if($CheckName > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','Sate name already exists.');
			echo json_encode($arr);
			exit();
		}
		$CheckCode = $this->LocationModel->CheckStateEditName(['state_code'=>$Save['state_code']],$EditID);
		if($CheckCode > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','Sate code already exists.');
			echo json_encode($arr);
			exit();
		}

		$Result = $this->LocationModel->UpdateState($EditID,$Save);
		if($Result){
			$arr['status'] 	= 1;
			$arr['msg'] 		= Common::AlertErrorMsg('Success','State Details Has been Saved.');
		}else{
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','OOPS! Something Wrong Please Try Again.');
		}
		echo json_encode($arr);
		exit();
	}

	/*City List*/
	public function City(){
		$CountryList = $this->LocationModel->GetCountryList();
		$Data['Title'] 				= 'City List';
		$Data['Menu'] 				= 'Location';
		$Data['SubMenu'] 			= 'City';
		$Data['CountryList'] 	= $CountryList;
		return View('Admin/Location/City')->with($Data);
	}
	public function CityList(Request $request){
		$Data = $request->all();

		$Search['country_id'] = $Data['country_id'];
		$Search['state'] 	= $Data['state'];
		$Search['city'] 	= $Data['city'];
		$Search['code']		= $Data['code'];
		$Search['status']	= $Data['status'];
		$page 						= $Data['page'];

		$numofrecords   = Session::get('no_of_page');
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;

		$Result				= $this->LocationModel->CityList($start,$numofrecords,$Search);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];

		foreach ($Result_arr as $row) {
			$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',1)"><span class="badge badge-danger m-1">De-active</span></a>';
			if($row->status==1){
				$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',0)"><span class="badge badge-success m-1">Active</span></a>';
			}
		?>
			<tr>
				<td><?=$row->country_name?></td>
				<td><?=$row->state_name?></td>
				<td><?=$row->city_name?></td>
				<td><?=$row->city_code?></td>
				<td><?=$Status?></td>
        <td>
        	<a href="<?=route('EditCity',array('ID'=>base64_encode($row->id)) )?>">
        		<button class="btn btn-success"> <i class="fa fa-edit"></i> </button>
        	</a>
        </td>
			</tr>		
		<?php	
		}
		echo '<tr><td colspan="10">'.Common::Pagination($numofrecords, $Count, $page).'</td></tr>';
	}
	public function CityChangeStatus(Request $request){
		$Data 	= $request->all();
		$id 		= $Data['id'];
		$status = $Data['status'];
		$this->LocationModel->UpdateCity($id,['status'=>$status]);
	}
	public function AddCity(){
		$CountryList = $this->LocationModel->GetCountryList();
		$Data['Title'] 				= 'Add City';
		$Data['Menu'] 				= 'Location';
		$Data['SubMenu'] 			= 'City';
		$Data['CountryList'] 	= $CountryList;
		return View('Admin/Location/AddCity')->with($Data);
	}
	public function EditCity($ID){
		$id = base64_decode($ID);
		$CheckID = $this->LocationModel->CheckCityName(['id'=>$id]);
		if($CheckID==0){
			return Redirect()->back();
		}
		$Result 					= $this->LocationModel->GetCityDetails($id);
		$CountryList 			= $this->LocationModel->GetCountryList();

		$StateList = $this->LocationModel->GetStateList($Result->country_id);

		$Data['Result'] 			= $Result;
		$Data['CountryList'] 	= $CountryList;
		$Data['StateList'] 		= $StateList;
		$Data['Title'] 				= 'Edit City';
		$Data['Menu'] 				= 'Location';
		$Data['SubMenu'] 			= 'City';
		return View('Admin/Location/EditCity')->with($Data);
	}
	public function InsertCity(Request $request){
		$Data = $request->all();

		$Save['country_id'] 			= $Data['country_id'];
		$Save['state_id'] 				= $Data['state_id'];
		$Save['city_name'] 				= $Data['name'];
		$Save['city_code'] 				= $Data['code'];
		$Save['sort'] 						= $Data['sort'];
		$Save['status'] 					= $Data['status'];
		$Save['update_date'] 			= date('Y-m-d H:i:s');

		$CheckName = $this->LocationModel->CheckCityName(['state_id'=>$Save['state_id'],'city_name'=>$Save['city_name'] ]);
		if($CheckName > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','City name already exists.');
			echo json_encode($arr);
			exit();
		}
		$CheckCode = $this->LocationModel->CheckCityName(['state_id'=>$Save['state_id'],'city_code'=>$Save['city_code']]);
		if($CheckCode > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','City code already exists.');
			echo json_encode($arr);
			exit();
		}
		$Result = $this->LocationModel->AddCity($Save);
		if($Result){
			$arr['status'] 	= 1;
			$arr['msg'] 		= Common::AlertErrorMsg('Success','City Details Has been Added.');
		}else{
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','OOPS! Something Wrong Please Try Again.');
		}
		echo json_encode($arr);
		exit();
	}
	public function SaveCity(Request $request){
		$Data = $request->all();
		$EditID							 			= $Data['edit_id'];
		
		$Save['country_id'] 			= $Data['country_id'];
		$Save['state_id'] 				= $Data['state_id'];
		$Save['city_name'] 				= $Data['name'];
		$Save['city_code'] 				= $Data['code'];
		$Save['sort'] 						= $Data['sort'];
		$Save['status'] 					= $Data['status'];
		$Save['update_date'] 			= date('Y-m-d H:i:s');

		$CheckName = $this->LocationModel->CheckCityEditName(['city_name'=>$Save['city_name']],$EditID);
		if($CheckName > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','City name already exists.');
			echo json_encode($arr);
			exit();
		}
		$CheckCode = $this->LocationModel->CheckCityEditName(['city_code'=>$Save['city_code']],$EditID);
		if($CheckCode > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','City code already exists.');
			echo json_encode($arr);
			exit();
		}

		$Result = $this->LocationModel->UpdateCity($EditID,$Save);
		if($Result){
			$arr['status'] 	= 1;
			$arr['msg'] 		= Common::AlertErrorMsg('Success','City Details Has been Saved.');
		}else{
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','OOPS! Something Wrong Please Try Again.');
		}
		echo json_encode($arr);
		exit();
	}

	/*Locality List*/
	public function Locality(){
		$CountryList = $this->LocationModel->GetCountryList();
		$Data['Title'] 				= 'Locality List';
		$Data['Menu'] 				= 'Location';
		$Data['SubMenu'] 			= 'Locality';
		$Data['CountryList'] 	= $CountryList;
		return View('Admin/Location/Locality')->with($Data);
	}
	public function LocalityList(Request $request){
		$Data = $request->all();

		$Search['country_id'] = $Data['country_id'];
		$Search['state'] 			= $Data['state'];
		$Search['city'] 			= $Data['city'];
		$Search['locality']		= $Data['locality'];
		$Search['status']			= $Data['status'];
		$page 								= $Data['page'];

		$numofrecords   = Session::get('no_of_page');
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;

		$Result				= $this->LocationModel->LocalityList($start,$numofrecords,$Search);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];

		foreach ($Result_arr as $row) {
			$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',1)"><span class="badge badge-danger m-1">De-active</span></a>';
			if($row->status==1){
				$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',0)"><span class="badge badge-success m-1">Active</span></a>';
			}
		?>
			<tr>
				<td><?=$row->country_name?></td>
				<td><?=$row->state_name?></td>
				<td><?=$row->city_name?></td>
				<td><?=$row->locality_name?></td>
				<td><?=$Status?></td>
        <td>
        	<a href="<?=route('EditLocality',array('ID'=>base64_encode($row->id)) )?>">
        		<button class="btn btn-success"> <i class="fa fa-edit"></i> </button>
        	</a>
        </td>
			</tr>		
		<?php	
		}
		echo '<tr><td colspan="10">'.Common::Pagination($numofrecords, $Count, $page).'</td></tr>';
	}
	public function AddLocality(){
		$CountryList = $this->LocationModel->GetCountryList();
		$Data['Title'] 				= 'Add Locality';
		$Data['Menu'] 				= 'Location';
		$Data['SubMenu'] 			= 'Locality';
		$Data['CountryList'] 	= $CountryList;
		return View('Admin/Location/AddLocality')->with($Data);
	}
	public function InsertLocality(Request $request){
		$Data = $request->all();

		$Save['country_id'] 			= $Data['country_id'];
		$Save['state_id'] 				= $Data['state_id'];
		$Save['city_id'] 					= $Data['city_id'];
		$Save['locality_name'] 		= $Data['name'];
		$Save['locality_code'] 		= $Data['code'];
		$Save['sort'] 						= $Data['sort'];
		$Save['status'] 					= $Data['status'];
		$Save['update_date'] 			= date('Y-m-d H:i:s');

		$CheckName = $this->LocationModel->CheckLocalityName(['locality_name'=>$Save['locality_name'],'city_id'=>$Save['city_id'] ]);
		if($CheckName > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','Locality name already exists.');
			echo json_encode($arr);
			exit();
		}
		$CheckCode = $this->LocationModel->CheckLocalityName(['city_id'=>$Save['city_id'],'locality_code'=>$Save['locality_code']]);
		if($CheckCode > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','Locality code already exists.');
			echo json_encode($arr);
			exit();
		}
		$Result = $this->LocationModel->AddLocality($Save);
		if($Result){
			$arr['status'] 	= 1;
			$arr['msg'] 		= Common::AlertErrorMsg('Success','Locality Details Has been Added.');
		}else{
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','OOPS! Something Wrong Please Try Again.');
		}
		echo json_encode($arr);
		exit();
	}
	public function EditLocality($ID){
		$id = base64_decode($ID);
		$CheckID = $this->LocationModel->CheckLocalityName(['id'=>$id]);
		if($CheckID==0){
			return Redirect()->back();
		}
		$Result 					= $this->LocationModel->GetLocalityDetails($id);
		$CountryList 			= $this->LocationModel->GetCountryList();

		$StateList = $this->LocationModel->GetStateList($Result->country_id);
		$CityList = $this->LocationModel->GetCityList($Result->city_id);

		$Data['Result'] 			= $Result;
		$Data['CountryList'] 	= $CountryList;
		$Data['StateList'] 		= $StateList;
		$Data['CityList'] 		= $CityList;
		$Data['Title'] 				= 'Edit Locality';
		$Data['Menu'] 				= 'Location';
		$Data['SubMenu'] 			= 'Locality';
		return View('Admin/Location/EditLocality')->with($Data);
	}
	public function SaveLocality(Request $request){
		$Data = $request->all();
		$EditID							 			= $Data['edit_id'];
		
		$Save['country_id'] 			= $Data['country_id'];
		$Save['state_id'] 				= $Data['state_id'];
		$Save['city_id'] 					= $Data['city_id'];
		$Save['locality_name'] 		= $Data['name'];
		$Save['locality_code'] 		= $Data['code'];
		$Save['sort'] 						= $Data['sort'];
		$Save['status'] 					= $Data['status'];
		$Save['update_date'] 			= date('Y-m-d H:i:s');

		$CheckName = $this->LocationModel->CheckLocalityEditName(['locality_name'=>$Save['locality_name']],$EditID);
		if($CheckName > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','Locality name already exists.');
			echo json_encode($arr);
			exit();
		}
		$CheckCode = $this->LocationModel->CheckLocalityEditName(['locality_code'=>$Save['locality_code']],$EditID);
		if($CheckCode > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','Locality code already exists.');
			echo json_encode($arr);
			exit();
		}

		$Result = $this->LocationModel->UpdateLocality($EditID,$Save);
		if($Result){
			$arr['status'] 	= 1;
			$arr['msg'] 		= Common::AlertErrorMsg('Success','Locality Details Has been Saved.');
		}else{
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','OOPS! Something Wrong Please Try Again.');
		}
		echo json_encode($arr);
		exit();
	}
	public function LocalityChangeStatus(Request $request){
		$Data 	= $request->all();
		$id 		= $Data['id'];
		$status = $Data['status'];
		$this->LocationModel->UpdateLocality($id,['status'=>$status]);
	}
	/*Locality List*/



	public function GetStateList(Request $request){
		$Data = $request->all();
		$country_id = $Data['country_id'];

		$ResultArr = $this->LocationModel->GetStateList($country_id);
		$StateList = '<option value="">Select State</option>';
		foreach ($ResultArr as $row) {
			$StateList.= '<option value="'.$row->id.'">'.$row->state_name.'</option>';
		}
		echo $StateList;
		exit();
	}
	public function GetCityList(Request $request){
		$Data = $request->all();
		$state_id = $Data['state_id'];

		$ResultArr = $this->LocationModel->GetCityList($state_id);
		$StateList = '<option value="">Select City</option>';
		foreach ($ResultArr as $row) {
			$StateList.= '<option value="'.$row->id.'">'.$row->city_name.'</option>';
		}
		echo $StateList;
		exit();
	}
}