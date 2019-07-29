<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 07.09.2018
 * Time: 12:13
 */

namespace App\Form;

use App\Entity\User;
use App\Entity\Types\Enum\GenderEnum;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        /** @var \App\Entity\User $user */
        $user = null;
        if ($builder->getData() instanceof User) {
            $user = $builder->getData();
        }

        $emailOptions = [];
        $sexOptions = [
            'required' => true,
            'choices' => GenderEnum::getAvailableTypes(),
            'invalid_message' => sprintf('Invalid sex value (can be has only % values)', '"' . implode('", "', GenderEnum::getAvailableTypes()) . '"')
        ];
        $ageOptions = [];
        if ($this->action === self::ACTION_UPDATE && $user !== null) {
            // Email is not required for "update" action - we can use current email
            $emailOptions['required'] = false;
            $emailOptions['empty_data'] = $user->getEmail();

            // Sex is not required for "update" action - we can use current sex
            $sexOptions['required'] = false;
            $sexOptions['empty_data'] = $user->getSex();

            // Age is not required for "update" action - we can use current age
            $ageOptions['required'] = false;
            $ageOptions['empty_data'] = $user->getAge();
        }

        $builder
            ->add('email', EmailType::class, $emailOptions)
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'invalid_message' => 'The password fields are not match.',
            ])
            ->add('firstName', TextType::class, [
                'required' => false,
                'empty_data' => $user !== null ? $user->getFirstName() : '',
            ])
            ->add('lastName', TextType::class, [
                'required' => false,
                'empty_data' => $user !== null ? $user->getLastName() : '',
            ])
            ->add('sex', ChoiceType::class, $sexOptions)
            ->add('age', IntegerType::class, $ageOptions)
            ->add('aboutMe', TextType::class, [
                'required' => false,
                'empty_data' => $user !== null ? $user->getAboutMe() : '',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}