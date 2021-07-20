<?php

defined('BASEPATH') OR exit('No direct script access allowed');

//===from-goquestmedia-start===
$config = array(
    'protocol' => 'smtp', // 'mail', 'sendmail', or 'smtp'
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 465,
    // 'smtp_user' => 'AKIA47L22LCZXZZCBS4Q',
    // 'smtp_pass' => 'BDlOdbNSu4AM6O5VfY1FJT0jFyTBj2p/HHT66aHJtJWF',
    'smtp_user' => 'tech@goquestmedia.com',
    'smtp_pass' => '42ncDUB$ekXq',
    'smtp_crypto' => 'tls', //can be 'ssl' or 'tls' for example
    'mailtype' => 'html', //plaintext 'text' mails or 'html'
    'smtp_timeout' => '4', //in seconds
    'charset' => 'utf-8',
    'wordwrap' => TRUE,
    'starttls' => true,
    'newline' => "\r\n"
);
//===from-goquestmedia-end===