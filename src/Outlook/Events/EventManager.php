<?php
/**
 * Author: sagar <sam.coolone70@gmail.com>
 *
 */

namespace Outlook\Events;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Outlook\Authorizer\Token;
use Outlook\Exceptions\Events\EventCallException;

class EventManager
{
    /**
     * @var Token
     */
    protected $token;


    protected $apiClient;

    /**
     * EventManager constructor.
     * @param Token $token
     */
    public function __construct(Token $token)
    {
        $this->token = $token;
        $this->apiClient = new Client([
            'base_uri' => 'https://outlook.office.com/api/v2.0',
            'headers' => [
                "Authorization" => "{$this->token->getTokenType()} {$this->token->getAccessToken()}",
                "Accept: application/json"
            ]
        ]);
    }

    public function getEvents()
    {
        $response = $this->callApi('/me/events', 'get', [
            '$select' => 'Subject,Organizer,Start,End'
        ]);
        return $response;
    }

    protected function callApi($uri = '/me', $method = "get", $params = [])
    {
        $requestOptions = [];
        if (count($params)) {
            if ($method === 'get') {
                $uri .= '?'. $this->buildQuery($params);
            } else {
                $requestOptions['form_params'] = $params;
            }
        }
        $request = new Request($method, $uri);
        try {
            $response = $this->apiClient->send($request, $requestOptions);
            var_dump($response->getBody->getContent());
        } catch (\Exception $e) {
            echo $e->getMessage();
//            throw new EventCallException($e->getMessage());
        }
    }

    /**
     * @param array $params
     * @return string
     */
    protected function buildQuery($params = [])
    {
        $queryString = '';
        foreach ($params as $key => $value) {
            $queryString .= "$key=$value&";
        }
        return rtrim($queryString, '&');
    }
}
