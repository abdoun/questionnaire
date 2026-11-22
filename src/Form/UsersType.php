<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('password', PasswordType::class, ['required' => !$options['is_edit']])
            ->add('email')
        ;

        if ($options['is_admin']) {
            $builder
                ->add('active', CheckboxType::class)
                ->add('level');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
            'is_admin' => false,
            'is_edit' => false,
        ]);
        $resolver->setAllowedTypes('is_admin', 'bool');
        $resolver->setAllowedTypes('is_edit', 'bool');
    }
}
