<?php
/**
 * Author: sagar <sam.coolone70@gmail.com>
 *
 */

namespace Outlook\Events;

use Outlook\Authorizer\Authenticator;
use Outlook\Authorizer\Contracts\SessionContract;
use Outlook\Authorizer\Token;
use Outlook\Exceptions\Authorizer\ClientException;

class EventAuthorizer
{
    /**
     * @var Authenticator
     */
    protected $authenticator;

    /**
     * @var SessionContract
     */
    protected $sessionManager;

    /**
     * EventAuthorizer constructor.
     * @param Authenticator $authenticator
     * @param SessionContract $sessionManager
     */
    public function __construct(Authenticator $authenticator, SessionContract $sessionManager)
    {
        $this->authenticator = $authenticator;
        $this->sessionManager = $sessionManager;
        $this->authenticator->setSessionManager($this->sessionManager);
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->authenticator->getLoginUrl([
            'https://outlook.office.com/calendars.readwrite'
        ]);
    }

    /**
     * @return bool|Token
     */
    public function renewToken()
    {
        $token = $this->sessionManager->get();
        if ($token) {
            return $this->authenticator->renewToken($token);
        }
        return false;
    }

    /**
     * @return bool|Token
     */
    public function isAuthenticated()
    {
        $token = $this->sessionManager->get();
        if ($token && !$token->isExpired()) {
            return $token;
        }
        // we clean up any existing expired token
        $this->sessionManager->remove();
        // if not in session we capture code parameter and send request to get token
        return $this->authenticator->getToken();
    }
}
