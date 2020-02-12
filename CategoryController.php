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
use App\Http\Models\Front\CategoryModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class CategoryController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->CategoryModel = new CategoryModel();
	}

	public function CategoryList(Request $request)
	{
		$Data['Title'] 			= 'Category';
		$Data['Menu'] 			= 'Category';
		$Data['CategoryList'] 	= $this->CategoryModel->GetCategoryList();

		return View('Front/Pages/Category/CategoryList')->with($Data);
	}
	
	public function CategoryDetail(Request $request, $CatSlug)
	{
		$Data['Title'] 			= 'Category Detail';
		$Data['Menu'] 			= 'Category';
		$Data['CategoryDetail'] = $this->CategoryModel->GetCategoryDetail($CatSlug);
		
		return View('Front/Pages/Category/CategoryDetails')->with($Data);
	}

}