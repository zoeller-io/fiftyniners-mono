<?php

namespace App\Controller\Admin;

use App\Entity\FinancialLiability;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FinancialLiabilityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FinancialLiability::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setDisabled(),
            AssociationField::new('member'),
            NumberField::new('amount')
                ->formatValue(function ($value) {
                    return sprintf("%01.2f", $value / 100);
                }),
            TextField::new('type'),
            TextField::new('reason'),
            TextField::new('comment'),
            DateTimeField::new('dueAt')->setFormat('short', 'none'),
            DateTimeField::new('paidAt')->setFormat('short', 'none'),
            BooleanField::new('isPaid'),
            AssociationField::new('transactions')
                ->setDisabled()
                ->formatValue(function ($value, $entity) {
                    $result = '';
                    foreach ($entity->getTransactions() as $transaction) {
                        $result .= sprintf("[#%d] %s ", $transaction->getId(), $transaction->getPaidAt()->format('Y-m-d'));
                    }
                    return $result;
                }),
            ArrayField::new('tags'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('member')
            ->add('type')
            ->add('reason')
            ->add('paidAt')
            ->add('tags');
    }
}
