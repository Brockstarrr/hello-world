<?php
namespace App\Http\Controllers\API;
use Route;
use Mail;
use Auth, Hash;
use Validator;
use Session;
use Redirect;
use DB;
use Crypt;
use Illuminate\Http\Request;
use App\Http\Models\Front\ViewJobModel;
use App\Http\Models\Front\MyScheduleModel;
use App\Http\Models\Front\MessageModel;
use App\Http\Models\API\ChatModel;
use App\Http\Models\API\JobApplyModel;
use App\Http\Models\API\JobModel;
use App\Http\Models\API\CommonModel;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Pagination;
use App\Helpers\Common;

class ChatController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->CommonModel 		= new CommonModel();
		$this->MessageModel 	= new MessageModel();
		$this->ChatModel 		= new ChatModel();
		$this->Common 			= new Common();
	}

	public function OneToOneEmployeeListAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$EmployeeList 	= array();
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$ChatUserList	= $this->MessageModel->GetEmployeeList($UserID);
			if(!empty($ChatUserList))
			{
				$Sample = array();
				foreach($ChatUserList as $cl)
				{
					$Sample['EmployeeID'] 	= $cl->id;
					$Sample['Name'] 	= $cl->name;
					if($cl->image==''){  
                      $Image  = asset('public/Front/Design/img/pro_pic.png');
                    } else{
                    	$Image  = asset('public/Front/Users/Profile').'/'.$cl->image;
                     }
					$Sample['Image'] 	= $Image;
					array_push($EmployeeList, $Sample);
				}
			}
			$Response = ['Status'	=> true,
						'Message' 	=> 'One To One Employee List.',
						'EmployeeList'=> $EmployeeList
						];
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function OneToOneEmployerListAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$EmployerList 	= array();
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$ChatUserList	= $this->MessageModel->GetEmployerList($UserID);
			if(!empty($ChatUserList))
			{
				$Sample = array();
				foreach($ChatUserList as $cl)
				{
					$Sample['EmployerID'] 	= $cl->id;
					$Sample['Name'] 	= $cl->name;
					if($cl->image==''){  
                      $Image  = asset('public/Front/Design/img/pro_pic.png');
                    } else{
                    	$Image  = asset('public/Front/Users/Profile').'/'.$cl->image;
                     }
					$Sample['Image'] 	= $Image;
					array_push($EmployerList, $Sample);
				}
			}
			$Response = ['Status'		=> true,
						'Message' 		=> 'One To One Employer List.',
						'EmployerList'	=> $EmployerList
						];
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function EmployeeChatAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$EmployeeID 	= $Data['EmployeeID'];
		$EmployeeChat 	= array();
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}
		if($EmployeeID=='')
		{
			$Response = ['Status' => false,'Message' => 'EmployeeID Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$GetUserDetails = $this->MessageModel->GetUserDetails($EmployeeID);
			$EmployeeName = $GetUserDetails->first_name.' '.$GetUserDetails->last_name;
			$GetEmployeeChat = $this->MessageModel->GetEmployeeChat($UserID,$EmployeeID);
			if(!empty($GetEmployeeChat))
			{
				$Sample = array();
				foreach($GetEmployeeChat as $ec)
				{
					$Position = 0;
       				if($ec->message_from==$UserID)
       				{
       					$Position = 1;
       				}
                    $Sample['MessageID'] = $ec->id;
                    $Sample['Position'] = $Position;
					if($ec->message!='')
					{
                      	$Sample['Message'] 	= $ec->message;
                      	$Sample['File'] 	= "";
                      	$Sample['FileName'] = "";
                    } 
                    else
                    {
                    	$File  = asset('public/Front/Users/Message/Attachment').'/'.$ec->attachment_temp;
                    	$Sample['Message'] 	= "";
                    	$Sample['File'] 	= $File;
                    	$Sample['FileName'] = $ec->attachment;
                    }
                    $Sample['DateTime'] = strtotime($ec->message_at);
					array_push($EmployeeChat, $Sample);
				}
			}
			else
			{
				$Sample = array();
				$Sample['Message'] 	= "";
            	$Sample['File'] 	= "";
            	$Sample['FileName'] = "";
            	array_push($EmployeeChat, $Sample);
			}
			$Response = ['Status'		=> true,
						'Message' 		=> 'One To One Employee Chat.',
						'EmployeeID'	=> $EmployeeID,
						'EmployeeName' 	=> $EmployeeName,
						'EmployeeChat' 	=> $EmployeeChat
						];
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function EmployerChatAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$EmployerID 	= $Data['EmployerID'];
		$EmployerChat 	= array();
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}
		if($EmployerID=='')
		{
			$Response = ['Status' => false,'Message' => 'EmployerID Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$GetUserDetails = $this->MessageModel->GetUserDetails($EmployerID);
			$EmployerName = $GetUserDetails->first_name.' '.$GetUserDetails->last_name;
			$GetEmployerChat = $this->MessageModel->GetEmployeeChat($UserID,$EmployerID);
			if(!empty($GetEmployerChat))
			{
				$Sample = array();
				foreach($GetEmployerChat as $ec)
				{
					$Position = 0;
       				if($ec->message_from==$UserID)
       				{
       					$Position = 1;
       				}
                    $Sample['MessageID'] = $ec->id;
                    $Sample['Position'] = $Position;
					if($ec->message!='')
					{
                      	$Sample['Message'] 	= $ec->message;
                      	$Sample['File'] 	= "";
                      	$Sample['FileName'] = "";
                    } 
                    else
                    {
                    	$File  = asset('public/Front/Users/Message/Attachment').'/'.$ec->attachment_temp;
                    	$Sample['Message'] 	= "";
                    	$Sample['File'] 	= $File;
                    	$Sample['FileName'] = $ec->attachment;
                    }
                    $Sample['DateTime'] = strtotime($ec->message_at);
					array_push($EmployerChat, $Sample);
				}
			}
			$Response = ['Status'		=> true,
						'Message' 		=> 'One To One Employee Chat.',
						'EmployerID'	=> $EmployerID,
						'EmployerName'	=> $EmployerName,
						'EmployerChat'	=> $EmployerChat
						];
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function SendMessageEmployeeAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$EmployeeID 	= $Data['EmployeeID'];
		$Message 		= $Data['Message'];
		$EmployerChat 	= array();
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}
		if($EmployeeID=='')
		{
			$Response = ['Status' => false,'Message' => 'EmployeeID Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			
			$Details['message_from'] 	= $UserID ;
			$Details['message_to'] 		= $EmployeeID;
			$Details['message'] 		= '';
			if($Message!='')
			{
				$Details['message'] 		= $Message;
			}
			$Details['attachment'] 		= '';
			$File 						= $request->file('Attachment');
		    if(!empty($File)){
			    $Path = 'public/Front/Users/Message/Attachment';
		        $ImageName = $File->getClientOriginalName();
		        $ImageNameTemp = time().'_'.$File->getClientOriginalName();
		        $Upload = $File->move($Path, $ImageNameTemp);
		        $Details['attachment'] 		= $ImageName;
		        $Details['attachment_temp'] = $ImageNameTemp;
	        }
			$Save = $this->MessageModel->SaveMessage($Details);
			if($Save)
			{
				$Response = ['Status'=> true,'Message'=> 'Message Sent Successfully.'];
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'Something Wrong Please Try Again.'];
			}			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function SendMessageEmployerAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$EmployerID 	= $Data['EmployerID'];
		$Message 		= $Data['Message'];
		$EmployerChat 	= array();
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}
		if($EmployerID=='')
		{
			$Response = ['Status' => false,'Message' => 'EmployerID Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			
			$Details['message_from'] 	= $UserID ;
			$Details['message_to'] 		= $EmployerID;
			$Details['message'] 		= '';
			if($Message!='')
			{
				$Details['message'] 		= $Message;
			}
			$Details['attachment'] 		= '';
			$File 						= $request->file('Attachment');
		    if(!empty($File)){
			    $Path = 'public/Front/Users/Message/Attachment';
		        $ImageName = $File->getClientOriginalName();
		        $ImageNameTemp = time().'_'.$File->getClientOriginalName();
		        $Upload = $File->move($Path, $ImageNameTemp);
		        $Details['attachment'] 		= $ImageName;
		        $Details['attachment_temp'] = $ImageNameTemp;
	        }
			$Save = $this->MessageModel->SaveMessage($Details);
			if($Save)
			{
				$Response = ['Status'=> true,'Message'=> 'Message Sent Successfully.'];
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'Something Wrong Please Try Again.'];
			}			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}

	//////////////////

	public function GetMyPostedJobListAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$EmployeeList 	= array();
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$JobList 	= array();
			$Jobs		= $this->MessageModel->JobList($UserID);
			if(!empty($Jobs))
			{
				$Sample = array();
				foreach($Jobs as $j)
				{
					$Sample['JobID'] 	= $j->id;
					$Sample['JobTitle'] = $j->job_title;
					array_push($JobList, $Sample);
				}
				$Response = ['Status'	=> true,
							'Message' 	=> 'Get My Posted Job List.',
							'JobList'	=> $JobList
							];
			}
			else
			{
				$Response = ['Status'	=> true,
							'Message' 	=> 'Sorry! You Have Not Posted Any Job.',
							'JobList'	=> $JobList
							];
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function GetAppliedMemberListAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$JobID 			= $Data['JobID'];
		$EmployeeList 	= array();
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}
		if($JobID=='')
		{
			$Response = ['Status' => false,'Message' => 'JobID Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$AppliedMemberList 	= array();
			$AppliedMember = $this->MessageModel->AppliedMemberList($JobID,$UserID);
			if(!empty($AppliedMember))
			{
				$Sample = array();
				foreach($AppliedMember as $am)
				{
					$Sample['EmployeeID'] 	= $am->id;
					$Sample['EmployeeName'] = $am->first_name.' '.$am->last_name;
					$Sample['AppliedFor'] 	= $am->position;
					array_push($AppliedMemberList, $Sample);
				}
				$Response = ['Status'	=> true,
							'Message' 	=> 'Applied Member List.',
							'AppliedMemberList'	=> $AppliedMemberList
							];
			}
			else
			{
				$Sample = array();
				$Sample['EmployeeID'] 	= "";
				$Sample['EmployeeName'] = "";
				$Sample['AppliedFor'] 	= "";
				array_push($AppliedMemberList, $Sample);
				$Response = ['Status'	=> false,
							'Message' 	=> 'No One Applied For This Job.',
							'AppliedMemberList'	=> $AppliedMemberList
							];
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function CreateChatGroupAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$JobID 			= $Data['JobID'];
		$GroupName 		= $Data['GroupName'];
		$AppliedUser 	= $Data['AppliedUser'];
		$EmployeeList 	= array();
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}
		if($JobID=='')
		{
			$Response = ['Status' => false,'Message' => 'JobID Parameter Missing.'];
			return response()->json($Response);
		}
		if($GroupName=='')
		{
			$Response = ['Status' => false,'Message' => 'GroupName Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$Group['group_owner'] = $UserID;
			$Group['group_name']  = $GroupName;
			$Group['job_id']	  = $JobID;
			$Group['members']	  = $AppliedUser;
			$CreateChatGroup = $this->MessageModel->CreateGroup($Group);
			if($CreateChatGroup)
			{
				$Response = ['Status'	=> true,
							'Message' 	=> 'Group Creaed Successfully.'
							];
			}
			else
			{
				$Response = ['Status'	=> true,
							'Message' 	=> 'Something Wrong Please Try Again.'
							];
			}			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function GetMyGroupListAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$MyGroupList 	= array();
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$CreatedGroupChatList	= $this->MessageModel->CreatedGroupChatList($UserID);
			if(!empty($CreatedGroupChatList))
			{
				$Sample = array();
				foreach($CreatedGroupChatList as $cl)
				{
                    
					if($cl->image=='')
					{
                      	$Image = asset('public/Front/Design/img/pro_pic.png');
                    } 
                    else
                    {
                    	$Image = asset('public/Front/Users/Profile').'/'.$cl->image;
                    }
                    $Sample['GroupID'] 		= $cl->id;
                    $Sample['GroupName'] 	= $cl->group_name;
                    $Sample['Image'] 		= $Image;
					array_push($MyGroupList, $Sample);
				}
				$Response = ['Status'		=> true,
							'Message' 		=> 'Get My Group Chat List.',
							'MyGroupList'	=> $MyGroupList
							];
			}
			else
			{		
				$Sample['GroupID'] 		= '';
                $Sample['GroupName'] 	= '';
                $Sample['Image'] 		= '';
				array_push($MyGroupList, $Sample);		
				$Response = ['Status'		=> false,
							'Message' 		=> 'You Dont Have Any Group.',
							'MyGroupList'	=> $MyGroupList
							];
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function GetGroupIAmInListAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$GroupIAmIn 	= array();
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$CreatedGroupChatList	= $this->MessageModel->GroupMemberChatList($UserID);
			if(!empty($CreatedGroupChatList))
			{
				$Sample = array();
				foreach($CreatedGroupChatList as $cl)
				{
                    
					if($cl->image=='')
					{
                      	$Image = asset('public/Front/Design/img/pro_pic.png');
                    } 
                    else
                    {
                    	$Image = asset('public/Front/Users/Profile').'/'.$cl->image;
                    }
                    $Sample['GroupID'] 		= $cl->id;
                    $Sample['GroupName'] 	= $cl->group_name;
                    $Sample['Image'] 		= $Image;
					array_push($GroupIAmIn, $Sample);
				}
				$Response = ['Status'		=> true,
							'Message' 		=> 'Get My Group Chat List.',
							'GroupIAmIn'	=> $GroupIAmIn
							];
			}
			else
			{		
				$Sample = array();
				$Sample['GroupID'] 		= "";
				$Sample['GroupName'] 	= "";
				$Sample['Image'] 		= "";
				array_push($GroupIAmIn, $Sample);		
				$Response = ['Status'		=> false,
							'Message' 		=> 'You Are Not A Member Of Any Group.',
							'GroupIAmIn'	=> $GroupIAmIn
							];
			}
			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function GetMyGroupChatListAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$GroupID 		= $Data['GroupID'];
		$MyGroupChatList 	= array();
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$GetGroupDetails 	= $this->MessageModel->GetGroupDetails($UserID,$GroupID);
			$UserList 			= $GetGroupDetails->members;
			$UserNameList 		= $this->MessageModel->GetUserNameList($UserList);
			$GroupMessageList 	= $this->MessageModel->GetGroupMessageList($GroupID);
			if(!empty($GroupMessageList))
			{
				$Sample = array();
				foreach($GroupMessageList as $ec)
				{
                    $Sample['MessageID'] = $ec->id;
                     $Sample['MessageFrom'] = $ec->first_name.' '.$ec->last_name;
					if($ec->message!='')
					{
                      	$Sample['Message'] 	= $ec->message;
                      	$Sample['File'] 	= "";
                      	$Sample['FileName'] = "";
                    } 
                    else
                    {
                    	$File  = asset('public/Front/Users/Message/Group/Attachment').'/'.$ec->attachment_temp;
                    	$Sample['Message'] 	= "";
                    	$Sample['File'] 	= $File;
                    	$Sample['FileName'] = $ec->attachment;
                    }
                    $Sample['DateTime'] = $this->Common->TimeElapsedString($ec->updated_at,false);;
					array_push($MyGroupChatList, $Sample);
				}
			}
			else
			{
				$Sample = array();
				$Sample['Message'] 		= "";
				$Sample['File'] 		= "";
				$Sample['FileName'] 	= "";
				array_push($MyGroupChatList, $Sample);
			}
			$Response = ['Status'			=> true,
						'Message' 			=> 'Get My Group Chat List.',
						'GroupName'			=> $GetGroupDetails->group_name,
						'UserNameList'		=> $UserNameList,
						'MyGroupChatList'	=> $MyGroupChatList
						];			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function GetGroupIAmInChatListAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$GroupID 		= $Data['GroupID'];
		$GroupIAmInChatList 	= array();
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$GetGroupDetails 	= $this->MessageModel->GetGroupDetails($UserID,$GroupID);
			$UserList 			= $GetGroupDetails->members;
			$UserNameList 		= $this->MessageModel->GetUserNameList($UserList);
			$GroupMessageList 	= $this->MessageModel->GetGroupMessageList($GroupID);
			if(!empty($GroupMessageList))
			{
				$Sample = array();
				foreach($GroupMessageList as $ec)
				{
                    $Sample['MessageID'] = $ec->id;
                    $Sample['MessageFrom'] = $ec->first_name.' '.$ec->last_name;
					if($ec->message!='')
					{
                      	$Sample['Message'] 	= $ec->message;
                      	$Sample['File'] 	= "";
                      	$Sample['FileName'] = "";
                    } 
                    else
                    {
                    	$File  = asset('public/Front/Users/Message/Group/Attachment').'/'.$ec->attachment_temp;
                    	$Sample['Message'] 	= "";
                    	$Sample['File'] 	= $File;
                    	$Sample['FileName'] = $ec->attachment;
                    }
                    $Sample['DateTime'] = $this->Common->TimeElapsedString($ec->updated_at,false);;
					array_push($GroupIAmInChatList, $Sample);
				}
			}
			$Response = ['Status'			=> true,
						'Message' 			=> 'Get My Group Chat List.',
						'GroupName'			=> $GetGroupDetails->group_name,
						'UserNameList'		=> $UserNameList,
						'GroupIAmInChatList'=> $GroupIAmInChatList
						];			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
	public function SendMessageInGroupAPI(Request $request)
	{
		$Data 			= $request->all();	
		$Response   	= array();	
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$GroupID 		= $Data['GroupID'];
		$Message 		= $Data['Message'];
		$EmployerChat 	= array();
		if($UserID=='')
		{
			$Response = ['Status' => false,'Message' => 'UserID Parameter Missing.'];
			return response()->json($Response);
		}		
		if($AccessToken=='')
		{
			$Response = ['Status' => false,'Message' => 'AccessToken Parameter Missing.'];
			return response()->json($Response);
		}
		if($GroupID=='')
		{
			$Response = ['Status' => false,'Message' => 'GroupID Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{			
			$Details['group_id'] 		= $GroupID;
			$Details['from'] 			= $UserID;
			$Details['message'] 		= '';
			if($Message!='')
			{
				$Details['message'] 		= $Message;
			}
			$Details['attachment'] 		= '';
			$File 						= $request->file('Attachment');
		    if(!empty($File)){
			    $Path = 'public/Front/Users/Message/Group/Attachment';
		        $ImageName = $File->getClientOriginalName();
		        $ImageNameTemp = time().'_'.$File->getClientOriginalName();
		        $Upload = $File->move($Path, $ImageNameTemp);
		        $Details['attachment'] 		= $ImageName;
		        $Details['attachment_temp'] = $ImageNameTemp;
	        }
			$Save = $this->MessageModel->SendGroupMessage($Details);
			if($Save)
			{
				$Response = ['Status'=> true,'Message'=> 'Message Sent Successfully.'];
			}
			else
			{
				$Response = ['Status'=>false,'Message'=>'Something Wrong Please Try Again.'];
			}			
		}
		else
		{
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
		}
	  	return response()->json($Response);
	}
}