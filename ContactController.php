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
use App\Http\Models\Admin\ContactModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class ContactController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->ContactModel = new ContactModel();
	}

	public function List()
	{
		$Data['Title'] 				= 'Slider List';
		$Data['Menu'] 				= 'CMS';
		$Data['SubMenu'] 			= 'ContactFormList';
		return View('Admin/CMS/ContactFormList')->with($Data);
	}

	public function ContactFormData(Request $request){
		$Data = $request->all();
		$Search['name'] 		= $Data['name'];
		$Search['email']		= $Data['email'];
		$page 							= $Data['page'];

		$numofrecords   = Session::get('no_of_page');
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;

		$Result				= $this->ContactModel->List($start,$numofrecords,$Search);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];
		foreach($Result_arr as $row){
		?>
			<tr>
        <td id="Name<?=$row->id?>"><?=$row->name?></td>
        <td id="Email<?=$row->id?>"><?=$row->email?></td>
        <td id="Subject<?=$row->id?>"><?=$row->phone?></td>
        <td id="Date<?=$row->id?>"><?=date('d-M-Y',strtotime($row->added_at))?></td>
        <td>
        	<input type="hidden" id="Message<?=$row->id?>" value="<?=$row->message?>">
        	<a href="javascript:void(0)" onclick="OpenViewModal(<?=$row->id?>)">
        		<button class="btn btn-primary"> <i class="fa fa-eye"></i> </button>
        	</a>
        </td>
      </tr>
		<?php	
		}
		echo '<tr><td colspan="10">'.Common::Pagination($numofrecords, $Count, $page).'</td></tr>';
	}
}