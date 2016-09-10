<?php
/**
 * Author: sagar <sam.coolone70@gmail.com>
 *
 */

namespace Outlook\Events;

/**
 * Class Event
 * @package Outlook\Events
 */
class Event extends \stdClass
{
    /**
     * Event constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        foreach ($properties as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->Body ? $this->Body->Content : '';
    }

    /**
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        } else {
            $propsArray = $this->toArray();
            return isset($propsArray[$name]) ? $propsArray[$name] : null;
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_change_key_case((array) $this);
    }


    /**
     * @return array
     */
    public function toParams()
    {
        $parameters = [];
        foreach ($this as $key => $value) {
            $parameters[ucfirst($key)] = $value;
        }
        foreach ($this->getNonParmas() as $key) {
            unset($parameters[$key]);
        }
        return $parameters;
    }

    /**
     * @return array
     */
    protected function getNonParmas()
    {
        return [
            'ICalUId',
            'Calendar@odata.associationLink',
            'Calendar@odata.navigationLink',
            'StatusCode'
        ];
    }
}
