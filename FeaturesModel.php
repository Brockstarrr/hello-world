<?php
namespace App\Http\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use DB;
class FeaturesModel extends Model 
{
	public function FeaturesList($start,$numofrecords,$Search){
    $Data = DB::table('features');
    if($Search['name']!=''){
      $Data->where('name','like', '%' . $Search['name'] . '%');
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
    $result = DB::table('features')->where('id',$id)->update($data);
    return true;
  }
  public function GetDetails($id){
    $result = DB::table('features')->where('id',$id)->first();
    return $result;
  }
  public function InsertData($Details){
    $result=DB::table('features')->insertGetId($Details);
    return $result; 
  }
  public function CheckFeatures($data){
    $result = DB::table('features')->where($data)->count();
    return $result;
  }
  public function CheckCouponEditCode($data,$ID){
    $result = DB::table('features')->where($data)->where('id','!=',$ID)->count();
    return $result;
  }
}