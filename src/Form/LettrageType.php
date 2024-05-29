<?php

namespace App\Form;

use App\Entity\Expense;
use App\Entity\Lettrage;
use App\Entity\Sale;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LettrageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('recordedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('label')
            ->add('amountToBank')
            ->add('dateToBank')
            ->add('recordedBy', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('sales', EntityType::class, [
                'class' => Sale::class,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('s')
                        ->where('s.lettrage is null')
                        ->andWhere('s.received = true')
                        ->orderBy('s.id', 'ASC');
                },
                'choice_label' => function (Sale $sale) {
                    $article = $sale->getItem();
                    $pu = ($sale->getQty() > 0)?$article->getSellPrice()- $sale->getPromo():$article->getSellPrice();
                    $montant = $pu * ($sale->getQty() - $sale->getQtyReturned());
                    return $sale->getSoldOn()->format('d/m/Y')
                        . ' : ' . number_format($montant, 2, ',', ' ')
                        . ' [ ' . $article->getLabel()
                        . ' - (vendus): ' . $sale->getQty()
                        . ' - (retours): ' . $sale->getQtyReturned()
                        . ' ]';
                },
                'expanded' => false,
                'multiple' => true,
                'required' => false,
            ])
            ->add('expenses', EntityType::class, [
                'class' => Expense::class,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('e')
                        ->where('e.lettrage is null')
                        ->andWhere('e.paid = true')
                        ->orderBy('e.id', 'ASC');
                },
                'choice_label' => function (Expense $expense) {
                    return $expense->getRecordedAt()->format('d/m/Y')
                        . ' : ' . number_format($expense->getAmount(), 2, ',', ' ')
                        . ' [ ' . $expense->getDescription()
                        . ' ]';
                },
                'expanded' => false,
                'multiple' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lettrage::class,
        ]);
    }
}
