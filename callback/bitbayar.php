<?php

/*
 * WHMCS module to receive bitcoin payment using BitBayar (Bitcoin payment processor)
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
 * BitBayar - Indonesian Bitcoin payment processor
 * Website: https://bitbayar.com
 */

# Required File Includes
include("../../../dbconnect.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

$gatewaymodule = "bitbayar";

$GATEWAY = getGatewayVariables($gatewaymodule);
$gatewayapi = $GATEWAY['apibitbayar'];
if (!$GATEWAY["type"]) die("Module Not Activated"); # Checks gateway module is active before accepting callback

$invoiceid = $_POST["invoice_id"];
$transid = $_POST["id"];
$amount = $_POST["rp"];
$fee = 0;

$invoiceid = checkCbInvoiceID($invoiceid,$GATEWAY["name"]); # Checks invoice ID is a valid invoice number or ends processing

checkCbTransID($transid); # Checks transaction number isn't already in the database and ends processing if it does

if ($transid) {

    $data = array(
      'token'=>$gatewayapi,
      'id'=>$transid
    );
    $url = 'https://bitbayar.com/api/check_invoice';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $return = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($return);

    if ($data->status == "paid") { # make sure payment is valid
      # Successful
      addInvoicePayment($invoiceid,$transid,$amount,$fee,$gatewaymodule); # Apply Payment to Invoice: invoiceid, transactionid, amount paid, fees, modulename
      logTransaction($GATEWAY["name"],$_POST,"Successful"); # Save to Gateway Log: name, data array, status
    }
} else {
	# Unsuccessful
    logTransaction($GATEWAY["name"],$_POST,"Unsuccessful"); # Save to Gateway Log: name, data array, status
}

?>
