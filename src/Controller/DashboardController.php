<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app.dashboard', methods: ["GET"])]
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('dashboard/index.html.twig', [
            "last3tricks" => $em->getRepository(Trick::class)->findBy([], ["createdAt" => "DESC"], 3),
            "last3users" => $em->getRepository(User::class)->findBy([], ["createdAt" => "DESC"], 3),
            "allTricks" => $em->getRepository(Trick::class)->findBy([]),
            "allUsers" => $em->getRepository(User::class)->findBy([]),
        ]);
    }
}
