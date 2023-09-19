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
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route(path: "/tricks", name: "app.tricks", methods: ["GET", "POST"])]
class TrickController extends AbstractController
{
    #[IsGranted("ROLE_ADMIN")]
    #[Route('/creer', name: '_create')]
    public function create(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick->setCreatedAt(new DateTimeImmutable())
                ->setUser($this->getUser())
                ->setSlug($slugger->slug($trick->getName())->lower());

            $em->persist($trick);
            $em->flush();

            return $this->redirectToRoute("app.tricks_manage");
        }

        return $this->render('trick/create.html.twig', [
            "form" => $form->createView()
        ]);
    }

    #[Route(
        path: '/supprimer/{slug}',
        name: '_delete',
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
        '/editer/{slug}',
        name: '_edit',
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

            return $this->redirectToRoute("app.tricks_show_one", ["slug" => $trick->getSlug()]);
        }

        return $this->render("trick/edit.html.twig", [
            "form" => $form->createView(),
            "trick" => $trick
        ]);
    }

    #[Route(
        '/{slug}',
        name: '_show_one',
        methods: ["GET"]
    )]
    public function showOne(Request $request, EntityManagerInterface $em, Trick $trick): Response
    {

        return $this->render('trick/show_one.html.twig', [
            "trick" => $trick
        ]);
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route(path: "/manage", name: "_manage", priority:1)]
    public function manage(EntityManagerInterface $em): Response
    {
        return $this->render("trick/manage.html.twig", [
            "tricks" => $em->getRepository(Trick::class)->findBy([], ["createdAt" => "DESC"])
        ]);
    }

}
