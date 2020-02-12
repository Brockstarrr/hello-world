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
use App\Http\Models\Admin\DashboardModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class DashboardController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->DashboardModel = new DashboardModel();
	}

	public function Dashboard()
	{
		$Data['TotalUser'] 	= $this->DashboardModel->TotalUser();
		$Data['TotalJobs'] 	= $this->DashboardModel->TotalJobs();
		$Data['Title'] 		= 'Dashboard';
		$Data['Menu'] 		= 'Dashboard';
		$Data['SubMenu'] 	= '';
		return View('Admin/Dashboard')->with($Data);
	}
}