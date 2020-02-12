<?php
namespace App\Http\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use DB;
class CouponCodeModel extends Model 
{
	public function CouponCodeList($start,$numofrecords,$Search){
    $Data = DB::table('coupon_code');
    if($Search['name']!=''){
      $Data->where('name','like', '%' . $Search['name'] . '%');
    }
    if($Search['code']!=''){
      $Data->where('code','like', '%' . $Search['code'] . '%');
    }
    if($Search['status']!='All'){
      $Data->where('status',$Search['status']);
    }
    $Data->select('*');
    $Data->orderBy('id','desc');
    $Res['Count'] = $Data->count();
    $Res['Res'] 	= $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }
  public function UpdateData($id,$data){
    $result = DB::table('coupon_code')->where('id',$id)->update($data);
    return true;
  }
  public function GetDetails($id){
    $result = DB::table('coupon_code')->where('id',$id)->first();
    return $result;
  }
  public function InsertData($Details){
    $result=DB::table('coupon_code')->insertGetId($Details);
    return $result; 
  }
  public function CheckCouponCode($data){
    $result = DB::table('coupon_code')->where($data)->count();
    return $result;
  }
  public function CheckCouponEditCode($data,$ID){
    $result = DB::table('job_position')->where($data)->where('id','!=',$ID)->count();
    return $result;
  }
}