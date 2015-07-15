Unity Ads S2S Callback Example
==============================

A server-side example for handling [Server-to-Server Redeem Callbacks](http://unityads.unity3d.com/help/Documentation%20for%20Publishers/Server-to-server-Redeem-Callbacks) for [Unity Ads](http://unityads.unity3d.com).

Plese keep in mind that this is not a complete example. It only works as far as testing callback functionality between the Unity Ads server and your server. It can however be used as a basis for writing your own callback scripts. See more details in the section for [customizing the reward script](#customizing-the-reward-script).

## Setup Instructions

### [Creating a secret script](id:secret)
1.	Open [**secrets/12345.inc**](callback/secrets/12345.inc) in a text editor.
1.	Update the [variable `$secret`](callback/secrets/12345.inc#L2) with the secret md5 hash associated with your game ID. If you don't already know your secret, please contact Unity Ads Support at <unityads-support@unity3d.com>.
1.	Then **Save As** using your game ID as the file name to create a new file with your changes. 

### [Creating a callback script](id:callback)
1.	Open [**12345.php**](callback/12345.php) in a text editor and update the [`include` path](callback/12345.php#L2) to your secret script.
1.	Then **Save As** using your game ID as the file name to create a new file with your changes.

### [Customizing the reward script](id:reward)
1.	Open [**reward.inc**](callback/reward.inc) in a text editor.
1.	If you would like to receive alerts by email, update the [variable `$email`](callback/reward.inc#L4) with the email address you would like alerts sent to. Otherwise, leave the string empty.
	
	**Note:** This assumes your hosting provider allows sending email from PHP scripts.
	
1.	To actually validate and reward users, you still need to write the logic for the following functions:
	*	[`bool check_duplicate_orders ( string $oid )`](callback/reward.inc#L23-L28)
	*	[`bool give_item_to_player ( string $sid, string $product )`](callback/reward.inc#L30-L35)
	*	[`bool save_order_number ( string $oid )`](callback/reward.inc#L37-L42)

1.	Then **Save** and **Quit**.

### [Publishing to your web server](id:publish)

1.	Transfer the contents of the "callbacks" directory to your web server.

1.	Set permissions for the "secrets" directory to only allow user ownership access:

		$ chmod 700 secrets
	
	**Important Note:** As a security precaution, the "secrets" directory should be placed outside of the web visible file structure. Move the "secrets" directory to the parent directory of web visible root directory. Then update the relative path references to it within the callback scripts.

### [Testing the callback script](id:test)
1.	Open a browser window.
1.	Enter the URL for your callback script into the address bar of the browser and press enter.
1.	You should see "Response: 202 - Test OK" on an otherwise blank page, indicating that the script is both publicly visible and working properly.

If the test is successful, contact Unity Ads Support at <unityads-support@unity3d.com> and request to update the callback URL for your game ID with the URL to your callback script.
