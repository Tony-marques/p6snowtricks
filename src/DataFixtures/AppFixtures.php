<?php

namespace App\DataFixtures;

use App\DataFixtures\FixturesHelpers\CategoryChoice;
use App\DataFixtures\FixturesHelpers\VideoChoice;
use App\Entity\Trick;
use DateTimeImmutable;
use App\Entity\Category;
use App\Entity\Image;
use App\Entity\User;
use App\Entity\Video;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    private SluggerInterface $slugger;
    private UserPasswordHasherInterface $hasher;
    private CategoryChoice $categoryChoice;
    private VideoChoice $videoChoice;

    public function __construct(SluggerInterface $slugger, UserPasswordHasherInterface $hasher, CategoryChoice $categoryChoice, VideoChoice $videoChoice)
    {
        $this->slugger = $slugger;
        $this->hasher = $hasher;
        $this->categoryChoice = $categoryChoice;
        $this->videoChoice = $videoChoice;
    }

    public function load(ObjectManager $manager): void
    {
        $categories = [];
        $users = [];
        $categoriesName = ['Grabs', 'Rotations', 'Flips', 'Rotations désaxées', 'Slides', 'One foot', 'Old school'];
        $tricksName = ['Mute', 'Indy', 'Backflip', 'Frontflip', '360', '720', 'Misty', 'Tail slide', 'Method air', 'Backside air'];

        // Categories
        foreach ($categoriesName as $categoryName) {
            $category = new Category();
            $category->setName($categoryName)
                ->setSlug($this->slugger->slug($categoryName))
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($category);
            $categories[] = $category;
        }

        // Users
        for ($u = 1; $u < 10; $u++) {
            $user = new User();
            $user->setAge(rand(15, 99))
                ->setPassword($this->hasher->hashPassword($user, "P@ssword123"))
                ->setPseudo("pseudo $u")
                ->setEmail("user$u@gmail.com")
                ->setCreatedAt(new DateTimeImmutable());

            // Admin
            if ($u == 1) {
                $user->setRoles(["ROLE_ADMIN"])
                    ->setEmail("admin@gmail.com");
            };

            $manager->persist($user);
            $users[] = $user;
        }

        // Tricks
        foreach ($tricksName as $trick) {
            $newTrick = new Trick();

            $userKey = \array_rand($users);

            $categoryChoice = $this->categoryChoice->choice($trick, $categories);

            $newTrick->setCategory($categoryChoice)
                ->setUser($users[$userKey])
                ->setName($trick)
                ->setDescription("Description du trick $trick")
                ->setSlug($this->slugger->slug($trick)->lower())
                ->setMainImageName("$trick main.jpg")
                ->setCreatedAt(new DateTimeImmutable());

            // Images
            for ($i = 1; $i < 4; $i++) {
                $image = new Image();
                $image->setName("$trick $i.jpg")
                    ->setCreatedAt(new DateTimeImmutable())
                    ->setTrick($newTrick);

                $manager->persist($image);
            }

            $videoUrl = $this->videoChoice->choice($trick);

            $video = new Video();
            $video->setUrl($videoUrl)
                ->setTrick($newTrick);

            $manager->persist($video);
            $manager->persist($newTrick);
        }

        $manager->flush();
    }
}
