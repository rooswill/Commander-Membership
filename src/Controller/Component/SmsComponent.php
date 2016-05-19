<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
//use Cake\ORM\TableRegistry;

class SmsComponent extends Component
{

	public $components = array('SmsPortal', 'SendSms');

    public $SiteId;
    public $Destination;
    public $FormattedNumber;
    public $SendFrom;
    public $Message;
    public $SecondMessage;
    public $CustomerId;
    public $CampaignId;
    public $DeliveryReceipt;
    public $Username;
    public $Password;
    public $varGUID;
    
    public function setValues($site_id, $to, $msg, $customer_id=3, $campaign_id=0, $from = SMS_FROM_NUMBER, $delivery_receipt = 0, $user_name='', $password='') 
    {
		$this->SiteId = $site_id;
		$this->Destination = $to;
		$this->FormattedNumber = $this->ValidateNumber($to);
		$this->SendFrom = $from;
        $this->Message = $msg;
		$this->CustomerId = $customer_id;
		$this->CampaignId = $campaign_id;
		$this->DeliveryReceipt = $delivery_receipt;
		$this->Username = $user_name;
		$this->Password = $password;
		//Unique identifier for the message - used for delivery reports	
		$this->varGUID = str_replace('.', '', uniqid($_SERVER['REMOTE_ADDR'], TRUE));
    }
	
	public function Send()
	{
		$ActionResult = false;
		if(SMS_ENABLED)
		{
			if(!$this->FormattedNumber)
			{
            	$ActionResult = $this->ProcessError();
			} 
			else 
			{
				if($this->CheckSAPhoneNumber($this->FormattedNumber))
				{
                    $ActionResult = $this->SmsPortal();
					if($ActionResult)
					{
                    	$this->ManageLocalCredits($this->SiteId);
					}
				}
				else 
				{
                	$ActionResult = $this->SendSms();
					if($ActionResult)
					{
                    	$this->ManageInternationalCredits($this->SiteId);
					}	
				}
			}
		}
		return $ActionResult;
	}
	
	public function SendSms()
	{
		if($this->Username == '' || $this->Username == null)
			$this->Username = SMS_USER_NAME;
		if($this->Password == '' || $this->Password == null)
			$this->Password = SMS_PASSWORD;
		
		$ActionResult = $this->SendSms->setValues($this->Username, $this->Password, $this->FormattedNumber, $this->SendFrom, $this->Message, $this->CustomerId, $this->CampaignId, $this->DeliveryReceipt, $this->varGUID);
        return $ActionResult;
	}

    public function SmsPortal()
	{		
		if($this->Username == '' || $this->Username == null)
			$this->Username = SMS_SA_LOCAL_USER_NAME;
		if($this->Password == '' || $this->Password == null)
			$this->Password = SMS_SA_LOCAL_PASSWORD;

		$smsPortal = $this->SmsPortal;
		$ActionResult = $smsPortal->setValues($this->Username, $this->Password, $this->FormattedNumber, $this->SendFrom, $this->Message, $this->CustomerId, $this->CampaignId, $this->DeliveryReceipt, $this->varGUID);
        return $ActionResult;
	}
	
	private function ValidateNumber($number)
	{
		//Validate and format
        $chars = array(' ', '(', ')', '+');
        $clean_no = str_replace($chars, '', $number);
        $clean_no = trim($clean_no);

        if (strlen($clean_no) >= 8 && strlen($clean_no) <= 12) {
            if (ctype_digit($clean_no)) {
                if ($clean_no[0] == '0') {
                    $clean_no = substr_replace($clean_no, '+27', 0, 1);
                }

                if ($clean_no[0] != '+') {
                    $clean_no = '+' . $clean_no;
                }
                return $clean_no;
            } else {
                return false;
            }
        } else {
            return false;
        }		
	}
	
	public function CheckSAPhoneNumber($aTelephoneNumber) {
        return preg_match("/^\+27[0-9]{9}$/", $aTelephoneNumber);
    }
	
	private function ManageLocalCredits($Site_Id)
	{
		// $members = TableRegistry::get('Member');
  //       $members->query("UPDATE SMSCredits SET Current_Local_Credit = (Current_Local_Credit - 1) WHERE SiteId = ".$Site_Id);
  //       $members->query("INSERT INTO SMSTrack(SiteId, SMSType) VALUES('".$Site_Id."', '1')");
	}
	
	private function ManageInternationalCredits($Site_Id)
	{
		// $members = TableRegistry::get('Member');
		// $members->query("UPDATE SMSCredits SET Current_International_Credit = (Current_International_Credit - 1) WHERE SiteId = ".$Site_Id);
  //       $members->query("INSERT INTO SMSTrack(SiteId, SMSType) VALUES('".$Site_Id."', '2')");
	}	
	
	public function ProcessError()
	{
		// $members = TableRegistry::get('Member');
		// $members->query("INSERT INTO SMS_Error(SiteId, Destination, Message) VALUES('".$this->SiteId."', '".$this->Destination."', '".mysql_escape_string($this->Message)."')");
  //       return 'Number not formatted correctly';
	}
}