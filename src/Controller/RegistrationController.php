<?php

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;


class RegistrationController extends Controller
{

    public $globalPayment = false;

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Cashlog');
        $this->loadComponent('Payfast');
        $this->loadComponent('Shopify');
        // $this->loadComponent('SendSms');
        // $this->loadComponent('Sms');
        // $this->loadComponent('SmsPortal');
        $this->viewBuilder()->layout('default');
    }

    public function organic()
    {
        $pfHost = ( PAYFAST_SERVER == 'LIVE' ) ? 'www.payfast.co.za' : 'sandbox.payfast.co.za';
        $this->set('payfast_host', $pfHost);
    }

    public function renew()
    {
        $pfHost = ( PAYFAST_SERVER == 'LIVE' ) ? 'www.payfast.co.za' : 'sandbox.payfast.co.za';
        $this->set('payfast_host', $pfHost);
    }

    public function index()
    {
        if(isset($this->request->query['p']))
        {
            $paymentType = $this->request->query['p'];
            $paymentTypes = array('cashlog', 'payfast', 'snapchat');

            foreach($paymentTypes as $ptype)
            {
                if(hash('md5', $ptype) == $paymentType)
                    $paymentType = $ptype;
            }

            $this->set('paymentType', $paymentType);
        }
        
        $pfHost = ( PAYFAST_SERVER == 'LIVE' ) ? 'www.payfast.co.za' : 'sandbox.payfast.co.za';
        $this->set('payfast_host', $pfHost);

        $this->set('submitButton', true);

        //$this->request->session()->delete('userData');

        if($this->request->session()->read('cashlogData'))
        {
        	if($this->request->is('post'))
        	{
                $data = $this->request->session()->read('cashlogData');

                if($data->mainErrorCode != NULL && $data->mainErrorCode != '')
                    return $this->redirect(SITE_URL.'/registration/failed');
                else
                {
                    if($this->createMainUser($this->request->data))  
                        return $this->redirect(STORE_URL.'/account/login');
                }
        	}
        }
        else
        {
            //user coming from organic flow.
            $this->set('submitButton', false);
        }
    }

    public function createMainUser($data)
    {
        // check if users exist in shopify DB
        $checkUser = $this->Shopify->_findUsers($data['email']);

        $ownDB = $this->createCustomers($data);

        if(count($checkUser->customers) > 0)
        {
            /* user found in shopify DB, check for tag */
            $userTagStatus = $this->Shopify->_checkUserTagStatus($checkUser);
            if($userTagStatus)
            {
                /* Login and Redirect User */
                //echo "User found already, you will be redirected shortly.";
                return true;
            }
            else
            {
                /* Update User with Premium tag */
                //echo "User found in DB with no tag, updating user. You will be redirected shortly.";
                $userUpdateStatus = $this->updateUser($data['email']);
                if($userUpdateStatus)
                {
                    $this->sendUserUpdateEmail($data['email']);
                    return true;
                }
                else
                    echo "Profile could not be updated please try again later.";
            }
        }
        else
        {
            /* create new premium user (Shopify) with details provided. */
            $requestData = array(
                "customer" => array(
                    "first_name" => $data['first_name'],
                    "last_name" => $data['last_name'],
                    "email" => $data['email'],
                    "verified_email" => false,
                    "password" => $data['password'],
                    "password_confirmation" => $data['password'],
                    "send_email_welcome" => true,
                    "tags" => "club_2309",
                    "address" => [

                    ]
                )
            );

            if($userData = $this->createNewUser($requestData))
            {
                // $cellphone = '+27810255611';
                // $textMessage = 'Hi '.$data['first_name'].', Welcome to CommanderHQ, thanks for signing up, your R50 membership fee will automatically renew (opt out anytime). To contact us visit <a href="http://www.commanderhq.net">www.commanderhq.net</a>';

                // $sms = $this->Sms->setValues(1222, trim($cellphone), $textMessage, 3, 0, SMS_FROM_NUMBER, 0, null, null);
                // $smsResult = $this->Sms->Send();
                $this->sendUserUpdateEmail($data['email']);
                return true;
            }
            else
                echo "User account could not be created. Please contact us on 012929292929";
        }
    }

    public function createNewUser($data = NULL)
    {
        if($this->Shopify->_createUser($data))
        	return true;
        else
        	return false;
    }

    public function saveUserDetails()
    {
        //save user details to session for later use after payment
        if($this->request->is('post'))
        {
            $this->request->session()->write('userFormData', $this->request->data);
            $data = $this->request->session()->read('userFormData');
            
            if(isset($data))
                $returnData['status'] = true;
            else
                $returnData['status'] = false;
        }

        $checkUser = $this->Shopify->_findUsers($this->request->data['email']);
        if(count($checkUser->customers) > 0)
        {
            $customers = TableRegistry::get('Customers');
            $customerData = $customers->find()->where(['email' => $data['email']])->toArray();

            if(isset($customerData))
            {
                foreach($customerData as $customer)
                {
                    if($customer['email'] == $this->request->data['email'])
                    {
                        if($customer['status'] != 'inactive')
                            $returnData['already_member'] = true;
                    }
                }
            }
            else
                $returnData['already_member'] = true;
        }

        echo json_encode($returnData);
        die();
    }

    public function updateUser($email = NULL)
    {
    	$requestData = array(
            "customer" => array(
			    "tags" => "club_2309"
        	)
        );

    	if($data = $this->Shopify->_updateUser($email, $requestData))
    		return true;
    	else
    		return false;
    }

    public function sendActivationUrl($customerEmail = NULL)
    {

        $checkUser = $this->Shopify->_findUsers($customerEmail);
        $customerID = $checkUser->customers[0]->id;

        $requestData = array(
            "customer" => array(
                "id" => $customerID
            )
        );

        if($data = $this->Shopify->_getActivationUrl($customerID, $requestData))
        {
            if($this->sendUserActivation($customerEmail, $data))
                return true;
        }
        else
            return false;
    }

    public function sendUserActivation($userEmail, $data)
    {
        Email::configTransport('gmail', [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => 'rooswill@gmail.com',
            'password' => 'TransF0rm3rs',
            'className' => 'Smtp',
            'tls' => true
        ]);

        $data = json_decode($data);

        $fromEmail = array('activations@commanderhq.com' => 'Customer Account Activation');
        $email = new Email();
        $email->viewVars(['data' => $data->account_activation_url]);
        $email->template('customer', 'default');
        $email->emailFormat('html');
        $email->to($userEmail);
        $email->from($fromEmail);
        $email->send();

        return true;
    }

    public function sendUserUpdateEmail($userEmail)
    {
        Email::configTransport('gmail', [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => 'rooswill@gmail.com',
            'password' => 'TransF0rm3rs',
            'className' => 'Smtp',
            'tls' => true
        ]);

        $fromEmail = array('info@commanderhq.com' => 'PostNet Store Locator');
        $email = new Email();
        $email->viewVars(['data' => $userEmail]);
        $email->template('welcome', 'postnet');
        $email->emailFormat('html');
        $email->to($userEmail);
        $email->from($fromEmail);
        $email->send();
    }

    public function processCashlogDetails()
    {
        if(isset($this->request->data['response']))
        {
            $data = $this->Cashlog->process($this->request->data);
            
            if(isset($data->mainErrorCode))
                return $this->redirect(SITE_URL.'/registration/failed');
            else
            {
                $this->request->session()->write('cashlogData', $data);
                return $this->redirect(STORE_URL);
            }
        }
    }

    public function processRegistration()
    {
        $formData = $this->request->session()->read('userFormData');
        $customers = TableRegistry::get('Customers');
        $customerData = $customers->find()->where(['email' => $formData['email']])->toArray();

        $date = date("Y-m-d H:i:s");

        if(isset($this->request->data['response']))
        {
            $data = $this->Cashlog->process($this->request->data);
            
            if($data->mainErrorCode != NULL && $data->mainErrorCode != '')
                return $this->redirect(SITE_URL.'/registration/failed');
            else
            {
                if(count($customerData) > 0)
                {
                    foreach($customerData as $customer)
                    {
                        if($customer['email'] == $formData['email'])
                        {
                            if($customer['status'] == 'inactive')
                            {
                                if($this->sendActivationUrl($customer['email']))
                                {

                                    $customer->status = 'active';
                                    $customer->date_paid = date("Y-m-d H:i:s");
                                    $customers->save($customer);

                                    return $this->redirect(STORE_URL.'/account/login');
                                }
                            }
                        }
                    }
                }
                else
                {
                    if($this->createMainUser($formData))
                    {
                        if($this->createCustomers($formData))
                            return $this->redirect(STORE_URL.'/account/login');
                    }
                }
            }
        }
        else
        {
            $data = $this->Payfast->success();

            if($data['payment_status'] == 'COMPLETE')
            {
                if(isset($customerData) && count($customerData) > 0)
                {
                    foreach($customerData as $customer)
                    {
                        if($customer['email'] == $formData['email'])
                        {
                            if($customer['status'] == 'inactive')
                            {
                                if($this->sendActivationUrl($customer['email']))
                                {

                                    $customer->status = 'active';
                                    $customer->date_paid = date("Y-m-d H:i:s");
                                    $customers->save($customer);

                                    return $this->redirect(STORE_URL.'/account/login');
                                }
                            }
                        }
                    }
                }
                else
                {
                    if($this->createMainUser($formData))
                    {
                        if($this->createCustomers($formData))
                            return $this->redirect(STORE_URL.'/account/login');
                    }
                    else
                        return $this->redirect(SITE_URL);
                }
            }
        }
        die();
    }

    public function failed()
    {
        if($this->request->session()->read('userData'))
        {
            $data = $this->request->session()->read('userData');
            $this->set('errorDescription', $this->returnError($data->mainErrorCode, $data->detailedErrorCode));
            $this->request->session()->delete('userData');
        }
        else
            $this->set('errorDescription', 'No response from server');
        
    }

    public function snapscan()
    {
        $orderID = 'order'.rand(1, 1000);
        $this->set('orderID', $orderID);

        $this->request->session()->write('orderID', $orderID);

        if($this->request->is('post'))
        {
            $stringData = file_get_contents("php://input");

            parse_str(file_get_contents("php://input"), $data);
            $data = (object)$data;
            
            $details = json_decode($data->payload);

            if($details->status == 'completed')
            {

                $snapscan = TableRegistry::get('Snapscan');

                if(isset($details->extra->id))
                    $orderID = $details->extra->id;
                else
                    $orderID = NULL;

                $snapscan->query()->insert([
                    'order_id', 
                    'string', 
                    'status'
                ])->values([
                    'order_id' => $orderID,
                    'string' => $stringData,
                    'status' => $details->status
                ])->execute();

            }
        }
    }

    public function checkDB()
    {
        $customers = TableRegistry::get('Customers');

        $customers->query()->insert([
            'name', 
            'surname', 
            'email',
            'status',
            'date_paid',
            'created',
            'modified'
        ])->values([
            'name' => 'Will', 
            'surname' => 'Roos', 
            'email' => 'rooswill@gmail.com',
            'status' => 'active',
            'date_paid' => NULL,
            'created' => NULL,
            'modified' => NULL
        ])->execute();

        die();
    }

    public function verifySnapScan()
    {
        $formData = $this->request->session()->read('userFormData');
        $orderID = $this->request->session()->read('orderID');

        $customers = TableRegistry::get('Customers');
        $customerData = $customers->find()->where(['email' => $formData['email']])->toArray();
        $date = date("Y-m-d H:i:s");

        $snapscan = TableRegistry::get('Snapscan');
        $data = $snapscan->find()->where(['order_id' => $orderID])->all();

        $returnData['member_status'] = 'inactive';

        if(isset($data))
        {
            foreach($data as $d)
            {
                parse_str($d->string, $data);
                $data = (object)$data;
                
                $details = json_decode($data->payload);

                if($d->status = 'completed' && $d->order_id == $orderID)
                {

                    if(count($customerData) > 0)
                    {
                        foreach($customerData as $customer)
                        {
                            if($customer['email'] == $formData['email'])
                            {
                                if($customer['status'] == 'inactive')
                                {
                                    if($this->sendActivationUrl($customer['email']))
                                    {
                                        $customer->status = 'active';
                                        $customer->date_paid = date("Y-m-d H:i:s");
                                        $customers->save($customer);

                                        $this->request->session()->delete('orderID');
                                        $this->request->session()->delete('userFormData');
                                        $returnData['member_status'] = 'active';
                                    }
                                }
                            }
                            else
                            {
                                $this->request->session()->delete('orderID');
                                $this->request->session()->delete('userFormData');
                                $returnData['member_status'] = 'active';
                            }
                        }

                    }
                    else
                    {
                        if($this->createMainUser($formData))
                        {
                            if($this->createCustomers($formData))
                            {
                                $this->request->session()->delete('orderID');
                                $this->request->session()->delete('userFormData');
                                $returnData['member_status'] = 'active';
                            }
                        }
                    }
                }
            }
        }

        echo json_encode($returnData);
        die();
    }

    // custom user management

    public function createCustomers($data = NULL)
    {

        $customers = TableRegistry::get('Customers');
        $customerData = $customers->find()->where(['email' => $data['email']])->toArray();

        $date = date("Y-m-d H:i:s");

        if(count($customerData) <= 0)
        {
            $customers->query()->insert([
                'name', 'surname', 'email', 'status', 'date_paid', 'created', 'modified'
            ])->values([
                'name' => $data['first_name'], 
                'surname' => $data['last_name'], 
                'email' => $data['email'],
                'status' => 'active',
                'date_paid' => $date,
                'created' => $date,
                'modified' => $date
            ])->execute();
        }

        return true;
    }

    // deactivate customer accounts

    public function checkCustomerAccountStatus()
    {
        $customers = TableRegistry::get('Customers');
        $customerData = $customers->find()->toArray();

        $dayLimit = 31;

        if(count($customerData) > 0)
        {
            foreach($customerData as $customer)
            {
                $now = time(); // or your date as well
                $your_date = strtotime($customer['date_paid']);
                $datediff = $now - $your_date;
                $dayStatus = floor( $datediff / (60*60*24) );

                if($dayStatus == 28)
                {
                    echo 'send first notification';
                    $this->sendCustomerNotifications($customer, $dayStatus);
                }
                elseif($dayStatus == 30)
                {
                    echo 'send final notification';
                    $this->sendCustomerNotifications($customer, $dayStatus);
                }
                elseif($dayStatus >= $dayLimit)
                {
                    if($customer['status'] == 'active')
                    {
                        $customer->status = 'inactive';
                        $customers->save($customer);

                        $find = $this->Shopify->_findUsers($customer['email']);
                        $customerID = $find->customers[0]->id;

                        $this->sendDeactivationEmail($customer['email'], $customerID);
                    }
                }

            }
        }

        die();

    }

    public function sendCustomerNotifications($customer, $notification)
    {

        // Email::configTransport('gmail', [
        //     'host' => 'smtp.gmail.com',
        //     'port' => 587,
        //     'username' => 'rooswill@gmail.com',
        //     'password' => 'TransF0rm3rs',
        //     'className' => 'Smtp',
        //     'tls' => true
        // ]);

        $adminEmail = "rooswill@gmail.com";

        if($notification == 28)
            $template = 'first_notification';
        elseif($notification == 30)
            $template = 'final_notification';

        $fromEmail = array('membership@commanderhq.com' => 'Membership Notifications');
        $email = new Email();
        $email->viewVars(['data' => $customer]);
        $email->template($template, 'default');
        $email->emailFormat('html');
        $email->subject('CommanderHQ Membership');
        $email->to($customer['email']);
        $email->from($fromEmail);
        
        if($email->send())
        {
            echo "Email has been sent to (".$customer['name'].") for customer account - ".$customer['email'];
            return true;
        }
            
    }

    public function sendDeactivationEmail($userEmail, $customerID)
    {

        Email::configTransport('gmail', [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => 'rooswill@gmail.com',
            'password' => 'TransF0rm3rs',
            'className' => 'Smtp',
            'tls' => true
        ]);

        $adminEmail = "rooswill@gmail.com";

        $fromEmail = array('activations@commanderhq.com' => 'Customer Account De-Activation');
        $email = new Email();
        $email->viewVars(['data' => $userEmail, 'customerID' => $customerID]);
        $email->template('activation', 'default');
        $email->emailFormat('html');
        $email->to($adminEmail);
        $email->from($fromEmail);
        
        if($email->send())
        {
            echo "Email has been sent to (".$adminEmail.") for customer account - ".$userEmail;
            return true;
        }
            
    }

}
