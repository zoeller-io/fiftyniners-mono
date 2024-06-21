<?php

namespace App\Controller\Admin;

use App\Entity\FinancialLiability;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
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
            IdField::new('id'),
            AssociationField::new('member'),
            IntegerField::new('amount'),
            TextField::new('type'),
            TextField::new('reason'),
            TextField::new('comment'),
            DateTimeField::new('dueAt')->setFormat('short', 'none'),
            DateTimeField::new('paidAt')->setFormat('short', 'none'),
            ArrayField::new('tags'),
        ];
    }
}
