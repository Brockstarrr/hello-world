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
use App\Http\Models\Admin\FeaturesModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class FeaturesController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->FeaturesModel = new FeaturesModel();
	}

	public function List()
	{
		$Data['Title'] 				= 'Features List';
		$Data['Menu'] 				= 'CMS';
		$Data['SubMenu'] 			= 'FeaturesList';
		return View('Admin/Features/List')->with($Data);
	}
	public function Add(){
		$Data['Title'] 				= 'Add Features';
		$Data['Menu'] 				= 'CMS';
		$Data['SubMenu'] 			= 'FeaturesList';
		return View('Admin/Features/Add')->with($Data);
	}
	public function EditFeatures($ID){
		$id = base64_decode($ID);
		$CheckID = $this->FeaturesModel->CheckFeatures(['id'=>$id]);
		if($CheckID==0){
			return Redirect()->back();
		}
		$Result 					= $this->FeaturesModel->GetDetails($id);
		$Data['Result'] 	= $Result;
		$Data['Title'] 		= 'Edit Features';
		$Data['Menu'] 		= 'CMS';
		$Data['SubMenu'] 	= 'FeaturesList';
		return View('Admin/Features/Edit')->with($Data);
	}
	public function InsertFeatures(Request $request){
		$Data = $request->all();

		if(!empty($Data['image'])){
			$image = $Data['image'];
   	 	$Path = 'public/Front/Features';
      $extension = $image->getClientOriginalExtension();
      $ImageName = Common::GenerateRandomId(10).'.'.$extension;
      $Upload = $image->move($Path, $ImageName);
      $Save['image'] 		= $ImageName;
		}

		$Save['name'] 				= $Data['name'];
		$Save['description'] 	= $Data['description'];
		$Save['sort'] 				= $Data['sort'];
		$Save['status'] 			= $Data['status'];
		$Save['add_date'] 		= date('Y-m-d H:i:s');

		$result = $this->FeaturesModel->InsertData($Save);
		$msg = Common::AlertErrorMsg('Success','Features Details Has been Saved.');
		Session::flash('message', $msg);
		return Redirect()->back();
	}
	public function SaveFeatures(Request $request){
		$Data = $request->all();

		$EditID	= $Data['edit_id'];

		if(!empty($Data['image'])){
			$image = $Data['image'];
   	 	$Path = 'public/Front/Features';
      $extension = $image->getClientOriginalExtension();
      $ImageName = Common::GenerateRandomId(10).'.'.$extension;
      $Upload = $image->move($Path, $ImageName);
      $Save['image'] 		= $ImageName;

      if($Data['OldImage']){
      	@unlink($Path.'/'.$Data['OldImage']);
      }
		}

		$Save['name'] 				= $Data['name'];
		$Save['description'] 	= $Data['description'];
		$Save['sort'] 				= $Data['sort'];
		$Save['status'] 			= $Data['status'];
		$Save['add_date'] 		= date('Y-m-d H:i:s');

		$Result = $this->FeaturesModel->UpdateData($EditID,$Save);
		$msg = Common::AlertErrorMsg('Success','Features Details Has been Saved.');
		Session::flash('message', $msg);
		return Redirect()->back();
	}
	public function FeaturesList(Request $request){
		$Data = $request->all();
		$Search['name'] 	= $Data['name'];
		$Search['status']	= $Data['status'];
		$page 						= $Data['page'];

		$numofrecords   = Session::get('no_of_page');
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;
		$Result				= $this->FeaturesModel->FeaturesList($start,$numofrecords,$Search);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];
		foreach($Result_arr as $row){

			$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',1)"><span class="badge badge-danger m-1">De-active</span></a>';
			if($row->status==1){
				$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',0)"><span class="badge badge-success m-1">Active</span></a>';
			}
			$Image = asset('public/Admin/assets/images/logo-icon.png');
      if($row->image!=''){
        $Image = asset('public/Front/Features/'.$row->image);
      }
		?>
			<tr>
				<td><img src="<?=$Image?>" style="height: 50px"></td>
        <td id="Name<?=$row->id?>"><?=$row->name?></td>
        <td><?=$row->sort?></td>
        <td><?=$Status?></td>
        <td>
        	<input type="hidden" id="Desc<?=$row->id?>" value="<?=$row->description?>">
        	<a href="<?=route('EditFeatures',array('ID'=>base64_encode($row->id)) )?>">
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
	public function FeaturesChangeStatus(Request $request){
		$Data 	= $request->all();
		$id 		= $Data['id'];
		$status = $Data['status'];
		$this->FeaturesModel->UpdateData($id,['status'=>$status]);
	}
}