<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use App\Helpers\Uploader;
use App\Form\EditUserType;
use App\Form\RegisterType;
use App\Form\ShowUserType;
use App\Form\EditUserAdminType;
use App\Form\ResetPasswordType;
use App\Form\ForgetPasswordType;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

            $this->addFlash("success", "Compte créer avec succès !");

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

    #[IsGranted("ROLE_ADMIN")]
    #[Route(path: "/utilisateurs/edition/{id}", name: "app.users_update")]
    public function updateUser(User $user, Request $request, EntityManagerInterface $em): Response
    {

        $form = $this->createForm(EditUserAdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUpdatedAt(new DateTimeImmutable());
            $em->persist($user);
            $em->flush();

            $this->addFlash("update", "L'utilisateur {$user->getEmail()} a été modifié avec succès !");

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

        $this->addFlash("delete", "L'utilsateur {$user->getEmail()} a été supprimé avec succès !");

        return $this->redirectToRoute("app.users");
    }

    #[Route(path: "/mot-de-passe-oublié", name: "app.forget_password")]
    public function forgetPassword(Request $request, UserRepository $userRepo, MailerInterface $mailer, EntityManagerInterface $em): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute("app.home");
        }

        $user = new User();
        $form = $this->createForm(ForgetPasswordType::class, $user);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $token = bin2hex(random_bytes(32));

            $user = $userRepo->findOneBy(["email" => $form->get("email")->getData()]);
            $user->setResetToken($token);

            $em->persist($user);
            $em->flush();

            $resetLink = $this->generateUrl('app.reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            $email = (new Email())
                ->from('noreply@example.com')
                ->to($user->getEmail())
                ->subject('Réinitialisation de mot de passe')
                ->html('Cliquez sur le lien suivant pour réinitialiser votre mot de passe : <a href="' . $resetLink . '">Réinitialiser le mot de passe</a>');

            $mailer->send($email);
        }

        return $this->render("user/forgetPassword.html.twig", [
            "form" => $form->createView()
        ]);
    }

    #[Route(path: "/mot-de-passe-oublié/{token}", name: "app.reset_password")]
    public function resetPassword(string $token, Request $request, UserRepository $userRepo, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $userRepo->findOneBy(["resetToken" => $token]);

        if ($user) {
            $form = $this->createForm(ResetPasswordType::class);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {

                $user->setResetToken("")
                    ->setPassword($passwordHasher->hashPassword($user, $form->get("password")->getData()));

                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute("app.login");
            }

            return $this->render("user/resetPassword.html.twig", [
                "form" => $form->createView()
            ]);
        }

        return $this->redirectToRoute("app.forget_password");
    }

    #[Route(path: "/profil/{id}", name: "app.show_profile")]
    public function showProfile(User $user): Response
    {
        return $this->render("user/show_profile.html.twig", [
            "user" => $user
        ]);
    }

    #[Route(path: "/profil/edition/{id}", name: "app.edit_profile")]
    public function editProfile(Request $request, User $user, EntityManagerInterface $em, Uploader $uploader): Response
    {
        // Créer le voter, peut modifier uniquement son propre profil
        $form = $this->createForm(EditUserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $user->getProfileImageFile();
            
            $uploader->removeImage("upload/profile/{$user->getProfileImage()}");
            $nameImage = $uploader->newNameImage($imageFile);
            $uploader->upload($imageFile, "upload/profile", $nameImage);

            $user->setProfileImage($nameImage);
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("app.show_profile", ["id" => $user->getId()]);
        }

        return $this->render("user/edit_profile.html.twig", [
            "user" => $user,
            "form" => $form->createView()
        ]);
    }
}
