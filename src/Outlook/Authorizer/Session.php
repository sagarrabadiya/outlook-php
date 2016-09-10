<?php
/**
 * Author: sagar <sam.coolone70@gmail.com>
 *
 */

namespace Outlook\Authorizer;

use Outlook\Authorizer\Contracts\SessionContract;

/**
 * Class Session
 * @package Outlook\Authorizer
 */
class Session implements SessionContract
{
    /**
     * Session constructor.
     */
    public function __construct()
    {
        /**
         * if sesion is not started we start it. :)
         */
        if (!session_id()) {
            session_start();
        }
    }

    /**
     * @param $token
     * @return $this
     */
    public function set($token)
    {
        if (gettype($token) === 'string') {
            $_SESSION[$this->getSessionKey()] = $token;
        } else {
            $_SESSION[$this->getSessionKey()] = $this->serialize($token);
        }
        return $this;
    }

    /**
     * @return bool|mixed
     */
    public function get()
    {
        if (isset($_SESSION[$this->getSessionKey()])) {
            $token = $_SESSION[$this->getSessionKey()];
            if (gettype($token) === 'string') {
                return $this->deserialize($_SESSION[$this->getSessionKey()]);
            }
            return $token;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function remove()
    {
        if (isset($_SESSION[$this->getSessionKey()])) {
            unset($_SESSION[$this->getSessionKey()]);
        }
        return true;
    }

    /**
     * @return bool|mixed
     */
    public function hasToken()
    {
        $token = $this->get();
        if (!$token) {
            return false;
        }
        return $this->deserialize($token);
    }

    /**
     * @return string
     */
    public function getSessionKey()
    {
        return 'outlook.session.token';
    }

    /**
     * @param $token
     * @return string
     */
    public function serialize($token)
    {
        return serialize($token);
    }

    /**
     * @param $strToken
     * @return mixed
     */
    public function deserialize($strToken)
    {
        return unserialize($strToken);
    }
}
