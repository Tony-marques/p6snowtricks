<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditUserAdminType;
use App\Form\ShowUserType;
use App\Form\ForgetPasswordType;
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
        $form = $this->createForm(EditUserAdminType::class, $user);
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

    #[Route(path: "/profil/{id}", name: "app.show_profile")]
    public function showProfile(User $user): Response
    {
        return $this->render("user/show_profile.html.twig", [
            "user" => $user
        ]);
    }

    #[Route(path: "/profil/edition/{id}", name: "app.edit_profile")]
    public function editProfile(Request $request, User $user, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ShowUserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('profileImage')->getData();
            $nameImage = md5(uniqid()) . '.' . $imageFile->guessExtension();
            $imageFile->move("upload/profile", $nameImage);

            $user->setProfileImage($nameImage);
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("app.show_profile");
        }

        return $this->render("user/edit_profile.html.twig", [
            "user" => $user,
            "form" => $form->createView()
        ]);
    }
}
