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
use App\Http\Models\Admin\CouponCodeModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class CouponCodeController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->CouponCodeModel = new CouponCodeModel();
	}

	public function List()
	{
		$Data['Title'] 				= 'Coupon Code List';
		$Data['Menu'] 				= 'CouponCode';
		$Data['SubMenu'] 			= 'CouponCode';
		return View('Admin/CouponCode/List')->with($Data);
	}
	public function Add(){
		$Data['Title'] 				= 'Add Coupon Code';
		$Data['Menu'] 				= 'CouponCode';
		$Data['SubMenu'] 			= 'CouponCode';
		return View('Admin/CouponCode/Add')->with($Data);
	}
	public function EditCouponCode($ID){
		$id = base64_decode($ID);
		$CheckID = $this->CouponCodeModel->CheckCouponCode(['id'=>$id]);
		if($CheckID==0){
			return Redirect()->back();
		}
		$Result 					= $this->CouponCodeModel->GetDetails($id);
		$Data['Result'] 	= $Result;
		$Data['Title'] 		= 'Edit Coupon Code';
		$Data['Menu'] 		= 'CouponCode';
		$Data['SubMenu'] 	= 'CouponCode';
		return View('Admin/CouponCode/Edit')->with($Data);
	}
	public function InsertCouponCode(Request $request){
		$Data = $request->all();

		$Save['name'] 				= $Data['name'];
		$Save['code'] 				= $Data['code'];
		$Save['start_date'] 	= date('Y-m-d',strtotime($Data['start']));
		$Save['end_date'] 		= date('Y-m-d',strtotime($Data['end']));
		$Save['coupon_type'] 	= $Data['coupon_type'];
		$Save['dispaly'] 			= $Data['dispaly'];
		$Save['amount'] 			= $Data['amount'];
		$Save['status'] 			= $Data['status'];
		$Save['add_date'] 		= date('Y-m-d H:i:s');

		$CheckName = $this->CouponCodeModel->CheckCouponCode(['code'=>$Save['code']]);
		if($CheckName > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','Coupon Code already exists.');
			echo json_encode($arr);
			exit();
		}		
		$result = $this->CouponCodeModel->InsertData($Save);
		if($result){
			$arr['status'] 	= 1;
			$arr['msg'] 		= Common::AlertErrorMsg('Success','Coupon Code Has been Saved.');
		}else{
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','OOPS! Something Wrong Please Try Again.');
		}
		echo json_encode($arr);
		exit();
	}
	public function SaveCouponCode(Request $request){
		$Data = $request->all();

		$EditID	= $Data['edit_id'];

		$Save['name'] 				= $Data['name'];
		$Save['code'] 				= $Data['code'];
		$Save['start_date'] 	= date('Y-m-d',strtotime($Data['start']));
		$Save['end_date'] 		= date('Y-m-d',strtotime($Data['end']));
		$Save['amount'] 			= $Data['amount'];
		$Save['coupon_type'] 	= $Data['coupon_type'];
		$Save['dispaly'] 			= $Data['dispaly'];
		$Save['status'] 			= $Data['status'];
		$Save['add_date'] 		= date('Y-m-d H:i:s');

		$CheckName = $this->CouponCodeModel->CheckCouponEditCode(['code'=>$Save['code']],$EditID);
		if($CheckName > 0){
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','Coupon code already exists.');
			echo json_encode($arr);
			exit();
		}		
		$Result = $this->CouponCodeModel->UpdateData($EditID,$Save);
		if($Result){
			$arr['status'] 	= 1;
			$arr['msg'] 		= Common::AlertErrorMsg('Success','Coupon Code Details Has been Saved.');
		}else{
			$arr['status'] 	= 0;
			$arr['msg'] 		= Common::AlertErrorMsg('Danger','OOPS! Something Wrong Please Try Again.');
		}
		echo json_encode($arr);
		exit();
	}
	public function CouponCodeList(Request $request){
		$Data = $request->all();
		$Search['name'] 	= $Data['name'];
		$Search['code']		= $Data['code'];
		$Search['status']	= $Data['status'];
		$page 						= $Data['page'];

		$numofrecords   = Session::get('no_of_page');
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;
		$Result				= $this->CouponCodeModel->CouponCodeList($start,$numofrecords,$Search);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];
		foreach($Result_arr as $row){

			$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',1)"><span class="badge badge-danger m-1">De-active</span></a>';
			if($row->status==1){
				$Status = '<a href="javascript:void(0)" onclick="OpenModal('.$row->id.',0)"><span class="badge badge-success m-1">Active</span></a>';
			}
		?>
			<tr>
        <td><?=$row->name?></td>
        <td><?=$row->code?></td>
        <td><?=$row->amount?></td>
        <td><?=$row->start_date?></td>
        <td><?=$row->end_date?></td>
        <td><?=$Status?></td>
        <td>
        	<a href="<?=route('EditCouponCode',array('ID'=>base64_encode($row->id)) )?>">
        		<button class="btn btn-success"> <i class="fa fa-edit"></i> </button>
        	</a>
        </td>
      </tr>
		<?php	
		}
		echo '<tr><td colspan="10">'.Common::Pagination($numofrecords, $Count, $page).'</td></tr>';
	}
	public function CouponChangeStatus(Request $request){
		$Data 	= $request->all();
		$id 		= $Data['id'];
		$status = $Data['status'];
		$this->CouponCodeModel->UpdateData($id,['status'=>$status]);
	}
}