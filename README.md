# outlook-rest-php
A handy tool to work with outlook rest api

## example code is available in the examples directory

#### Installation
    composer require sagar/outlook-php

#### Authenticator
```php
define('APP_ID', '07cdaba2-7865-4cec-8c82-b6d69679c88c');
define('APP_PASSWORD', 'qR7tHeUpDhLXiZhPdXaT5aU');

// redirect uri can be one of the absolute url registered with the app
// if not given or null then it takes current url
$authenticator = new Outlook\Authorizer\Authenticator(APP_ID, APP_PASSWORD, $redirectUri = null);

// to get token from code the below function call should be placed at the top of page
// where redirection is coming back
// the below function simply exchange code and get token from authorization server.
$token = $authenticator->getToken();

if(!$token) {
    //if $token is not available then we get login url
    echo $authenticator->getLoginUrl($scopes = [], $redirectUri = null);
} else {
    // if $token is available it is instance of Outlook\Authorizer\Token
    var_dump($token);
}
```

#### Event Management
```php
define('APP_ID', '07cdaba2-7865-4cec-8c82-b6d69679c88c');
define('APP_PASSWORD', 'qR7tHeUpDhLXiZhPdXaT5aU');

$authenticator = new Outlook\Authorizer\Authenticator(
    APP_ID,
    APP_PASSWORD,
    $redirectUri = "http://localhost/outlook-php/examples/events.php"
);

// create session instance which implements SessionContract
// you can replace session implementation with your class implementing SessionContract
$sessionManager = new \Outlook\Authorizer\Session();

//token can be removed manually
//$sessionManager->remove(); // if need to remove token manually from session

// create event authorizer to get token with appropriate scope
// it accepts authenticator and session instance
$eventAuthorizer = new \Outlook\Events\EventAuthorizer($authenticator, $sessionManager);

// if token is already available in session and not expired
$token = $eventAuthorizer->isAuthenticated();

if (!$token) {
    //if token is not available we create login url with necessary scopes
    echo '<a href='.$eventAuthorizer->getLoginUrl().'>Login</a>';
} else {
    //if we have token we can create eventManager
    // it accepts Outlook\Authorizer\Token as argument
    $eventManager = new \Outlook\Events\EventManager($token);
    
    // get all events returns each item as Event object
    $events = $eventManager->all();

    foreach ($events as $event) {
        echo $event->id. " -> ". $event;
        echo '<br />';
    }
    
    //get single event returns Outlook\Events\Event instance
    $event = $eventManager->get($eventId = 'XXXX');
    var_dump($event);
    
    //create event accepts Outlook\Events\Event instance
    // nested key name must be case sensitive correctly according to their docs.
    // only outer properties will be converted to Study case automatically
    $event = new \Outlook\Events\Event(['subject' => 'Discuss the Calendar REST API']);
    $event->body = ['ContentType' => 'HTML', 'Content' => 'Hello this is test Event'];
    $event->start = ["DateTime" => "2014-02-02T18:00:00", "TimeZone" => "Pacific Standard Time"];
    $event->end = ["DateTime" => "2014-02-02T19:00:00", "TimeZone" => "Pacific Standard Time"];
    $event = $eventManager->create($event);
    var_dump($event);
    
    //update event accepts Outlook\Events\Event instance with valid id
    $event = $eventManager->get($eventId = 'XXXX');
    $event->Body->Content = "new Updated Content";
    $updateEvent = $eventManager->update($event);
    var_dump($updateEvent); // event instance with updated values
    
    //delete event accepts Outlook\Events\Event instance with valid id
    $event = $eventManager->get($eventId = 'XXXX');
    $response = $eventManager->delete($event);
    var_dump($response); // response true or raised exception
```

# Api Documentation
   * [Outlook Rest Api](https://msdn.microsoft.com/en-us/office/office365/api/calendar-rest-operations)

# TODO
   * Add support for sync events
   * Add calendar apis
   * Add contacts apis
   * Add mail apis


# contributions
* pull requests are most welcome.
* please follow psr2 standards for the code and submit your pull request
    with valid breaking/enhancement use case.