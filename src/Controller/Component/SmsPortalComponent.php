<?php

namespace App\Controller\Component;

use Cake\Controller\Component;

class SmsPortalComponent extends Component 
{

    public $components = array('SendSms');
    
    /* Description of Variables:
    $id      = customer username for gaining access to SMSPortal
    $pw      = customer password
    $dnr     = number of destination
    $snr     = originating number
    $msg     = SMS text
    $customer_id = The customer ID
    $campaign_id = The campaign ID
    $delivery_receipt = Do we want to receive a delivery receipt? 
    $unique_msg_id = 32 character id for the message, used in delivery receipts
    */
    public $ActionResult;
    
    public function setValues($id, $pw, $dnr, $snr, $msg, $customer_id, $campaign_id, $delivery_receipt, $unique_msg_id)
    {
        $this->ActionResult = false;
        
        if (160 < strlen($msg)) 
        {
            $NearestSpace = strpos($msg, ' ', 160);
            $Message = substr($msg, 0, $NearestSpace);
            $SecondMessage = substr($msg, $NearestSpace, strlen($msg));
        }
        else
        {
            $Message = $msg;
            $SecondMessage = "";
        }
        
        $data= array(
            "Type"=> "sendparam",
            "Username" => $id,
            "Password" => $pw,
            "live" => "true",
            "numto" => $dnr,
            "data1" => $Message
        );

        // This contains data that you will send to the server.
        $data = http_build_query($data); //builds the post string ready for posting
        $res = $this->do_post_request('http://www.mymobileapi.com/api5/http5.aspx', $data);  //Sends the post, and returns the result from the server.
        //echo $res;
        
        if($SecondMessage != "")
        {
            $data= array(
                "Type"=> "sendparam",
                "Username" => $id,
                "Password" => $pw,
                "live" => "true",
                "numto" => $dnr,
                "data1" => $SecondMessage
            );
        }

        $data = http_build_query($data); //builds the post string ready for posting
        $res = $this->do_post_request('http://www.mymobileapi.com/api5/http5.aspx', $data);  //Sends the post, and returns the result from the server.

        // $this->loadModel('Member');
        
        // $SQL='INSERT INTO MessagingOutGoingLog(AdminCustomerID,CampaignCampaignID,MessagingMessageTypeID,DeliveryReceipt,Destination,Message,MessageKey,Result) 
        // VALUES('.$customer_id.', '.$campaign_id.', 4, "'.$delivery_receipt.'", "'.$dnr.'", "'.mysql_real_escape_string($msg).'", "'.$unique_msg_id.'", "'.mysql_real_escape_string($res).'")';
        
        // $results = $this->Member->query($SQL);

        return true;

    }

    //Posts data to server and recieves response from server
    //DO NOT EDIT unless you are sure of your changes
    public function do_post_request($url, $data, $optional_headers = null)
    {
        $params = array('http' => array(
          'method' => 'POST',
          'content' => $data
          ));

        if ($optional_headers !== null) 
        {
            $params['http']['header'] = $optional_headers;
        }

        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);

        if (!$fp) 
        {
            $this->ActionResult = "Problem with $url";
        }

        $response = @stream_get_contents($fp);

        if ($response === false) 
        {
            $this->ActionResult = "Problem reading data from $url";
        }
        else
        {
            $this->ActionResult = true;
        }

        return $this->formatXmlString($response);
    }

        //takes the XML output from the server and makes it into a readable xml file layout
        //DO NOT EDIT unless you are sure of your changes
    public function formatXmlString($xml)
    {
            // add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
        $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

            // now indent the tags
        $token      = strtok($xml, "\n");
        $result     = ''; // holds formatted version as it is built
        $pad        = 0; // initial indent
        $matches    = array(); // returns from preg_matches()

        // scan each line and adjust indent based on opening/closing tags
        while ($token !== false) 
        {

            // test for the various tag states

            // 1. open and closing tags on same line - no change
            if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) :
                $indent=0;
            // 2. closing tag - outdent now
            elseif (preg_match('/^<\/\w/', $token, $matches)) :
                $pad--;
            // 3. opening tag - don't pad this one, only subsequent tags
            elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) :
                $indent=1;
            // 4. no indentation needed
            else :
                $indent = 0;
            endif;

            // pad the line with the required number of leading spaces
            $line    = str_pad($token, strlen($token)+$pad, ' ', STR_PAD_LEFT);
            $result .= $line . "\n"; // add to the cumulative result, with linefeed
            $token   = strtok("\n"); // get the next token
            $pad    += $indent; // update the pad size for subsequent lines

        }

        return $result;
    }

}
?>
