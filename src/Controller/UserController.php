<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditUserType;
use App\Form\ForgetPasswordType;
use App\Repository\UserRepository;
use DateTimeImmutable;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route(path: '/inscription', name: 'app.register')]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute("app.home");
        // }

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setCreatedAt(new DateTimeImmutable());
            $user->setPassword($hasher->hashPassword($user, $user->getPassword()));
            $user->setRoles(["ROLE_USER"]);
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("app.login");
        }

        return $this->render('user/register.html.twig', [
            "form" => $form->createView()
        ]);
    }

    #[Route('/connexion', name: 'app.login')]
    public function connexion(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //    return $this->redirectToRoute("app.home");
        // }

        $error = $authenticationUtils->getLastAuthenticationError();
        $username = $authenticationUtils->getLastUsername();

        return $this->render("user/login.html.twig", [
            "error" => $error,
            "username" => $username
        ]);
    }

    #[Route(path: "/deconnexion", name: "app.logout")]
    public function logout(): void
    {
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route(path: "/utilisateurs", name: "app.users")]
    public function allUsers(EntityManagerInterface $em): Response
    {
        return $this->render("user/manage.html.twig", [
            "users" => $em->getRepository(User::class)->findBy([], ["createdAt" => "ASC"])
        ]);
    }

    #[IsGranted("ROLE_USER")]
    #[Route(path: "/utilisateurs/edition/{id}", name: "app.users_update")]
    public function updateUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
//        Créer la logique un utilisateur peut modifier uniquement son profil (les voters)
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUpdatedAt(new DateTimeImmutable());
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("app.users");
        }

        return $this->render("user/edit.html.twig", [
            "form" => $form->createView()
        ]);
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route(path: "/utilisateurs/suppression/{id}", name: "app.users_delete")]
    public function deleteUser(User $user, EntityManagerInterface $em)
    {
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute("app.users");
    }

    #[Route(path: "mot-de-passe-oublié", name: "app.forget_password")]
    public function forgetPassword(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute("app.home");
        }

        $user = new User();
        $form = $this->createForm(ForgetPasswordType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        }

        return $this->render("user/forgetPassword.html.twig", [
            "form" => $form->createView()
        ]);
    }
}
