<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Category;
use App\Controller\ProductController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

//  #[IsGranted('ROLE_SUPER_ADMIN','ROLE_ADMIN' )]
class DashboardController extends AbstractDashboardController
{
    private $authChecker;

    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }
    
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
      //  return parent::index();

    //   $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);


    //   if (!$this->authChecker->isGranted('ROLE_SUPER_ADMIN') && !$this->authChecker->isGranted('ROLE_ADMIN')) {
    //     throw new AccessDeniedException('Accès refusé.');
    //   }

        
         
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Ventalis Project');
    }

    public function configureMenuItems(): iterable
    {
         yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
         yield MenuItem::linkToCrud('Produits', 'fas fa-list', Product::class);
         yield MenuItem::linkToCrud('Categories ', 'fas fa-list', Category::class);
         yield MenuItem::linkToCrud('Utilisateurs ', 'fas fa-list', User::class)->setPermission('ROLE_SUPER_ADMIN');
         yield MenuItem::section('Gestion des commandes');
         yield MenuItem::linkToCrud('Commandes', 'fas fa-fa-list', Order::class);

         yield  MenuItem::linkToRoute('Retour Accueil', 'fa fa-arrow-left', 'app_accueil'); // Add this line for the home button


      
    }
}
