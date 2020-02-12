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
use App\Http\Models\Front\BlogModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;

class BlogController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->BlogModel = new BlogModel();
	}

	public function BlogList(Request $request)
	{
		$Data['Title'] 			= 'Blog';
		$Data['Menu'] 			= 'BlogPage';
		$Data['BlogCategory'] 	= $this->BlogModel->GetBlogCategory();
		$Data['BlogList'] 		= $this->BlogModel->BlogList();
		$Data['RecentBlogList'] = $this->BlogModel->RecentBlogList();

		return View('Front/Pages/Blog/BlogList')->with($Data);
	}
	
	public function BlogListByCategory(Request $request, $cat)
	{
		$Data['Title'] 				= 'Blog Details';
		$Data['Menu'] 				= 'BlogPage';
		$Data['BlogCategory'] 		= $this->BlogModel->GetBlogCategory();
		$Data['BlogList'] 			= $this->BlogModel->BlogListByCategory($cat);
		$Data['RecentBlogList'] 	= $this->BlogModel->RecentBlogList();
		
		return View('Front/Pages/Blog/BlogListByCategory')->with($Data);
	}
	public function BlogDetail(Request $request, $cat, $slug)
	{
		$Data['Title'] 				= 'Blog Details';
		$Data['Menu'] 				= 'BlogPage';
		$Data['BlogCategory'] 		= $this->BlogModel->GetBlogCategory();
		$Data['RecentBlogList'] 	= $this->BlogModel->RecentBlogList();
		$BlogDetails 				= $this->BlogModel->BlogDetails($slug);
		$BlogID 					= $BlogDetails->id;
		$Data['BlogDetails'] 		= $BlogDetails;
		$Data['BlogComments'] 		= $this->BlogModel->BlogComments($BlogID);
		
		return View('Front/Pages/Blog/BlogDetails')->with($Data);
	}

	public function BlogSearch()
	{
		$AutoSearchList 	= array();
		$Search['Term'] 	= $_GET['term'];
		$List  				= $this->BlogModel->BlogSerach($Search);

		foreach($List as $l)
		{
            $Sample['id'] 	= url('blog-details').'/'.$l->cat_slug.'/'.$l->slug;
			$Sample['lable']= ucfirst($l->title.'-'.$l->category);
			$Sample['value']= ucfirst($l->title.'-'.$l->category);
			
			array_push($AutoSearchList, $Sample);
 		}
 		echo json_encode($AutoSearchList);
		exit();
	}
}