<?php
/**
 * Author: sagar <sam.coolone70@gmail.com>
 *
 */


require_once '../vendor/autoload.php';

define('CLIEND_ID', '07cdaba2-7865-4cec-8c82-b6d69679c88c');
define('CLIEND_SECRET', 'qR7tHeUpDhLXiZhPdXaT5aU');

$authenticator = new Outlook\Authorizer\Authenticator(CLIEND_ID, CLIEND_SECRET);

$token = $authenticator->getToken();

if (!$token) {
    echo $authenticator->getLoginUrl();
} else {
    var_dump($token);
}
