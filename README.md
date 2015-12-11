# Unity Ads S2S Callback Example

A server-side example for handling [Server-to-Server (S2S) Redeem Callbacks](http://unityads.unity3d.com/help/Documentation%20for%20Publishers/Server-to-server-Redeem-Callbacks) from [Unity Ads](http://unityads.unity3d.com) using PHP and MySQL.

>_**Please note:** The use of S2S Redeem Callbacks are not necessary to reward users for watching video ads; users can be rewarded through client-side callback methods triggered by the Unity Ads SDK._
>
> _Additionally, we highly recommend using the client-side callback methods for rewarding users, while using S2S Redeem Callbacks as a sanity check to prevent users from abusing the reward system._
>
> _Please be aware that relying solely on S2S Redeem Callbacks for rewarding players has the potential to negatively affect the user experience if any latencies occur during the process._

Rewarding users through S2S Redeem Callbacks is a two-stage process:

1. Validate and store the parameters of the callback (in-bound).
2. Reward the user and notify the client (out-bound).

This example covers the first stage of this process.

## Outline

* [Setting the Callback URL](#setting-the-callback-url)
* [Configuring the Database](#configuring-the-database)
  * [The `games` Table](#the-games-table)
  * [The `callbacks` Table](#the-callbacks-table)
* [Configuring the Scripts](#configuring-the-scripts)
  * [The Callback Script](#the-callback-script)
  * [The Connect Script](#the-connect-script)
  * [The Update Script](#the-update-script)
* [Basic Testing](#basic-testing)

## Setting the Callback URL

The [Unity Ads dashboard](http://dashboard.unityads.unity3d.com) does not currently provide a way for you to configure the S2S Redeem Callback URL. To set the callback URL, you will need to submit a request to [Unity Ads Support](mailto:unityads-support@unity3d.com).

> Unity Ads only supports one callback URL per game profile.

When you contact support, please provide the following information:

* Your developer ID
* The game ID of each game profile
* The base callback URL for each game ID

The base callback URL should consist of two parts:

1. The URL where [callback.php](callback.php) will be hosted on your server
2. The query string containing a `pid` parameter and value

```
http://mydomain.com/callback.php?pid=12345
```

The `pid` parameter is equivalent to your game ID. The callback script uses it to determine which _secret_ is used to sign the query string of the callback.

> The _secret_ is provided to you by Unity Ads Support when you request to have your base callback URL set. The value of the _secret_ is unique to each game profile.

The `oid`, `sid`, and `hmac` parameters are added to the query string of the base callback URL when the callback is triggered.

[⇧ Back to top](#unity-ads-s2s-callback-example)

## Configuring the Database

For the purposes of this example, we need to configure two tables in MySQL.

### The `games` Table

This table contains information specific to each game profile.


```
mysql> SHOW COLUMNS FROM games;
+----------+-----------------------+------+-----+---------+-------+
| Field    | Type                  | Null | Key | Default | Extra |
+----------+-----------------------+------+-----+---------+-------+
| pid      | varchar(8)            | NO   | PRI | NULL    |       |
| name     | varchar(32)           | NO   |     | NULL    |       |
| platform | enum('ios','android') | NO   |     | NULL    |       |
| secret   | varchar(32)           | NO   |     | NULL    |       |
+----------+-----------------------+------+-----+---------+-------+
4 rows in set (0.02 sec)
```

### The `callbacks` Table

This table contains a record of each valid callback received.

```
mysql> SHOW COLUMNS FROM callbacks;
+----------+-------------+------+-----+---------------------+-------+
| Field    | Type        | Null | Key | Default             | Extra |
+----------+-------------+------+-----+---------------------+-------+
| oid      | varchar(16) | NO   | PRI | NULL                |       |
| sid      | varchar(24) | NO   |     | NULL                |       |
| pid      | varchar(8)  | NO   |     | NULL                |       |
| hmac     | char(32)    | NO   |     | NULL                |       |
| datetime | datetime    | NO   |     | 0000-00-00 00:00:00 |       |
+----------+-------------+------+-----+---------------------+-------+
5 rows in set (0.03 sec)
```

[⇧ Back to top](#unity-ads-s2s-callback-example)

## Configuring the Scripts

### The Callback Script

### The Connect Script

### The Update Script

[⇧ Back to top](#unity-ads-s2s-callback-example)

## Basic Testing

The following steps can be used to verify that the callback script is both publicly accessible and working properly.

1. Open a new browser window
1. Enter the URL to your callback script without any parameters
```
http://mydomain.com/callback.php
```
1. The following text should appear:
```
Response: 202 - Test OK
```

[⇧ Back to top](#unity-ads-s2s-callback-example)
