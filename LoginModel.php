<?php
namespace App\Http\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use DB;
class LoginModel extends Model 
{
	public function AdminLoginValidate($Email,$Password)
	{
		$LoginDetails=DB::table('admin')->where('email',$Email)->where('password',$Password)->first();
    return $LoginDetails;
	}

	public function CheckEmailID($Email)
	{
		$reslut=DB::table('admin')->where('email',$Email)->first();
    return $reslut;
	}
}

