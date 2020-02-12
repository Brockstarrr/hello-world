<?php
namespace App\Http\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use DB;
class LocationModel extends Model 
{
	/*Country Section*/
	public function CountryList($start,$numofrecords,$Search){
    $Data = DB::table('country as c');
    if($Search['name']!=''){
      $Data->where('c.country_name','like', '%' . $Search['name'] . '%');
    }
    if($Search['code']!=''){
      $Data->where('c.country_code','like', '%' . $Search['code'] . '%');
    }
    if($Search['status']!='All'){
      $Data->where('c.status',$Search['status']);
    }
    if($Search['sort']!=''){
      $Data->where('c.sort',$Search['sort']);
    }
    $Data->select('c.*');
    $Data->orderBy('c.id','desc');                   
                      
    $Res['Count'] = $Data->count();
    $Res['Res'] 	= $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }
  public function UpdateCountry($id,$data){
  	$result = DB::table('country')->where('id',$id)->update($data);
    return true;
  }
  public function CheckCountryName($data){
  	$result = DB::table('country')->where($data)->count();
    return $result;
  }
  public function CheckCountryEditName($data,$ID){
  	$result = DB::table('country')->where($data)->where('id','!=',$ID)->count();
    return $result;
  }
  public function AddCountry($Details){
  	$result=DB::table('country')->insertGetId($Details);
    return $result;	
  }
  public function GetCountryDetails($id){
  	$result = DB::table('country')->where('id',$id)->first();
    return $result;
  }
  /*State Section*/
  public function StateList($start,$numofrecords,$Search){
    $Data = DB::table('state as s');
    if($Search['country_id']!=''){
      $Data->where('s.country_id',$Search['country_id']);
    }
    if($Search['name']!=''){
      $Data->where('s.state_name','like', '%' . $Search['name'] . '%');
    }
    if($Search['code']!=''){
      $Data->where('s.state_code','like', '%' . $Search['code'] . '%');
    }
    if($Search['status']!='All'){
      $Data->where('s.status',$Search['status']);
    }
    if($Search['sort']!=''){
      $Data->where('s.sort',$Search['sort']);
    }
    $Data->join('country as c','c.id', '=', 's.country_id');
    $Data->select('s.*','c.country_name');
    $Data->orderBy('s.id','desc');                   
                      
    $Res['Count'] = $Data->count();
    $Res['Res'] 	= $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }
  public function UpdateState($id,$data){
  	$result = DB::table('state')->where('id',$id)->update($data);
    return true;
  }
  public function GetCountryList(){
  	$result = DB::table('country')->select('id','country_name')->orderBy('sort','ASC')->get();
  	return $result;
  }
  public function CheckStateName($Data){
  	$result = DB::table('state')->where($Data)->count();
    return $result;
  }
  public function CheckStateEditName($data,$ID){
  	$result = DB::table('state')->where($data)->where('id','!=',$ID)->count();
    return $result;
  }
  public function AddState($Details){
  	$result=DB::table('state')->insertGetId($Details);
    return $result;	
  }
  public function GetStateDetails($id){
  	$result = DB::table('state')->where('id',$id)->first(); 
  	return $result;
  }
  /*City Section*/
  public function CityList($start,$numofrecords,$Search){
    $Data = DB::table('city as c');
    if($Search['country_id']!=''){
      $Data->where('c.country_id',$Search['country_id']);
    }
    if($Search['city']!=''){
      $Data->where('c.city_name','like', '%' . $Search['city'] . '%');
    }
    if($Search['code']!=''){
      $Data->where('c.city_code','like', '%' . $Search['code'] . '%');
    }
    if($Search['status']!='All'){
      $Data->where('c.status',$Search['status']);
    }
    if($Search['state']!=''){
      $Data->where('s.state_name','like', '%' . $Search['state'] . '%');
    }
    $Data->join('country','country.id', '=', 'c.country_id');
    $Data->join('state as s','s.id', '=', 'c.state_id');
    $Data->select('c.*','s.state_name','country.country_name');
    $Data->orderBy('c.id','desc');                   
                      
    $Res['Count'] = $Data->count();
    $Res['Res'] 	= $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }
  public function UpdateCity($id,$data){
  	$result = DB::table('city')->where('id',$id)->update($data);
    return true;
  }
  public function CheckCityName($Data){
  	$result = DB::table('city')->where($Data)->count();
    return $result;
  }
  public function AddCity($Details){
  	$result=DB::table('city')->insertGetId($Details);
    return $result;	
  }
  public function GetCityDetails($id){
  	$result = DB::table('city')->where('id',$id)->first(); 
  	return $result;
  }
  public function CheckCityEditName($data,$ID){
  	$result = DB::table('city')->where($data)->where('id','!=',$ID)->count();
    return $result;
  }

  /*Locality Section*/
  public function LocalityList($start,$numofrecords,$Search){
    $Data = DB::table('locality as l');
    if($Search['country_id']!=''){
      $Data->where('l.country_id',$Search['country_id']);
    }
    if($Search['city']!=''){
      $Data->where('c.city_name','like', '%' . $Search['city'] . '%');
    }
    if($Search['locality']!=''){
      $Data->where('l.locality_name','like', '%' . $Search['locality'] . '%');
    }
    if($Search['state']!=''){
      $Data->where('s.state_name','like', '%' . $Search['state'] . '%');
    }
    if($Search['status']!='All'){
      $Data->where('l.status',$Search['status']);
    }
    $Data->join('country','country.id', '=', 'l.country_id');
    $Data->join('state as s','s.id', '=', 'l.state_id');
    $Data->join('city as c','c.id', '=', 'l.city_id');
    $Data->select('l.*','c.city_name','s.state_name','country.country_name');
    $Data->orderBy('l.id','desc');                   
                      
    $Res['Count'] = $Data->count();
    $Res['Res'] 	= $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }
  public function CheckLocalityName($Data){
  	$result = DB::table('locality')->where($Data)->count();
    return $result;
  }
  public function AddLocality($Details){
  	$result=DB::table('locality')->insertGetId($Details);
    return $result;	
  }
  public function GetLocalityDetails($id){
  	$result = DB::table('locality')->where('id',$id)->first(); 
  	return $result;
  }
  public function CheckLocalityEditName($data,$ID){
  	$result = DB::table('locality')->where($data)->where('id','!=',$ID)->count();
    return $result;
  }
  public function UpdateLocality($id,$data){
  	$result = DB::table('locality')->where('id',$id)->update($data);
    return true;
  }


  public function GetStateList($country_id){
  	$Result = DB::table('state')->where('country_id',$country_id)->orderBy('sort','ASC')->get();
  	return $Result;
  }
  public function GetCityList($state_id){
  	$Result = DB::table('city')->where('state_id',$state_id)->orderBy('sort','ASC')->get();
  	return $Result;
  }
}

