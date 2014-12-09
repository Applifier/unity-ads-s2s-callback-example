<?php

// Specify an email address to receive alerts at.
$email = 'nikkolai@unity3d.com';

function generate_hash ($params, $secret)
{
	// All parameters are always checked in alphabetical order.
	ksort($params);
	$s = '';

	foreach ($params as $key => $value)
	{
		$s .= "$key=$value,";
	}

	$s = substr($s, 0, -1);
	$hash = hash_hmac('md5', $s, $secret);

	return $hash;
}

function check_duplicate_orders ($oid)
{
	$result = false;
	// TODO: Was order ID previously used? Add your code here to handle it.
	return $result;
}

function give_item_to_player ($sid, $product)
{
	$result = false;
	// TODO: Was player rewarded? Add your code here to handle it.
	return $result;
}

function save_order_number ($oid)
{
	$result = false;
	// TODO: Was order ID saved? Add your code here to handle it.
	return $result;
}

function send_alert_email ($message)
{
	global $email, $product;
	if (empty($email)) return;
	$subject = 'Unity Ads Result Callback Alert';
	$message = "Product: $product \n".$message.
		"\n\nGET request parameters:";
	foreach($_GET as $key => $value)
	{
		$message .= "\n - $key equals $value";
	}
	$message .= "\n\nRemote IP: ".$_SERVER['REMOTE_ADDR'];
	$message .= "\nUser Agent: ".$_SERVER['HTTP_USER_AGENT'];
	mail($email, $subject, $message);
}

$hash = $_GET['hmac'];
unset($_GET['hmac']);

// Insert here the shared hash key.
$signature = generate_hash($_GET, $secret);

error_log("req hmac ".$hash);
error_log("sig hmac ".$signature);

// Accept without parameters as a test.
if (count($_GET) == 0)
{
	$message = "Response: 202 - Test OK";
	if ($testMode) send_alert_email($message);
	header('HTTP/1.1 202 Accepted');
	echo $message;
	exit;
}

// Check signature.
if ($hash != $signature)
{
	$message = "Response: 403 - Signature did not match";
	if ($testMode) send_alert_email($message);
	header('HTTP/1.1 403 Forbidden');
	echo $message;
	exit;
}

// Check duplicate orders.
if (check_duplicate_orders($_GET['oid']))
{
	$message = "Response: 403 - Dupliate order";
	if ($testMode) send_alert_email($message);
	header('HTTP/1.1 403 Forbidden');
	echo $message;
	exit;
}

// Give the player the item and check that it succeeds.
if (!give_item_to_player($_GET['sid'], $pid))
{
	$message = "Response: 500 - Failed to give item to the player";
	if ($testMode) send_alert_email($message);
	header('HTTP/1.1 500 Internal Server Error');
	echo $message;
	exit;
}

// Save the order ID for dublicate checking.
if (save_order_number($_GET['oid']))
{
	$message = "Response: 500 - Order ID saving failed; user granted item";
	if ($testMode) send_alert_email($message);
	header('HTTP/1.1 500 Internal Server Error');
	echo $message;
	exit;
}

// Everything OK.
$message = "Response: 200 - OK";
if ($testMode) send_alert_email($message);
header('HTTP/1.1 200 OK');
echo $message;

?>
