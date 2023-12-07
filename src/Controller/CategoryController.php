<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted("ROLE_ADMIN")]
#[Route(path: "/categories", name: "categories_")]
class CategoryController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/creer', name: 'create', methods: ["GET", "POST"])]
    public function index(Request $request, SluggerInterface $slugger): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setCreatedAt(new \DateTimeImmutable())
                ->setSlug($slugger->slug($category->getName())->lower());

            $this->em->persist($category);
            $this->em->flush();

            $this->addFlash("success", "Catégorie créée avec succès !");

            return $this->redirectToRoute("categories_manage");
        }

        return $this->render('category/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    
    #[Route(path: "/", name: "manage", methods: ["GET"])]
    public function manageCategory(): Response
    {
        return $this->render("category/manage.html.twig", [
            "categories" => $this->em->getRepository(Category::class)->findBy([], ["createdAt" => "DESC"]),
        ]);
    }

    
    #[Route(path: "/edition/{slug}", name: "edit", methods: ["GET", "POST"])]
    public function editCategory(Category $category, Request $request, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category
                ->setSlug($slugger->slug($category->getName())->lower())
                ->setUpdatedAt(new \DateTimeImmutable());

            $this->em->persist($category);
            $this->em->flush();

            $this->addFlash("success", "Catégorie modifiée avec succès !");

            return $this->redirectToRoute("categories_manage");
        }

        return $this->render("category/edit.html.twig", [
            "category" => $category,
            "form" => $form->createView()
        ]);
    }

    
    #[Route(path: "/supprimer/{slug}", name: "delete", methods: ["GET"])]
    public function delete(Category $category): Response
    {
        $this->em->remove($category);
        $this->em->flush();

        $this->addFlash("delete", "La catégorie {$category->getName()} a été supprimée avec succès !");

        return $this->redirectToRoute("categories_manage");
    }
}
