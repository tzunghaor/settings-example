<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomIntType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('attr', ['style' => 'border: 5px solid yellow;']);
    }

    public function getParent(): string
    {
        return IntegerType::class;
    }
}