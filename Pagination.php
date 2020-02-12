<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Session;
class Pagination extends Controller
{   
	public function Links($numofrecords, $count, $page)
	{
		$per_page = $numofrecords;
		$previous_btn = true;
		$next_btn = true;
		$first_btn = true;
		$last_btn = true;
		$start = $page * $per_page;
		$cur_page = $page;
		$msg = "";
		$no_of_paginations = ceil($count / $per_page);
		if($count>0)
		{
			if ($cur_page >= 7) {
				$start_loop = $cur_page - 3;
				if ($no_of_paginations > $cur_page + 3)
				$end_loop = $cur_page + 3;
				else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {
	    			$start_loop = $no_of_paginations - 6;
	    			$end_loop = $no_of_paginations;
				} else {
	    			$end_loop = $no_of_paginations;
				}
			} else {
				$start_loop = 1;
				if ($no_of_paginations > 7)
	    			$end_loop = 7;
				else
	    			$end_loop = $no_of_paginations;
			}
			$msg .= "<div class='gig-pagination'><ul class='pagination'>";

			if ($previous_btn && $cur_page > 1) {
				$pre = $cur_page - 1;
				$msg .= "<li p='$pre' class='active' >
						<a class='page-link' href='javascript:void(0);' onclick='Pagination($pre)'>Previous</a>
						</li>";
			} else if ($previous_btn) {
				$msg .= "<li class='inactive'>
						<a class='page-link' href='javascript:void(0);'>Previous</a>
						</li>";
			}
			for ($i = $start_loop; $i <= $end_loop; $i++) {
				if ($cur_page == $i)
	    			$msg .= "<li p='$i' class='active current-page page-item'>
	    						<a class='page-link' href='javascript:void(0);'>{$i}</a>
	    					</li>";
				else
	    			$msg .= "<li p='$i' class='page-item'>
	    						<a class='page-link' href='javascript:void(0);'  onclick='Pagination($i)'>{$i}</a>
	    					</li>";
				}
			    if ($next_btn && $cur_page < $no_of_paginations) {
			        $nex = $cur_page + 1;
			        $msg .= "<li p='$nex' class='page-item' >
			        			<a class='page-link' href='javascript:void(0);'  onclick='Pagination($nex)'>Next</a>
			        		</li>";
			    } else if ($next_btn) {
			        $msg .= "<li class=' page-item'>
			        			<a class='page-link' href='javascript:void(0);'>Next</a>
			        		</li>";
			    }
			$goto = "<input type='text' style='display:none;' class='goto' size='1' style='margin-top:-1px;margin-left:60px;'/><input type='button' id='go_btn' class='go_button' value='Go' style='display:none;'/>";
			$msg = $msg . "</ul>" . $goto. "</div>";
				return $msg;
		}
		else
		{
			return '<div class="col-md-12 text-center" style="color:red"><strong>No Result Found</strong></div>';
		}
	}

	public function Links2($numofrecords, $count, $page)

	{

		$per_page = $numofrecords;

		$previous_btn = true;

		$next_btn = true;

		$first_btn = true;

		$last_btn = true;

		$start = $page * $per_page;

		$cur_page = $page;

		$msg = "";



		$no_of_paginations = ceil($count / $per_page);



		if($count>0)

		{

			if ($cur_page >= 7) {

				$start_loop = $cur_page - 3;

				if ($no_of_paginations > $cur_page + 3)

				$end_loop = $cur_page + 3;

				else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {

	    			$start_loop = $no_of_paginations - 6;

	    			$end_loop = $no_of_paginations;

				} else {

	    			$end_loop = $no_of_paginations;

				}

			} else {

				$start_loop = 1;

				if ($no_of_paginations > 7)

	    			$end_loop = 7;

				else

	    			$end_loop = $no_of_paginations;

			}

			$msg .= "<div class='pagination page-in'><ul class='list-inline'>";

			/*if ($first_btn && $cur_page > 1) {

					$msg .= "<li p='1' class='active' onclick='Pagination2(1)'>First</li>";

			} else if ($first_btn) {

					$msg .= "<li p='1' class='inactive'>First</li>";

			}*/

			if ($previous_btn && $cur_page > 1) {

				$pre = $cur_page - 1;

				$msg .= "<li p='$pre' class='active' onclick='Pagination2($pre)'>«</li>";

			} else if ($previous_btn) {

				$msg .= "<li class='inactive'>«</li>";

			}

			for ($i = $start_loop; $i <= $end_loop; $i++) {

				if ($cur_page == $i)

	    			$msg .= "<li p='$i' class='active current-page'>{$i}</li>";

				else

	    			$msg .= "<li p='$i' class='active' onclick='Pagination2($i)'>{$i}</li>";

				}

			    if ($next_btn && $cur_page < $no_of_paginations) {

			        $nex = $cur_page + 1;

			        $msg .= "<li p='$nex' class='active' onclick='Pagination2($nex)'>»</li>";

			    } else if ($next_btn) {

			        $msg .= "<li class='inactive'>»</li>";

			    }



				/*if ($last_btn && $cur_page < $no_of_paginations) {

					$msg .= "<li p='$no_of_paginations' class='active' onclick='Pagination2($no_of_paginations)'>Last</li>";

				} else if ($last_btn) {

					$msg .= "<li p='$no_of_paginations' class='inactive'>Last</li>";

				}*/

				$goto = "<input type='text' style='display:none;' class='goto' size='1' style='margin-top:-1px;margin-left:60px;'/><input type='button' id='go_btn' class='go_button' value='Go' style='display:none;'/>";

				/*$total_string = "<span class='total' a='$no_of_paginations'>(page<b>" . $cur_page . "</b> of <b>$no_of_paginations</b> )</span>";

				$msg = $msg . "</ul>" . $goto . $total_string . "</div>";*/

				$msg = $msg . "</ul>" . $goto. "</div>";

				return $msg;

		}

		else

		{

			return '<div class="col-md-12 text-center" style="color:red"><strong>No Record Found</strong></div>';

		}

	}



	public function FrontLinks($numofrecords, $count, $page)

	{

		$per_page = $numofrecords;

		$previous_btn = true;

		$next_btn = true;

		$first_btn = true;

		$last_btn = true;

		$start = $page * $per_page;

		$cur_page = $page;

		$msg = "";



		$no_of_paginations = ceil($count / $per_page);



		if($count>0)

		{

			if ($cur_page >= 7) {

				$start_loop = $cur_page - 3;

				if ($no_of_paginations > $cur_page + 3)

				$end_loop = $cur_page + 3;

				else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {

	    			$start_loop = $no_of_paginations - 6;

	    			$end_loop = $no_of_paginations;

				} else {

	    			$end_loop = $no_of_paginations;

				}

			} else {

				$start_loop = 1;

				if ($no_of_paginations > 7)

	    			$end_loop = 7;

				else

	    			$end_loop = $no_of_paginations;

			}

			$msg .= "<div class='pagination page-in'><ul class='list-inline'>";

			/*if ($first_btn && $cur_page > 1) {

					$msg .= "<li p='1' class='active' onclick='Pagination(1)'>First</li>";

			} else if ($first_btn) {

					$msg .= "<li p='1' class='inactive'>First</li>";

			}*/

			if ($previous_btn && $cur_page > 1) {

				$pre = $cur_page - 1;

				$msg .= "<li p='$pre' class='active' onclick='Pagination($pre)'>«</li>";

			} else if ($previous_btn) {

				$msg .= "<li class='inactive'>«</li>";

			}

			for ($i = $start_loop; $i <= $end_loop; $i++) {

				if ($cur_page == $i)

	    			$msg .= "<li p='$i' class='active current-page'>{$i}</li>";

				else

	    			$msg .= "<li p='$i' class='active' onclick='Pagination($i)'>{$i}</li>";

				}

			    if ($next_btn && $cur_page < $no_of_paginations) {

			        $nex = $cur_page + 1;

			        $msg .= "<li p='$nex' class='active' onclick='Pagination($nex)'>»</li>";

			    } else if ($next_btn) {

			        $msg .= "<li class='inactive'>»</li>";

			    }



				/*if ($last_btn && $cur_page < $no_of_paginations) {

					$msg .= "<li p='$no_of_paginations' class='active' onclick='Pagination($no_of_paginations)'>Last</li>";

				} else if ($last_btn) {

					$msg .= "<li p='$no_of_paginations' class='inactive'>Last</li>";

				}*/

				$goto = "<input type='text' style='display:none;' class='goto' size='1' style='margin-top:-1px;margin-left:60px;'/><input type='button' id='go_btn' class='go_button' value='Go' style='display:none;'/>";

				/*$total_string = "<span class='total' a='$no_of_paginations'>(page<b>" . $cur_page . "</b> of <b>$no_of_paginations</b> )</span>";

				$msg = $msg . "</ul>" . $goto . $total_string . "</div>";*/

				$msg = $msg . "</ul>" . $goto. "</div>";

				return $msg;

		}

		else

		{

			return '<div class="col-md-12 text-center" style="color:red"><strong>No Record Found</strong></div>';

		}

	}



	function SendMail($to_email, $from_email, $Cc, $Bcc, $subject, $message) { 

    $New_Line = "\n";

    $headers = "MIME-Version: 1.0" .$New_Line;

    $headers .= "Content-type: text/html; charset=iso-8859-1" .$New_Line;

    $headers .= "Content-Transfer-Encode: 7bit " .$New_Line;

    $headers .= "From: $from_email ".$New_Line;

    if(!empty($Cc)) {

      $headers .= "Cc: $Cc" .$New_Line;

    }

    if(!empty($Bcc)) {

      $headers .= "Bcc: $Bcc " .$New_Line;

    }

    $headers .= "X-Mailer: PHP " .$New_Line; // mailer

    $headers .= "Return-Path: < $to_email > " .$New_Line;  // Return path for errors        

    $mail_sent = mail($to_email, $subject, $message, $headers);

    return $mail_sent;

	}



	public function GenerateRandomId($length)

	{

    $id_length = $length;

    $alfa = "abcdefghijklmnpqrstuvwxyz123456789ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz1234567890";

    $token = "";

    for($i = 1; $i < $id_length; $i ++) {

      @$token .= $alfa[rand(1, strlen($alfa))];

    }

    return $token;

	}

	public function GenerateRandomCode($length)

	{

    $id_length = $length;

    $alfa = "123456789ABCDEFGHIJKLMNPQRSTUVWXYZ1234567890ABCDEFGHIJKLMNPQRSTUVWXYZ";

    $token = "";

    for($i = 1; $i < $id_length; $i ++) {

      @$token .= $alfa[rand(1, strlen($alfa))];

    }

    return $token;

	}



}

?>