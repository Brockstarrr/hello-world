<?php
namespace App\Http\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use DB;
class HappyCandidatesModel extends Model 
{
	public function HappyCandidatesList($start,$numofrecords,$Search){
    $Data = DB::table('happy_candidate');
    if($Search['name']!=''){
      $Data->where('name','like', '%' . $Search['name'] . '%');
    }
    if($Search['location']!=''){
      $Data->where('location','like', '%' . $Search['location'] . '%');
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
    $result = DB::table('happy_candidate')->where('id',$id)->update($data);
    return true;
  }
  public function GetDetails($id){
    $result = DB::table('happy_candidate')->where('id',$id)->first();
    return $result;
  }
  public function InsertData($Details){
    $result=DB::table('happy_candidate')->insertGetId($Details);
    return $result; 
  }
}