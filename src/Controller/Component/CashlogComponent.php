<?php

namespace App\Controller\Component;

use Cake\Controller\Component;

class CashlogComponent extends Component
{
	public $siteCode = '3434';
	public $singlePurchaseUrl = 'https://api.cashlog.com/mpay-ws/v2/country/{countryCode}/site/{siteCode}/singlePurchase/purchase';
	public $recurrentPurchaseUrl = 'https://api.cashlog.com/mpay-ws/v2/country/{countryCode}/site/{siteCode}/subscription/subscribe';

    public function doComplexOperation($amount1, $amount2)
    {
        return $amount1 + $amount2;
    }

    public function process()
    {
        if(isset($this->request->data['response']))
        {
            $data = json_decode($this->request->data['response']);
           	return $data;
        }
    }

    public function returnError($code = NULL, $subcode = NULL)
    {
        $errors = array(
            'ERR_0001' => array(
                'ERR_0408' => 'Insufficient prepaid balance'
            ),
            'ERR_0002' => array(
                'ERR_0406' => 'Exceeded daily limit'
            ),
            'ERR_0003' => array(
                'ERR_0408' => 'Insufficient prepaid balance'
            ),
            'ERR_0004' => array(
                'ERR_0402' => 'Error, User exceeded Purchase cost limits',
                'ERR_0410' => 'Exceeded transaction limit',
                'ERR_0411' => 'Exceeded merchant limit'
            ),
            'ERR_0005' => array(
                'ERR_0200' => 'Customer temporary blacklisted',
                'ERR_0201' => 'User suspended by cc'
            ),
            'ERR_0006' => array(
                'ERR_9915' => 'Operator Session/Time expired'
            ),
            'ERR_0007' => array(
                'ERR_9999' => 'Generic failure',
                'ERR_9998' => 'Customer not subscribe',
                'ERR_9997' => 'Customer not authorized',
                'ERR_9996' => 'Wrong customer',
                'ERR_0800' => 'Financial Accounting Issue',
                'ERR_0900' => 'Caring Message not Sent, connection problem',
                'ERR_0901' => 'Country Unknown, Caring Message not Sent',
                'ERR_0902' => 'Operator not Found, Caring Message not Sent',
                'ERR_0903' => 'Operator not Found, Caring Message not Sent',
                'ERR_0904' => 'Error during persisting Caring Message',
                'ERR_0905' => 'Technical Error, Caring Message not Sent',
                'ERR_0401' => 'Error during Cashlog Rules Evaluation',
                'ERR_0403' => 'Error during Merchant Rules Evaluation',
                'ERR_0500' => 'OTP Message not Sent, connection problem',
                'ERR_0501' => 'Country Unknown, OTP Message not Sent',
                'ERR_0502' => 'Operator not Found, OTP Message not Sent',
                'ERR_0503' => 'Operator not Found, OTP Message not Sent',
                'ERR_0504' => 'Error during persist OTP Message',
                'ERR_0505' => 'Technical Error, OTP Message not Sent'
            ),
            'ERR_0008' => array(
                'ERR_0506' => 'Maximum number of generated OTP achieved'
            ),
            'ERR_0010' => array(
                'ERR_0551' => 'Maximum number of OTP retries achieved'
            ),
            'ERR_0011' => array(
                'ERR_0102' => 'Error, Merchant Blocked',
                'ERR_0103' => 'Error, Merchant Deleted',
                'ERR_0107' => 'Error, Site is deleted or blocked',
                'ERR_0108' => 'Error, Merchant Blocked or Deleted by Operator',
                'ERR_0109' => 'Error, Site is in trial'
            ),
            'ERR_0012' => array(
                'ERR_0602' => 'Error in retrieving the type of Subscription Frequency',
                'ERR_0106' => 'Site not opened for billing country',
                'ERR_0707' => 'Price not Available',
                'ERR_9900' => 'Invalid Input Parameters',
                'ERR_0100' => 'Invalid Merchant',
                'ERR_0101' => 'Invalid Site',
                'ERR_0104' => 'Error, Site linked to Other Merchant',
                'ERR_0105' => 'Error, Site not linked to Merchant',
                'ERR_0400' => 'Error during transaction validation',
                'ERR_0451' => 'Error, price is not Valid'
            ),
            'ERR_0013' => array(
                'ERR_0600' => 'Error during Subscription Phase',
                'ERR_0601' => 'Subscription not found for MSISDN',
                'ERR_0700' => 'Error during retrieving Authentication for Billing Execution',
                'ERR_0701' => 'Technical Error during Billing Phase',
                'ERR_0703' => 'Not Billed',
                'ERR_0705' => 'Billing Phase Timed Out',
                'ERR_0702' => 'Fatal Error in Billing Phase',
                'ERR_0704' => 'Not Billed, no retry possible',
                'ERR_0706' => 'Not Billed, the MSISDN may be not valid'
            ),
            'ERR_0014' => array(
                'ERR_0550' => 'Invalid OTP entered'
            ),
            'ERR_0015' => array(
                'ERR_0603' => 'Subscription Already Present for the MSISDN'
            ),
            'ERR_0016' => array(
                'ERR_0300' => 'Operator Recognition, Critical Error',
                'ERR_0301' => 'Operator Recognition, Technical Error',
                'ERR_0302' => 'Operator Recognition, Operator not Found',
                'ERR_0404' => 'Error during Operator Rules Evaluation',
                'ERR_0405' => 'Operator not available'
            ),
            'ERR_0017' => array(
                'ERR_0409' => 'Too many days without billing'
            ),
            'ERR_0018' => array(
                'ERR_0150' => 'The Refund has not been authorized.',
                'ERR_0151' => 'The transaction cannot be refunded because is too old.',
                'ERR_0152' => 'The transaction cannot be refund because already refunded.',
                'ERR_0153' => 'The amount to be refunded is not equal to the transaction amount.',
                'ERR_0154' => 'The Operator does not accept the refund, for invalid request.',
                'ERR_0155' => 'There is not a transaction for the specified input parameters.',
                'ERR_0156' => 'The transaction cannot be refund it is not in a compatible status.',
                'ERR_0157' => 'Refund not enabled for this operator'
            ),
            'ERR_0019' => array(
                'ERR_0158' => 'Invalid Promo',
                'ERR_0159' => 'Expired Promo',
                'ERR_0160' => 'Promo Already Used',
                'ERR_0161' => 'Promotion Overlapping',
                'ERR_0162' => 'Invalid Profile (it’s returned only if the operator is Movistar Spain)'
            ),
            'ERR_0050' => array(
                'ERR_0110' => 'MSISDN format invalid'
            ),
            'ERR_0051' => array(
                'ERR_0111' => 'MSISDN value not present'
            ),
            'ERR_0052' => array(
                'ERR_0112' => 'OTP format invalid'
            ),
            'ERR_0053' => array(
                'ERR_0113' => 'OTP value not present'
            ),
            'ERR_0054' => array(
                'ERR_0133' => 'Service unavailable it notify a wrong action at start',
                'ERR_0135' => 'Service unavailable for wrong action call or an inexistent one',
                'ERR_0136' => 'Service unavailable, not business user'
            ),
            'ERR_0055' => array(
                'ERR_0124' => 'Session expired or timeout'
            ),
            'ERR_0056' => array(
                'ERR_0126' => 'Price for Operator recognized not supported'
            ),
            'ERR_0057' => array(
                'ERR_0127' => 'Operation aborted by user.'
            ),
            'ERR_0058' => array(
                'ERR_0303' => 'Recognition methods not available'
            )
        );

        return $errors[$code][$subcode];

    }
}