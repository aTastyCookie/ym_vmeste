<?php

abstract class Ymoney_Model_Abstract {
    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }
 
    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method)) {
            $this->{$method}($value);
        } else {
            if (!preg_match('/^_/', $name)) {
                $varName = '_' . $name;
            } else {
                $varName = $name;
            }
            $this->{$varName} = $value;
        }
    }
 
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->{$method}();
        } else {
            $varName = '_' . $name;
            return $this->{$varName};
        }
    }
 
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $this->$key = $value;
        }
        
        return $this;
    }
}
