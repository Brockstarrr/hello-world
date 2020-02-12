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
use Excel;
use PDF;
use Illuminate\Http\Request;
use App\Http\Models\Front\WalletModel;
use App\Http\Models\Front\UserModel;
use App\Http\Models\Front\ViewJobModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;
use Stripe;
class WalletController extends Controller 
{
	public function __construct(Request $request)
	{		
		$this->WalletModel 	= new WalletModel();
		$this->ViewJobModel 	= new ViewJobModel();
		$this->UserModel 	= new UserModel();
		$this->Common 		= new Common();
	}

	public function MyWallet()
	{
		$UserID 				= Session::get('UserID');
		$Data['Title'] 			= 'My Wallet';
		$Data['Menu'] 			= 'Wallet';
		
		return View('Front/Pages/User/Wallet')->with($Data);
	}
	public function AddMoneyInWallet()
	{
		$UserID 				= Session::get('UserID');
		$Data['Title'] 			= 'Add Money In Wallet';
		$Data['Menu'] 			= 'Wallet';
		
		return View('Front/Pages/User/AddMoneyInWallet')->with($Data);
	}
	public function AddMoneyInWalletDetails(Request $request)
	{
		$UserID 				= Session::get('UserID');
		Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $Data = Stripe\Charge::create ([
                "amount" => $request->Amount*100,
                "currency" => "usd",
                "source" => $request->stripeToken,
                "description" => "Add Amount In Gig-Assist" 
        ]);
       	$Details = array();
       	if($Data['status']=='succeeded' && $Data['captured']=='1')
       	{
       		$Amount 		= $Data['amount']/100;
       		$Balance 		= 0;
       		$BalanceAmount 	= $this->WalletModel->GetBalance($UserID);
       		$Balance 		= $BalanceAmount + $Amount;

       		$Details['profile_id'] 			= $UserID;
       		$Details['transaction_type'] 	= 1;
       		$Details['transaction_id'] 		= $Data['id'];
       		$Details['amount'] 				= $Amount;
       		$Details['balance'] 			= $Balance;
       		$Details['added_at'] 			= date('Y-m-d H:i:s A',time());
       		$Details['receipt_url'] 		= $Data['receipt_url'];
       		$Details['status'] 				= 1;
       		$Details['response'] 			= $Data;

       		$Save = $this->WalletModel->SaveAmountDetails($Details);
       		if($Save)
			{
				Session::flash('message', 'Amount Added Successfully.'); 
				Session::flash('alert-class', 'alert-success'); 
			}
			else
	      	{
				Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
				Session::flash('alert-class', 'alert-danger');
	      	}
	      	return Redirect::route('AddMoneyInWallet');
       	}
       	else if($Data['status']!='succeeded' && $Data['captured']=='1')
       	{
       		$Details['profile_id'] 	= $UserID;
       		$Details['transaction_id'] 	= $Data['id'];
       		$Details['amount'] 		= $Data['amount']/100;
       		$Details['added_at'] 	= date('Y-m-d H:i:s A',time());
       		$Details['receipt_url'] = $Data['receipt_url'];
       		$Details['status'] 		= 0;
       		$Details['response'] 	= $Data;

       		$Save = $this->WalletModel->SaveAmountDetails($Details);
       		if($Save)
			{
				Session::flash('message', 'Amount Added Successfully. Wait For Captured'); 
				Session::flash('alert-class', 'alert-success'); 
			}
			else
	      	{
				Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
				Session::flash('alert-class', 'alert-danger');
	      	}
	      	return Redirect::route('AddMoneyInWallet');
       	}
       	else
       	{
       		Session::flash('message', 'OOPS! Something Wrong Please Try Again.'); 
			Session::flash('alert-class', 'alert-danger');
			return Redirect::route('AddMoneyInWallet');
       	}       
	}


	public function GetCreditDetails(Request $request)
	{
		$Data 	= $request->all();
		$UserID = Session::get('UserID');
		$CreditDate = $Data['CreditDate'];
		$CreditDetails = $this->WalletModel->GetCreditDetails($UserID,$CreditDate);
		$TotalAmount = '0';
		?>
			<?php if(!empty($CreditDetails)){ ?>
				<?php foreach($CreditDetails as $c){ 
					if($c->status==1){ 
					$TotalAmount = $TotalAmount+$c->amount; 
					} 
					?>
					<tr>
						<td>
							<?php 
							if($c->transaction_id=='')
							{ 
								$JobID = $c->job_id;
								$GetJobDetails = $this->ViewJobModel->GetJobDetail($JobID);

								?> 
								Earn From Job (<?php if(!empty($GetJobDetails)){ echo $GetJobDetails->job_title; }?>)
								<?php 
							}
							else 
							{ 
								?> 
								Recharge 
								<?php 
							} 
							?> 
						</td>
						<td><?php echo $c->transaction_id; ?></td>						
						<td><?php echo date('d-M, Y',strtotime($c->added_at)); ?></td>
						<td>$<?php echo number_format($c->amount,2); ?></td>
					</tr>
				<?php } ?>
				<tr>
					<td colspan="3">Total Balance</td>
					<td colspan="1">$<?php echo number_format($TotalAmount,2); ?></td>
				</tr>
			<?php } else { ?>
			<tr>
				<td colspan="4">No Record Found.</td>
			</tr>
			<tr>
				<td colspan="3">Total Balance</td>
				<td colspan="1">$<?php echo number_format($TotalAmount,2); ?></td>
			</tr>
			<?php } ?>
		<?php
		exit();
	}

	public function GetDebitDetailsList(Request $request){
		$Data 	= $request->all();
		$UserID = Session::get('UserID');
		$DebitDate = $Data['DebitDate'];
		$CreditDetails = $this->WalletModel->GetDebitDetails($UserID,$DebitDate);
		$TotalAmount = '0';
		$Balance = '0';
		
		if(!empty($CreditDetails)){
			$Balance = $this->WalletModel->GetBalance($UserID);  
		 	foreach($CreditDetails as $c){ 
				if($c->status==1){ 
					$TotalAmount = $TotalAmount+$c->amount; 
				}

				$Response = json_decode($c->response);
				$employee_id = $Response->employee_id;
				$JobId = $Response->job_id;
				$Job = DB::table('jobs')->select('job_title')->where('id',$JobId)->first();
				$Emp=DB::table('profile')->select('first_name','last_name')->where('id',$employee_id)->first();
				?>
				<tr>
					<td><?=$Emp->first_name.' '.$Emp->last_name?></td>
					<td><?=$Job->job_title?></td>
					<td><?=date('d-m-Y',strtotime($c->added_at))?></td>
					<td>
						<?php
						if($c->transaction_type=='2')
						{
							echo 'Transfered';
						}
						else if($c->transaction_type=='3')
						{
							echo 'Escrow';
						}
						?>
					</td>
					<td>$<?=number_format($c->amount,2)?></td>
				</tr>
				<?php } ?>
				<tr>
					<td colspan="4">Remaining  Balance</td>
					<td colspan="1">$<?php echo number_format($Balance,2); ?></td>
				</tr>
			<?php } else { ?>
			<tr>
				<td colspan="4">No Record Found.</td>
			</tr>
			<tr>
				<td colspan="4">Remaining Balance</td>
				<td colspan="1">$<?php echo number_format($Balance,2); ?></td>
			</tr>
			<?php } ?>
		<?php
		exit();
	}
	public function GetRevertDetailsList(Request $request){
		$Data 	= $request->all();
		$UserID = Session::get('UserID');
		$RevertDate = $Data['RevertDate'];
		$CreditDetails = $this->WalletModel->GetRevertDetailsList($UserID,$RevertDate);
		$TotalAmount = '0';
		
		if(!empty($CreditDetails)){ 
		 	foreach($CreditDetails as $c){ 
				if($c->status==1){ 
					$TotalAmount = $TotalAmount+$c->amount; 
				}
				$Response = json_decode($c->response);
				$employee_id = $Response->employee_id;
				$JobId = $Response->job_id;
				$Job = DB::table('jobs')->select('job_title')->where('id',$JobId)->first();
				$Emp=DB::table('profile')->select('first_name','last_name')->where('id',$employee_id)->first();
				?>
				<tr>
					<td><?=$Emp->first_name.' '.$Emp->last_name?></td>
					<td><?=$Job->job_title?></td>
					<td>$<?=number_format($c->amount,2)?></td>
					<td><?=date('d-m-Y',strtotime($c->added_at))?></td>
				</tr>
				<?php } ?>
				<tr>
					<td colspan="2">Total Amount</td>
					<td colspan="2">$<?php echo number_format($TotalAmount,2); ?></td>
				</tr>
			<?php } else { ?>
			<tr>
				<td colspan="4">No Record Found.</td>
			</tr>
			<tr>
				<td colspan="2">Total Added</td>
				<td colspan="2">$<?php echo number_format($TotalAmount,2); ?></td>
			</tr>
			<?php } ?>
		<?php
		exit();
	}

	public function DownloadTransaction()
	{
		$UserID = Session::get('UserID');
		$FileName = "DownloadTransaction_".date('Y-m-d').'.csv';
        header("Content-type: text/csv");
	    header("Content-Disposition: attachment; filename=".$FileName."");
	    header("Pragma: no-cache");
	    header("Expires: 0");
	    $AllTransaction = $this->WalletModel->GetAllTransactionDetails($UserID);
	    $columns = array('Sr.No.', 'TransactionID', 'TransactionType', 'Amount', 'Balance', 'Date-Time');
		$file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        if(!empty($AllTransaction))
        {
	        $i=1;
	        foreach($AllTransaction as $at) 
	        {
	        	$TansactionType='';
	        	if($at->transaction_type=='1')
	        	{
	        		$TansactionType='Credit';
	        	}
	        	else if($at->transaction_type=='2')
	        	{
	        		$TansactionType='Debit';
	        	}
	        	else if($at->transaction_type=='3')
	        	{
	        		$TansactionType='Escrow';
	        	}
	        	else if($at->transaction_type=='4')
	        	{
	        		$TansactionType='Redeem';
	        	}
	        	else if($at->transaction_type=='5')
	        	{
	        		$TansactionType='Revert';
	        	}

	            fputcsv($file, array($i,$at->transaction_id,$TansactionType,$at->amount,$at->balance,$at->added_at));
	            $i++;
	        }
    	}
        fclose($file);
        exit();
	}
}