<?php

namespace App\Controller\Component;

use Cake\Controller\Component;

class PayfastComponent extends Component
{

    public function success() 
    {
        $title_for_layout = 'Commander HQ | Payment Successfull';

        $pmtToken = isset( $this->request->query['pt'] ) ?  $this->request->query['pt'] : null;
        $authToken = '0a1e2e10-03a7-4928-af8a-fbdfdfe31d43';

        $req = 'pt='. $pmtToken .'&at='. $authToken;
        
        $pfHost = ( PAYFAST_SERVER == 'LIVE' ) ? 'www.payfast.co.za' : 'sandbox.payfast.co.za';

        $header = "POST /eng/query/fetch HTTP/1.0\r\n";
        $header .= 'Host: '. $pfHost ."\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= 'Content-Length: '. strlen( $req ) ."\r\n\r\n";

        $socket = fsockopen( 'ssl://'. $pfHost, 443, $errno, $errstr, 10 );
 
        if( !$socket )
        {
            print( 'errno = '. $errno .', errstr = '. $errstr );
            return false;
        }

        fputs( $socket, $header . $req );

        $res = '';
        $headerDone = false;

        while( !feof( $socket ) )
        {
            $line = fgets( $socket, 1024 );
         
            // Check if we are finished reading the header yet
            if( strcmp( $line, "\r\n" ) == 0 )
                $headerDone = true;
            else if( $headerDone )
                $res .= $line;
        }
         
        // Parse the returned data
        $lines = explode( "\n", $res );

        $result = trim( $lines[0] );
 
        // If the transaction was successful
        if( strcmp( $result, 'SUCCESS' ) == 0 )
        {
            // Process the reponse into an associative array of data
            for( $i = 1; $i < count( $lines ) - 1; $i++ )
            {
                list( $key, $val ) = explode( "=", $lines[$i] );
                $data[urldecode( $key )] = stripslashes( urldecode( $val ) );
            }

            // update member status;
            $member_id = $data['m_payment_id'];
            $purchase_date = date('Y-m-d H:i:s');
            $expire_date = date('Y-m-d H:i:s', strtotime('+'.$data['custom_int1'].' months'));

            return $data;
        }
        // If the transaction was NOT successful
        else if( strcmp( $result, 'FAIL' ) == 0 )
            return false;

        if( $socket )
            fclose( $socket );
    }

    public function failed() 
    {
        $title_for_layout = 'Commander HQ | Payment Failed';
    }

    public function process() 
    {
        // process PayFast Payment.
    }
}