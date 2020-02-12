<?php
namespace App\Http\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use DB;
class BlogModel extends Model 
{
	public function CategoryList($start,$numofrecords,$Search){
    $Data = DB::table('blog_category');
    if($Search['name']!=''){
      $Data->where('category','like', '%' . $Search['name'] . '%');
    }
    if($Search['slug']!=''){
      $Data->where('slug', $Search['slug']);
    }
    if($Search['status']!='All'){
      $Data->where('status',$Search['status']);
    }
    if($Search['sort']!=''){
      $Data->where('sort',$Search['sort']);
    }
    $Data->select('*');
    $Data->orderBy('id','desc');                   
                      
    $Res['Count'] = $Data->count();
    $Res['Res'] 	= $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }
  public function CheckCategoryName($data){
    $result = DB::table('blog_category')->where($data)->count();
    return $result;
  }
  public function CheckCategoryEditName($data,$ID){
    $result = DB::table('blog_category')->where($data)->where('id','!=',$ID)->count();
    return $result;
  }
  public function UpdateCategory($id,$data){
    $result = DB::table('blog_category')->where('id',$id)->update($data);
    return true;
  }
  public function AddCategory($Details){
    $result=DB::table('blog_category')->insertGetId($Details);
    return $result; 
  }
  public function GetCategoryDetails($ID){
    $result=DB::table('blog_category')->where('id',$ID)->first();
    return $result; 
  }
  public function GetAllCategory(){
    $result=DB::table('blog_category')->select('id','category')->get();
    return $result;
  }

  /*Post Position*/
  public function UpdatePost($id,$data){
    $result = DB::table('blog')->where('id',$id)->update($data);
    return true;
  }
  public function AddPost($Details){
    $result=DB::table('blog')->insertGetId($Details);
    return $result; 
  }
  public function GetPostDetails($ID){
    $result=DB::table('blog')->where('id',$ID)->first();
    return $result; 
  }
  public function CheckPostName($data){
    $result = DB::table('blog')->where($data)->count();
    return $result;
  }
  public function CheckPostEditName($data,$ID){
    $result = DB::table('blog')->where($data)->where('id','!=',$ID)->count();
    return $result;
  }
  public function PostList($start,$numofrecords,$Search){
    $Data = DB::table('blog as b');
    if($Search['name']!=''){
      $Data->where('b.title','like', '%' . $Search['name'] . '%');
    }
    if($Search['author']!=''){
      $Data->where('b.author','like', '%' . $Search['author'] . '%');
    }
    if($Search['cat_id']!='All'){
      $Data->where('b.cat_id', $Search['cat_id']);
    }
    if($Search['status']!='All'){
      $Data->where('b.status',$Search['status']);
    }
    if($Search['publish_date']!=''){
      $publish_date = date('Y-m-d',strtotime($Search['publish_date']));
      $Data->where('b.publish_date',$publish_date);
    }
    
    $Data->join('blog_category as c','c.id', '=','b.cat_id');
    $Data->select('b.*','c.category');
    $Data->orderBy('b.id','desc');                   
                      
    $Res['Count'] = $Data->count();
    $Res['Res']   = $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }

  public function CommentList($start,$numofrecords,$Search){
    $Data = DB::table('blog_comments as c');
    if($Search['title']!=''){
      $Data->where('b.title','like', '%' . $Search['title'] . '%');
    }
    if($Search['user_name']!=''){
      $Data->where('user.first_name','like', '%' . $Search['user_name'] . '%');
    }
    if($Search['cat_id']!='All'){
      $Data->where('b.cat_id', $Search['cat_id']);
    }
    if($Search['status']!='All'){
      $Data->where('c.status',$Search['status']);
    }
    
    $Data->join('blog as b','b.id', '=','c.blog_id');
    $Data->join('blog_category as cat','cat.id', '=','b.cat_id');
    $Data->join('profile as user','user.id', '=','c.user_id');
    $Data->select('c.id','c.status','c.comment','c.user_id','b.title','user.first_name','user.last_name','cat.category');
    $Data->orderBy('b.id','desc');                   
                      
    $Res['Count'] = $Data->count();
    $Res['Res']   = $Data->offset($start)->limit($numofrecords)->get();
    return $Res;
  }

  public function UpdateComment($id,$data){
    $result = DB::table('blog_comments')->where('id',$id)->update($data);
    return true;
  }
}