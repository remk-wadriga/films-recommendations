<?php


namespace App\TestService;


abstract class AbstractEntity
{
    public function __construct(array $params)
    {
        foreach ($params as $name => $value) {
            $setter = 'set' . ucfirst($name);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            } elseif (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
        $this->init($params);
    }

    public function init(array $params)
    {

    }
}