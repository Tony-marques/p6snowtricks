<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Form\TrickType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route(path: "/tricks", name: "app.tricks", methods: ["GET", "POST"])]
class TrickController extends AbstractController
{
    #[IsGranted("ROLE_USER")]
    #[Route('/creer', name: '_create')]
    public function create(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        $userRole = $this->getUser()->getRoles();

        if ($form->isSubmitted() && $form->isValid()) {
            $trick->setCreatedAt(new DateTimeImmutable())
                ->setUser($this->getUser())
                ->setSlug($slugger->slug($trick->getName())->lower());

            $em->persist($trick);
            $em->flush();

            if (in_array("ROLE_ADMIN", $userRole)) {
                return $this->redirectToRoute("app.tricks_manage");
            } else {
                return $this->redirectToRoute("app.home");
            }
        }

        if (in_array("ROLE_ADMIN", $userRole)) {
            return $this->render('trick/create_admin.html.twig', [
                "form" => $form->createView()
            ]);
        } else {
            return $this->render('trick/create.html.twig', [
                "form" => $form->createView()
            ]);
        }
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route(
        path: '/supprimer/{slug}',
        name: '_delete',
        methods: ["GET", "POST"]
    )]
    public function delete(Request $request, EntityManagerInterface $em, Trick $trick): JsonResponse|Response
    {
        $em->remove($trick);
        $em->flush();
        if ($request->getMethod() === "POST") {
            $this->addFlash("success", "Le trick {$trick->getName()} a été supprimé avec succès !");
        }

        return $this->json([
            "message" => "Le trick {$trick->getName()} a été supprimé avec succès !",
        ]);
    }

    #[IsGranted("ROLE_USER")]
    #[Route(
        '/editer/{slug}',
        name: '_edit',
        methods: ["POST"]
    )]
    public function edit(Request $request, EntityManagerInterface $em, Trick $trick): Response
    {
        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        $userRole = $this->getUser()->getRoles();

        if ($form->isSubmitted() && $form->isValid()) {
            $trick->setUpdatedAt(new DateTimeImmutable());
            $em->persist($trick);
            $em->flush();

            return $this->redirectToRoute("app.tricks_show_one", ["slug" => $trick->getSlug()]);
        }

        if (in_array("ROLE_ADMIN", $userRole)) {
            return $this->render("trick/edit_admin.html.twig", [
                "form" => $form->createView(),
                "trick" => $trick
            ]);
        } else {
            return $this->render('trick/edit.html.twig', [
                "form" => $form->createView(),
                "trick" => $trick
            ]);
        }
    }

    #[Route(
        '/{slug}',
        name: '_show_one',
        methods: ["GET", "POST"]
    )]
    public function showOne(Request $request, EntityManagerInterface $em, Trick $trick): Response
    {
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);

        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setCreatedAt(new DateTimeImmutable())
                ->setUser($this->getUser())
                ->setTrick($trick);

            $em->persist($comment);
            $em->flush();

            $this->redirectToRoute("app.tricks_show_one", ["slug" => $trick->getSlug()]);
        }

        return $this->render('trick/show_one.html.twig', [
            "trick" => $trick,
            "commentForm" => $commentForm->createView(),
            "comments" => $em->getRepository(Comment::class)->findBy(["trick" => $trick], ["createdAt" => "DESC"])
        ]);
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route(path: "/manage", name: "_manage", priority: 1)]
    public function manage(EntityManagerInterface $em): Response
    {
        return $this->render("trick/manage.html.twig", [
            "tricks" => $em->getRepository(Trick::class)->findBy([], ["createdAt" => "DESC"])
        ]);
    }

}
