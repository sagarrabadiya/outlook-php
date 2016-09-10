<?php
/**
 * Author: sagar <sam.coolone70@gmail.com>
 *
 */

namespace Outlook\Authorizer\Contracts;

interface SessionContract
{
    /**
     * Function to set item into session
     * @param $token
     * @return mixed
     */
    public function set($token);

    /**
     * function to get item from session registry
     * @return mixed
     */
    public function get();

    /**
     * function to remove token from session
     * @return mixed
     */
    public function remove();

    /**
     * function to check if session has token
     * @return mixed
     */
    public function hasToken();

    /**
     * function to serialize token instance into string to save
     * @param $token
     * @return mixed
     */
    public function serialize($token);

    /**
     * function to deserialize token instance into object
     * @param $strToken
     * @return mixed
     */
    public function deserialize($strToken);

    /**
     * function to return the key in which session should be saved.
     * @return mixed
     */
    public function getSessionKey();
}
