<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Sale;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('soldOn', null, [
                'widget' => 'single_text',
                'data' => new \DateTime(),
            ])
            ->add('qty', null, [
                'attr' => [
                    'min' => 0,
                ],
                'data' => 0,
            ])
            ->add('qtyReturned', null, [
                'attr' => [
                    'min' => 0,
                ],
                'data' => 0,
            ])
            ->add('promo', null, [
                'attr' => [
                    'min' => 0,
                ],
                'data' => 0,
            ])
            ->add('received')
            ->add('registeredBy', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ])
            ->add('item', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'label',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->orderBy('a.label', 'ASC')
                    ;
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sale::class,
        ]);
    }
}
