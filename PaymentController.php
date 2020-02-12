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
use App\Http\Models\Front\WalletModel;
use App\Http\Models\API\CommonModel;
use App\Http\Models\Front\UserModel;
use Illuminate\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Helpers\Common;
use Stripe;
class PaymentController extends Controller 
{
	public function __construct(Request $request)
	{		
		//$this->PaymentModel 	= new PaymentModel();
		$this->CommonModel 	= new CommonModel();
		$this->WalletModel 	= new WalletModel();
		$this->UserModel 	= new UserModel();
		$this->Common 		= new Common();
	}
	public function AddMoneyInWalletAPI(Request $request)
	{
		$Data = $request->all();
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
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
			$Data['Title']  = 'Add Money In Wallet';
			$Data['UserID'] = $UserID;
			return View('API/AddMoneyInWallet')->with($Data);
		}	
		else
		{			
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
			return response()->json($Response);
		}  
	}

	public function AddMoneyInWalletDetailsAPI(Request $request)
	{
		$UserID = $request->UserID;
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
       		$Amount 		= $request->Amount/100;
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
				return Redirect::route('PaymentSuccess');
			}
			else
	      	{
	      		return Redirect::route('PaymentFailed');
	      	}
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
				return Redirect::route('PaymentSuccess');
			}
			else
	      	{
	      		return Redirect::route('PaymentFailed');
	      	}
       	}
       	else
       	{
       		return Redirect::route('PaymentFailed');
       	}      
	}

	public function PaymentSuccess(Request $request)
	{
		$Arr['Title']  = 'Success';
		return View('API/AddMoneyInWalletSuccess')->with($Arr);
	}
	public function PaymentFailed(Request $request)
	{
		$Arr['Title']  = 'Failed';
       	return View('API/AddMoneyInWalletFailed')->with($Arr);
	}
	public function GetCreditDetailsAPI(Request $request)
	{
		$Data = $request->all();
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$CreditDate 	= $Data['CreditDate'];
		$CreditDetail 	= array();
		$TotalAmount 	= 0.00;
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
		if($CreditDate=='')
		{
			$Response = ['Status' => false,'Message' => 'CreditDate Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$CreditDetails = $this->WalletModel->GetCreditDetails($UserID,$CreditDate);
			$TotalAmount = '0';
			if(!empty($CreditDetails))
			{
				$Sample =array();
				foreach($CreditDetails as $c)
				{
					$Sample['TransactionID'] = $c->transaction_id;
					if($c->status==1)
					{
						$Sample['Status'] = 'Completed';
					} 
					else 
					{ 
						$Sample['Status'] = 'Pending';					
					}
					$Sample['Amount'] = number_format($c->amount,2);
					$Sample['Date'] = date('d-M, Y',strtotime($c->added_at));
					array_push($CreditDetail, $Sample);
					$TotalAmount  = $TotalAmount  + number_format($c->amount,2);
				}
				$Response = ['Status'=>True,'Message'=>'Credit Details','CreditDetail'=>$CreditDetail,'TotalAmount'=>number_format($TotalAmount,2)];
				return response()->json($Response);
			}
			else
			{
				$Sample['TransactionID'] = '';
				$Sample['Status'] = '';
				$Sample['Amount'] = '';
				$Sample['Date']   = '';
				array_push($CreditDetail, $Sample);
				$Response = ['Status'=>True,'Message'=>'Credit Details','CreditDetail'=>$CreditDetail,'TotalAmount'=>number_format($TotalAmount,2)];
				return response()->json($Response);
			} 
		}	
		else
		{			
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
			return response()->json($Response);
		}
		
	}
	public function GetDebitDetailsAPI(Request $request)
	{
		$Data = $request->all();
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$DebitDate 		= $Data['DebitDate'];
		$DebitDetail 	= array();
		$TotalAmount 	= 0.00;
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
		if($DebitDate=='')
		{
			$Response = ['Status' => false,'Message' => 'DebitDate Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$CreditDetails = $this->WalletModel->GetDebitDetails($UserID,$DebitDate);
			$TotalAmount = '0';
			if(!empty($CreditDetails))
			{
				$Sample =array();
				foreach($CreditDetails as $c)
				{
					if($c->status==1){ 
					$TotalAmount = $TotalAmount+$c->amount; 
					}
					$Response = json_decode($c->response);
					$employee_id = $Response->employee_id;
					$JobId 	= $Response->job_id;
					$Job 	= DB::table('jobs')->select('job_title')->where('id',$JobId)->first();
					$Emp 	= DB::table('profile')->select('first_name','last_name')->where('id',$employee_id)->first();

					$Sample['TransactionID'] = $Emp->transaction_id;
					$Sample['EmployeeName'] = $Emp->first_name.' '.$Emp->last_name;
					$Sample['JobTitle'] 	= $Job->job_title;
					$Sample['Amount'] 		= number_format($c->amount,2);
					$Sample['Date']   		= date('d-m-Y',strtotime($c->added_at));

					array_push($DebitDetail, $Sample);
				}
				$Response = ['Status'=>True,'Message'=>'Debit Details','DebitDetail'=>$DebitDetail,'TotalAmount'=>number_format($TotalAmount,2)];
				return response()->json($Response);
			}
			else
			{
				$Sample['TransactionID'] = '';
				$Sample['EmployeeName'] = '';
				$Sample['JobTitle'] 	= '';
				$Sample['Amount'] 		= '';
				$Sample['Date']   		= '';
				array_push($DebitDetail, $Sample);
				$Response = ['Status'=>True,'Message'=>'Debit Details','DebitDetail'=>$DebitDetail,'TotalAmount'=>number_format($TotalAmount,2)];
				return response()->json($Response);
			} 
		}	
		else
		{			
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
			return response()->json($Response);
		}
		
	}

	public function GetRevertDetailsAPI(Request $request)
	{
		$Data = $request->all();
		$UserID 		= $Data['UserID'];	
		$AccessToken 	= $Data['AccessToken'];
		$RevertDate 	= $Data['RevertDate'];
		$RevertDetail 	= array();
		$TotalAmount 	= 0.00;
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
		if($RevertDate=='')
		{
			$Response = ['Status' => false,'Message' => 'RevertDate Parameter Missing.'];
			return response()->json($Response);
		}
		$CheckLoginDetails  = $this->CommonModel->CheckLoginDetails($UserID, $AccessToken);
		if(!empty($CheckLoginDetails))
		{
			$CreditDetails = $this->WalletModel->GetRevertDetailsList($UserID,$RevertDate);
			$TotalAmount = '0';
			if(!empty($CreditDetails))
			{
				$Sample =array();
				foreach($CreditDetails as $c)
				{
					if($c->status==1){ 
					$TotalAmount = $TotalAmount+$c->amount; 
					}
					$Response = json_decode($c->response);
					$employee_id = $Response->employee_id;
					$JobId 	= $Response->job_id;
					$Job 	= DB::table('jobs')->select('job_title')->where('id',$JobId)->first();
					$Emp 	= DB::table('profile')->select('first_name','last_name')->where('id',$employee_id)->first();

					$Sample['EmployeeName'] = $Emp->first_name.' '.$Emp->last_name;
					$Sample['JobTitle'] 	= $Job->job_title;
					$Sample['Amount'] 		= number_format($c->amount,2);
					$Sample['Date']   		= date('d-m-Y',strtotime($c->added_at));

					array_push($RevertDetail, $Sample);
				}
				$Response = ['Status'=>True,'Message'=>'Credit Details','RevertDetail'=>$RevertDetail,'TotalAmount'=>number_format($TotalAmount,2)];
				return response()->json($Response);
			}
			else
			{
				$Sample['EmployeeName'] = '';
				$Sample['JobTitle'] 	= '';
				$Sample['Amount'] 		= '';
				$Sample['Date']   		= '';
				array_push($RevertDetail, $Sample);
				$Response = ['Status'=>True,'Message'=>'Credit Details','RevertDetail'=>$RevertDetail,'TotalAmount'=>number_format($TotalAmount,2)];
				return response()->json($Response);
			} 
		}	
		else
		{			
			$Response = ['Status'=>false,'Message'=>'Invalid UserID OR Access-Token'];
			return response()->json($Response);
		}
		
	}
}