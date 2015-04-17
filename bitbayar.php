<?php

/*
 * This is WHMCS module using BitBayar payment gateway
 * Author : Gusti Tammam
 * URL : http://www.tammam.web.id
 * Release Date: 2015.04.17
 * License : http://opensource.org/licenses/MIT
 */
/*
 * WHMCS - The Complete Client Management, Billing & Support Solution
 * Copyright (c) WHMCS Ltd. All Rights Reserved,
 * Email: info@whmcs.com
 * Website: http://www.whmcs.com
 */
/*
 * BitBayar - Indonesian Bitcoin Payment Gateway
 * Website: https://bitbayar.com
 */

function bitbayar_config() {
    $configarray = array(
     "FriendlyName" => array("Type" => "System", "Value"=>"BitBayar"),
     "apibitbayar" => array("FriendlyName" => "API Token Key", "Type" => "text", "Size" => "40", ),
     "instructions" => array("FriendlyName" => "BitBayar Instructions", "Type" => "textarea", "Rows" => "5", "Description" => "The instructions you want displaying to customers who choose this payment method - the invoice number will be shown underneath the text entered above", ),
    );
  return $configarray;
}

function bitbayar_link($params) {

  # Gateway Specific Variables
  $gatewayapi = $params['apibitbayar'];
  $callbackurl = $params['systemurl'] . "/modules/gateways/callback/bitbayar.php";
  $redirecturl = $params['systemurl'] . "/viewinvoice.php?id=" . $params['invoiceid'];

  # Invoice Variables
  $invoiceid = $params['invoiceid'];
  $description = $params["description"];
  $amount = $params['amount']; # Format: ##.##
  $currency = $params['currency']; # Currency Code

  # Client Variables
  $firstname = $params['clientdetails']['firstname'];
  $lastname = $params['clientdetails']['lastname'];
  $email = $params['clientdetails']['email'];
  $address1 = $params['clientdetails']['address1'];
  $address2 = $params['clientdetails']['address2'];
  $city = $params['clientdetails']['city'];
  $state = $params['clientdetails']['state'];
  $postcode = $params['clientdetails']['postcode'];
  $country = $params['clientdetails']['country'];
  $phone = $params['clientdetails']['phonenumber'];

  # System Variables
  $companyname = $params['companyname'];
  $systemurl = $params['systemurl'];
  $currency = $params['currency'];

  $data = array(
    'token'=>$gatewayapi,
    'invoice_id'=>$invoiceid,
    'rupiah'=>$amount,
    'memo'=>$description,
    'callback_url'=>$callbackurl,
    'url_success'=>$redirecturl,
    'url_failed'=>$redirecturl
  );
  $url = 'https://bitbayar.com/api/create_invoice';
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
  $return = curl_exec($ch);
  curl_close($ch);
  $data = json_decode($return);
  if($data->success){
    $paymenturl = $data->payment_url;
    $code = '<br><a href="'.$paymenturl.'"><img src="https://bitbayar.com/images/button/buy-white-small.png"/></a><br>'.$params['instructions'];
    return $code;
    exit;
  }
  else{
    exit('BitBayar API Error: '.$data->error_message);
  }
}

?>
