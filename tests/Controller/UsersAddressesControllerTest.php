<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Users;
use App\Entity\UsersAddresses;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UsersAddressesControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /**
     * @var EntityRepository<UsersAddresses>
     */
    private EntityRepository $usersAddressRepository;

    /**
     * @var EntityRepository<Users>
     */
    private EntityRepository $usersRepository;
    private Users $testUser;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        
        /** @var ManagerRegistry $doctrine */
        $doctrine = self::getContainer()->get(ManagerRegistry::class);

        /** @var EntityManagerInterface $manager */
        $manager = $doctrine->getManager();
        $this->manager = $manager;
        $this->usersAddressRepository = $this->manager->getRepository(UsersAddresses::class);
        $this->usersRepository = $this->manager->getRepository(Users::class);

        // Clean up existing data
        foreach ($this->usersAddressRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
        foreach ($this->usersRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
        $this->manager->flush();

        // Create a test user
        $this->testUser = new Users();
        $this->testUser->setFirstName('John');
        $this->testUser->setLastName('Doe');
        $this->testUser->setEmail('john.doe@example.com');
        $this->testUser->setStatus('ACTIVE');
        $this->manager->persist($this->testUser);
        $this->manager->flush();
    }

    public function testList(): void
    {
        $this->client->request('GET', sprintf('/user/%d/addresses/list/1', $this->testUser->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Users Addresses');
    }

    public function testListWithInvalidPage(): void
    {
        $this->client->request('GET', sprintf('/user/%d/addresses/list/0', $this->testUser->getId()));

        self::assertResponseStatusCodeSame(404);
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('/user/%d/addresses/new', $this->testUser->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('New Address');
    }

    public function testNewSubmit(): void
    {
        $this->client->request('GET', sprintf('/user/%d/addresses/new', $this->testUser->getId()));

        $this->client->submitForm('Save', [
            'users_addresses[addressType]' => 'HOME',
            'users_addresses[validFrom]' => '2024-01-01T00:00:00',
            'users_addresses[street]' => 'Test Street',
            'users_addresses[buildingNumber]' => '123',
            'users_addresses[postCode]' => '12345',
            'users_addresses[city]' => 'Test City',
            'users_addresses[countryCode]' => 'USA',
        ]);

        self::assertResponseRedirects();

        $addresses = $this->usersAddressRepository->findAll();
        self::assertCount(1, $addresses);
        self::assertSame('HOME', $addresses[0]->getAddressType());
        self::assertSame('Test Street', $addresses[0]->getStreet());
    }

    public function testEdit(): void
    {
        // Create a test address
        $address = new UsersAddresses();
        $address->setUser($this->testUser);
        $address->setAddressType('HOME');
        $address->setValidFrom(new \DateTime('2024-01-01 12:00:00'));
        $address->setStreet('Original Street');
        $address->setBuildingNumber('123');
        $address->setPostCode('12345');
        $address->setCity('Original City');
        $address->setCountryCode('USA');

        $this->manager->persist($address);
        $this->manager->flush();

        $validFromTimestamp = $address->getValidFrom()->getTimestamp();

        $this->client->request('GET', sprintf('/user/%d/addresses/edit/HOME/%d', $this->testUser->getId(), $validFromTimestamp));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Edit Address');
    }

    public function testEditSubmit(): void
    {
        // Create a test address
        $address = new UsersAddresses();
        $address->setUser($this->testUser);
        $address->setAddressType('HOME');
        $address->setValidFrom(new \DateTime('2024-01-01 12:00:00'));
        $address->setStreet('Original Street');
        $address->setBuildingNumber('123');
        $address->setPostCode('12345');
        $address->setCity('Original City');
        $address->setCountryCode('USA');

        $this->manager->persist($address);
        $this->manager->flush();

        $validFromTimestamp = $address->getValidFrom()->getTimestamp();

        $this->client->request('GET', sprintf('/user/%d/addresses/edit/HOME/%d', $this->testUser->getId(), $validFromTimestamp));

        $this->client->submitForm('Update', [
            'users_addresses[addressType]' => 'WORK',
            'users_addresses[validFrom]' => '2024-01-01T12:00:00',
            'users_addresses[street]' => 'Updated Street',
            'users_addresses[buildingNumber]' => '456',
            'users_addresses[postCode]' => '54321',
            'users_addresses[city]' => 'Updated City',
            'users_addresses[countryCode]' => 'CAN',
        ]);

        self::assertResponseRedirects();

        $this->manager->refresh($address);
        self::assertSame('WORK', $address->getAddressType());
        self::assertSame('Updated Street', $address->getStreet());
        self::assertSame('Updated City', $address->getCity());
    }

    public function testEditNotFound(): void
    {
        $this->client->request('GET', sprintf('/user/%d/addresses/edit/HOME/1234567890', $this->testUser->getId()));

        self::assertResponseStatusCodeSame(404);
    }

    public function testDelete(): void
    {
        // Create a test address
        $address = new UsersAddresses();
        $address->setUser($this->testUser);
        $address->setAddressType('HOME');
        $address->setValidFrom(new \DateTime('2024-01-01 12:00:00'));
        $address->setStreet('Test Street');
        $address->setBuildingNumber('123');
        $address->setPostCode('12345');
        $address->setCity('Test City');
        $address->setCountryCode('USA');

        $this->manager->persist($address);
        $this->manager->flush();

        $validFromTimestamp = $address->getValidFrom()->getTimestamp();

        $this->client->request('POST', sprintf('/user/%d/addresses/delete/HOME/%d', $this->testUser->getId(), $validFromTimestamp));

        self::assertResponseRedirects();
        self::assertSame(0, $this->usersAddressRepository->count([]));
    }

    public function testDeleteNotFound(): void
    {
        $this->client->request('POST', sprintf('/user/%d/addresses/delete/HOME/1234567890', $this->testUser->getId()));

        self::assertResponseStatusCodeSame(404);
    }
}
