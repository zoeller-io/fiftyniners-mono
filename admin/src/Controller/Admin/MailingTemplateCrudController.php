<?php

namespace App\Controller\Admin;

use App\Entity\MailingTemplate;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MailingTemplateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MailingTemplate::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->setDisabled(),
            TextField::new('name'),
            ArrayField::new('recipientFilters'),
            TextField::new('subject'),
            CodeEditorField::new('textBody')
                ->setLanguage('twig'),
            CodeEditorField::new('htmlBody')
                ->setLanguage('twig'),
            DateTimeField::new('createdAt')
                ->setDisabled(),
            DateTimeField::new('updatedAt')
                ->setDisabled(),
        ];
    }
}
