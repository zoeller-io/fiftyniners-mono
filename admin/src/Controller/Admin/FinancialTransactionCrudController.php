<?php

namespace App\Controller\Admin;

use App\Entity\FinancialTransaction;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FinancialTransactionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FinancialTransaction::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setDisabled(),
            AssociationField::new('member'),
            AssociationField::new('liabilities'),
//            TextField::new('method'),
            TextField::new('reference'),
            TextField::new('owner')->hideOnIndex(),
            TextField::new('reason'),
            IntegerField::new('amount'),
            DateTimeField::new('paidAt')->setFormat('short', 'none'),
            ArrayField::new('tags'),
        ];
    }
}
