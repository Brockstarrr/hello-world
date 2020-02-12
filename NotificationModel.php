<?php
namespace App\Http\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use DB;
class NotificationModel extends Model 
{
	public function NotificationList($start,$numofrecords,$Search){
    $Data = DB::table('notification');
    if($Search['name']!=''){
      $Data->where('name','like', '%' . $Search['name'] . '%');
    }
    if($Search['title']!=''){
      $Data->where('title','like', '%' . $Search['title'] . '%');
    }
    $Data->orderBy('id','desc');
    $Data->select('*');                  
    $Res['Count'] = $Data->count();
    $Res['Res'] 	= $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }
  public function DeleteNotification($id){
    DB::table('notification')->where('id',$id)->delete();
    DB::table('notification_list')->where('notification_id',$id)->delete();
    return true;
  }
  public function AddNotification($Details){
    $result=DB::table('notification')->insertGetId($Details);
    return $result; 
  }

  public function AddUserNotification($Details){
    DB::table('notification_list')->insert($Details);
    return true; 
  }
  public function GetUserList(){
    $result=DB::table('profile')->select('id')->where('device_token','!=','')->where('status',1)->get()->toArray();
    return $result;
  }

  public function AddCustomNotification($Details){
    $result=DB::table('custom_notification')->insertGetId($Details);
    return $result; 
  }
  public function AddCustomUserNotification($Details){
    DB::table('custom_notification_list')->insert($Details);
    return true; 
  }
  public function CustomNotificationList($start,$numofrecords,$Search){
    $Data = DB::table('custom_notification as notification');
    if($Search['title']!=''){
      $Data->where('notification.title','like', '%' . $Search['title'] . '%');
    }
    if($Search['category']!=''){
      $Data->where('cat.category','like', '%' . $Search['category'] . '%');
    }
    $Data->join('job_category as cat','cat.id','=','notification.cat_id');
    $Data->orderBy('notification.id','desc');
    $Data->select('notification.*','cat.category');                  
    $Res['Count'] = $Data->count();
    $Res['Res']   = $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }
  public function DeleteCustomNotification($id){
    DB::table('custom_notification')->where('id',$id)->delete();
    DB::table('custom_notification_list')->where('notification_id',$id)->delete();
    return true;
  }
}