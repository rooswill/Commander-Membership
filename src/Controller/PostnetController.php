<?php

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;


class PostnetController extends Controller
{

    public $searchURL = 'http://storefinder.customsoftuk.com/soundslike?suburb=';
    public $branchURL = 'http://storefinder.customsoftuk.com/store/locate?suburb=';

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Cashlog');
        $this->loadComponent('Shopify');
        $this->viewBuilder()->layout('postnet');
    }

    public function getCurlData($url)
    {
        $ch = curl_init();  
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
     
        $output = curl_exec($ch);
     
        curl_close($ch);
        return $output;
    }

    public function index()
    {
        if(isset($this->request->query['e']))
            $this->request->session()->write('userEmail', urldecode($this->request->query['e']));
        else
            $this->request->session()->write('userEmail', NULL);
    }

    public function updateUser()
    {
        if($this->request->is('post'))
        {
            if($this->request->data)
            {
                $userEmail = $this->request->session()->read('userEmail');

                if(isset($userEmail))
                {
                    $requestData = array(
                        "customer" => array(
                            "note" => $this->request->data['notes']
                        )
                    );

                    if($data = $this->Shopify->_updateUser($userEmail, $requestData))
                        $return['updated'] = 'true';
                    else
                        $return['updated'] = 'false';
                }
                else
                {
                    $return['updated'] = 'false';
                }
            }
        }

        echo json_encode($return);

        die();
    }

    public function searchSuburb($keyword)
    {
        //$keyword = $this->request->data['keyword'];

        $url = $this->searchURL.$keyword;
        $data = $this->getCurlData($url);
        $result = json_decode($data, true);
        echo $data;
        die();
    }

    public function findPostNet()
    {   
        $suburb = urlencode($this->request->data['suburb']);

        // $this->searchSuburb($suburb);
        // die();

        $url = $this->branchURL.$suburb;
        $data = $this->getCurlData($url);
        $result = json_decode($data, true);

        $returnData['status'] = false;
        $returnData['data'] = '';

        if(!isset($result['notfound']) && !isset($result['error']))
        {
            $returnData['status'] = true;
            foreach($result as $key => $value)
            {
                $hiddenData = $key."\n";
                $returnData['data'] .= '<div class="branch-details" onclick="updateUserProfile(this);">';

                    $returnData['data'] .= '<div class="branch-name">'.$key.'</div>';

                    foreach($value as $k => $v)
                    {
                        $hiddenData .= ucfirst($k). ' : ' . $v."\n";
                        $returnData['data'] .= '<div class="branch-inner-detail"><span>'.ucfirst($k).'</span> : '.$v.'</div>';
                    }
                        
                $returnData['data'] .= '<div class="hiddenContent" style="display:none;">'.nl2br($hiddenData).'</div>';
                $returnData['data'] .= '</div>';
            }
        }
        else
        {
            $returnData['status'] = false;
            $returnData['data'] = '<div class="branch-details">No Branches found in your area, please try another location</div>';
        }

        echo json_encode($returnData);

        die();
    }
}
