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
use App\Http\Models\Admin\PaymentModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class ReportController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->PaymentModel = new PaymentModel();
	}
	public function ReferralReport(){
		$Data['Title'] 				= 'Referral List';
		$Data['Menu'] 				= 'ReferralReport';
		$Data['SubMenu'] 			= 'ReferralReport';
		return View('Admin/Payment/CreditList')->with($Data);
	}
	public function ReferralList(Request $request){
		$Data = $request->all();
		$Search['name'] 	= $Data['name'];
		$Search['transaction_id']	= $Data['TransactionID'];
		$Search['status']	= $Data['status'];
		$OnClick 					= $Data['OnClick'];
		$page 						= $Data['page'];

		$numofrecords   = Session::get('no_of_page');
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;
		$Result				= $this->PaymentModel->CreditDetailsList($start,$numofrecords,$Search);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];
		foreach($Result_arr as $row){
			if($row->status==1){
				$Status = '<span class="badge badge-success m-1">Success</span>';
			}elseif ($row->status==2) {
				$Status = '<span class="badge badge-danger m-1">Pending</span>';
			}else{
				$Status = '<span class="badge badge-danger m-1">Failed</span>';
			}
		?>
			<tr>
        <td><?=$row->first_name?></td>
        <td><?=$row->transaction_id?></td>
        <td>$<?=$row->amount?></td>
        <td><?=date('d-m-Y',strtotime($row->added_at))?></td>
        <td><?=$Status?></td>
        <td>
        	<a href="<?=$row->receipt_url?>" target="_blank">
        		<button class="btn btn-success"> Receipt </button>
        	</a>
        </td>
      </tr>
		<?php	
		}
		echo '<tr><td colspan="10">'.Common::DynamicPagination($numofrecords, $Count, $page,$OnClick).'</td></tr>';
	}
}