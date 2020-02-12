<?php
namespace App\Http\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use DB;
class JobsModel extends Model 
{
	public function JobListing($start,$numofrecords,$Search){
    $Data = DB::table('jobs');
    if($Search['name']!=''){
      $Data->where('jobs.job_title','like', '%' . $Search['name'] . '%');
    }
    if($Search['user_id']!='All'){
      $Data->where('jobs.profile_id',$Search['user_id']);
    }
    if($Search['cat_id']!='All'){
      $Data->where('jobs.job_cat',$Search['cat_id']);
    }
    if($Search['job_type']!='All'){
      $Data->where('jobs.job_type',$Search['job_type']);
    }
    if($Search['status']!='All'){
      $Data->where('jobs.status',$Search['status']);
    }
    $Data->join('profile','profile.id','=','jobs.profile_id');
    $Data->join('job_category as cat','cat.id','=','jobs.job_cat');
    $Data->select('jobs.*','profile.first_name','profile.last_name','cat.category');
    $Data->orderBy('jobs.id','DESC');
    $Res['Count'] = $Data->count();
    $Res['Res'] 	= $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }
  public function UpdateData($id,$data){
    $result = DB::table('jobs')->where('id',$id)->update($data);
    return true;
  }
  public function UserList(){
    $result = DB::table('profile')->select('id','first_name','last_name')->get();
    return $result;
  }
  public function CategoryList(){
    $result = DB::table('job_category')->select('id','category')->get();
    return $result;
  }

  public function GetJobCategory()
   {
    $GetJobCategory = DB::table('job_category as jc')
                ->where('jc.status',1)
                ->orderBy('jc.sort','ASC')
                ->select('jc.*')
                ->get()
                ->toArray();
      return $GetJobCategory;
   }  

   public function HairColor()
   {
      $HairColor = DB::table('hair_color')
                        ->select('*')
                        ->get()
                        ->toArray();
      return $HairColor;
   }

   public function EyeColor()
   {
      $EyeColor = DB::table('eye_color')
                        ->select('*')
                        ->get()
                        ->toArray();
      return $EyeColor;
   }

   public function LanguageList()
   {
      $EyeColor = DB::table('language_list')
                        ->select('*')
                        ->get()
                        ->toArray();
      return $EyeColor;
   }
   public function UserTypeList()
   {
      $UserList = DB::table('profile')
                        ->select('id','first_name','last_name')
                        ->where('type','!=',1)
                        ->get()
                        ->toArray();
      return $UserList;
   }

   public function GetJobSubCategory($CategoryID)
   {
      $GetJobSubCategory = DB::table('job_position as jc')
                        ->where('jc.category_id',$CategoryID)
                        ->where('jc.status',1)
                        ->orderBy('jc.sort','ASC')
                        ->select('jc.*')
                        ->get()
                        ->toArray();
      return $GetJobSubCategory;
   } 

    public function GetPreference($CategoryID)
   {
      $GetPreference = DB::table('job_category_preference as jc')
                        ->where('jc.cat_id',$CategoryID)
                        ->select('jc.*')
                        ->get()
                        ->toArray();
      return $GetPreference;
   }

    public function SaveJobDetails($JobDetails,$LanguageArray,$SubCatAndOpeningArray)
   {
      $JobID = DB::table('jobs')->insertGetId($JobDetails);
      if($JobID!='')
      {
         if(!empty($LanguageArray))
         {
            $this->SaveLanguageDetails($JobID,$LanguageArray);            
         }
         if(!empty($SubCatAndOpeningArray))
         {
            $this->SaveSubCatAndOpeningDetails($JobID,$SubCatAndOpeningArray);
         }  
         return true;          
      }
      else
      {
         return false;
      }
   }

    public function SaveLanguageDetails($JobID,$LanguageArray)
   {
      foreach($LanguageArray as $l)
      {
         $Sample['job_id']    = $JobID;
         $Sample['language']  = $l['language'];
         $Sample['level']     = $l['level'];
         DB::table('jobs_language_preference')->insert($Sample);   
      }
   }
   public function SaveSubCatAndOpeningDetails($JobID,$SubCatAndOpeningArray)
   {
      foreach($SubCatAndOpeningArray as $l)
      {
         $l['job_id'] = $JobID;
         DB::table('jobs_sub_cat_openings')->insert($l);   
      }
   }

   public function PostAJob($id)
   {
    $data = DB::table('jobs as j')
            ->join('profile as p','p.id','=','j.profile_id')
            ->join('job_category as jc','j.job_cat','=','jc.id')
            ->select('j.*','p.first_name','p.last_name')
            ->where('j.id',$id)
            ->first();
           
    return $data;
   }

   public function EditPositionAndOpenings($id)
   {
    $data = DB::table('jobs_sub_cat_openings as jsco')
            ->join('job_position as jp','jp.id','=','jsco.sub_cat')
            ->select('jsco.*','jp.position')
            ->where('jsco.job_id',$id)
            ->get(); 

   return $data; 
   }

   public function DeletepositionRow($ID)
   {
     $Data = DB::table('jobs_sub_cat_openings')->where('id',$id)->delete();
      return $Data; 
   }

   public function LanguageOptions($id)
   {
    $data = DB::table('jobs as j')
            ->join('language_info as li','li.profile_id','=','j.profile_id')
            ->select('li.language','li.level')
            ->where('j.id',$id)
            ->get(); 

            // echo "<pre>";
            // print_r($data);
            // die;

   return $data; 
   }
   public function HairColorOptions($id)
   {
    $data = DB::table('jobs as j')
            ->join('hair_color as hc','hc.id','=','j.hair_color')
            ->select('hc.color')
            ->where('j.id',$id)
            ->get(); 
   return $data; 
   }

  
}