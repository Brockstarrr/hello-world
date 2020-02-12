<?php
namespace App\Helpers;
use Illuminate\Database\Eloquent\Model;
use DateTime;
use Date;
use DB;
class Common {
	
	public static function AlertErrorMsg($ErrorType,$Msg) {
		if($ErrorType == 'Success'){
			$AlertErrorMsg = '<div class="alert alert-success alert-dismissible" role="alert">
				    							<button type="button" class="close" data-dismiss="alert">×</button>
				    							<div class="alert-icon"><i class="icon-check"></i></div>
				    							<div class="alert-message">
				      							<span>'.$Msg.'</span>
				    							</div>
                  			</div>';
		}elseif($ErrorType == 'Danger'){
			$AlertErrorMsg = '<div class="alert alert-danger alert-dismissible" role="alert">
				    							<button type="button" class="close" data-dismiss="alert">×</button>
				    							<div class="alert-icon"><i class="icon-close"></i></div>
				    							<div class="alert-message">
				      							<span>'.$Msg.'</span>
				    							</div>
                  			</div>';

		}elseif($ErrorType == 'Info'){
			$AlertErrorMsg = '<div class="alert alert-info alert-dismissible" role="alert">
				    							<button type="button" class="close" data-dismiss="alert">×</button>
				    							<div class="alert-icon"><i class="icon-info"></i></div>
				    							<div class="alert-message">
				      							<span>'.$Msg.'</span>
				    							</div>
                  			</div>';
		}elseif($ErrorType == 'Warning'){
			$AlertErrorMsg = '<div class="alert alert-warning alert-dismissible" role="alert">
				    							<button type="button" class="close" data-dismiss="alert">×</button>
				    							<div class="alert-icon"><i class="icon-exclamation"></i></div>
				    							<div class="alert-message">
				      							<span>'.$Msg.'</span>
				    							</div>
                  			</div>';
		}else{
			$AlertErrorMsg = '';
		}
		return $AlertErrorMsg;
	}

	public static function Pagination($numofrecords, $count, $page){
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
			$msg .= "<ul class='pagination pagination-outline-secondary'>";
			if ($first_btn && $cur_page > 1) {
				//$msg .= "<li p='1' class='page-item'><a onclick='Pagination(1)' class='page-link' href='javascript:void(0)'>First</a>First</li>";
			} else if ($first_btn) {
				//$msg .= "<li p='1' class='page-item inactive'><a class='page-link' href='javascript:void(0)'>First</a></li>";
			}
			if ($previous_btn && $cur_page > 1) {
				$pre = $cur_page - 1;
				$msg .= "<li p='$pre' class='page-item'><a onclick='Pagination($pre)' class='page-link' href='javascript:void(0)'>Previous</a></li>";
			} else if ($previous_btn) {
				$msg .= "<li class='page-item inactive'><a class='page-link' href='javascript:void(0)'>Previous</a></li>";
			}
			for ($i = $start_loop; $i <= $end_loop; $i++) {
				if ($cur_page == $i)
	    		$msg .= "<li p='$i' class='page-item active'><a class='page-link' href='javascript:void(0)'>{$i}</a></li>";
				else
	    		$msg .= "<li p='$i' class='page-item'><a class='page-link' onclick='Pagination($i)' href='javascript:void(0)'>{$i}</a></li>";
				}
			    if ($next_btn && $cur_page < $no_of_paginations) {
			      $nex = $cur_page + 1;
			      $msg .= "<li p='$nex' class='page-item'><a onclick='Pagination($nex)' class='page-link' href='javascript:void(0)'>Next</a></li>";
			    } else if ($next_btn) {
			      $msg .= "<li class='page-item inactive'><a class='page-link' href='javascript:void(0)'>Next</a></li>";
			    }

				if ($last_btn && $cur_page < $no_of_paginations) {
					//$msg .= "<li p='$no_of_paginations' class='page-item active' onclick='Pagination($no_of_paginations)'>Last</li>";
				} else if ($last_btn) {
					//$msg .= "<li p='$no_of_paginations' class='page-item inactive'>Last</li>";
				}
				//$goto = "<input type='text' style='display:none;' class='goto' size='1' style='margin-top:-1px;margin-left:60px;'/><input type='button' id='go_btn' class='go_button' value='Go' style='display:none;'/>";
				/*$total_string = "<span class='total' a='$no_of_paginations'>(page<b>" . $cur_page . "</b> of <b>$no_of_paginations</b> )</span>";
				$msg = $msg . "</ul>" . $goto . $total_string . "</div>";*/
				//$msg = $msg . "</ul>" . $goto;
				$msg = $msg . "</ul>";
				return $msg;
		}
		else
		{
			return '<div class="col-md-12 text-center" style="color:red"><strong>No Record Found</strong></div>';
		}
	}
	public static function GenerateRandomId($length)
	{
    $id_length = $length;
    $alfa = "abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz";
    $token = "";
    for($i = 1; $i < $id_length; $i ++) {
      @$token .= $alfa[rand(1, strlen($alfa))];
    }
    return $token;
	}
	public static function DateDiff($From, $To){
		$diff = abs($From - $To);
    $years = floor($diff / (365*60*60*24));
    $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
    $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
    $Still = '';
    if($years!=0) $Still.=$years.' Years ';
    if($months!=0) $Still.=$months.' Months ';
    if($days!=0) $Still.=$days.' Days';
    return $Still;
	}
	public static function DateDiff2($From, $To)
	{
		$diff = abs(strtotime($From) - strtotime($To));
	    $years = floor($diff / (365*60*60*24));
	    $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
	    $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
	    $Still = '';
	    if($years!=0) $Still.=$years.' Years ';
	    if($months!=0) $Still.=$months.' Months ';
	    return $Still;
	}
	public static function GenerateOTP($length)
  {
    $characters = '0123456789876543210123456789876543210';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return "1234";
  }

  public static function limitTextWords($content = false, $limit = false, $stripTags = false, $ellipsis = false) 
	{
	    if ($content && $limit) {
	        $content = ($stripTags ? strip_tags($content) : $content);
	        $content = explode(' ', $content, $limit+1);
	        array_pop($content);
	        if ($ellipsis) {
	            array_push($content, '');
	        }
	        $content = implode(' ', $content);
	    }
	    return $content;
	}
	public function TimeElapsedString($datetime, $full = false) 
	{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    $string = array(
	        'y' => 'year',
	        'm' => 'month',
	        'w' => 'week',
	        'd' => 'day',
	        'h' => 'hour',
	        'i' => 'minute',
	        's' => 'second',
	    	);
	  foreach ($string as $k => &$v) {
      if ($diff->$k) {
        $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
      } else {
        unset($string[$k]);
      }
	  }
    if (!$full) $string = array_slice($string, 0, 1);
	  return $string ? implode(', ', $string) . ' ago' : 'just now';
	}
	public static function DynamicPagination($numofrecords, $count, $page, $OnClick){
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
			$msg .= "<ul class='pagination pagination-outline-secondary'>";
			if ($first_btn && $cur_page > 1) {
				//$msg .= "<li class='page-item'><a onclick='$OnClick(1)' class='page-link' href='javascript:void(0)'>First</a></li>";
			} else if ($first_btn) {
				//$msg .= "<li class='page-item inactive'><a class='page-link' href='javascript:void(0)'>First</a></li>";
			}
			if ($previous_btn && $cur_page > 1) {
				$pre = $cur_page - 1;
				$msg .= "<li p='$pre' class='page-item'><a onclick='$OnClick($pre)' class='page-link' href='javascript:void(0)'>Previous</a></li>";
			} else if ($previous_btn) {
				$msg .= "<li class='page-item inactive'><a class='page-link' href='javascript:void(0)'>Previous</a></li>";
			}
			for ($i = $start_loop; $i <= $end_loop; $i++) {
				if ($cur_page == $i)
	    		$msg .= "<li p='$i' class='page-item active'><a class='page-link' href='javascript:void(0)'>{$i}</a></li>";
				else
	    		$msg .= "<li p='$i' class='page-item'><a class='page-link' onclick='$OnClick($i)' href='javascript:void(0)'>{$i}</a></li>";
				}
			    if ($next_btn && $cur_page < $no_of_paginations) {
			      $nex = $cur_page + 1;
			      $msg .= "<li class='page-item'><a onclick='$OnClick($nex)' class='page-link' href='javascript:void(0)'>Next</a></li>";
			    } else if ($next_btn) {
			      $msg .= "<li class='page-item inactive'><a class='page-link' href='javascript:void(0)'>Next</a></li>";
			    }

				if ($last_btn && $cur_page < $no_of_paginations) {
					//$msg .= "<li p='$no_of_paginations' class='page-item'><a class='page-link' href='javascript:void(0)' onclick='$OnClick($no_of_paginations)'>Last</a></li>";
				} else if ($last_btn) {
					//$msg .= "<li class='page-item inactive'><a class='page-link' href='javascript:void(0)'>Last</a></li>";
				}
				$msg = $msg . "</ul>";
				return $msg;
		}
		else
		{
			return '<div class="col-md-12 text-center" style="color:red"><strong>No Record Found</strong></div>';
		}
	}
	public static function SocialMeadiaLink()
	{
		$SocilaLink = DB::table('social_media')
                          ->select('*')
                          ->get()
                          ->first();
        ?>
        <ul>
			<li><a target='_blank' href="<?php echo $SocilaLink->facebook; ?>"><i class="fa fa-facebook"></i></a></li>
			<li><a target='_blank' href="<?php echo $SocilaLink->twitter; ?>"><i class="fa fa-twitter"></i></a></li>
			<li><a target='_blank' href="<?php echo $SocilaLink->instagram; ?>"><i class="fa fa-instagram"></i></a></li>
			<li><a target='_blank' href="<?php echo $SocilaLink->linkedin; ?>"><i class="fa fa-linkedin	"></i></a></li>
			<li><a target='_blank' href="<?php echo $SocilaLink->youtube; ?>"><i class="fa fa-youtube"></i></a></li>
			<li><a target='_blank' href="<?php echo $SocilaLink->pinterest; ?>"><i class="fa fa-pinterest"></i></a></li>
		</ul>
        <?php
	}
	public static function GetTotalHours($FromDate,$ToDate)
	{
		$date1 = new DateTime($FromDate);
		$date2 = new DateTime($ToDate);
		$diff 	= $date2->diff($date1);
		$Hours 	= $diff->h;
		$Hours 	= $Hours + ($diff->days*24);
		return  $Hours;
	}
	public static function DistanceCalculate($lat1, $lon1, $lat2, $lon2, $unit){
	  if (($lat1 == $lat2) && ($lon1 == $lon2)) {
	    return 0;
	  }
	  else {
	    $theta = $lon1 - $lon2;
	    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	    $dist = acos($dist);
	    $dist = rad2deg($dist);
	    $miles = $dist * 60 * 1.1515;
	    $unit = strtoupper($unit);

	    if ($unit == "K") {
	      return ($miles * 1.609344);
	    } else if ($unit == "N") {
	      return ($miles * 0.8684);
	    } else {
	      return $miles;
	    }
	  }
	}

	public function AddNotification($Details)
	{
		$Save = DB::table('user_notifications')->insert($Details);
		if($Save)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public static function ShowNotification($UserID)
	{
		$Notification = '';
		$ShowNotification = DB::table('user_notifications')
						->where('user_id',$UserID)
						->where('read_unread','1')
						->orderBy('date','DESC')
						->get()
						->toArray();
		
					$class = "";
					if(!empty($ShowNotification))
					{
								$class = "active";
					}	
				 $Notification.= '<div class="noti_bell '.$class.'">
												<a href="javascript:void(0);" class="notification">
													<span class="noti_dot"></span>
													<span>
														<img src="'.asset("public/Front/Design/img/notification.png").'" alt="" />
													</span>				
												</a>
												<div class="noti_bell_cont">
													<ul>';
				if(!empty($ShowNotification))
				{
							foreach($ShowNotification as $n)
							{
									$Notification.= '<li>
																		<a href="javascript:void(0);" onclick="GetOpenNotification('.$n->id.')" title="'.$n->text.'"></a>
																		<strong>'.$n->text.'</strong>
																		<span>'.date('d, M-Y',strtotime($n->date)).'</span>
																	</li>';
							}	
				}
				$Notification.='</ul>
														</div>
														</div>';
		echo $Notification;
	}

}
