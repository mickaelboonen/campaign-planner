<?php

namespace App\Form;

use App\DTO\CreateCampaignData;
use App\DTO\EditCampaignData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CampaignType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'form.name.label',
            ])
            ->add('color', ColorType::class, [
                'label' => 'form.color.label',
                'required' => false,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'form.image.label',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateCampaignData::class,
            'translation_domain' => 'campaign',
        ]);

        $resolver->setAllowedValues('data_class', [
            CreateCampaignData::class,
            EditCampaignData::class,
        ]);
    }
}
