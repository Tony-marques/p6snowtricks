<?php

namespace App\Controller;

use App\Entity\User;
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

    #[Route(path: "mot-de-passe-oublié", name: "app.forget_password")]
    public function forgetPassword(Request $request): Response{
        if ($this->getUser()){
            return $this->redirectToRoute("app.home");
        }

        $user = new User();
        $form = $this->createForm(ForgetPasswordType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

        }

        return $this->render("user/forgetPassword.html.twig", [
            "form" => $form->createView()
        ]);
    }
}
