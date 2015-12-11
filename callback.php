<?php # callback.php (PHP 5) - Evaluates the Unity Ads S2S Redeem Callback.

//-----------------------------------------------------------------------------
// Each callback should contain the following parameters in the query string:
//  pid  -- product/game ID specified as part of the base callback URL
//  sid  -- user ID set through the Unity Ads SDK
//  oid  -- offer ID unique to each callback
//  hmac -- HDMAC-MD5 hash of the query string
//-----------------------------------------------------------------------------

$public_ips_file = 'public_ips.json';

$header_200 = $_SERVER['SERVER_PROTOCOL'].' 200 OK';
$header_400 = $_SERVER['SERVER_PROTOCOL'].' 400 Bad Request';
$header_403 = $_SERVER['SERVER_PROTOCOL'].' 403 Forbidden';
$header_500 = $_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error';
$header = $header_500;

$email = 'alerts@mydomain.com'; // Update value with a valid email address.
$subject = 'S2S Redeem Callback Alert';
if (isset($_GET['pid'])) $subject .= ' for '.$_GET['pid'];
$mailheaders = 'From: noreply@mydomain.com'."\r\n".
	'Reply-To: noreply@mydomain.com'."\r\n".
	'X-Mailer: PHP/'.phpversion();
$message = '';

function cidr_match ($addr, $prefix)
{
	list ($subnet, $bits) = explode('/', $prefix);
	$addr = ip2long($addr);
	$subnet = ip2long($subnet);
	$mask = -1 << (32 - $bits);
	$subnet &= $mask;
	return ($addr & $mask) == $subnet;
}

function is_valid_addr ($addr)
{
	global $public_ips_file;
	$public_ips = json_decode(file_get_contents($public_ips_file));
	foreach ($public_ips->prefixes as $prefix)
	{
		if (cidr_match($addr,$prefix->ip_prefix)) return true;
	}
	return false;
}

function generate_hash ($params, $secret)
{
	ksort($params);

	$s = '';
	foreach ($params as $key => $value)
	{
		$s .= "$key=$value,";
	}
	$s = substr($s, 0, -1);

	return hash_hmac('md5', $s, $secret);;
}

function get_secret ($pid)
{
	$secret = '';
	$query = "SELECT secret FROM games WHERE pid = '$pid'";
	$result = @mysql_query($query);
	if ($result != false)
	{
		$row = mysql_fetch_assoc($result);
		$secret = $row['secret'];
	}
	return $secret;
}

function is_valid_pid ($pid)
{
	$query = "SELECT pid FROM games WHERE pid = '$pid'";
	$result = @mysql_query($query);
	return ($result != false && mysql_num_rows($result) > 0);
}

function is_unique_oid ($oid)
{
	$query = "SELECT oid FROM callbacks WHERE oid = $oid";
	$result = @mysql_query($query);
	return ($result == false || mysql_num_rows($result) == 0);
}

function save_callback ($params, $hash)
{
	$query = "INSERT INTO callbacks (oid, sid, pid, hmac, datetime) ".
		"VALUES ('".$params['oid']."', '".$params['sid']."', '".
		$params['pid']."', '".$hash."', UTC_TIMESTAMP())";
	return @mysql_query($query);
}

function get_request_params_message ($params)
{
	$msg = "Request Parameters:";
	foreach ($params as $key => $value)
	{
		$msg .= "\r\n  $key = $value";
	}
	return $msg;
}

function get_server_info_message ()
{
	return "Server Info:".
		"\r\n  Remote IP: ".$_SERVER['REMOTE_ADDR'].
		"\r\n  User Agent: ".$_SERVER['HTTP_USER_AGENT'].
		"\r\n  Query String: ".$_SERVER['QUERY_STRING'];
}

if (count($_GET) == 0) // Accept 0 parameters as a test.
{
	$header = $header_200;
	$message = "Response: 200 - Test OK.";
}
else if (empty($_GET['sid'])) // Validate sid parameter.
{
	$header = $header_400;
	$message = "Response: 400 - 'sid' value is not set.";
}
else if (empty($_GET['pid'])) // Verify pid parameter exists and is set.
{
	$header = $header_400;
	$message = "Response: 400 - 'pid' " .
		((array_key_exists('pid',$_GET)) ?
			"value is not set." : "parameter is missing.");
}
else if (!file_exists($public_ips_file)) // Verify file exists.
{
	$header = $header_500;
	$message = "Response: 500 - File not found: " . $public_ips_file;
}
else if (!is_valid_addr($_SERVER['REMOTE_ADDR'])) // Validate remote IP.
{
	$header = $header_403;
	$message = "Response: 403 - Request orignated from an invalid address.";
}
else
{
	require_once('mysql_connect.php');

	if ($dbc == false) // Verify DB connection.
	{
		$header = $header_500;
		$message = "Response: 500 - Failed to connect to DB.";
	}
	else if (!is_valid_pid($_GET['pid'])) // Validate pid parameter value.
	{
		$header = $header_400;
		$message = "Response: 400 - 'pid' value is invalid.";
	}
	else
	{
		$hash = $_GET['hmac'];
		unset($_GET['hmac']);

		$secret = get_secret($_GET['pid']);

		if ($hash != generate_hash($_GET,$secret)) // Validate signature.
		{
			$header = $header_400;
			$message = "Response: 400 - Signatures did not match.";
		}
		else if (!is_unique_oid($_GET['oid'])) // Validate oid parameter.
		{
			$header = $header_400;
			$message = "Response: 400 - 'oid' value is not unique.";
		}
		else if (!save_callback($_GET,$hash)) // Attempt to save.
		{
			$header = $header_500;
			$message = "Response: 500 - Failed to save callback data.";
		}
		else // Everything OK.
		{
			$header = $header_200;
			$message = "Response: 200 - OK";
		}
	}

	mysql_close();
	unset($dbc);
}

$message .= "\r\n";

header($header);
print($message);

if ($header != $header_200) // Send email alert with details.
{
	if (empty($email) || $email == 'alerts@mydomain.com')
	{
		print("Failed to send email alert! Address is invalid.");
	}
	else
	{
		$message .= "\r\n".get_request_params_message($_GET)."\r\n";
		$message .= "\r\n".get_server_info_message()."\r\n";

		mail($email,$subject,$message,$mailheaders);
	}
}

exit();

?>
