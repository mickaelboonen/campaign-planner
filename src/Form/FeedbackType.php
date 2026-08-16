<?php

namespace App\Form;

use App\DTO\CreateFeedbackData;
use App\Enum\FeedbackType as FeedbackTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'form.type.label',
                'placeholder' => 'form.type.placeholder',
                'choices' => FeedbackTypeEnum::cases(),
                'choice_label' => static fn (FeedbackTypeEnum $type) => $type->translationKey(),
                'choice_value' => static fn (?FeedbackTypeEnum $type) => $type?->value,
                'choice_translation_domain' => 'enums',
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.email.label',
                'required' => !$options['authenticated'],
                'disabled' => $options['authenticated'],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'form.message.label',
                'attr' => ['rows' => 5],
            ])
            ->add('subject', TextType::class, [
                'label' => 'form.subject.label',
                'required' => false,
                'attr' => [
                    'maxlength' => 120,
                    'placeholder' => 'form.subject.placeholder',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateFeedbackData::class,
            'authenticated' => false,
            'translation_domain' => 'feedback',
        ]);

        $resolver->setAllowedTypes('authenticated', 'bool');
    }
}
