<?php

namespace App\Controller;

use App\Entity\Trick;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Form\TrickType;
use App\Form\CommentType;
use App\Helpers\Paginator;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
// \dd($trick);
        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $trick->getMainImage();
            $nameImage = md5(uniqid()) . '.' . $imageFile->getFile()->guessExtension();
            $imageFile->getFile()->move("upload/tricks", $nameImage);

            $imageFile->setName($nameImage);
            $imageFile->setCreatedAt(new DateTimeImmutable());
            $imageFile->setTrick($trick);
            $trick->addImage($imageFile);
            // \dd($imageFile);

            // foreach ($trick->getImages() as $image) {

            //     $file = $image->getFile();
            //     $name = md5(uniqid()) . '.' . $file->guessExtension();

            //     $file->move(
            //         "upload/tricks",
            //         $name
            //     );

            //     $image->setName($name);
            //     $image->setCreatedAt(new DateTimeImmutable());
            //     $image->setTrick($trick);
            //     $trick->addImage($image);

            // }

            $trick->setCreatedAt(new DateTimeImmutable())
                ->setUser($this->getUser())
                ->setSlug($slugger->slug($trick->getName())->lower())
                ->setMainImage($imageFile);

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

    #[IsGranted("ROLE_USER")]
    #[Route(
        path: '/supprimer/{slug}',
        name: '_delete',
        methods: ["GET", "POST"]
    )]
    public function delete(Request $request, EntityManagerInterface $em, Trick $trick): JsonResponse|Response
    {
        $this->denyAccessUnlessGranted("TRICK_DELETE", $trick);

        $em->remove($trick);
        $em->flush();

        // \dd($trick->getMainImage()->getName());

        if (\file_exists("upload/tricks/{$trick->getMainImage()->getName()}")) {
            \unlink("upload/tricks/{$trick->getMainImage()->getName()}");
        }

        foreach ($trick->getImages() as $image) {
            if (\file_exists("upload/tricks/{$image->getName()}")) {
                \unlink("upload/tricks/{$image->getName()}");
            }
        }

        return $this->redirectToRoute("app.home");

        // if ($request->getMethod() === "POST") {
        //     $this->addFlash("success", "Le trick {$trick->getName()} a été supprimé avec succès !");
        // }

        // return $this->json([
        //     "message" => "Le trick {$trick->getName()} a été supprimé avec succès !",
        // ]);
    }

    #[IsGranted("ROLE_USER")]
    #[Route(
        '/editer/{slug}',
        name: '_edit',
        methods: ["POST"]
    )]
    public function edit(Request $request, EntityManagerInterface $em, Trick $trick, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted("TRICK_EDIT", $trick);

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        $userRole = $this->getUser()->getRoles();

        if ($form->isSubmitted() && $form->isValid()) {
            if (\file_exists("upload/tricks/{$trick->getMainImage()->getName()}")) {
                \unlink("upload/tricks/{$trick->getMainImage()->getName()}");
            }

            $imageFile = $form->get('mainImage')->getData();
            $nameImage = md5(uniqid()) . '.' . $imageFile->getFile()->guessExtension();
            $imageFile->getFile()->move("upload/tricks", $nameImage);

            foreach ($trick->getImages() as $image) {
                if (\file_exists("upload/tricks/{$image->getName()}")) {
                    \unlink("upload/tricks/{$image->getName()}");
                }

                $file = $image->getFile();
                \dd($file->guessExtension());

                $name = md5(uniqid()) . '.' . $file->guessExtension();

                $file->move(
                    "upload/tricks",
                    $name
                );

                $image->setName($name);
                $image->setCreatedAt(new DateTimeImmutable());
                $image->setTrick($trick);
                $trick->addImage($image);
                // \dd($image->getName());
            }

            $trick->setMainImage($nameImage);

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
    public function showOne(Request $request, EntityManagerInterface $em, Trick $trick, CommentRepository $commentRepo, Paginator $paginator): Response
    {
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);

        $comments = $commentRepo->findBy(["trick" => $trick]);
        $currentPage = $request->get("page") ?? "1";

        [$commentForOnePage, $totalPages] = $paginator->paginate(items: $comments, currentPage: $currentPage, limit: 4);

        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setCreatedAt(new DateTimeImmutable())
                ->setUser($this->getUser())
                ->setTrick($trick);

            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute("app.tricks_show_one", ["slug" => $trick->getSlug()]);
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
