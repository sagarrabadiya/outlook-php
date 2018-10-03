<?php
/**
 * Author: sagar <sam.coolone70@gmail.com>
 *
 */

require_once '../vendor/autoload.php';

define('APP_ID', '07cdaba2-7865-4cec-8c82-b6d69679c88c');
define('APP_PASSWORD', 'qR7tHeUpDhLXiZhPdXaT5aU');

$authenticator = new Outlook\Authorizer\Authenticator(
    APP_ID,
    APP_PASSWORD,
    $redirectUri = "http://localhost/playground/outlook-api/examples/events.php"
);

$sessionManager = new \Outlook\Authorizer\Session();
//$sessionManager->remove(); // if need to remove token manually from session

$eventAuthorizer = new \Outlook\Events\EventAuthorizer($authenticator, $sessionManager);

$token = $eventAuthorizer->isAuthenticated();
if (!$token) {
    echo '<a href='.$eventAuthorizer->getLoginUrl().'>Login</a>';
} else {
    if (isset($_GET['refresh_token']) && $_GET['refresh_token']) {
        $newToken = $eventAuthorizer->renewToken();
        echo 'Refresh Token: <br />';
        var_dump($newToken);
        die();
    }
    $eventManager = new \Outlook\Events\EventManager($token);

    // get all events returns each item as Event object
    $events = $eventManager->all();

    foreach ($events as $event) {
        echo $event->id. " -> ". $event;
        echo '<br />';
    }

    // get single event with id
    $event = $eventManager->get($eventId = 'AQMkADAwATM0MDAAMS1mYWJlLTc2ZDMtMDACLTAwCgBGAAADxtSZX36Ug0qmRAm-Pups1QcAebOFJWlMG0Oc5CRjAVwMrgAAAgENAAAAebOFJWlMG0Oc5CRjAVwMrgAAAiDBAAAA');

    //create event
    // nested key name must be case sensitive correctly according to their docs.
    // only outer properties will be converted to Study case automatically
    $event = new \Outlook\Events\Event(['subject' => 'Discuss the Calendar REST API']);
    $event->body = ['ContentType' => 'HTML', 'Content' => 'Hello this is test Event'];
    $event->start = ["DateTime" => "2014-02-02T18:00:00", "TimeZone" => "Pacific Standard Time"];
    $event->end = ["DateTime" => "2014-02-02T19:00:00", "TimeZone" => "Pacific Standard Time"];
    $event = $eventManager->create($event);
//    var_dump($event);

    // make sure the properties are in exact same case as defiend in the docs.
//    $event = $eventManager->get('AQMkADAwATM0MDAAMS1mYWJlLTc2ZDMtMDACLTAwCgBGAAADxtSZX36Ug0qmRAm-Pups1QcAebOFJWlMG0Oc5CRjAVwMrgAAAgENAAAAebOFJWlMG0Oc5CRjAVwMrgAAAAJckAEAAAA=');
    $event->Body->Content = "new Updated Content";
    $updateEvent = $eventManager->update($event);
    var_dump($updateEvent); // event instance with updated values

//    $event = $eventManager->get('AQMkADAwATM0MDAAMS1mYWJlLTc2ZDMtMDACLTAwCgBGAAADxtSZX36Ug0qmRAm-Pups1QcAebOFJWlMG0Oc5CRjAVwMrgAAAgENAAAAebOFJWlMG0Oc5CRjAVwMrgAAAAJckAIAAAA=');
    $response = $eventManager->delete($event);
    var_dump($response); // response true or raised exception

    echo '<a href="?refresh_token=true">Renew Token</a>';
}
