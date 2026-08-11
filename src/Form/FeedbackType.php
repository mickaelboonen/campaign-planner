<?php

namespace App\Form;

use App\DTO\CreateFeedbackData;
use App\Enum\FeedbackType as FeedbackTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FeedbackType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type de demande',
                'choices' => FeedbackTypeEnum::cases(),
                'choice_label' => static fn (FeedbackTypeEnum $type) => $type->label(),
                'choice_value' => static fn (?FeedbackTypeEnum $type) => $type?->value,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'required' => !$options['authenticated'],
                'disabled' => $options['authenticated'],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'rows' => 5,
                ],
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver,
    ): void {
        $resolver->setDefaults([
            'data_class' => CreateFeedbackData::class,
            'authenticated' => false,
        ]);

        $resolver->setAllowedTypes(
            'authenticated',
            'bool',
        );
    }
}
