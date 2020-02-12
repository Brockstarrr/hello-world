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
use App\Http\Models\Admin\NotificationModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;
use App\Helpers\Notifiction;

class NotificationController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->NotificationModel = new NotificationModel();
		$this->Notifiction = new Notifiction();
	}
	public function List(){
		$Data['Title'] 				= 'Notification List';
		$Data['Menu'] 				= 'Notification';
		$Data['SubMenu'] 			= 'Notification';
		return View('Admin/Notification/List')->with($Data);
	}
	public function Add(){
		$Data['Title'] 				= 'Add Notification';
		$Data['Menu'] 				= 'Notification';
		$Data['SubMenu'] 			= 'Notification';
		return View('Admin/Notification/Add')->with($Data);
	}
	public function Edit($ID){
		$id = base64_decode($ID);
		$CheckID = $this->CategoryModel->CheckCategoryName(['id'=>$id]);
		if($CheckID==0){
			return Redirect()->back();
		}
		$Result 					= $this->CategoryModel->GetCategoryDetails($id);
		$PreferenceList		= $this->CategoryModel->GetPreferenceDetails($id);
		$Data['Result'] 	= $Result;
		$Data['PreferenceList'] = $PreferenceList;
		$Data['Title'] 		= 'Edit Notification';
		$Data['Menu'] 		= 'Notification';
		$Data['SubMenu'] 	= 'Notification';
		return View('Admin/Notification/Edit')->with($Data);
	}
	public function Insert(Request $request){
		$Data = $request->all();

		$Save['name'] 		= $Data['name'];
		$Save['title'] 		= $Data['title'];
		$Save['message'] 	= $Data['description'];
		$Save['add_date'] = date('Y-m-d H:i:s');

		if(!empty($Data['banner'])){
			$image = $Data['banner'];
   	 	$Path = 'public/Front/Notification';
      $extension = $image->getClientOriginalExtension();
      $ImageName = date('Ymdhis').'.'.$extension;
      $Upload = $image->move($Path, $ImageName);
      $Save['img'] 		= $ImageName;
		}

		$Notification_id = $this->NotificationModel->AddNotification($Save);
		if($Notification_id){
			/*Insert Notification List*/				
			$UserList = $this->NotificationModel->GetUserList();
			if($UserList){
				$NotificationArr = array();
				foreach ($UserList as $user) {
					$row['user_id'] = $user->id;
					$row['notification_id'] = $Notification_id;
					$row['status'] = '0';
					$row['add_date'] = date('Y-m-d H:i:s');
					$NotificationArr[] = $row;
				}
				$this->NotificationModel->AddUserNotification($NotificationArr);
			}
			/*Insert Notification List*/
			$msg = Common::AlertErrorMsg('Success','Notification Details Has been Saved.');
		}else{
			$msg = Common::AlertErrorMsg('Danger','OOPS! Something Wrong Please Try Again.');
		}
		Session::flash('message', $msg);
		return Redirect()->back();
	}
	public function NotificationList(Request $request){
		$Data = $request->all();
		$Search['name'] 	= $Data['name'];
		$Search['title']	= $Data['title'];
		$page 						= $Data['page'];

		$numofrecords   = Session::get('no_of_page');
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;
		$Result				= $this->NotificationModel->NotificationList($start,$numofrecords,$Search);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];
		foreach($Result_arr as $row){
			$Image = asset('public/Admin/assets/images/logo-icon.png');
      if($row->img!=''){
        $Image = asset('public/Front/Notification/'.$row->img);
      }
      $TotalUser = DB::table('notification_list')->where('notification_id',$row->id)->count();
		?>
			<tr>
        <td><img src="<?=$Image?>" style="height: 50px"></td>
        <td><?=$row->name?></td>
        <td><?=$row->title?></td>
        <td><?=$TotalUser?></td>
        <td><?=date('d-m-Y',strtotime($row->add_date))?></td>
        <td>
        	<!-- <a href="<? //route('UserNotification',['id'=>base64_encode($row->id)])?>">
        		<button class="btn btn-primary"> <i class="fa fa-eye"></i> </button>
        	</a> -->
        	<a href="javascript:void(0)" onclick="OpenModal(<?=$row->id?>);">
        		<button class="btn btn-danger"> <i class="fa fa-trash"></i> </button>
        	</a>
        </td>
      </tr>
		<?php	
		}
		echo '<tr><td colspan="10">'.Common::Pagination($numofrecords, $Count, $page).'</td></tr>';
	}
	public function UserList($id){
		$ID = base64_decode($id);
		$Count = DB::table('notification')->where('id',$ID)->count();
		if($Count == 0){
			return Redirect()->back();
		}
		$Data['Title'] 				= 'Notification List';
		$Data['ID'] 					= $ID;
		$Data['Menu'] 				= 'Notification';
		$Data['SubMenu'] 			= 'Notification';
		return View('Admin/Notification/List')->with($Data);
	}
	public function DeleteNotification(Request $request){
		$Data 	= $request->all();
		$id 		= $Data['id'];
		$this->NotificationModel->DeleteNotification($id);
	}
	public function DeletePreference(Request $request){
		$Data 	= $request->all();
		$id 		= $Data['id'];
		DB::table('job_category_preference')->where('id',$id)->delete();
	}

	/*Custom Notification*/
	public function CustomList(){
		$Data['Title'] 				= 'Custom Notification List';
		$Data['Menu'] 				= 'Notification';
		$Data['SubMenu'] 			= 'CustomNotification';
		return View('Admin/Notification/CustomList')->with($Data);
	}
	public function AddCustom(){
		$Data['Title'] 				= 'Add Custom Notification';
		$Data['Menu'] 				= 'Notification';
		$Data['SubMenu'] 			= 'CustomNotification';
		$Data['CategoryArr'] 	= DB::table('job_category')->select('id','category')->where('status',1)->orderBy('sort','ASC')->get();
		return View('Admin/Notification/AddCustom')->with($Data);
	}
	public function GetJobPosition(Request $request){
		$Data = $request->all();
		$cat_id = $Data['cat_id'];
		$ResultArr = DB::table('job_position')->where('category_id',$cat_id)->where('status',1)->orderBy('sort','ASC')->get();
		$Msg = '';
		foreach($ResultArr as $row) {
			$Msg.= '<option value="'.$row->id.'">'.$row->position.'</option>';
		}
		return $Msg;
	}
	public function InsertCustom(Request $request){
		$Data = $request->all();

		$Save['title'] 		= $Data['title'];
		$Save['message'] 	= $Data['description'];
		$Save['cat_id'] 	= $Data['cat_id'];
		$Save['position_id']= implode(',', $Data['position_id']);
		$Save['add_date'] = date('Y-m-d H:i:s');

		if(!empty($Data['banner'])){
			$image = $Data['banner'];
   	 	$Path = 'public/Front/Notification';
      echo $extension = $image->getClientOriginalExtension();
      $ImageName = date('Ymdhis').'.'.$extension;
      $Upload = $image->move($Path, $ImageName);
      $Save['img'] 		= $ImageName;
		}
		$Notification_id = $this->NotificationModel->AddCustomNotification($Save);
		if($Notification_id){
			$UserList = DB::table('job_preference as jp')
			              ->join('profile as user','user.id','=','jp.profile_id')
			              ->select('user.id','user.device_token')
			              ->whereIn('jp.job_sub_cat',$Data['position_id'])
			              ->where('user.device_token','!=','')
			              ->groupBy('jp.profile_id')
			              ->get()
			              ->toArray();

			$NotificationArr = array();              
	    if($UserList){
	    	foreach ($UserList as $user) {
		    	$fcm_id = $user->device_token;
		    	$SendNotification = $this->Notifiction->SendNotifiction($fcm_id,$Save['title'],$Save['message']);      

		    	$row['user_id'] = $user->id;
					$row['notification_id'] = $Notification_id;
					$row['status'] = '0';
					$row['add_date'] = date('Y-m-d H:i:s');
					$NotificationArr[] = $row;      
		    }
				$this->NotificationModel->AddUserNotification($NotificationArr);
	    }
	    $msg = Common::AlertErrorMsg('Success','Notification Details Has been Saved.');   
    }else{
			$msg = Common::AlertErrorMsg('Danger','OOPS! Something Wrong Please Try Again.');
		}
		Session::flash('message', $msg);
		return Redirect()->back();
	}
	public function CustomNotificationList(Request $request){
		$Data = $request->all();
		$Search['title']	= $Data['title'];
		$Search['category']	= $Data['category'];
		$page 						= $Data['page'];

		$numofrecords   = Session::get('no_of_page');
		$cur_page 			= $page;

		$Limitpage 			= $page-1;
		$start 					= $Limitpage * $numofrecords;
		$Result				= $this->NotificationModel->CustomNotificationList($start,$numofrecords,$Search);
		$Result_arr 	= $Result['Res'];
		$Count 				= $Result['Count'];
		foreach($Result_arr as $row){
			$Image = asset('public/Admin/assets/images/logo-icon.png');
      if($row->img!=''){
        $Image = asset('public/Front/Notification/'.$row->img);
      }
      $TotalUser = DB::table('custom_notification_list')->where('notification_id',$row->id)->count();
		?>
			<tr>
        <td><img src="<?=$Image?>" style="height: 50px"></td>
        <td><?=$row->category?></td>
        <td><?=$row->title?></td>
        <td><?=$TotalUser?></td>
        <td><?=date('d-m-Y',strtotime($row->add_date))?></td>
        <td>
        	<!-- <a href="<? //route('UserNotification',['id'=>base64_encode($row->id)])?>">
        		<button class="btn btn-primary"> <i class="fa fa-eye"></i> </button>
        	</a> -->
        	<a href="javascript:void(0)" onclick="OpenModal(<?=$row->id?>);">
        		<button class="btn btn-danger"> <i class="fa fa-trash"></i> </button>
        	</a>
        </td>
      </tr>
		<?php	
		}
		echo '<tr><td colspan="10">'.Common::Pagination($numofrecords, $Count, $page).'</td></tr>';
	}
	public function DeleteCustomNotification(Request $request){
		$Data 	= $request->all();
		$id 		= $Data['id'];
		$this->NotificationModel->DeleteCustomNotification($id);
	}
}