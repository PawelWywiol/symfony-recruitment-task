<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UsersControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /**
     * @var EntityRepository<Users>
     */
    private EntityRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        /** @var ManagerRegistry $doctrine */
        $doctrine = static::getContainer()->get(ManagerRegistry::class);
        /** @var EntityManagerInterface $manager */
        $manager = $doctrine->getManager();
        $this->manager = $manager;
        $this->userRepository = $this->manager->getRepository(Users::class);

        // Clean up existing data
        foreach ($this->userRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $this->client->request('GET', '/users');

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Users List');
    }

    public function testList(): void
    {
        $this->client->request('GET', '/users/list/1');

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Users List');
    }

    public function testListWithInvalidPage(): void
    {
        $this->client->request('GET', '/users/list/0');

        self::assertResponseStatusCodeSame(404);
    }

    public function testNew(): void
    {
        $this->client->request('GET', '/users/new');

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('New User');
    }

    public function testNewSubmit(): void
    {
        $this->client->request('GET', '/users/new');

        $this->client->submitForm('Save', [
            'user[firstName]' => 'John',
            'user[lastName]' => 'Doe',
            'user[initials]' => 'JD',
            'user[email]' => 'john.doe@example.com',
            'user[status]' => 'ACTIVE',
        ]);

        self::assertResponseRedirects();

        $users = $this->userRepository->findAll();
        self::assertCount(1, $users);
        self::assertSame('John', $users[0]->getFirstName());
        self::assertSame('Doe', $users[0]->getLastName());
        self::assertSame('john.doe@example.com', $users[0]->getEmail());
    }

    public function testNewSubmitWithValidationErrors(): void
    {
        $this->client->request('GET', '/users/new');

        $this->client->submitForm('Save', [
            'user[firstName]' => '',
            'user[lastName]' => '', // Required field
            'user[email]' => 'invalid-email', // Invalid email
            'user[status]' => 'ACTIVE',
        ]);

        self::assertResponseStatusCodeSame(200); // Form should be re-displayed with errors
    }

    public function testEdit(): void
    {
        // Create a test user
        $user = new Users();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setEmail('john.doe@example.com');
        $user->setStatus('ACTIVE');

        $this->manager->persist($user);
        $this->manager->flush();

        $this->client->request('GET', sprintf('/users/edit/%d', $user->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Edit User');
    }

    public function testEditSubmit(): void
    {
        // Create a test user
        $user = new Users();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setEmail('john.doe@example.com');
        $user->setStatus('ACTIVE');

        $this->manager->persist($user);
        $this->manager->flush();

        $this->client->request('GET', sprintf('/users/edit/%d', $user->getId()));

        $this->client->submitForm('Update', [
            'user[firstName]' => 'Jane',
            'user[lastName]' => 'Smith',
            'user[initials]' => 'JS',
            'user[email]' => 'jane.smith@example.com',
            'user[status]' => 'INACTIVE',
        ]);

        self::assertResponseRedirects();

        $this->manager->refresh($user);
        self::assertSame('Jane', $user->getFirstName());
        self::assertSame('Smith', $user->getLastName());
        self::assertSame('jane.smith@example.com', $user->getEmail());
        self::assertSame('INACTIVE', $user->getStatus());
    }

    public function testEditNotFound(): void
    {
        $this->client->request('GET', '/users/edit/999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDelete(): void
    {
        // Create a test user
        $user = new Users();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setEmail('john.doe@example.com');
        $user->setStatus('ACTIVE');

        $this->manager->persist($user);
        $this->manager->flush();

        $userId = $user->getId();

        $this->client->request('POST', sprintf('/users/delete/%d', $userId), [], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ], '_token=test_token');

        self::assertResponseRedirects();
        self::assertSame(0, $this->userRepository->count([]));
    }

    public function testDeleteNotFound(): void
    {
        $this->client->request('POST', '/users/delete/999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDeleteWithInvalidCsrfToken(): void
    {
        // Create a test user
        $user = new Users();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setEmail('john.doe@example.com');
        $user->setStatus('ACTIVE');

        $this->manager->persist($user);
        $this->manager->flush();

        $this->client->request('POST', sprintf('/users/delete/%d', $user->getId()), [], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ], '_token=invalid_token');

        self::assertResponseRedirects();
        // User should not be deleted due to invalid CSRF token
        self::assertSame(1, $this->userRepository->count([]));
    }
}
