<?php

namespace App\Form;

use App\DTO\CreateParticipantData;
use App\DTO\EditParticipantData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ParticipantType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('name', TextType::class, [
                'label' => 'form.name.label',
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.email.label',
            ])
            ->add('phone', TelType::class, [
                'label' => 'form.phone.label',
                'required' => false,
            ])
            ->add('characterName', TextType::class, [
                'label' => 'form.character_name.label',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateParticipantData::class,
            'translation_domain' => 'participant',
        ]);

        $resolver->setAllowedValues('data_class', [
            CreateParticipantData::class,
            EditParticipantData::class,
        ]);
    }
}
