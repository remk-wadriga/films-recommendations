<?php


namespace App\Helpers\Web;


abstract class AbstractWebReader implements WebReaderInterface
{
    protected $extra = [];

    public $source;

    public function __construct(array $config)
    {
        foreach ($config as $name => $value) {
            $setter = 'set' . ucfirst($name);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
                unset($config[$name]);
            } elseif (property_exists($this, $name)) {
                $this->$name = $value;
                unset($config[$name]);
            }
        }
        $this->extra = $config;
    }
}