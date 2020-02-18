<?php

namespace App\DataFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker;
use App\Entity\User;

class UserFixtures extends Fixture
{
    private $passwordEncoder;
    private $faker;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        // Utilisateur admin
        $user = new User();
        $user->setEmail("admin@mds.com");
        $user->setFirstname($this->faker->firstName);
        $user->setLastname($this->faker->lastname);
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'root'));
        $user->setRoles([User::ROLE_ADMIN]);
        $manager->persist($user);

        // Utilisateur app
        $user = new User();
        $user->setEmail("user@mds.com");
        $user->setFirstname($this->faker->firstName);
        $user->setLastname($this->faker->lastname);
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'root'));
        $user->setRoles([User::ROLE_USER]);
        $user->setPoints(1000);
        $manager->persist($user);

        $manager->flush();
    }
}
