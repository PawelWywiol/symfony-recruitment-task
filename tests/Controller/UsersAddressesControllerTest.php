<?php

namespace App\Tests\Controller;

use App\Entity\UsersAddresses;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UsersAddressesControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $usersAddressRepository;
    private string $path = '/users/addresses/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->usersAddressRepository = $this->manager->getRepository(UsersAddresses::class);

        foreach ($this->usersAddressRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('UsersAddress index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'users_address[addressType]' => 'Testing',
            'users_address[validFrom]' => 'Testing',
            'users_address[postCode]' => 'Testing',
            'users_address[city]' => 'Testing',
            'users_address[countryCode]' => 'Testing',
            'users_address[street]' => 'Testing',
            'users_address[buildingNumber]' => 'Testing',
            'users_address[createdAt]' => 'Testing',
            'users_address[updatedAt]' => 'Testing',
            'users_address[user]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->usersAddressRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new UsersAddresses();
        $fixture->setAddressType('My Title');
        $fixture->setValidFrom('My Title');
        $fixture->setPostCode('My Title');
        $fixture->setCity('My Title');
        $fixture->setCountryCode('My Title');
        $fixture->setStreet('My Title');
        $fixture->setBuildingNumber('My Title');
        $fixture->setCreatedAt('My Title');
        $fixture->setUpdatedAt('My Title');
        $fixture->setUser('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('UsersAddress');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new UsersAddresses();
        $fixture->setAddressType('Value');
        $fixture->setValidFrom('Value');
        $fixture->setPostCode('Value');
        $fixture->setCity('Value');
        $fixture->setCountryCode('Value');
        $fixture->setStreet('Value');
        $fixture->setBuildingNumber('Value');
        $fixture->setCreatedAt('Value');
        $fixture->setUpdatedAt('Value');
        $fixture->setUser('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'users_address[addressType]' => 'Something New',
            'users_address[validFrom]' => 'Something New',
            'users_address[postCode]' => 'Something New',
            'users_address[city]' => 'Something New',
            'users_address[countryCode]' => 'Something New',
            'users_address[street]' => 'Something New',
            'users_address[buildingNumber]' => 'Something New',
            'users_address[createdAt]' => 'Something New',
            'users_address[updatedAt]' => 'Something New',
            'users_address[user]' => 'Something New',
        ]);

        self::assertResponseRedirects('/users/addresses/');

        $fixture = $this->usersAddressRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getAddressType());
        self::assertSame('Something New', $fixture[0]->getValidFrom());
        self::assertSame('Something New', $fixture[0]->getPostCode());
        self::assertSame('Something New', $fixture[0]->getCity());
        self::assertSame('Something New', $fixture[0]->getCountryCode());
        self::assertSame('Something New', $fixture[0]->getStreet());
        self::assertSame('Something New', $fixture[0]->getBuildingNumber());
        self::assertSame('Something New', $fixture[0]->getCreatedAt());
        self::assertSame('Something New', $fixture[0]->getUpdatedAt());
        self::assertSame('Something New', $fixture[0]->getUser());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new UsersAddresses();
        $fixture->setAddressType('Value');
        $fixture->setValidFrom('Value');
        $fixture->setPostCode('Value');
        $fixture->setCity('Value');
        $fixture->setCountryCode('Value');
        $fixture->setStreet('Value');
        $fixture->setBuildingNumber('Value');
        $fixture->setCreatedAt('Value');
        $fixture->setUpdatedAt('Value');
        $fixture->setUser('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/users/addresses/');
        self::assertSame(0, $this->usersAddressRepository->count([]));
    }
}
