<?php
namespace App\Http\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use DB;
class FaqModel extends Model 
{
	public function FaqListing($start,$numofrecords,$Search){
    $Data = DB::table('faq');
    if($Search['name']!=''){
      $Data->where('name','like', '%' . $Search['name'] . '%');
    }
    if($Search['status']!='All'){
      $Data->where('status',$Search['status']);
    }
    $Data->select('*');
    $Data->orderBy('sort','ASC');
    $Res['Count'] = $Data->count();
    $Res['Res'] 	= $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }
  public function UpdateData($id,$data){
    $result = DB::table('faq')->where('id',$id)->update($data);
    return true;
  }
  public function GetDetails($id){
    $result = DB::table('faq')->where('id',$id)->first();
    return $result;
  }
  public function InsertData($Details){
    $result=DB::table('faq')->insertGetId($Details);
    return $result; 
  }
  public function CheckFaq($data){
    $result = DB::table('faq')->where($data)->count();
    return $result;
  }
  public function CheckCouponEditCode($data,$ID){
    $result = DB::table('faq')->where($data)->where('id','!=',$ID)->count();
    return $result;
  }
}