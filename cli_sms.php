#!/usr/bin/php
<?php
// Version 0.7

// The service you're using. Choices include clickatell, budgetsms, nexmo and clockwork
$service = "";

// The API key.
$apikey = "";

// Username and password. With BudgetSMS use userid as password. Username not needed for Nexmo or Clockwork
$user = "";
$password = "";

// The sender name / number. This has to conform to your service provider's regulations.
$from = "";

// if debug is true, log files will be generated
$debug = false;

// Where should this script log to? The directory must already exist.
$logfilelocation = "/var/log/zabbix/zabbix_sms.log";

/******************************************************************************************
  !! Do not change anything below this line unless you _really_ know what you are doing !!
 ******************************************************************************************/

if (count($argv)<3) {
  die ("Usage: ".$argv[0]." recipientmobilenumber \"subject\" \"message\"\n");
}

if ( $debug ) {
  file_put_contents($logfilelocation, date("Ymd:His")." ".$service.": ".serialize($argv)."\r\n", FILE_APPEND);
}

$to         = $argv[1];
$subject    = $argv[2];
$message    = $argv[3];

$text = $subject.": ".$message;

switch ($service) {
  case 'clickatell':
    $apiargs = array(
      "api_id"    => $apikey,
      "user"      => $user,
      "password"  => $password,
      "to"        => $to,
      "text"      => $text,
      "from"      => $from,
      "concat"    => '3',
    );
    $baseurl    = "https://api.clickatell.com/http/sendmsg";
  break;
  case 'budgetsms':
    $apiargs = array(
      "handle"    => $apikey,
      "username"  => $user,
      "userid"    => $password,
      "to"        => $to,
      "msg"       => $text,
      "from"      => $from,
    );
    $baseurl = 'https://www.budgetsms.net/api/sendsms';
  break;
  case 'nexmo':
    $apiargs = array(
      "username"  => $apikey,
      "password"  => $password,
      "to"        => $to,
      "text"      => $text,
      "from"      => $from,
    );
    $baseurl = 'https://rest.nexmo.com/sms/json';
  break;
  case 'clockwork':
    $apiargs = array(
      "key"       => $apikey,
      "to"        => $to,
      "content"   => $text,
      "from"      => $from,
    );
    $baseurl = 'https://api.clockworksms.com/http/send';
  break;
}

$params    = "";
foreach ($apiargs as $k=>$v) {
  if ( $params != "" ) {
    $params .= "&";
  }
  $params .= $k."=".urlencode($v);
}

$url = $baseurl . '?' . $params;

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($curl);

if ( $result === false ) {
  file_put_contents($logfilelocation, date("Ymd:His")." ".$service."-error: ".curl_error($curl)."\r\n", FILE_APPEND);
  die(curl_error($curl)."\n");
} 
else {
  if ( $debug || $result != 100 ) {
    file_put_contents($logfilelocation, date("Ymd:His")." ".$service."-answer: ".$result."\r\n", FILE_APPEND);
  }
}