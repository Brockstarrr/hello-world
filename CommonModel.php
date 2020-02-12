<?php
namespace App\Http\Models\API;
use Illuminate\Database\Eloquent\Model;
use App\Http\Models\Web\Front\MemberModel;
use App\Http\Controllers\Pagination;
use DB;
class CommonModel extends Model 
{
	public function __construct()
	{
		$this->Pagination = new Pagination();
	}
	
	public function CheckLoginDetails($UserID, $AccessToken)
	{
		$Details = DB::table('profile')
					->where('id',$UserID)
					->where('access_token',$AccessToken)
					->select('*')
					->first();
		return $Details;
	}

	public function CategoryList()
	{
		$Details = DB::table('job_category')
					->where('status',1)
					->select('*')
					->get()
					->toArray();
		return $Details;
	}
	public function SubCategoryList($CatID)
	{
		$Details = DB::table('job_position')
					->where('category_id',$CatID)
				->where('status',1)
					->select('*')
					->get()
					->toArray();
		return $Details;
	}

	public function CategoryPreferenceList($CatID)
	{
		$Details = DB::table('job_category_preference')
					->where('cat_id',$CatID)
					->select('*')
					->get()
					->toArray();
		return $Details;
	}

	public function GetOpeningTimeSlot()
	{
		$TimeSlot = DB::table('opening_time_slot')
					->select('*')
					->orderBy('id','ASC')
					->get()
					->toArray();
		return $TimeSlot;
	}
	

}