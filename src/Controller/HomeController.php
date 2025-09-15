<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(): Response
    {
        $user = $this->getUser();
        
        if ($user->isAdmin()) {
            return $this->render('home/admin.html.twig', [
                'user' => $user,
            ]);
        } else {
            return $this->render('home/user.html.twig', [
                'user' => $user,
            ]);
        }
    }
}