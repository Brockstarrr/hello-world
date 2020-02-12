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
use App\Http\Models\Admin\FaqModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class FaqController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->FaqModel = new FaqModel();
	}

	public function List()
	{
		$Data['Title'] 				= 'Faq List';
		$Data['Menu'] 				= 'CMS';
		$Data['SubMenu'] 			= 'Faq';
		return View('Admin/Faq/List')->with($Data);
	}
	public function Add(){
		$Data['Title'] 				= 'Add Faq';
		$Data['Menu'] 				= 'CMS';
		$Data['SubMenu'] 			= 'Faq';
		return View('Admin/Faq/Add')->with($Data);
	}
	public function EditFaq($ID){
		$id = base64_decode($ID);
		$CheckID = $this->FaqModel->CheckFaq(['id'=>$id]);
		if($CheckID==0){
			return Redirect()->back();
		}
		$Result 					= $this->FaqModel->GetDetails($id);
		$Data['Result'] 	= $Result;
		$Data['Title'] 		= 'Edit Faq';
		$Data['Menu'] 		= 'CMS';
		$Data['SubMenu'] 	= 'Faq';
		return View('Admin/Faq/Edit')->with($Data);
	}
	public function InsertFaq(Request $request){
		$Data = $request->all();

		$Save['name'] 				= $Data['name'];
		$Save['description'] 	= $Data['description'];
		$Save['sort'] 				= $Data['sort'];
		$Save['status'] 			= $Data['status'];
		$Save['add_date'] 		= date('Y-m-d H:i:s');

		$i = 0;
		$FaqArr = array();
		foreach ($Data['questions'] as $a) {
			$Json['questions'] 	= $a;
			$Json['answer']			= $Data['answer'][$i];
			$FaqArr[] 	= $Json;
			$i++;
		}
		$Save['faq'] = json_encode($FaqArr);

		if(!empty($Data['image'])){
			$image = $Data['image'];
   	 	$Path = 'public/Front/Faq';
      $extension = $image->getClientOriginalExtension();
      $ImageName = Common::GenerateRandomId(10).'.'.$extension;
      $Upload = $image->move($Path, $ImageName);
      $Save['image'] 		= $ImageName;
		}
		$result = $this->FaqModel->InsertData($Save);
		$msg = Common::AlertErrorMsg('Success','Faq Details Has been Saved.');
		Session::flash('message', $msg);
		return Redirect()->back();
	}
	public function SaveFAQ(Request $request){
		$Data = $request->all();

		$EditID	= $Data['edit_id'];

		if(!empty($Data['image'])){
			$image = $Data['image'];
   	 	$Path = 'public/Front/Faq';
      $extension = $image->getClientOriginalExtension();
      $ImageName = Common::GenerateRandomId(10).'.'.$extension;
      $Upload = $image->move($Path, $ImageName);
      $Save['image'] 		= $ImageName;

      if($Data['OldImage']){
      	@unlink($Path.'/'.$Data['OldImage']);
      }
		}
		$i = 0;
		$FaqArr = array();
		foreach ($Data['questions'] as $a) {
			$Json['questions'] 	= $a;
			$Json['answer']			= $Data['answer'][$i];
			$FaqArr[] 	= $Json;
			$i++;
		}
		$Save['faq'] = json_encode($FaqArr);
		
		$Save['name'] 				= $Data['name'];
		$Save['description'] 	= $Data['description'];
		$Save['sort'] 				= $Data['sort'];
		$Save['status'] 			= $Data['status'];
		$Save['add_date'] 		= date('Y-m-d H:i:s');

		$Result = $this->FaqModel->UpdateData($EditID,$Save);
		$msg = Common::AlertErrorMsg('Success','Faq Details Has been Saved.');
		Session::flash('message', $msg);
		return Redirect()->back();
	}
	public function FaqListing(Request $request){
		$Data = $request->all();
		$Search['name'] 	= $Data['name'];
		$Search['status']	= $Data['status'];
		$page 						= $Data['page'];

		$numofrecords   = Session::get('no_of_page');
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;
		$Result				= $this->FaqModel->FaqListing($start,$numofrecords,$Search);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];
		foreach($Result_arr as $row){

			$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',1)"><span class="badge badge-danger m-1">De-active</span></a>';
			if($row->status==1){
				$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',0)"><span class="badge badge-success m-1">Active</span></a>';
			}
			$Image = asset('public/Admin/assets/images/logo-icon.png');
      if($row->image!=''){
        $Image = asset('public/Front/Faq/'.$row->image);
      }
		?>
			<tr>
				<td><img src="<?=$Image?>" style="height: 50px"></td>
        <td><?=$row->name?></td>
        <td><?=$row->sort?></td>
        <td><?=$Status?></td>
        <td>
        	<a href="<?=route('EditFaq',array('ID'=>base64_encode($row->id)) )?>">
        		<button class="btn btn-success"> <i class="fa fa-edit"></i> </button>
        	</a>
        </td>
      </tr>
		<?php	
		}
		echo '<tr><td colspan="10">'.Common::Pagination($numofrecords, $Count, $page).'</td></tr>';
	}
	public function FaqChangeStatus(Request $request){
		$Data 	= $request->all();
		$id 		= $Data['id'];
		$status = $Data['status'];
		$this->FaqModel->UpdateData($id,['status'=>$status]);
	}
}