<?php

namespace App\Form;

use App\DTO\UpdateCampaignNotesData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CampaignNotesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('privateNotes', TextareaType::class, [
            'label' => 'notes.form.label',
            'required' => false,
            'attr' => [
                'rows' => 16,
                'maxlength' => 5000,
                'placeholder' => 'notes.form.placeholder',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UpdateCampaignNotesData::class,
            'translation_domain' => 'campaign',
        ]);
    }
}
