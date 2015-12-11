<?php # update_public_ips.php (PHP 5) - Creates a local copy of public_ips.json

//-----------------------------------------------------------------------------
// For efficiency and reliability reasons, a local copy of public_ips.json
// is used to validate the origin of Unity Ads S2S Redeem Callbacks.
//
// A cron job should be configured to run this script once every 24 hrs.
//
// Example:
//  50 0 * * * /usr/local/php5/bin/php "$HOME/html/update_public_ips.php"
//-----------------------------------------------------------------------------

$remote_file = 'http://static.applifier.com/public_ips.json';
$local_file = dirname(__FILE__).'/public_ips.json';

$data = file_get_contents($remote_file);

try { file_put_contents($local_file,$data); }
catch (Exception $e)
{
	exit('Caught exception: '.$e->getMessage()."\r\n");
}

exit();

?>
