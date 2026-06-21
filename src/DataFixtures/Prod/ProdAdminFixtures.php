<?php

declare(strict_types=1);

namespace App\DataFixtures\Prod;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProdAdminFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        #[Autowire(env: 'PASS_ADMIN')]
        private readonly string $motDePasse,
    ) {
    }

    /** @return string[] */
    public static function getGroups(): array
    {
        return ['prod', 'prod-admin'];
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('michaeljpitz@gmail.com')
            ->setUserName('Michael Pitz')
            ->setRoles(['ROLE_ADMIN'])
            ->setStatus(1)
            ->setBiography('Développeur PHP/Symfony passionné par les bonnes pratiques et l\'architecture logicielle.')
            ->setExternalLink1('https://github.com/mikhawa')
            ->setPassword($this->passwordHasher->hashPassword($admin, $this->motDePasse));

        $manager->persist($admin);
        $manager->flush();
    }
}
