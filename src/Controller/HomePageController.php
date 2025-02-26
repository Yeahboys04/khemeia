<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomePageController extends AbstractController
{
    #[Route('/', name: 'home_page')]
    public function index(): Response
    {
        $user = $this->getUser();
        if ($user != null){
            $endRightDate = $user->getEndrightdate();
            $today = new DateTime();
            if ($endRightDate >= $today || $endRightDate == null){
                return $this->render('home_page/index.html.twig', [
                    'controller_name' => 'HeaderController',
                ]);
            }
            else {
                return $this->render('home_page/archived.html.twig', [
                    'controller_name' => 'HeaderController',
                ]);
            }
        }
        return $this->redirectToRoute('app_login');
    }
}