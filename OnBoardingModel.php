<?php
namespace App\Http\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use DB;
class OnBoardingModel extends Model 
{
  public function List($start,$numofrecords,$Search){
    $Data = DB::table('onboarding_quiz');
    if($Search['question']!=''){
      $Data->where('question','like', '%' . $Search['question'] . '%');
    }
    if($Search['status']!='All'){
      $Data->where('status',$Search['status']);
    }
    $Data->select('*');
    $Data->orderBy('sort','ASC');                   
                      
    $Res['Count'] = $Data->count();
    $Res['Res']   = $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }
  public function CheckOnBoardingID($data){
    $result = DB::table('onboarding_quiz')->where($data)->count();
    return $result;
  }
  public function GetOnBoardingDetails($ID){
    $result=DB::table('onboarding_quiz')->where('id',$ID)->first();
    return $result; 
  }
  public function UpdateData($id,$data){
    $result = DB::table('onboarding_quiz')->where('id',$id)->update($data);
    return true;
  }
  public function InsertData($Details){
    $result=DB::table('onboarding_quiz')->insertGetId($Details);
    return $result; 
  }
  public function UpdateStartQuiz($id,$data){
    $result = DB::table('onboarding_quiz_screen')->where('id',$id)->update($data);
    return true;
  }
}