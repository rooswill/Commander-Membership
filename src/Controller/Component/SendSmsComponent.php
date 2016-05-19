<?php

namespace App\Controller\Component;

use Cake\Controller\Component;

class SendSmsComponent extends Component 
{
    /* Description of Variables:
    $id      = customer id for gaining access to HTTP2SMS 
    $pw      = customer PIN 
    $dnr     = number of destination 
    $snr     = originating number 
    $msg     = SMS text 
    $customer_id = The customer ID 
    $campaign_id = The campaign ID 
    $delivery_receipt = Do we want to receive a delivery receipt? 
    $unique_msg_id = 32 character id for the message, used in delivery receipts 
    */

    public function setValues($id, $pw, $dnr, $snr, $msg, $customer_id, $campaign_id, $delivery_receipt, $unique_msg_id)
    {
        $ActionResult = false;
        
        if (160 < strlen($msg)) {
            $NearestSpace = strpos($msg, ' ', 160);
            $Message = substr($msg, 0, $NearestSpace);
            $SecondMessage = substr($msg, $NearestSpace, strlen($msg));
        }else{
            $Message = $msg;
            $SecondMessage = "";
        }        
        
        $url = "http://smsgw1.a2p.mme.syniverse.com/sms.php?"."id=$id"."&pw=".UrlEncode($pw)."&drep=".$delivery_receipt."&dnr=".UrlEncode($dnr)."&snr=".UrlEncode($snr)."&dtag=".$unique_msg_id."&msg=".UrlEncode($Message);

        if (($f = @fopen($url, "r")))
        {
            $answer = fgets($f, 255);
            if (substr($answer, 0, 1) == "+")
            {
              $res = "success: $answer";
              $ActionResult = true;
          }
          else
          {
              $res = "failure: $answer";
              $ActionResult = $res;
          }
      }
      else
      {
        $res = "failure: error opening URL";
    }

    if($SecondMessage != ""){
        $url = "http://smsgw1.a2p.mme.syniverse.com/sms.php?"."id=$id"."&pw=".UrlEncode($pw)."&drep=".$delivery_receipt."&dnr=".UrlEncode($dnr)."&snr=".UrlEncode($snr)."&dtag=".$unique_msg_id."&msg=".UrlEncode($SecondMessage);

        if (($f = @fopen($url, "r")))
        {
            $answer = fgets($f, 255);
            if (substr($answer, 0, 1) == "+")
            {
              $res = "success: $answer";
              $ActionResult = true;
          }
          else
          {
              $res = "failure: $answer";
              $ActionResult = $res;
          }
      }
      else
      {
        $res = "failure: error opening URL";
    }            
}

$db = new DatabaseManager(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

$SQL='INSERT INTO MessagingOutGoingLog(AdminCustomerID,CampaignCampaignID,MessagingMessageTypeID,DeliveryReceipt,Destination,Message,MessageKey,Result) 
VALUES('.$customer_id.', '.$campaign_id.', 4, "'.$delivery_receipt.'", "'.$dnr.'", "'.mysql_real_escape_string($msg).'", "'.$unique_msg_id.'", "'.mysql_real_escape_string($res).'")';
$db->setQuery($SQL);
$db->Query();

return $ActionResult;
}
}
?>
