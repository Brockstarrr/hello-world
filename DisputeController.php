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
use App\Http\Models\Admin\ReferModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class DisputeController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->ReferModel = new ReferModel();
	}

	public function List()
	{
		$Data['Title'] 				= 'Dispute Job List';
		$Data['Menu'] 				= 'DisputeJob';
		$Data['SubMenu'] 			= 'DisputeJob';
		return View('Admin/Dispute/List')->with($Data);
	}
	public function ReferToFriendList(Request $request){
		$Data = $request->all();
		$Search['name_from'] 	= $Data['name_from'];
		$Search['name_to']		= $Data['name_to'];
		$Search['refer_code']	= $Data['refer_code'];
		$page 								= $Data['page'];

		$numofrecords   = Session::get('no_of_page');
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;
		$Result				= $this->ReferModel->ReferToFriendList($start,$numofrecords,$Search);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];
		$Row['Result_arr'] = $Result_arr;
		$Row['Pagination'] = Common::Pagination($numofrecords, $Count, $page);
		echo view('Admin/Dispute/Pagination',$Row);
	}
}