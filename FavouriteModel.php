<?php
namespace App\Http\Models\API;
use Illuminate\Database\Eloquent\Model;
use App\Http\Models\Web\Front\MemberModel;
use App\Http\Controllers\Pagination;
use DB;
class FavouriteModel extends Model 
{
	public function __construct()
	{
		$this->Pagination = new Pagination();
	}
	 	
	public function IsFavouriteAlready($UserID, $JobID)
	{
		return $Count  =DB::table('fav_jobs')
					->where('profile_id',$UserID)
					->where('job_id',$JobID)
					->select('*')
					->count();
	}
	public function AddToFavouriteJob($Details)
	{
		$Add  =DB::table('fav_jobs')->insert($Details);
		if($Add)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function DeleteToFavouriteJob($UserID, $JobID)
	{
		$Delete  =DB::table('fav_jobs')
				->where('profile_id',$UserID)
				->where('job_id',$JobID)
				->delete();
		if($Delete)
		{
			return true;
		}
		else
		{
			return false;
		}
	}	
	public function FavouriteJobList($UserID)
	{
		$Data = DB::table('fav_jobs as fj')
                ->join('jobs as j','j.id','=','fj.job_id')
                ->join('profile as p','p.id','=','j.profile_id')
                ->where('fj.profile_id',$UserID)
                ->select('j.*',DB::raw("CONCAT(p.first_name,' ',p.last_name) AS username"),'p.id as UserID')
        		->get()
                ->toArray();

        return $Data; 
	}

	///////////////////////////
	public function IsFavouriteProsAlready($UserID, $ProsID)
	{
		return $Count  =DB::table('fav_pros')
					->where('profile_id',$UserID)
					->where('pros_id',$ProsID)
					->select('*')
					->count();
	}
	public function AddToFavouritePros($Details)
	{
		$Add  =DB::table('fav_pros')->insert($Details);
		if($Add)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function DeleteToFavouritePros($UserID, $ProsID)
	{
		$Delete  =DB::table('fav_pros')
				->where('profile_id',$UserID)
				->where('pros_id',$ProsID)
				->delete();
		if($Delete)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function FavouriteProsList($UserID)
	{
		$Data = DB::table('fav_pros as fj')
                ->join('profile as p','p.id','=','fj.pros_id')
                ->where('fj.profile_id',$UserID)
                ->select('p.*')
        		->get()
                ->toArray();

        return $Data; 
	}
}