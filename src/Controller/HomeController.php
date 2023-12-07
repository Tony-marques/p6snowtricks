<?php

namespace App\Controller;

use App\Helpers\Paginator;
use App\Repository\TrickRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app.home', methods: ["GET"])]
    public function index(TrickRepository $trickRepository, Request $request, Paginator $paginator): Response
    {
        $tricks = $trickRepository->findBy([], ["createdAt" => "ASC"]);

        $currentPage = $request->get("page") ?? "1";

        [$tricksForOnePage, $totalPages] = $paginator->paginate(items: $tricks, currentPage: $currentPage, limit: 6);

        return $this->render('home/index.html.twig', [
            "tricks" => $tricksForOnePage,
            "totalPages" => $totalPages,
            "currentPage" => $currentPage
        ]);
    }
}
