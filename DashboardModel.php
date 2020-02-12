<?php
namespace App\Http\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use DB;
class DashboardModel extends Model 
{
	public function TotalUser()
	{
		$result = DB::table('profile')->count();
    return $result;
	}

	public function TotalJobs()
	{
		$result = DB::table('jobs')->count();
    return $result;
	}
}

