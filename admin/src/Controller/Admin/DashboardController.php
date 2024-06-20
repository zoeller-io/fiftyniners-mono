<?php

namespace App\Controller\Admin;

use App\Entity\FinancialLiability;
use App\Entity\FinancialTransaction;
use App\Entity\MailingHistory;
use App\Entity\MailingJob;
use App\Entity\MailingTemplate;
use App\Entity\Member;
use App\Entity\Payment;
use App\Entity\PostalAddress;
use App\Entity\SeasonTicket;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/', name: 'admin')]
    public function index(): Response
    {
//        return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(MemberCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Mailing');
//        yield MenuItem::linkToCrud('Job', 'fas fa-list', MailingJob::class);
        yield MenuItem::linkToCrud('Template', 'fas fa-list', MailingTemplate::class);
        yield MenuItem::linkToCrud('History', 'fas fa-list', MailingHistory::class);

        yield MenuItem::section('Ticketing');
        yield MenuItem::linkToCrud('Season Ticket', 'fas fa-list', SeasonTicket::class);

        yield MenuItem::section('Accounting');
        yield MenuItem::linkToCrud('Liability', 'fas fa-list', FinancialLiability::class);
        yield MenuItem::linkToCrud('Transaction', 'fas fa-list', FinancialTransaction::class);

        yield MenuItem::section('Membership');
        yield MenuItem::linkToCrud('Member', 'fas fa-list', Member::class);
        yield MenuItem::linkToCrud('Postal Address', 'fas fa-list', PostalAddress::class);
    }
}
