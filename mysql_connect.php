<?php # mysql_connect.php (PHP 5) - Connects to a MySQL database.

//-----------------------------------------------------------------------------
// For security reasons, the permissions on this file should be set to
// read-only for the owner, with no permissions for the group or others.
//
// Example:
//  $ chmod 400 mysql_connect.php
//
// This file should also be placed outside of the web root directory.
//-----------------------------------------------------------------------------

define ('DB_HOST',''); // Set the hostname of the MySQL server.
define ('DB_NAME',''); // Set the database name.
define ('DB_USER',''); // Set the username.
define ('DB_PASS',''); // Set the password.

$dbc = @mysql_connect(DB_HOST,DB_USER,DB_PASS);

@mysql_select_db(DB_NAME);

?>
