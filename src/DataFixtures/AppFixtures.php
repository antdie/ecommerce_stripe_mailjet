<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Carrier;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;
    private $slugger;

    private static $productNames = [
        'Jean',
        'T-shirt',
        'Short',
        'Basket',
    ];

    private static $categoryNames = [
        'Clothing',
        'Sportswear',
        'Swag',
    ];

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, SluggerInterface $slugger)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $user1 = new User();
        $user1->setRoles(['ROLE_SUPER_ADMIN'])
            ->setEmail('sa@example.com')
            ->setPassword(
                $this->userPasswordHasher->hashPassword($user1, 'sa')
            )
            ->setFirstname($faker->firstNameMale())
            ->setLastname($faker->lastName());
        $manager->persist($user1);

        $user2 = new User();
        $user2->setRoles(['ROLE_ADMIN'])
            ->setEmail('a@example.com')
            ->setPassword(
                $this->userPasswordHasher->hashPassword($user2, 'a')
            )
            ->setFirstname($faker->firstNameMale())
            ->setLastname($faker->lastName());
        $manager->persist($user2);

        $user3 = new User();
        $user3->setRoles(['ROLE_USER'])
            ->setEmail('u@example.com')
            ->setPassword(
                $this->userPasswordHasher->hashPassword($user3, 'u')
            )
            ->setFirstname($faker->firstNameMale())
            ->setLastname($faker->lastName());
        $manager->persist($user3);

        foreach (self::$categoryNames as $categoryName) {
            $category = new Category();
            $category->setName($categoryName)
                ->setSlug($this->slugger->slug($categoryName)->lower());
            $manager->persist($category);
        }

        $carrier1 = new Carrier();
        $carrier1->setName('Amazon Prime')
            ->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit.')
            ->setPrice('999');
        $manager->persist($carrier1);

        $carrier2 = new Carrier();
        $carrier2->setName('Colissimo')
            ->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit.')
            ->setPrice('399');
        $manager->persist($carrier2);

        $manager->flush();


        $users = $manager->getRepository(User::class)->findAll();
        for ($i = 0; $i < 10; $i++) {
            $user = $users[array_rand($users)];
            $address = new Address();
            $address->setName('Adress of '.$faker->streetSuffix())
                ->setCustomer($user)
                ->setFirstname($user->getFirstname())
                ->setLastname($user->getLastname())
                ->setAddress($faker->streetAddress())
                ->setCode($faker->postcode())
                ->setCity($faker->city())
                ->setCountry($faker->countryCode())
                ->setPhone($faker->e164PhoneNumber());
            $manager->persist($address);
        }

        $categories = $manager->getRepository(Category::class)->findAll();
        for ($i = 0; $i < 50; $i++) {
            $productName = $faker->randomElement(self::$productNames);
            $category = $categories[array_rand($categories)];
            $product = new Product();
            $product->setCategory($category)
                ->setName($productName)
                ->setSlug($this->slugger->slug($productName)->lower())
                ->setImage($faker->imageUrl(660, 480, 'Product', true, $productName, true))
                ->setDescription($faker->paragraph())
                ->setPrice($faker->numberBetween(1500, 8000));
            if ($i < 4) {
                $product->setHomepage(true);
            }
            $manager->persist($product);
        }

        $manager->flush();
    }
}
