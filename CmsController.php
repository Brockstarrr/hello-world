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
use App\Http\Models\Admin\CmsModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class CmsController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->CmsModel = new CmsModel();
	}
	public function GetPages(Request $request)
	{
		$Data['Title'] 				= 'Content List';
		$Data['Menu'] 				= 'CMS';
		$Data['SubMenu'] 			= 'ContentList';
		return View('Admin/CMS/ContentList')->with($Data);
	}



	public function GetAllPages(Request $request)
	{
		$data   		= $request->all();
		$p_name 		= $data['p_name'];
		$Searcher 		=array('page_heading'=>$p_name);
		$pages 		    = $this->CmsModel->GetAllpages($Searcher);
		
		if(count($pages)!=0)
		{
			foreach ($pages as $PA) 
			{ 
			?>
				<tr id="tr_<?php echo $PA->id;?>">
					<td id="pages_text_<?php echo $PA->id;?>"><?php echo $PA->title; ?></td>
					<td>
						<a href="<?=route('editpages',array('ID'=>base64_encode($PA->id)) )?>">
        		          <button class="btn btn-success"> <i class="fa fa-edit"></i> </button>
        	            </a>                                             
					</td>             
				</tr>
			<?php 
			} 
		}
		else
		{
		echo "<span style='color:red;'>No record Found</span>";
		}  
	}

	public function EditPages($ID)
	{
		$id = base64_decode($ID);
		$Result 			= $this->CmsModel->GetDetails($id);
		$Data['Result'] 	= $Result;
		$Data['Title'] 				= 'Edit Page';
		$Data['Menu'] 				= 'CMS';
		$Data['SubMenu'] 			= 'ContentList';
		return View('Admin/CMS/EditPages')->with($Data);
	}


	public function SavePage(Request $request){
		$Data = $request->all();
		$EditID	= $Data['edit_id'];
		$Save['title'] 				= $Data['title'];
		$Save['description'] 	    = $Data['description'];

		$Result = $this->CmsModel->UpdateData($EditID,$Save);
		$msg = Common::AlertErrorMsg('Success','Page Details Has been Saved.');
		Session::flash('message', $msg);
		return Redirect()->back();
	}
	
}