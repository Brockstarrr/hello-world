<?php
namespace App\Http\Controllers\Front;
use Route;
use Mail;
use Auth, Hash;
use Validator;
use Session;
use Redirect;
use DB;
use Crypt;
use Illuminate\Http\Request;
use App\Http\Models\Front\RatingModel;
use App\Http\Models\Front\UserModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class RatingController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->RatingModel 	= new RatingModel();
		$this->UserModel 	= new UserModel();
		$this->Common 		= new Common();
	}

	public function MyRatings()
	{
		$UserID 				= Session::get('UserID');
		$Data['Title'] 			= 'My Ratings';
		$Data['Menu'] 			= 'Rating';
		
		return View('Front/Pages/User/Rating')->with($Data);
	}
}