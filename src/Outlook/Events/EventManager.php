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
                    "Authorization" => "{$this->token->getTokenType()} {$this->token->getAccessToken()}",
                    "Content-Type" => "application/json"
                ]
            ]
        );
    }

    /**
     * @return array|Event
     * @throws RestApiException
     */
    public function all()
    {
        $response = $this->api->call('/me/events', 'get');
        if (isset($response->error)) {
            throw new RestApiException($response->error->message, $response->statusCode);
        }
        return $this->parseEvents($response->value);
    }

    /**
     * @param null $eventId
     * @return array|Event
     * @throws RestApiException
     */
    public function get($eventId = null)
    {
        if (is_null($eventId)) {
            throw new RestApiException('Event Id required to get single event');
        }
        $response = $this->api->call("/me/events/{$eventId}", 'get');
        return $this->parseEvents($response);
    }

    /**
     * @param Event $event
     * @return array|Event
     */
    public function create(Event $event)
    {
        if ($event->id) {
            return $this->get($event->id);
        }

        $response = $this->api->call('/me/events', 'post', $event->toParams());
        return $this->parseEvents($response);
    }

    /**
     * @param Event $event
     * @return array|Event
     * @throws RestApiException
     */
    public function update(Event $event)
    {
        if (! $event->id) {
            throw new RestApiException('Event id required to update the event');
        }

        $response = $this->api->call("/me/events/{$event->id}", 'patch', $event->toParams());
        return $this->parseEvents($response);
    }

    /**
     * @param Event $event
     * @return bool
     * @throws RestApiException
     */
    public function delete(Event $event)
    {
        if (! $event->id) {
            throw new RestApiException('Event id required to delete the event');
        }

        $this->api->call("/me/events/{$event->id}", 'delete');
        return true;
    }

    /**
     * @param array $responseEvents
     * @return array|Event
     */
    public function parseEvents($responseEvents = [])
    {
        // if direct event is given then return the event object
        if (is_object($responseEvents)) {
            return new Event((array) $responseEvents);
        }
        $events = [];
        foreach ($responseEvents as $event) {
            $event = new Event((array) $event);
            $events[] = new Event((array) $event);
        }
        return $events;
    }
}
