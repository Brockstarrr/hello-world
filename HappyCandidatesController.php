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
use App\Http\Models\Admin\HappyCandidatesModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class HappyCandidatesController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->HappyCandidatesModel = new HappyCandidatesModel();
	}

	public function HappyCandidates()
	{
		$Data['Title'] 				= 'Happy Candidates List';
		$Data['Menu'] 				= 'CMS';
		$Data['SubMenu'] 			= 'HappyCandidates';
		return View('Admin/CMS/HappyCandidates')->with($Data);
	}
	public function AddHappyCandidates(){
		$Data['Title'] 				= 'Add Happy Candidates';
		$Data['Menu'] 				= 'CMS';
		$Data['SubMenu'] 			= 'HappyCandidates';
		return View('Admin/CMS/AddHappyCandidates')->with($Data);
	}
	public function EditHappyCandidates($ID){
		$id = base64_decode($ID);
		$CheckID = DB::table('happy_candidate')->where('id',$id)->count();
		if($CheckID==0){
			return Redirect()->back();
		}
		$Result 					= $this->HappyCandidatesModel->GetDetails($id);
		$Data['Result'] 	= $Result;
		$Data['Title'] 		= 'Edit Happy Candidates';
		$Data['Menu'] 		= 'CMS';
		$Data['SubMenu'] 	= 'HappyCandidates';
		return View('Admin/CMS/EditHappyCandidates')->with($Data);
	}
	public function InsertHappyCandidates(Request $request){
		$Data = $request->all();

		if(!empty($Data['image'])){
			$image = $Data['image'];
   	 	$Path = 'public/Front/HappyCandidates';
      $extension = $image->getClientOriginalExtension();
      $ImageName = Common::GenerateRandomId(5).'.'.$extension;
      $Upload = $image->move($Path, $ImageName);
      $Save['image'] 		= $ImageName;
		}
		$Save['name'] 				= $Data['name'];
		$Save['location'] 		= $Data['location'];
		$Save['description'] 	= $Data['description'];
		$Save['status'] 			= $Data['status'];
		$Save['add_date'] 		= date('Y-m-d H:i:s');

		$this->HappyCandidatesModel->InsertData($Save);
		$msg = Common::AlertErrorMsg('Success','Happy Candidates Details Has been Saved.');
		Session::flash('message', $msg);
		return Redirect()->back();
	}
	public function SaveHappyCandidates(Request $request){
		$Data = $request->all();

		$edit_id = $Data['edit_id'];

		if(!empty($Data['image'])){
			$image = $Data['image'];
   	 	$Path = 'public/Front/HappyCandidates';
      $extension = $image->getClientOriginalExtension();
      $ImageName = Common::GenerateRandomId(5).'.'.$extension;
      $Upload = $image->move($Path, $ImageName);
      $Save['image'] 		= $ImageName;

      if($Data['OldImage']){
      	unlink($Path.'/'.$Data['OldImage']);
      }

		}
		$Save['name'] 				= $Data['name'];
		$Save['location'] 		= $Data['location'];
		$Save['description'] 	= $Data['description'];
		$Save['status'] 			= $Data['status'];
		$Save['add_date'] 		= date('Y-m-d H:i:s');

		$this->HappyCandidatesModel->UpdateData($edit_id,$Save);
		$msg = Common::AlertErrorMsg('Success','Happy Candidates Details Has been Saved.');
		Session::flash('message', $msg);
		return Redirect()->back();
	}
	public function HappyCandidatesList(Request $request){
		$Data = $request->all();
		$Search['name'] 	= $Data['name'];
		$Search['location']		= $Data['location'];
		$Search['status']	= $Data['status'];
		$page 						= $Data['page'];

		$numofrecords   = Session::get('no_of_page');
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;
		$Result				= $this->HappyCandidatesModel->HappyCandidatesList($start,$numofrecords,$Search);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];
		foreach($Result_arr as $row){

			$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',1)"><span class="badge badge-danger m-1">De-active</span></a>';
			if($row->status==1){
				$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',0)"><span class="badge badge-success m-1">Active</span></a>';
			}
			$Image = asset('public/Admin/assets/images/logo-icon.png');
      if($row->image!=''){
        $Image = asset('public/Front/HappyCandidates/'.$row->image);
      }
		?>
			<tr>
        <td><img src="<?=$Image?>" style="height: 50px"></td>
        <td id="Name<?=$row->id?>"><?=$row->name?></td>
        <td><?=$row->location?></td>
        <td><?=$Status?></td>
        <td>
        	<input type="hidden" id="Description<?=$row->id?>" value="<?=$row->description?>">
        	<a href="<?=route('EditHappyCandidates',array('ID'=>base64_encode($row->id)) )?>">
        		<button class="btn btn-success"> <i class="fa fa-edit"></i> </button>
        	</a>
        	<a href="javascript:void(0)" onclick="OpenViewModal(<?=$row->id?>)">
        		<button class="btn btn-primary waves-effect waves-light m-1"> <i class="fa fa-eye"></i> </button>
        	</a>
        </td>
      </tr>
		<?php	
		}
		echo '<tr><td colspan="10">'.Common::Pagination($numofrecords, $Count, $page).'</td></tr>';
	}
	public function HappyCandidatesChangeStatus(Request $request){
		$Data 	= $request->all();
		$id 		= $Data['id'];
		$status = $Data['status'];
		$this->HappyCandidatesModel->UpdateData($id,['status'=>$status]);
	}
}