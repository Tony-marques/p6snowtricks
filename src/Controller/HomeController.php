<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app.home')]
    public function index(TrickRepository $trickRepository): Response
    {
        $tricks = $trickRepository->findAll();
//dump($request->getFlash);
        return $this->render('home/index.html.twig', [
            "tricks" => $tricks
        ]);
    }

}
