<?php namespace Outlook\Authorizer;

use GuzzleHttp\Client;
use Outlook\Exceptions\Authorizer\ClientException;
use Outlook\Exceptions\Authorizer\TokenException;

/**
 * Class Authenticator
 * @package Outlook
 */
class Authenticator
{
    /**
     * @var null
     */
    private $clientId;

    /**
     * @var null
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $authority = "https://login.microsoftonline.com";

    /**
     * @var string
     */
    private $tokenUrl = "/common/oauth2/v2.0/token";

    /**
     * @var array
     */
    private $scopes = [];

    /**
     * @var string
     */
    private $redirectUri;


    /**
     * Authenticator constructor.
     * @param null $clientId
     * @param null $clientSecret
     * @param null $redirectUri
     * @throws ClientException
     */
    public function __construct($clientId = null, $clientSecret = null, $redirectUri = null)
    {
        if (is_null($clientId) || is_null($clientSecret)) {
            throw new ClientException("Client id and client secret is required for outlook!");
        }

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        if (is_null($redirectUri)) {
            $this->redirectUri = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
            // we clean up redirect uri and remove ?code=XXX and other query string params
            $this->redirectUri = str_replace("?{$_SERVER['QUERY_STRING']}", "", $this->redirectUri);
        }
    }

    /**
     * @param array $scopes
     * @param string $redirectUri
     * @return string
     */
    public function getLoginUrl($scopes = [], $redirectUri = null)
    {
        if (!is_null($redirectUri)) {
            $this->redirectUri = $redirectUri;
        }
        $this->scopes = $this->formatScopes(array_merge($scopes, ['openid', 'offline_access']));
        return $this->authority.sprintf(
            $this->getAuthorizeUrl(),
            $this->clientId,
            urlencode($this->redirectUri),
            $this->scopes
        );
    }

    /**
     * @return bool
     * @throws TokenException
     */
    public function getToken()
    {
        $grantType = 'authorization_code';
        $code = (isset($_GET['code'])) ? $_GET['code'] : null;
        if ($code) {
            $httpClient = new Client();
            $params = $this->buildParams($grantType, $code);
            try {
                $response = $httpClient->post($this->authority.$this->tokenUrl, [
                    'form_params' => $params
                ]);
                // we got token successfully save it to session
                $tokenResponse = $this->deserialize($response->getBody()->getContents());
//                var_dump($tokenResponse);
                $token = $this->buildTokenInstance($tokenResponse);
                var_dump($token);
            } catch (\Exception $e) {
                throw new TokenException($e->getMessage());
            }
        }
        return false;
    }

    /**
     * @param $grantType
     * @param $code
     * @return array
     */
    private function buildParams($grantType, $code)
    {
        $parameterName = $grantType;
        if (strcmp($parameterName, 'authorization_code') == 0) {
            $parameterName = 'code';
        }
        return [
            "grant_type" => $grantType,
            $parameterName => $code,
            "redirect_uri" => $this->redirectUri,
            "scope" => implode(" ", $this->scopes),
            "client_id" => $this->clientId,
            "client_secret" => $this->clientSecret
        ];
    }

    /**
     * @return string
     */
    private function getAuthorizeUrl()
    {
        return '/common/oauth2/v2.0/authorize?client_id=%1$s&redirect_uri=%2$s&response_type=code&scope=%3$s';
    }

    /**
     * @param $scopes
     * @return string
     */
    private function formatScopes($scopes)
    {
        return urlencode(implode(" ", array_unique($scopes)));
    }

    /**
     * @param $body
     * @return mixed
     */
    private function deserialize($body)
    {
        echo $body;
        return json_decode($body, true);
    }

    /**
     * @param $token
     */
    private function buildTokenInstance($token)
    {
        $token = new Token();
        $token->setAccessToken($token['access_token']);
        $token->setRefreshToken($token['refresh_token']);
        $token->setExpiresIn($token['expires_in']);
        $token->setExtExpiresIn($token['ext_expires_in']);
        $token->setIdToken($token['id_token']);
        $token->setTokenType($token['token_type']);
        return $token;
    }
}
