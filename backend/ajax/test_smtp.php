<?php

require_once 'configure.php';

if (!$managers->access('settings', $manager)) {
    exit();
}

use PHPMailer\PHPMailer\SMTP;

$host     = $settings->smtp_server = $request->post('server');
$port     = $settings->smtp_port   = $request->post('port');
$username = $settings->smtp_user   = $request->post('user');
$password = $settings->smtp_pass   = $request->post('pass');
$result   = [
    'status'  => false,
    'message' => '',
    'trace'   => '',
];
if ($port == 465) {
    // Добавляем протокол, если не указали
    $host = (strpos($host, "ssl://") === false) ? "ssl://".$host : $host;
}

ob_start();
//Create a new SMTP instance
$smtp = new SMTP;
//Enable connection-level debug output
$smtp->do_debug = SMTP::DEBUG_CONNECTION;
//Connect to an SMTP server
if (!$smtp->connect($host, $port)) {
    $result['message'] = 'Connect failed';
}
//Say hello
if (!$smtp->hello(gethostname())) {
    $result['message'] = 'EHLO failed: ' . $smtp->getError()['error'];
}
//Get the list of ESMTP services the server offers
$e = $smtp->getServerExtList();
//If server can do TLS encryption, use it
if (is_array($e) && array_key_exists('STARTTLS', $e)) {
    $tlsok = $smtp->startTLS();
    if (!$tlsok) {
        $result['message'] = 'Failed to start encryption: ' . $smtp->getError()['error'];
    }
    //Repeat EHLO after STARTTLS
    if (!$smtp->hello(gethostname())) {
        $result['message'] = 'EHLO (2) failed: ' . $smtp->getError()['error'];
    }
    //Get new capabilities list, which will usually now include AUTH if it didn't before
    $e = $smtp->getServerExtList();
}
//If server supports authentication, do it (even if no encryption)
if (is_array($e) && array_key_exists('AUTH', $e)) {
    if ($smtp->authenticate($username, $password)) {
        $result['message'] = 'Connected ok!';
        $result['status']  = true;
    } else {
        $result['message'] = 'Authentication failed: ' . $smtp->getError()['error'];
    }
}

//Whatever happened, close the connection.
$smtp->quit(true);

$result['trace'] = nl2br(ob_get_contents());
ob_end_clean();

$response->setContent(json_encode($result), RESPONSE_JSON);
$response->sendContent();
