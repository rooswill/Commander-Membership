<?php

namespace App\Controller\Component;

use Cake\Controller\Component;

class ShopifyComponent extends Component
{

	public $API_KEY = 'a1ba5f07525678b04deb55ea928a6db7';
	public $SECRET = 'e7b7bff6d5be47d686f1567cb2d49641';
	public $TOKEN = 'c583b0b9824e0a9d1956d733b4d97451';
	public $STORE_URL = 'commanderhq.myshopify.com';
	public $URL = '';
	//public $url = 'https://'.$this->API_KEY.':'.$this->SECRET.'@'.$this->STORE_URL.'/admin/';

    private function _requestGet()
    {
        $session = curl_init();

		curl_setopt($session, CURLOPT_URL, $this->URL);
		curl_setopt($session, CURLOPT_HTTPGET, 1); 
		curl_setopt($session, CURLOPT_HEADER, false);
		curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/xml', 'Content-Type: application/xml'));
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

		if(ereg("^(https)",$this->URL)) curl_setopt($session,CURLOPT_SSL_VERIFYPEER,false);

		$response = curl_exec($session);
		curl_close($session);
		return $response;
    }

    private function _requestPut($data = NULL)
    {
        $ch = curl_init($this->URL);

        pr($data);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, 0); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

		if(ereg("^(https)",$this->URL)) curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);

		$response = curl_exec($ch);

		curl_close($ch);
		return $response;
    }

    public function _requestPost($data)
    {
        $ch = curl_init($this->URL);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, 0); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 

        if(ereg("^(https)",$this->URL)) curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        
        $data = curl_exec($ch);
        
        if(curl_errno($ch)) 
        { 
            echo '<p>Error</p>';
            echo curl_error($ch); 
        } 
        else 
        {
            curl_close($ch); 
            return $data;
        }
    }

    public function setURL()
    {
    	$this->URL = 'https://'.$this->API_KEY.':'.$this->SECRET.'@'.$this->STORE_URL.'/admin/';
    }

    private function _updateUrl($request)
    {
    	$this->setURL();
    	return $this->URL .= $request;
    }

    public function _checkUserTagStatus($data)
    {
    	if(isset($data->customers[0]->tags) && $data->customers[0]->tags == 'club_2309')
    		return true;
    	else
    		return false;
    }

    public function _findUsers($email = NULL)
    {	
    	$this->_updateUrl('customers/search.json?query=email:'.urlencode($email));
    	$data = json_decode($this->_requestGet());

    	return $data;
    }

    public function _createUser($data = NULL)
    {
    	$this->_updateUrl('customers.json');
        $data = $this->_requestPost(json_encode($data));
        return $data;
    }

    public function _updateUser($email = NULL, $data = NULL)
    {
    	$this->_updateUrl('customers.json');
    	$userDetails = $this->_findUsers($email);
    	if(count($userDetails) > 0)
    	{
    		$data['customer']['id'] = $userDetails->customers[0]->id;
    		$this->_updateUrl('customers/'.$userDetails->customers[0]->id.'.json');
			$response = $this->_requestPut(json_encode($data));
			return $response;
    	}
    }

    public function _findUserById($customerID = NULL)
    {
        $this->_updateUrl('customers/'.$customerID.'.json');
        $response = $this->_requestGet();
        return $response;
    }

    public function _getActivationUrl($customerID = NULL, $data)
    {
        $this->_updateUrl('customers/'.$customerID.'/account_activation_url.json');
        $response = $this->_requestPost(json_encode($data));
        return $response;
    }

}