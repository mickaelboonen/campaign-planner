<?php

namespace App\Form;

use App\DTO\GameSessionData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class GameSessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'form.name.label',
                'required' => false,
            ])
            ->add('startTime', TimeType::class, [
                'label' => 'form.start_time.label',
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
            ])
            ->add('endTime', TimeType::class, [
                'label' => 'form.end_time.label',
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GameSessionData::class,
            'translation_domain' => 'game_session',
        ]);
    }
}
