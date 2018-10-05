<?php
/**
 * Author: sagar <sam.coolone70@gmail.com>
 *
 */

namespace Outlook\Calendars;

use Outlook\ApiRequester\Client;
use Outlook\Authorizer\Token;
use Outlook\Exceptions\Calenders\RestApiException;

class CalendarManager
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
     * @return array|Calendar
     * @throws RestApiException
     */
    public function all()
    {
        $response = $this->api->call('/me/calendarview?startDateTime=2017-01-01T00:00:00&endDateTime=2017-02-01T00:00:00&$select=Subject,Organizer,Start,End', 'get');
        if (isset($response->error)) {
            throw new RestApiException($response->error->message, $response->statusCode);
        }
        return $this->parseEvents($response->value);
    }

    /**
     * @return array|Calendar
     * @throws RestApiException
     */
    public function getAllCalendars()
    {
        $response = $this->api->call('/me/calendars', 'get');
        if (isset($response->error)) {
            throw new RestApiException($response->error->message, $response->statusCode);
        }
        return $this->parseEvents($response->value);
    }

    /**
     * @param null $eventId
     * @return array|Calendar
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
     * @param Calendar $event
     * @return array|Calendar
     */
    public function create(Calendar $event)
    {
        if ($event->id) {
            return $this->get($event->id);
        }

        $response = $this->api->call('/me/events', 'post', $event->toParams());
        return $this->parseEvents($response);
    }

    /**
     * @param Calendar $event
     * @return array|Calendar
     * @throws RestApiException
     */
    public function update(Calendar $event)
    {
        if (!$event->id) {
            throw new RestApiException('Event id required to update the event');
        }

        $response = $this->api->call("/me/events/{$event->id}", 'patch', $event->toParams());
        return $this->parseEvents($response);
    }

    /**
     * @param Calendar $event
     * @return bool
     * @throws RestApiException
     */
    public function delete(Calendar $event)
    {
        if (!$event->id) {
            throw new RestApiException('Event id required to delete the event');
        }

        $this->api->call("/me/events/{$event->id}", 'delete');
        return true;
    }

    /**
     * @param array $responseEvents
     * @return array|Calendar
     */
    public function parseEvents($responseEvents = [])
    {
        // if direct event is given then return the event object
        if (is_object($responseEvents)) {
            return new Calendar((array)$responseEvents);
        }
        $events = [];
        foreach ($responseEvents as $event) {
            $event = new Calendar((array)$event);
            $events[] = new Calendar((array)$event);
        }
        return $events;
    }
}
