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
use App\Http\Models\Admin\NeedAJobModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class NeedAProController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->NeedAJobModel = new NeedAJobModel();
	}

	public function List()
	{
		$Data['Title'] 				= 'Need A Pro List';
		$Data['Menu'] 				= 'CMS';
		$Data['SubMenu'] 			= 'NeedAPro';
		return View('Admin/NeedAPro/List')->with($Data);
	}
	public function Add(){
		$Data['Title'] 				= 'Add Need A Pro';
		$Data['Menu'] 				= 'CMS';
		$Data['SubMenu'] 			= 'NeedAPro';
		return View('Admin/NeedAPro/Add')->with($Data);
	}
	public function EditNeedAPro($ID){
		$id = base64_decode($ID);
		$CheckID = $this->NeedAJobModel->CheckJobData(['id'=>$id]);
		if($CheckID==0){
			return Redirect()->back();
		}
		$Result 					= $this->NeedAJobModel->GetDetails($id);
		$Data['Result'] 	= $Result;
		$Data['Title'] 		= 'Edit Need A Pro';
		$Data['Menu'] 		= 'CMS';
		$Data['SubMenu'] 	= 'NeedAPro';
		return View('Admin/NeedAPro/Edit')->with($Data);
	}
	public function InsertNeedAPro(Request $request){
		$Data = $request->all();

		if(!empty($Data['image'])){
			$image = $Data['image'];
   	 	$Path = 'public/Front/NeedAJob';
      $extension = $image->getClientOriginalExtension();
      $ImageName = Common::GenerateRandomId(10).'.'.$extension;
      $Upload = $image->move($Path, $ImageName);
      $Save['image'] 		= $ImageName;
		}
		$Save['job_type'] 		= 2;
		$Save['name'] 				= $Data['name'];
		$Save['description'] 	= $Data['description'];
		$Save['sort'] 				= $Data['sort'];
		$Save['status'] 			= $Data['status'];
		$Save['add_date'] 		= date('Y-m-d H:i:s');

		$result = $this->NeedAJobModel->InsertData($Save);
		$msg = Common::AlertErrorMsg('Success','Need A Pro Details Has been Saved.');
		Session::flash('message', $msg);
		return Redirect()->back();
	}
	public function SaveNeedAPro(Request $request){
		$Data = $request->all();

		$EditID	= $Data['edit_id'];

		if(!empty($Data['image'])){
			$image = $Data['image'];
   	 	$Path = 'public/Front/NeedAJob';
      $extension = $image->getClientOriginalExtension();
      $ImageName = Common::GenerateRandomId(10).'.'.$extension;
      $Upload = $image->move($Path, $ImageName);
      $Save['image'] 		= $ImageName;

      if($Data['OldImage']){
      	@unlink($Path.'/'.$Data['OldImage']);
      }
		}
		$Save['job_type'] 		= 2;
		$Save['name'] 				= $Data['name'];
		$Save['description'] 	= $Data['description'];
		$Save['sort'] 				= $Data['sort'];
		$Save['status'] 			= $Data['status'];
		$Save['add_date'] 		= date('Y-m-d H:i:s');

		$Result = $this->NeedAJobModel->UpdateData($EditID,$Save);
		$msg = Common::AlertErrorMsg('Success','Need A Pro Details Has been Saved.');
		Session::flash('message', $msg);
		return Redirect()->back();
	}
	public function NeedAProListing(Request $request){
		$Data = $request->all();
		$Search['name'] 	= $Data['name'];
		$Search['status']	= $Data['status'];
		$Search['job_type']	= 2;
		$page 						= $Data['page'];

		$numofrecords   = Session::get('no_of_page');
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;
		$Result				= $this->NeedAJobModel->Listing($start,$numofrecords,$Search);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];
		foreach($Result_arr as $row){

			$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',1)"><span class="badge badge-danger m-1">De-active</span></a>';
			if($row->status==1){
				$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',0)"><span class="badge badge-success m-1">Active</span></a>';
			}
			$Image = asset('public/Admin/assets/images/logo-icon.png');
      if($row->image!=''){
        $Image = asset('public/Front/NeedAJob/'.$row->image);
      }
		?>
			<tr>
				<td><img src="<?=$Image?>" style="height: 50px"></td>
        <td id="Name<?=$row->id?>"><?=$row->name?></td>
        <td><?=$row->sort?></td>
        <td><?=$Status?></td>
        <td>
        	<input type="hidden" id="Desc<?=$row->id?>" value="<?=$row->description?>">
        	<a href="<?=route('EditNeedAPro',array('ID'=>base64_encode($row->id)) )?>">
        		<button class="btn btn-success"> <i class="fa fa-edit"></i> </button>
        	</a>
        	<a href="javascript:void(0)" onclick="OpenViewModal(<?=$row->id?>)">
        		<button class="btn btn-primary"> <i class="fa fa-eye"></i> </button>
        	</a>
        </td>
      </tr>
		<?php	
		}
		echo '<tr><td colspan="10">'.Common::Pagination($numofrecords, $Count, $page).'</td></tr>';
	}
	public function NeedAProChangeStatus(Request $request){
		$Data 	= $request->all();
		$id 		= $Data['id'];
		$status = $Data['status'];
		$this->NeedAJobModel->UpdateData($id,['status'=>$status]);
	}
}