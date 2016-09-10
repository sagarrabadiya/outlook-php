<?php
/**
 * Author: sagar <sam.coolone70@gmail.com>
 *
 */

namespace Outlook\Events;

use Outlook\ApiRequester\Client;
use Outlook\Authorizer\Token;
use Outlook\Exceptions\Events\RestApiException;

class EventManager
{
    /**
     * @var Token
     */
    protected $token;

    /**
     * @var Client
     */
    protected $api;

    /**
     * EventManager constructor.
     * @param Token $token
     */
    public function __construct(Token $token)
    {
        $this->token = $token;
        $this->api = new Client(
            $this->token,
            'https://outlook.office.com/api/v2.0',
            [
                "headers" => [
                    "Authorization" => "{$this->token->getTokenType()} {$this->token->getAccessToken()}"
                ]
            ]
        );
    }

    /**
     * @return array|Event
     * @throws RestApiException
     */
    public function getEvents()
    {
        $response = $this->api->call('/me/events', 'get');
        if (isset($response->error)) {
            throw new RestApiException($response->error->message, $response->statusCode);
        }
        return $this->parseEvents($response->value);
    }

    /**
     * @param array $responseEvents
     * @return array|Event
     */
    public function parseEvents($responseEvents = [])
    {
        $events = [];
        foreach ($responseEvents as $event) {
            $event = new Event((array) $event);
            $events[] = new Event((array) $event);
        }
        return $events;
    }
}
