<?php
namespace App\Http\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use DB;
class NeedAJobModel extends Model 
{
	public function Listing($start,$numofrecords,$Search){
    $Data = DB::table('job_pro_feature');
    if($Search['name']!=''){
      $Data->where('name','like', '%' . $Search['name'] . '%');
    }
    if($Search['status']!='All'){
      $Data->where('status',$Search['status']);
    }
    $Data->where('job_type',$Search['job_type']);
    $Data->select('*');
    $Data->orderBy('sort','ASC');
    $Res['Count'] = $Data->count();
    $Res['Res'] 	= $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }
  public function UpdateData($id,$data){
    $result = DB::table('job_pro_feature')->where('id',$id)->update($data);
    return true;
  }
  public function GetDetails($id){
    $result = DB::table('job_pro_feature')->where('id',$id)->first();
    return $result;
  }
  public function InsertData($Details){
    $result=DB::table('job_pro_feature')->insertGetId($Details);
    return $result; 
  }
  public function CheckJobData($data){
    $result = DB::table('job_pro_feature')->where($data)->count();
    return $result;
  }
}