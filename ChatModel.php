<?php
namespace App\Http\Models\API;
use Illuminate\Database\Eloquent\Model;
use App\Http\Models\Web\Front\MemberModel;
use App\Http\Controllers\Pagination;
use DB;
class ChatModel extends Model 
{
	public function __construct()
	{
		$this->Pagination = new Pagination();
	}
}