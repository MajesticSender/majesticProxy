<?php
/*
 * Simple proxy script for Majestic Sender
 * Before using this script, please provide the server outgoing IP address to the Majestic Sender platform.
 *
 *
 * @author Ruben Harms <info@rubenharms.nl>
 * @link http://www.rubenharms.nl
 * @link https://www.github.com/RubenHarms
 * @package rsa/majestic-proxy
 * @version 0.01
 */

$config['baseUrl'] = 'http://tracking.majesticsender.com';

#### DO NOT EDIT BELOW THIS LINE ###

if(!is_callable('curl_init'))
    exit('Majestic proxy requires cURL, see: <a href="http://php.net/manual/en/curl.installation.php">http://php.net/manual/en/curl.installation.php</a> for the installation.');

if ($_SERVER['REQUEST_URI'] == '/')
    exit('Majestic proxy 0.01');

$baseUrl = $config['baseUrl'];
$url = $baseUrl . $_SERVER['REQUEST_URI'];
$ch = curl_init($url);

if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
}

$cookie = array();
foreach ($_COOKIE as $key => $value)
    $cookie[] = $key . '=' . $value;

$cookie = implode('; ', $cookie);

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'X_FORWARDED_FOR: ' . $_SERVER['REMOTE_ADDR'],
    'X_FORWARDED_HOST: ' . $_SERVER['HTTP_HOST'],
    'X_FORWARDED_PROTO: ' . $_SERVER['SERVER_PROTOCOL'],
    'X_FORWARDED_PORT: ' . $_SERVER['SERVER_PORT']
));

curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, $_GET['user_agent'] ? $_GET['user_agent'] : $_SERVER['HTTP_USER_AGENT']);
list($header, $contents) = preg_split('/([\r\n][\r\n])\\1/', curl_exec($ch), 2);

$status = curl_getinfo($ch);

curl_close($ch);

$header_text = preg_split('/[\r\n]+/', $header);

foreach ($header_text as $header) {
    if (preg_match('/^(?:Content-Type|Content-Language|Set-Cookie|Location):/i', $header)) {
        header($header);
    }
}

print $contents;