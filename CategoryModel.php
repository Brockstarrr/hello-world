<?php
namespace App\Http\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use DB;
class CategoryModel extends Model 
{
	public function CategoryList($start,$numofrecords,$Search){
    $Data = DB::table('job_category');
    if($Search['name']!=''){
      $Data->where('category','like', '%' . $Search['name'] . '%');
    }
    if($Search['code']!=''){
      $Data->where('code','like', '%' . $Search['code'] . '%');
    }
    if($Search['commission']!=''){
      $Data->where('commission_rate', $Search['commission']);
    }
    if($Search['status']!='All'){
      $Data->where('status',$Search['status']);
    }
    if($Search['name_sort']!='All'){
      $Data->orderBy('category',$Search['name_sort']);
    }
    $Data->orderBy('sort',$Search['sort']);
    $Data->select('*');          
                      
    $Res['Count'] = $Data->count();
    $Res['Res'] 	= $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }
  public function CheckCategoryName($data){
    $result = DB::table('job_category')->where($data)->count();
    return $result;
  }
  public function CheckCategoryEditName($data,$ID){
    $result = DB::table('job_category')->where($data)->where('id','!=',$ID)->count();
    return $result;
  }
  public function UpdateCategory($id,$data){
    $result = DB::table('job_category')->where('id',$id)->update($data);
    return true;
  }
  public function AddCategory($Details){
    $result=DB::table('job_category')->insertGetId($Details);
    return $result; 
  }
  public function GetCategoryDetails($ID){
    $result=DB::table('job_category')->where('id',$ID)->first();
    return $result; 
  }
  public function GetPreferenceDetails($cat_id){
    $result=DB::table('job_category_preference')->where('cat_id',$cat_id)->get();
    return $result; 
  }
  public function UpdatePreference($id,$data){
    $result = DB::table('job_category_preference')->where('id',$id)->update($data);
    return true;
  }
  public function AddPreference($Details){
    $result=DB::table('job_category_preference')->insertGetId($Details);
    return $result; 
  }
  public function GetAllCategory(){
    $result=DB::table('job_category')->select('id','category')->get();
    return $result;
  }

  /*Job Position*/
  public function UpdatePosition($id,$data){
    $result = DB::table('job_position')->where('id',$id)->update($data);
    return true;
  }
  public function AddPosition($Details){
    $result=DB::table('job_position')->insertGetId($Details);
    return $result; 
  }
  public function GetPositionDetails($ID){
    $result=DB::table('job_position')->where('id',$ID)->first();
    return $result; 
  }
  public function CheckPositionName($data){
    $result = DB::table('job_position')->where($data)->count();
    return $result;
  }
  public function CheckPositionEditName($data,$ID){
    $result = DB::table('job_position')->where($data)->where('id','!=',$ID)->count();
    return $result;
  }
  public function PositionList($start,$numofrecords,$Search){
    $Data = DB::table('job_position as p');
    if($Search['name']!=''){
      $Data->where('p.position','like', '%' . $Search['name'] . '%');
    }
    if($Search['code']!=''){
      $Data->where('p.code','like', '%' . $Search['code'] . '%');
    }
    if($Search['cat_id']!='All'){
      $Data->where('p.category_id', $Search['cat_id']);
    }
    if($Search['status']!='All'){
      $Data->where('p.status',$Search['status']);
    }
    $Data->orderBy('sort',$Search['sort']);
    $Data->join('job_category as c','c.id', '=','p.category_id');
    $Data->select('p.*','c.category');
    $Data->orderBy('p.id','desc');                   
                      
    $Res['Count'] = $Data->count();
    $Res['Res']   = $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }
}