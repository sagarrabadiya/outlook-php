<?php
/**
 * Author: sagar <sam.coolone70@gmail.com>
 *
 */

namespace Outlook\Events;

class Event extends \stdClass
{
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
        return $this->Body ?: '';
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        } else {
            $propsArray = $this->toArray();
            return isset($propsArray[$name]) ? $propsArray[$name] : null;
        }
    }

    public function toArray()
    {
        return array_change_key_case((array) $this, CASE_LOWER);
    }
}
