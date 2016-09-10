<?php
/**
 * Author: sagar <sam.coolone70@gmail.com>
 *
 */

require_once '../vendor/autoload.php';
require_once 'errorHandler.php'; // whoops page to show error nicely its optional

define('APP_ID', '07cdaba2-7865-4cec-8c82-b6d69679c88c');
define('APP_PASSWORD', 'qR7tHeUpDhLXiZhPdXaT5aU');

$authenticator = new Outlook\Authorizer\Authenticator(
    APP_ID,
    APP_PASSWORD,
    $redirectUri = "http://localhost/playground/outlook-api/examples/events.php"
);

$sessionManager = new \Outlook\Authorizer\Session();
//$sessionManager->remove();

$eventAuthorizer = new \Outlook\Events\EventAuthorizer($authenticator, $sessionManager);

$token = $eventAuthorizer->isAuthenticated();
if (!$token) {
    echo '<a href='.$eventAuthorizer->getLoginUrl().'>Login</a>';
} else {
    $eventManager = new \Outlook\Events\EventManager($token);
    var_dump($eventManager->getEvents());
}
