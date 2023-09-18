<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: "/tricks", name: "app.tricks", methods: ["GET", "POST"])]
class TrickController extends AbstractController
{
    #[IsGranted("ROLE_ADMIN")]
    #[Route('/creer', name: '_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick->setCreatedAt(new DateTimeImmutable())
                ->setUser($this->getUser());

            $em->persist($trick);
            $em->flush();

            return $this->redirectToRoute("app.tricks_manage");
        }

        return $this->render('trick/create.html.twig', [
            "form" => $form->createView()
        ]);
    }

    #[Route(
        path: '/supprimer/{id}',
        name: '_delete',
        requirements: [
            "id" => "\d+"
        ],
        methods: ["POST"]
    )]
    public function delete(Request $request, EntityManagerInterface $em, Trick $trick)
    {
        $em->remove($trick);
        $em->flush();

        return $this->redirectToRoute("app.home");
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route(
        '/editer/{id}',
        name: '_edit',
        requirements: [
            "id" => "\d+"
        ],
        methods: ["POST"]
    )]
    public function edit(Request $request, EntityManagerInterface $em, Trick $trick): Response
    {
        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick->setUpdatedAt(new DateTimeImmutable());
            $em->persist($trick);
            $em->flush();

            return $this->redirectToRoute("app.tricks_show_one", ["id" => $trick->getId()]);
        }


        return $this->render("trick/edit.html.twig", [
            "form" => $form->createView(),
            "trick" => $trick
        ]);
    }

    #[Route(
        '/{id}',
        name: '_show_one',
        requirements: [
            "id" => "\d+"
        ],
        methods: ["GET"]
    )]
    public function showOne(Request $request, EntityManagerInterface $em, Trick $trick): Response
    {

        return $this->render('trick/show_one.html.twig', [
            "trick" => $trick
        ]);
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route(path: "/manage", name: "_manage")]
    public function manage(EntityManagerInterface $em): Response
    {
        return $this->render("trick/manage.html.twig", [
            "tricks" => $em->getRepository(Trick::class)->findBy([], ["createdAt" => "DESC"])
        ]);
    }

}
