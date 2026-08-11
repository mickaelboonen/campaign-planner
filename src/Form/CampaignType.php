<?php

namespace App\Form;

use App\DTO\CreateCampaignData;
use App\DTO\EditCampaignData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CampaignType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('name', TextType::class)
            ->add('color', ColorType::class, [
                'required' => false,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image de la campagne',
                'required' => false,
            ])
            ->add('privateNotes', TextareaType::class, [
                'label' => 'Notes privées',
                'required' => false,
                'attr' => [
                    'rows' => 8,
                    'maxlength' => 5000,
                    'placeholder' => "## Joueurs\n- **Alice** : enfants les semaines paires\n- **Bob** : indisponible le premier week-end du mois",
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateCampaignData::class,
        ]);

        $resolver->setAllowedValues('data_class', [
            CreateCampaignData::class,
            EditCampaignData::class,
        ]);
    }
}
