<?php
namespace App\Helpers;
use DateTime;
use Date;
use DB;
class Notifiction {
	private $title;
  private $message;
  private $image;
  private $data;
  private $is_background;

	public function SendNotifiction($fcm_id,$title,$message){
		// optional payload
		$payload = array();
		$payload['team'] = 'India';
		$payload['score'] = '5.6';
		// push type - single user / topic
		$push_type = 'individual';
		// whether to include to image or not
		$include_image = FALSE;
		$this->setTitle($title);
		$this->setMessage($message);
		if ($include_image) {
		  $this->setImage('');
		} else {
		  $this->setImage('');
		}
		$this->setIsBackground(FALSE);
		$this->setPayload($payload);
		$json = '';
		$response = '';
		if ($push_type == 'topic') {
		  $json = $this->getPush();
		  $response = $this->sendToTopic('global', $json);
		} else if ($push_type == 'individual') {
		  $json = $this->getPush();
			$regId = $fcm_id;
			$response = $this->send($regId, $json);	
		}
		return $response;
	}

	/*Push*/
	public function setTitle($title) {
    $this->title = $title;
  }
  public function setMessage($message) {
    $this->message = $message;
  }
  public function setImage($imageUrl) {
    $this->image = $imageUrl;
  }
  public function setPayload($data) {
    $this->data = $data;
  }
  public function setIsBackground($is_background) {
    $this->is_background = $is_background;
  }
  public function getPush() {
	  $res = array();
	  $res['data']['title'] = $this->title;
	  $res['data']['is_background'] = $this->is_background;
	  $res['data']['message'] = $this->message;
	  $res['data']['image'] = $this->image;
	  $res['data']['payload'] = $this->data;
	  $res['data']['timestamp'] = date('Y-m-d G:i:s');
	  return $res;
	}
  /*Push*/

	/*firebase*/
	public function send($to, $message) {
    $fields = array(
      'to' => $to,
      'data' => $message,
    );
    return $this->sendPushNotification($fields);
  }
 
  // Sending message to a topic by topic name
  public function sendToTopic($to, $message) {
    $fields = array(
            'to' => '/topics/' . $to,
            'data' => $message,
  	      );
    return $this->sendPushNotification($fields);
  }
 
  // sending push message to multiple users by firebase registration ids
  public function sendMultiple($registration_ids, $message) {
    $fields = array(
          'to' => $registration_ids,
          'data' => $message,
  	      );
    return $this->sendPushNotification($fields);
  }
 
  // function makes curl request to firebase servers
  private function sendPushNotification($fields) {
 		//require_once __DIR__ . '/config.php';
 		// Set POST variables
    $url = 'https://fcm.googleapis.com/fcm/send';
    $API_ACCESS_KEY = 'AAAAB8CiIYc:APA91bGDNkHumfkvBqc5dY4R9eOlvl-saMSCQ8amSy1uPsbbsT_VtNE1SbePOJAZ70DMCEGgAuI9KXq8qbv0yg_h_pQdVQIYCp1Cf86MY7hTxLy0_lBu0lp1Bl2bTMXrXeDpMALK-yDi';
    $headers = array(
            'Authorization: key=' . $API_ACCESS_KEY,
            'Content-Type: application/json'
        		);
    // Open connection
    $ch = curl_init();
 
		// Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Disabling SSL Certificate support temporarly
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		// Execute post
		$result = curl_exec($ch);
		if ($result === FALSE) {
		  die('Curl failed: ' . curl_error($ch));
		}
 		// Close connection
    curl_close($ch);
    return $result;
  }
}