<?php namespace Outlook\Authorizer;

use GuzzleHttp\Client;
use Outlook\Authorizer\Contracts\SessionContract;
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
    protected $clientId;

    /**
     * @var null
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $authority = "https://login.microsoftonline.com";

    /**
     * @var string
     */
    protected $tokenUrl = "/common/oauth2/v2.0/token";

    /**
     * @var array
     */
    protected $scopes = [];

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * @var SessionContract
     */
    protected $sessionManager;

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
            throw new ClientException("Client id and client secret is required for outlook!", 500, new \Exception);
        }

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        if (is_null($redirectUri)) {
            $this->redirectUri = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
            // we clean up redirect uri and remove ?code=XXX and other query string params
            $this->redirectUri = str_replace("?{$_SERVER['QUERY_STRING']}", "", $this->redirectUri);
        } else {
            $this->redirectUri = $redirectUri;
        }
    }

    /**
     * @param array $scopes
     * @param string $redirectUri
     * @param array $additionalParams
     * @return string
     */
    public function getLoginUrl($scopes = [], $redirectUri = null, $additionalParams = [])
    {
        if (!is_null($redirectUri)) {
            $this->redirectUri = $redirectUri;
        }
        $this->scopes = array_merge($scopes, ['openid', 'offline_access']);
        return $this->authority.sprintf(
            $this->getAuthorizeUrl(),
            $this->clientId,
            urlencode($this->redirectUri),
            $this->formatScopes($this->scopes)
        ) . "&" . \http_build_query($additionalParams);
    }

    /**
     * @return bool|Token
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
                $token = $this->buildTokenInstance($tokenResponse);
                $this->sessionManager->set($token);
                return $token;
            } catch (\Exception $e) {
                throw new TokenException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return false;
    }

    /**
     * Method to get the new access token from existing refresh token
     *
     * @return bool|Token
     * @throws TokenException
     */
    public function renewToken(Token $token)
    {
        $grantType = 'refresh_token';
        $code = $token->getRefreshToken();
        if ($code) {
            $httpClient = new Client();
            $params = $this->buildParams($grantType, $code);
            try {
                $response = $httpClient->post($this->authority.$this->tokenUrl, [
                    'form_params' => $params
                ]);
                // we got token successfully save it to session
                $tokenResponse = $this->deserialize($response->getBody()->getContents());
                $token = $this->buildTokenInstance($tokenResponse);
                $this->sessionManager->set($token);
                return $token;
            } catch (\Exception $e) {
                throw new TokenException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return false;
    }

    /**
     * function to set scope
     *
     * @param array $scopes
     * @return Authenticator
     */
    public function setScopes($scopes = [])
    {
        $this->scopes = array_merge($scopes, ['openid', 'offline_access']);
        return $this;
    }

    /**
     * @param $grantType
     * @param $code
     * @return array
     */
    protected function buildParams($grantType, $code)
    {
        $parameterName = $grantType;
        if (strcmp($parameterName, 'authorization_code') == 0) {
            $parameterName = 'code';
        }
        if (strcmp($parameterName, 'refresh_token') == 0) {
            $parameterName = 'refresh_token';
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
    protected function getAuthorizeUrl()
    {
        return '/common/oauth2/v2.0/authorize?client_id=%1$s&redirect_uri=%2$s&response_type=code&scope=%3$s';
    }

    /**
     * @param $scopes
     * @return string
     */
    protected function formatScopes($scopes)
    {
        return urlencode(implode(" ", array_unique($scopes)));
    }

    /**
     * @param $body
     * @return mixed
     */
    protected function deserialize($body)
    {
        return json_decode($body, true);
    }

    /**
     * @param $token
     */
    protected function buildTokenInstance($token)
    {
        $tokenInstance = new Token();
        $tokenInstance->setAccessToken($token['access_token']);
        $tokenInstance->setRefreshToken($token['refresh_token']);
        $tokenInstance->setExpiresIn($token['expires_in']);
        $tokenInstance->setExtExpiresIn($token['ext_expires_in']);
        $tokenInstance->setIdToken($token['id_token']);
        $tokenInstance->setTokenType($token['token_type']);
        return $tokenInstance;
    }

    /**
     * @return mixed
     */
    public function getSessionManager()
    {
        return $this->sessionManager;
    }

    /**
     * @param SessionContract $sessionManager
     */
    public function setSessionManager(SessionContract $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }
}
