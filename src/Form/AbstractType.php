<?php


namespace App\Form;

use App\Entity\Types\Enum\IsEnabledEnum;
use Symfony\Component\Form\AbstractType as BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractType extends BaseAbstractType
{
    const ACTION_CREATE = 'action_create';
    const ACTION_UPDATE = 'action_update';

    protected static $actions = [self::ACTION_CREATE, self::ACTION_UPDATE];
    protected $action = self::ACTION_CREATE;

    public function setAction($action)
    {
        if (in_array($action, self::$actions)) {
            $this->action = $action;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if (!empty($options['action'])) {
            $this->setAction($options['action']);
        }
    }

    protected static function getOptionsForEnabledEnumType(): array
    {
        return [
            'required' => true,
            'choices' => IsEnabledEnum::getAvailableTypes(),
            'invalid_message' => sprintf('Invalid status value (can be has only % values)', '"' . implode('", "', IsEnabledEnum::getAvailableTypes()) . '"')
        ];
    }
}