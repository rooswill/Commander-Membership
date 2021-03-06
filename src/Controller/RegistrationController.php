<?php

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;

class RegistrationController extends AppController
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
        $this->tracking('organic', 'pageview');

        $pfHost = ( PAYFAST_SERVER == 'LIVE' ) ? 'www.payfast.co.za' : 'sandbox.payfast.co.za';
        $this->set('payfast_host', $pfHost);
    }

    public function renew()
    {
        $this->tracking('renew membership', 'pageview');
        $pfHost = ( PAYFAST_SERVER == 'LIVE' ) ? 'www.payfast.co.za' : 'sandbox.payfast.co.za';
        $this->set('payfast_host', $pfHost);
    }

    public function index()
    {
        if(isset($this->request->query['p']))
        {
            $this->tracking('registration - organic', 'pageview');

            $paymentType = $this->request->query['p'];
            $paymentTypes = array('cashlog', 'payfast', 'snapchat');

            foreach($paymentTypes as $ptype)
            {
                if(hash('md5', $ptype) == $paymentType)
                    $paymentType = $ptype;
            }

            $this->tracking($paymentType.'option', 'payment method');

            $this->set('paymentType', $paymentType);
        }
        
        $pfHost = ( PAYFAST_SERVER == 'LIVE' ) ? 'www.payfast.co.za' : 'sandbox.payfast.co.za';
        $this->set('payfast_host', $pfHost);

        $this->set('submitButton', true);

        //$this->request->session()->delete('userData');

        if($this->request->session()->read('cashlogData'))
        {
            $this->tracking('registration - cashlog banner', 'pageview');
        	if($this->request->is('post'))
        	{
                $data = $this->request->session()->read('cashlogData');
                $mobileNumber = $data->infoToDisplay->userId->msisdn;

                if(isset($data->mainErrorCode))
                    return $this->redirect(SITE_URL.'/registration/failed');
                else
                {
                    if($this->createMainUser($this->request->data))  
                    {
                        if($this->createCustomers($this->request->data, $mobileNumber))
                        {
                            $this->tracking('cashlog banner flow', 'customer created');
                            return $this->redirect(STORE_URL.'/account/login');
                        }
                    }
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
        $customers = TableRegistry::get('Customers');
        $customerData = $customers->find()->where(['email' => $data['email']])->toArray();

        //$ownDB = $this->createCustomers($data, $mobileNumber);

        if(count($checkUser->customers) > 0)
        {
            /* user found in shopify DB, check for tag */
            $userTagStatus = $this->Shopify->_checkUserTagStatus($checkUser);
            if($userTagStatus)
            {
                if(count($customerData) <= 0)
                    $this->createCustomers($data);
                /* Login and Redirect User */
                //echo "User found already, you will be redirected shortly.";
                $this->tracking('create customer', 'existing customer');
                return true;
            }
            else
            {
                /* Update User with Premium tag */
                //echo "User found in DB with no tag, updating user. You will be redirected shortly.";
                $userUpdateStatus = $this->updateUser($data['email']);
                if($userUpdateStatus)
                {
                    if(count($customerData) <= 0)
                        $this->createCustomers($data);

                    $this->tracking('create customer', 'existing customer - update tag status');

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
                $this->tracking('create customer', 'new customer');
                //$this->sendUserUpdateEmail($data['email']);
                return true;
            }
            else
                echo "User account could not be created. Please contact us on 012929292929";
        }
    }

    public function createNewUser($data = NULL)
    {
        $this->tracking('create customer', 'create customer internal database');
        if($this->Shopify->_createUser($data))
        	return true;
        else
        	return false;
    }

    public function saveUserDetails()
    {
        $returnData['redirect'] = false;
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
        $userTagStatus = $this->Shopify->_checkUserTagStatus($checkUser);

        if(count($checkUser->customers) > 0)
        {
            if($userTagStatus)
                $returnData['already_member'] = true;
        }

        $customers = TableRegistry::get('Customers');
        $customerData = $customers->find()->where(['email' => $data['email']])->toArray();

        if(count($customerData) > 0)
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

        if($this->request->data['renew'])
        {
            if(count($customerData) > 0)
                $returnData['already_member'] = false;
            else
                $returnData['redirect'] = true;
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

        //$this->tracking('customer', 'update customer tag');

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
            {
                $this->tracking('customer', 'send activation URL');
                return true;
            }
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

        if(isset($data->account_activation_url))
        {
            $fromEmail = array('activations@commanderhq.com' => 'Customer Account Activation');
            $email = new Email();
            $email->viewVars(['data' => $data->account_activation_url]);
            $email->template('customer', 'default');
            $email->emailFormat('html');
            $email->to($userEmail);
            $email->from($fromEmail);
            $email->send();
        }

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

    public function customerRedirect()
    {
        if($this->request->session()->read('cashlogData'))
            return $this->redirect('/');
        else
            return $this->redirect('/registration/organic');
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
                $this->tracking('payments', 'cashlog banner flow');
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

            $mobileNumber = $data->infoToDisplay->userId->msisdn;
            
            if(isset($data->mainErrorCode))
                return $this->redirect(SITE_URL.'/registration/failed');
            else
            {
                $this->tracking('payments', 'cashlog payment completed');
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
                                    $customer->modified = date("Y-m-d H:i:s");
                                    $customer->days_remaining = 31 + $customer['days_remaining'];
                                    $customer->renewed = 1;
                                    $customers->save($customer);

                                    return $this->redirect(STORE_URL.'/account/login');
                                }
                            }
                            else
                            {
                                $customer->status = 'active';
                                $customer->date_paid = date("Y-m-d H:i:s");
                                $customer->modified = date("Y-m-d H:i:s");
                                $customer->days_remaining = 31 + $customer['days_remaining'];
                                $customer->renewed = 1;
                                $customers->save($customer);

                                return $this->redirect(STORE_URL.'/account/login');
                            }
                        }
                    }
                }
                else
                {
                    if($this->createMainUser($formData))
                    {
                        if($this->createCustomers($formData, $mobileNumber))
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
                $this->tracking('payments', 'payfast payment completed');
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
                                    $customer->modified = date("Y-m-d H:i:s");
                                    $customer->days_remaining = 31 + $customer['days_remaining'];
                                    $customer->renewed = 1;
                                    $customers->save($customer);

                                    return $this->redirect(STORE_URL.'/account/login');
                                }
                            }
                            else
                            {
                                $customer->status = 'active';
                                $customer->date_paid = date("Y-m-d H:i:s");
                                $customer->modified = date("Y-m-d H:i:s");
                                $customer->days_remaining = 31 + $customer['days_remaining'];
                                $customer->renewed = 1;
                                $customers->save($customer);

                                return $this->redirect(STORE_URL.'/account/login');
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
        $this->tracking('payments', 'payment failed');
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

        $this->tracking('snapscan', 'pageview');

        if($this->request->is('post'))
        {
            $stringData = file_get_contents("php://input");

            parse_str(file_get_contents("php://input"), $data);
            $data = (object)$data;
            
            $details = json_decode($data->payload);

            if($details->status == 'completed')
            {
                $this->tracking('payments', 'snapscan payment completed');
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

        $this->tracking('snapscan', 'verify payment');

        if(isset($data))
        {
            foreach($data as $d)
            {
                parse_str($d->string, $data);
                $data = (object)$data;
                
                $details = json_decode($data->payload);

                if($d->status = 'completed' && $d->order_id == $orderID)
                {

                    $this->tracking('snapscan', 'verified payment');
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
                                        $customer->modified = date("Y-m-d H:i:s");
                                        $customer->days_remaining = 31 + $customer['days_remaining'];
                                        $customer->renewed = 1;
                                        $customers->save($customer);

                                        $this->request->session()->delete('orderID');
                                        $this->request->session()->delete('userFormData');
                                        $returnData['member_status'] = 'active';
                                    }
                                }
                            }
                            else
                            {
                                $customer->status = 'active';
                                $customer->date_paid = date("Y-m-d H:i:s");
                                $customer->modified = date("Y-m-d H:i:s");
                                $customer->days_remaining = 31 + $customer['days_remaining'];
                                $customer->renewed = 1;
                                $customers->save($customer);

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

    public function createCustomers($data = NULL, $mobileNumber = NULL)
    {

        $customers = TableRegistry::get('Customers');
        $customerData = $customers->find()->where(['email' => $data['email']])->toArray();

        $date = date("Y-m-d H:i:s");

        if(count($customerData) <= 0)
        {
            $customers->query()->insert([
                'name', 'surname', 'email', 'mobile_number', 'status', 'date_paid', 'days_remaining', 'renewed', 'created', 'modified'
            ])->values([
                'name' => $data['first_name'], 
                'surname' => $data['last_name'], 
                'email' => $data['email'],
                'mobile_number' => $mobileNumber,
                'status' => 'active',
                'date_paid' => $date,
                'days_remaining' => 31,
                'renewed' => 0,
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

        if(count($customerData) > 0)
        {
            foreach($customerData as $customer)
            {
                if($customer['days_remaining'] > 0)
                {
                    $customer->days_remaining = $customer['days_remaining'] - 1;
                    $customers->save($customer);
                }
                
                if($customer['days_remaining'] == 5)
                {
                    echo 'send first notification';
                    $this->sendCustomerNotifications($customer, $dayStatus);
                }
                elseif($customer['days_remaining'] == 3)
                {
                    echo 'send final notification';
                    $this->sendCustomerNotifications($customer, $dayStatus);
                }
                elseif($customer['days_remaining'] == 0)
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


