<?php

namespace App\Controller;

use App\Entity\Trick;
use DateTimeImmutable;
use Imagine\Image\Box;
use App\Entity\Comment;
use App\Form\TrickType;
use Imagine\Gd\Imagine;
use App\Form\CommentType;
use App\Helpers\Paginator;
use App\Helpers\Uploader;
use App\Repository\TrickRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route(path: "/tricks", name: "app.tricks", methods: ["GET", "POST"])]
class TrickController extends AbstractController
{
    #[IsGranted("ROLE_USER")]
    #[Route('/creer', name: '_create')]
    public function create(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, Uploader $uploader): Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick, ["validation_groups" => ["creation"]]);

        $form->handleRequest($request);

        $userRole = $this->getUser()->getRoles();

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('mainImage')->getData();


            if ($imageFile) {
                $nameImage = $uploader->newNameImage($imageFile);
                $uploader->upload($imageFile, "upload/tricks", $nameImage);

                $trick->setMainImageName($nameImage);
            }

            foreach ($trick->getImages() as $image) {
                $file = $image->getFile() ?? null;
                if ($file != null) {
                    $nameImage = $uploader->newNameImage($file);
                    $uploader->upload($file, "upload/tricks", $nameImage);

                    $image->setName($nameImage);
                    $image->setCreatedAt(new DateTimeImmutable());
                    $image->setTrick($trick);
                    $trick->addImage($image);
                }
            }

            foreach ($trick->getVideos() as $video) {
                $video->setTrick($trick);
            }

            $trick->setCreatedAt(new DateTimeImmutable())
                ->setUser($this->getUser())
                ->setSlug($slugger->slug($trick->getName())->lower())
                ->setMainImage($imageFile);

            $em->persist($trick);

            $em->flush();

            $this->addFlash("success", "Le trick {$trick->getName()} a été crée avec succès !");

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

    #[IsGranted("ROLE_USER")]
    #[Route(
        path: '/supprimer/{slug}',
        name: '_delete',
        methods: ["GET", "POST"]
    )]
    public function delete(EntityManagerInterface $em, Trick $trick, Request $request): JsonResponse|Response
    {
        $this->denyAccessUnlessGranted("TRICK_DELETE", $trick);

        $em->remove($trick);
        $em->flush();

        if (\file_exists("upload/tricks/{$trick->getMainImageName()}")) {
            \unlink("upload/tricks/{$trick->getMainImageName()}");
        }

        foreach ($trick->getImages() as $image) {
            if (\file_exists("upload/tricks/{$image->getName()}")) {
                \unlink("upload/tricks/{$image->getName()}");
            }
        }

        return $this->json([
            "message" => "Le trick {$trick->getName()} a été supprimé avec succès !",
        ]);
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route(
        path: '/supprimer/admin/{slug}',
        name: '_delete_dashboard',
        methods: ["GET", "POST"]
    )]
    public function deleteFromDashboard(EntityManagerInterface $em, Trick $trick): JsonResponse|Response
    {
        $this->denyAccessUnlessGranted("TRICK_DELETE", $trick);

        $em->remove($trick);
        $em->flush();

        if (\file_exists("upload/tricks/{$trick->getMainImageName()}")) {
            \unlink("upload/tricks/{$trick->getMainImageName()}");
        }

        foreach ($trick->getImages() as $image) {
            if (\file_exists("upload/tricks/{$image->getName()}")) {
                \unlink("upload/tricks/{$image->getName()}");
            }
        }

        return $this->redirectToRoute("app.tricks_manage");
    }

    #[IsGranted("ROLE_USER")]
    #[Route(
        '/editer/{slug}',
        name: '_edit',
        methods: ["POST"]
    )]
    public function edit(Request $request, EntityManagerInterface $em, Trick $trick, Uploader $uploader): Response
    {
        $this->denyAccessUnlessGranted("TRICK_EDIT", $trick);

        $form = $this->createForm(TrickType::class, $trick, ["validation_groups" => ["edition"]]);

        $form->handleRequest($request);

        $userRole = $this->getUser()->getRoles();

        if ($form->isSubmitted() && $form->isValid()) {


            $imageFile = $form->get('mainImage')->getData();

            if ($imageFile) {
                $uploader->removeImage("upload/tricks/{$trick->getMainImageName()}");
                $nameImage = $uploader->newNameImage($imageFile);
                $uploader->upload($imageFile, "upload/tricks", $nameImage);

                $trick->setMainImageName($nameImage)->setUpdatedAt(new DateTimeImmutable());
            }


            foreach ($trick->getImages() as $image) {
                $uploader->removeImage("upload/tricks/{$image->getName()}");

                $file = $image->getFile();

                $name = $uploader->newNameImage($file);
                $uploader->upload($file, "upload/tricks", $name);

                $image->setName($name);
                $image->setCreatedAt(new DateTimeImmutable());
                $image->setTrick($trick);
                $trick->addImage($image);
            }


            $em->persist($trick);

            $em->flush();

            $this->addFlash("success", "Le trick {$trick->getName()} a été modifié avec succès !");

            return $this->redirectToRoute("app.tricks_show_one", ["slug" => $trick->getSlug()]);
        }

        if (in_array("ROLE_ADMIN", $userRole)) {
            return $this->render("trick/edit_admin.html.twig", [
                "form" => $form->createView(),
                "trick" => $trick
            ]);
        } else {
            return $this->render(view: 'trick/edit.html.twig', parameters: [
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
    public function showOne(Request $request, EntityManagerInterface $em, Trick $trick, CommentRepository $commentRepo, Paginator $paginator, TrickRepository $trickRepo, string $slug): Response
    {
        $trick = $trickRepo->findOneBy(["slug" => $slug]);

        if ($trick == null) {
            throw $this->createNotFoundException("ttttt");
        }


        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);

        $comments = $commentRepo->findBy(["trick" => $trick]);
        $currentPage = $request->get("page") ?? "1";

        [$commentForOnePage, $totalPages] = $paginator->paginate(items: $comments, currentPage: $currentPage, limit: 10);

        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setCreatedAt(new DateTimeImmutable())
                ->setUser($this->getUser())
                ->setTrick($trick);

            $em->persist($comment);
            $em->flush();

            $url = $this->generateUrl("app.tricks_show_one", ["slug" => $trick->getSlug()]) . "#comments";
  
            return $this->redirect($url);
        }

        return $this->render('trick/show_one.html.twig', [
            "trick" => $trick,
            "commentForm" => $commentForm->createView(),
            "comments" => $commentForOnePage,
            "totalPages" => $totalPages,
            "currentPage" => $currentPage
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
