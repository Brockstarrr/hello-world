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
use App\Http\Models\Front\MessageModel;
use App\Http\Models\Front\UserModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;
use DateTime;
class MessageController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->MessageModel 	= new MessageModel();
		$this->UserModel 	= new UserModel();
		$this->Common 		= new Common();
	}
	public function Message()
	{
		$UserID 		= Session::get('UserID');
		$Data['Title'] 	= 'My Message';
		$Data['Menu'] 	= 'Message';
		return View('Front/Pages/User/Message')->with($Data);
	}
	public function GetUserSingleChatList(Request $request)
	{
		$Data 			= $request->all();
		$UserID 		= Session::get('UserID');
		$Type 			= $Data['Type'];
		$SortBy 		= $Data['SortBy'];
		$ChatUserList 	= array();
		if($Type=='1')
		{
			$ChatUserList	= $this->MessageModel->GetEmployeeList($UserID,$SortBy);
			
			if(!empty($ChatUserList))
			{
				?><ul><?php 
	          	foreach($ChatUserList as $cl)
	          	{
	          		$EmployeeID = $cl->id;
	          		$getLastMessage = $this->MessageModel->getLastMessage($UserID,$EmployeeID);	          		
	          		?>
	                <li>
	                   <input type="radio" name="chat_radio" value="<?php echo $cl->id; ?>"
	                   	onclick="GetEmployeeChat();">
	                   <div class="chat_check">
	                      <div class="chat_img">
	                        <?php if($cl->image==''){ ?> 
	                          <img src="<?php echo asset('public/Front/Design/img/pro_pic.png'); ?>" alt="" />
	                        <?php } else{ ?> 
	                          <img src="<?php echo asset('public/Front/Users/Profile').'/'.$cl->image; ?>" alt="" />
	                        <?php } ?>                                             
	                      </div>
	                      <div class="chat_cont">
	                         <h6><?php echo $cl->name; ?></h6>
	                         <?php if(!empty($getLastMessage)){?>	                         
	                         <p><?php echo $this->Common->TimeElapsedString($getLastMessage->message_at,false); ?></p>
	                         <?php } ?>
	                      </div>
	                   </div>
	                </li>
	                <?php
	          	}
	          	?></ul><?php 
	        }			
		}
		else if($Type=='2')
		{
			$ChatUserList	= $this->MessageModel->GetEmployerList($UserID,$SortBy);
			if(!empty($ChatUserList))
			{
				?><ul><?php 
	          	foreach($ChatUserList as $cl)
	          	{
	          		$EmployeeID = $cl->id;
					$getLastMessage = $this->MessageModel->getLastMessage($UserID,$EmployeeID);	
	          		?>
	                <li>
	                   <input type="radio" name="chat_radio" value="<?php echo $cl->id; ?>"
	                   	onclick="GetEmployerChat();">
	                   <div class="chat_check">
	                      <div class="chat_img">
	                        <?php if($cl->image==''){ ?> 
	                          <img src="<?php echo asset('public/Front/Design/img/pro_pic.png'); ?>" alt="" />
	                        <?php } else{ ?> 
	                          <img src="<?php echo asset('public/Front/Users/Profile').'/'.$cl->image; ?>" alt="" />
	                        <?php } ?>                                             
	                      </div>
	                      <div class="chat_cont">
	                         <h6><?php echo $cl->name; ?></h6>
	                         <?php if(!empty($getLastMessage)){?>	                         
	                         <p><?php echo $this->Common->TimeElapsedString($getLastMessage->message_at,false); ?></p>
	                         <?php } ?>
	                      </div>
	                   </div>
	                </li>
	                <?php
	          	}
	          	?></ul><?php 
	        }
		}


		exit();
	}
	public function GetEmployeeChat(Request $request)
	{
		$Data 			= $request->all();
		$UserID 		= Session::get('UserID');
		$EmployeeID 	= $Data['EmployeeID'];

		$GetUserDetails = $this->MessageModel->GetUserDetails($EmployeeID);
		$GetEmployeeChat = $this->MessageModel->GetEmployeeChat($UserID,$EmployeeID);
		?>
		<h5><?php echo $GetUserDetails->first_name.' '.$GetUserDetails->last_name; ?></h5>
        <div class="ct_box">
           <div class="chat_type">
			<div class="msg_send">
				<!-- <a href="javascript:void(0);">
					<i class="fa fa-smile-o" aria-hidden="true"></i>
				</a> -->
				<a href="javascript:void(0);" onclick="OpenBrowser1();">
					<i class="fa fa-paperclip" aria-hidden="true"></i>
				</a>
				<a href="javascript:void(0);" onclick="SendMessage();">
					<i class="fa fa-paper-plane" aria-hidden="true" ></i>
				</a>
			</div>				
          	<form action="" name="Browser1From" id="Browser1From">
              	<input type="hidden" name="From" value="<?=$UserID?>">
              	<input type="hidden" name="To" value="<?=$EmployeeID?>">
              	<input type="hidden" name="_token" value="<?=csrf_token()?>">
              	<input type='file' style="display:none;" name="Browser1" id="Browser1" onchange="GetBrowser1Value();">
      		</form>
              <input type="hidden" id='MessageCount' value="<?php echo count($GetEmployeeChat); ?>">
              <textarea onkeypress='Hide("ErrMessage"),CheckTest();' class="form-control inputfield2" 
              	placeholder="Type here..." id="MessageText" data-emoji-input="unicode" data-emojiable="true"></textarea>
           		<span id="ErrMessage"></span>
           </div>
           	<script>
			 	$(function() {
			      window.emojiPicker = new EmojiPicker({
			        emojiable_selector: '[data-emojiable=true]',
			        assetsPath: 'public/Front/Design/emoji/lib/img/',
			        popupButtonClasses: 'fa fa-smile-o'
			      });
			      window.emojiPicker.discover();
			  	});
			</script>
           <?php if(!empty($GetEmployeeChat)){ ?>
           <ul class="chating">
       			<?php 
       			foreach($GetEmployeeChat as $ec)
       			{ 
       				$Class="";
       				if($ec->message_from==$UserID)
       				{
       					$Class="class='sn'";
       				}
	       			?>
	       			<?php if($ec->message!=''){ ?>
	              	<li <?php echo $Class; ?>>
		                <div class="chating_cont">
		                	<p><?php echo nl2br($ec->message); ?></p>
		                	<span><?php echo $this->Common->TimeElapsedString($ec->message_at,false); ?></span>
		                </div>
		            </li> 
		            <?php } else{ ?>
		            <li <?php echo $Class; ?>>
		                <div class="chating_cont">
		                	<p>
		                		<a href="<?php echo asset('public/Front/Users/Message/Attachment').'/'.$ec->attachment_temp; ?>" 
		                		target='_blank'>
		                		<i class="fa fa-file"></i> <?php echo $ec->attachment; ?>
		                		</a>
		                	</p>
		                	<span><?php echo $this->Common->TimeElapsedString($ec->message_at,false); ?></span>
		                </div>
		            </li> 
		            <?php } ?>
		            <?php 
        		} 
        		?>
           </ul>
           <?php } else {?>
           Chat Not Available!
           <?php } ?>
        </div>
		<?php
		exit();
	}
	public function SendMessage(Request $request)
	{
		$Data 			= $request->all();
		$UserID 		= Session::get('UserID');
		$EmployeeID 	= $Data['EmployeeID'];
		$MessageText 	= $Data['MessageText'];

		$Detsils['message_from'] 	= $UserID ;
		$Detsils['message_to'] 		= $EmployeeID;
		$Detsils['message'] 		= $MessageText;
		$Detsils['attachment'] 		= '';

		$Save = $this->MessageModel->SaveMessage($Detsils);
		if($Save)
		{
			echo 1;
		}
		else
		{
			echo 0;
		}
		exit();
	}
	public function GetNewMessage(Request $request)
	{
		$Data 			= $request->all();
		$UserID 		= Session::get('UserID');
		$EmployeeID 	= $Data['EmployeeID'];
		$GetEmployeeChat = $this->MessageModel->GetEmployeeChat($UserID,$EmployeeID);
		echo count($GetEmployeeChat);
		exit();
	}

	///////////////////////////////////////////////////////////////////////
	public function CreateGroupFromJobList(Request $request)
	{
		$Data 			= $request->all();
		$UserID 		= Session::get('UserID');
		$GetJobList 	= $this->MessageModel->JobList($UserID);
		?>
		<div class="cont_wrap">
          <h3>Create Group Chat</h3>
        	<div class="row">
        		<?php if(!empty($GetJobList)){ ?>
	            <div class="col-md-6">
	            	<div class="form-group">
		              	<label class="input_label2">Group Name</label>
		                <input class="form-control inputfield2" id="GroupName" 
		                	onkeypress="Hide('CreateGroupErr');" placeholder="Group Name">
	              	</div>
					
	            </div>
	            <div class="col-md-6">
	            	<div class="form-group">
						<label class="input_label2">Job List</label>
						<select class="form-control inputfield2" id="JobID" 
							onchange="GetAppliedMemberList(),Hide('CreateGroupErr');">
							<option value="">Select Posted Job</option>
							<?php foreach($GetJobList as $j){ ?>
							<option value="<?php echo $j->id; ?>"><?php echo $j->job_title; ?></option>
							<?php }?>
						</select>
					</div>
	            </div>
	            <div class="col-md-6 chat_gr_cont" id="AppliedMemberList"> 
	            </div>
	            <div class="col-md-12 text-center mt-3">
	            	<span id="CreateGroupErr">
	            	</span>
	            </div>
	            <div class="col-md-12 text-center mt-3">
	              <a class="btn cus_btn2 blue" href="javascript:void(0);" onclick="CreateGroup();">Create Group</a>
	            </div>
	            <?php } else{ ?>
	            	Sorry! You Have Not Posted Any Job.
	            <?php } ?>
          	</div>
        </div>
		<?php
		exit();
	}
	public function GetAppliedMemberList(Request $request)
	{
		$Data 		= $request->all();
		$UserID 	= Session::get('UserID');
		$JobID 		= $Data['JobID'];
		$AppliedMemberList = $this->MessageModel->AppliedMemberList($JobID,$UserID);
		
		if(!empty($AppliedMemberList))
		{
			?>
			<input type="hidden" id="AppliedCount" value="<?php echo count($AppliedMemberList); ?>">
			<?php
			foreach ($AppliedMemberList as $ae) 
			{
				?>
				<div class="form-group my_job_check">
					<div class="custom-control custom-checkbox cus_check">
						<input type="checkbox" class="custom-control-input Applied" 
							id="chat_gr1_<?php echo $ae->id; ?>" 
							name="Applied" value="<?php echo $ae->id; ?>"
							onclick="Hide('CreateGroupErr');">
						<label class="custom-control-label" for="chat_gr1_<?php echo $ae->id; ?>">
							<?php echo $ae->first_name.' '.$ae->last_name.' ('.$ae->position.')';?>
						</label>
					</div>
				</div>				
				<?php
			}
		}
		else
		{
			?>
			<input type="hidden" id="AppliedCount" value="<?php echo count($AppliedMemberList); ?>">
			No One Applied For This Job.
			<?php
		}
		exit();
	}
	public function CreateGroup(Request $request)
	{
		$Data 			= $request->all();
		$UserID 		= Session::get('UserID');
		$JobID 			= $Data['JobID'];
		$AppliedUser 	= $Data['AppliedUser'];
		$GroupName 		= $Data['GroupName'];

		$Group['group_owner']= $UserID;
		$Group['group_name']= $GroupName;
		$Group['job_id']	= $JobID;
		$Group['members']	= json_encode($AppliedUser);
		
		$CreateGroup = $this->MessageModel->CreateGroup($Group);
		if($CreateGroup)
		{
			echo 1;
		}	
		else
		{
			echo 0;
		}
		exit();
	}
	public function GetGroupChatList(Request $request)
	{
		$Data 			= $request->all();
		$UserID 		= Session::get('UserID');
		$GroupChatList  = array();
		$GroupType 		= $Data['GroupType'];

		if($GroupType==1)
		{
			$CreatedGroupChatList	= $this->MessageModel->CreatedGroupChatList($UserID);
			if(!empty($CreatedGroupChatList))
			{
				?><ul><?php 
	          	foreach($CreatedGroupChatList as $cl)
	          	{
	          		?>
	                <li>
	                   <input type="radio" name="chat_radio2" value="<?php echo $cl->id; ?>"
	                   	onclick="GetCreatedGroupChat();">
	                   <div class="chat_check">
	                      <div class="chat_img">
	                        <?php if($cl->image==''){ ?> 
	                          <img src="<?php echo asset('public/Front/Design/img/pro_pic.png'); ?>" alt="" />
	                        <?php } else{ ?> 
	                          <img src="<?php echo asset('public/Front/Users/Profile').'/'.$cl->image; ?>" alt="" />
	                        <?php } ?>                                             
	                      </div>
	                      <div class="chat_cont">
	                         <h6><?php echo $cl->group_name; ?></h6>
	                         <p><?php echo $cl->group_name; ?></p>
	                      </div>
	                   </div>
	                </li>
	                <?php
	          	}
	          	?></ul><?php 
	        }	
		}
		else
		{
			$GroupMemberChatList	= $this->MessageModel->GroupMemberChatList($UserID);
			
			if(!empty($GroupMemberChatList))
			{
				?><ul><?php 
	          	foreach($GroupMemberChatList as $cl)
	          	{
	          		?>
	                <li>
	                   <input type="radio" name="chat_radio2" value="<?php echo $cl->id; ?>"
	                   	onclick="GetGroupMemberChat();">
	                   <div class="chat_check">
	                      <div class="chat_img">
	                        <?php if($cl->image==''){ ?> 
	                          <img src="<?php echo asset('public/Front/Design/img/pro_pic.png'); ?>" alt="" />
	                        <?php } else{ ?> 
	                          <img src="<?php echo asset('public/Front/Users/Profile').'/'.$cl->image; ?>" alt="" />
	                        <?php } ?>                                             
	                      </div>
	                      <div class="chat_cont">
	                         <h6><?php echo $cl->group_name; ?></h6>
	                          <p><?php echo $cl->group_name; ?></p>
	                      </div>
	                   </div>
	                </li>
	                <?php
	          	}
	          	?></ul><?php 
	        }	
		}
		
		exit();
	}
	public function GetCreatedGroupChat(Request $request)
	{
		$Data 			= $request->all();
		$UserID 		= Session::get('UserID');
		$GroupID 		= $Data['GroupID'];

		$GetGroupDetails 	= $this->MessageModel->GetGroupDetails($UserID,$GroupID);
		$UserList 			= $GetGroupDetails->members;
		$UserNameList 		= $this->MessageModel->GetUserNameList($UserList);
		$GroupMessageList 	= $this->MessageModel->GetGroupMessageList($GroupID);
		?>
		<h5><?php echo $GetGroupDetails->group_name; ?></h5>
		<span><?php echo $UserNameList; ?></span>
        <div class="ct_box">
           <div class="chat_type">
              <div class="msg_send">
                 <!-- <a href="javascript:void(0);">
                 	<i class="fa fa-smile-o" aria-hidden="true"></i>
                 </a> -->
                 <a href="javascript:void(0);" onclick="OpenBrowser2();">
                 	<i class="fa fa-paperclip" aria-hidden="true" ></i>
                 </a>
                 <a href="javascript:void(0);" onclick="SendGroupMessage();">
                 	<i class="fa fa-paper-plane" aria-hidden="true"></i>
                 </a>
              </div>
              	<form action="" name="Browser2From" id="Browser2From">
	              	<input type="hidden" name="GroupID" value="<?=$GroupID?>">
	              	<input type="hidden" name="_token" value="<?=csrf_token()?>">
	              	<input type='file' style="display:none;" name="Browser2" id="Browser2" 
	              		onchange="GetBrowser2Value();">
	      		</form>
              <input type="hidden" id='GruopMessageCount' value="<?php echo count($GroupMessageList); ?>">
              <textarea onkeypress='Hide("ErrMessage");' class="form-control inputfield2" 
              	placeholder="Type here..." id="GruopMessageText" data-emoji-input="unicode" data-emojiable="true"></textarea>
           		<span id="ErrGroupMessage"></span>
           </div>
           <script>
			 	$(function() {
			      window.emojiPicker = new EmojiPicker({
			        emojiable_selector: '[data-emojiable=true]',
			        assetsPath: 'public/Front/Design/emoji/lib/img/',
			        popupButtonClasses: 'fa fa-smile-o'
			      });
			      window.emojiPicker.discover();
			  	});
			</script>
           <?php if(!empty($GroupMessageList)){ ?>
           <ul class="chating">
       			<?php 
       			foreach($GroupMessageList as $ec)
       			{ 
       				?>
       				<?php if($ec->message!=''){ ?>
	              	<li class='sn'>
		                <div class="chating_cont">
		                	<p><?php echo nl2br($ec->message); ?></p>
		                	<span><?php echo $this->Common->TimeElapsedString($ec->updated_at,false); ?></span>
		                </div>
		            </li> 
		            <?php } else {?>
		            <li class='sn'>
		                <div class="chating_cont">
		                	<p>
		                		<a href="<?php echo asset('public/Front/Users/Message/Group/Attachment').'/'.$ec->attachment_temp; ?>" 
		                		target='_blank'>
		                		<i class="fa fa-file"></i> <?php echo $ec->attachment; ?>
		                		</a>
		                	</p>
		                	<span><?php echo $this->Common->TimeElapsedString($ec->updated_at,false); ?></span>
		                </div>
		            </li> 

		            <?php } ?>
		            <?php 
        		} 
        		?>
           </ul>
           <?php } else {?>
           Chat Not Available!
           <?php } ?>
        </div>
		<?php
		exit();
	}
	public function SendGroupMessage(Request $request)
	{
		$Data 			= $request->all();
		$UserID 		= Session::get('UserID');
		$GroupID 		= $Data['GroupID'];
		$MessageText 	= $Data['MessageText'];

		$Detsils['group_id'] 		= $GroupID;
		$Detsils['from'] 			= $UserID;
		$Detsils['message'] 		= $MessageText;
		$Detsils['attachment'] 		= '';

		$Save = $this->MessageModel->SendGroupMessage($Detsils);
		if($Save)
		{
			echo 1;
		}
		else
		{
			echo 0;
		}
		exit();
	}
	public function GetNewGroupMessage(Request $request)
	{
		$Data 			= $request->all();
		$UserID 		= Session::get('UserID');
		$GroupID 		= $Data['GroupID'];
		$GroupMessageList = $this->MessageModel->GetGroupMessageList($GroupID);
		echo count($GroupMessageList);
		exit();
	}

	public function GetGroupMemberChat(Request $request)
	{
		$Data 			= $request->all();
		$UserID 		= Session::get('UserID');
		$GroupID 		= $Data['GroupID'];

		$GetGroupDetails 	= $this->MessageModel->GetGroupDetails($UserID,$GroupID);
		$UserList 			= $GetGroupDetails->members;
		$UserNameList 		= $this->MessageModel->GetUserNameList($UserList);
		$GroupMessageList 	= $this->MessageModel->GetGroupMessageList($GroupID);

		?>
		<h5><?php echo $GetGroupDetails->group_name; ?></h5>
		<span><?php echo $UserNameList; ?></span>
        <div class="ct_box">
           <div class="chat_type">
             
           </div>
           <?php if(!empty($GroupMessageList)){ ?>
           <ul class="chating">
       			<?php 
       			foreach($GroupMessageList as $ec)
       			{ 
       				?>
	              	<li class='sn'>
		                <div class="chating_cont">
		                	<p><?php echo nl2br($ec->message); ?></p>
		                	<span><?php echo $this->Common->TimeElapsedString($ec->updated_at,false); ?></span>
		                </div>
		            </li> 
		            <?php 
        		} 
        		?>
           </ul>
           <?php } else {?>
           Chat Not Available!
           <?php } ?>
        </div>
		<?php
		exit();
	}


	public function SendFileSingleMessage(Request $request)
	{
		$Data 			= $request->all();

		$Details['message_from'] 	= $Data['From'];
		$Details['message_to'] 		= $Data['To'];
		$Details['message'] 		= '';
		$File 		= $request->file('Browser1');
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
			echo 1;
		}
		else
		{
			echo 0;
		}
		exit();
	}
	public function SendFileGroupMessage(Request $request)
	{
		$Data 			= $request->all();
		$UserID 		= Session::get('UserID');
		

		$Details['group_id'] 	= $Data['GroupID'];
		$Details['message'] 	= '';
		$File 					= $request->file('Browser2');
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
			echo 1;
		}
		else
		{
			echo 0;
		}
		exit();
	}
}
?>