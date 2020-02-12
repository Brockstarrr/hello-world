<?php
namespace App\Http\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use DB;
class ContactModel extends Model 
{
	public function List($start,$numofrecords,$Search){
    $Data = DB::table('contact_us');
    if($Search['name']!=''){
      $Data->where('name','like', '%' . $Search['name'] . '%');
    }
    if($Search['email']!=''){
      $Data->where('email','like', '%' . $Search['email'] . '%');
    }
    $Data->select('*');
    $Data->orderBy('id','desc');                   
                      
    $Res['Count'] = $Data->count();
    $Res['Res'] 	= $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }
}