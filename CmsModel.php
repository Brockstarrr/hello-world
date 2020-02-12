<?php
namespace App\Http\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use DB;
class CmsModel extends Model 
{
	public function GetAllpages()
	{
		$data = DB::table('cms')
		        ->select('*')
		        ->get();
		return $data;
	}

	public function GetDetails($id)
    {
      $data = DB::table('cms')->select('*')->where('id',$id)->first();
      return $data;
    } 

    public function UpdateData($id,$data){
    $result = DB::table('cms')->where('id',$id)->update($data);
    return true;
  }
}