<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\UsersAddresses;
use App\Form\UsersAddressesType;
use App\Repository\UsersAddressesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user/{id}/addresses')]
final class UsersAddressesController extends AbstractController
{
    public const DEFAULT_PAGE_INDEX = 1;

    #[Route('/list/{page}', name: 'app_users_addresses_list', methods: ['GET'])]
    public function list(Users $user, UsersAddressesRepository $usersAddressesRepository, int $page): Response
    {
        if ($user === null) {
            throw $this->createNotFoundException('User not found');
        }

        if ($page < self::DEFAULT_PAGE_INDEX) {
            throw $this->createNotFoundException('Page not found');
        }

        $paginateResult = $usersAddressesRepository->paginate($user->getId(), $page);
        return $this->render('users_addresses/list.html.twig', [
            'user' => $user,
            'addresses' => $paginateResult['addresses'],
            'total_pages' => $paginateResult['total_pages'],
            'current_page' => $paginateResult['current_page'],
        ]);
    }

    #[Route('/new', name: 'app_users_addresses_new', methods: ['GET', 'POST'])]
    public function new(Users $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($user === null) {
            throw $this->createNotFoundException('User not found');
        }

        $usersAddress = new UsersAddresses();
        $form = $this->createForm(UsersAddressesType::class, $usersAddress);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($usersAddress);
            $entityManager->flush();

            return $this->redirectToRoute('app_users_list', [
                'id' => $user->getId(),
                'page' => self::DEFAULT_PAGE_INDEX
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('users_addresses/new.html.twig', [
            'users_address' => $usersAddress,
            'form' => $form,
        ]);
    }

    #[Route('/edit/{addressType}/{validFrom}', name: 'app_users_addresses_edit', methods: ['GET', 'POST'])]
    public function edit(
        Users $user,
        Request $request,
        string $addressType,
        string $validFrom,
        EntityManagerInterface $entityManager,
    ): Response {
        if ($user === null) {
            throw $this->createNotFoundException('User not found');
        }

        $validFromTimestamp = (int) $validFrom;
        $validFromDate = new \DateTime();
        $validFromDate->setTimestamp($validFromTimestamp);

        $validFromDateMin = clone $validFromDate;
        $validFromDateMin->modify('-1 second');
        $validFromDateMax = clone $validFromDate;
        $validFromDateMax->modify('+1 second');

        $qb = $entityManager->createQueryBuilder();
        $qb->select('a')
            ->from(UsersAddresses::class, 'a')
            ->where('a.user = :userId')
            ->andWhere('a.addressType = :addressType')
            ->andWhere('a.validFrom BETWEEN :validFromMin AND :validFromMax')
            ->setParameter('userId', $user->getId())
            ->setParameter('addressType', $addressType)
            ->setParameter('validFromMin', $validFromDateMin)
            ->setParameter('validFromMax', $validFromDateMax)
            ->setMaxResults(1);

        $address = $qb->getQuery()->getOneOrNullResult();

        if (!$address) {
            throw $this->createNotFoundException('Address not found');
        }

        $form = $this->createForm(UsersAddressesType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_users_list', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('users_addresses/edit_address_form.html.twig', [
            'address' => $address,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{addressType}/{validFrom}', name: 'app_users_addresses_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        string $addressType,
        string $validFrom,
        EntityManagerInterface $entityManager,
        Users $user
    ): Response {
        if ($user === null) {
            throw $this->createNotFoundException('User not found');
        }

        if ($this->isCsrfTokenValid(
            'delete' . $addressType . $validFrom,
            $request->getPayload()->getString('_token')
        )) {
            $qb = $entityManager->createQueryBuilder();
            $qb->delete(UsersAddresses::class, 'a')
                ->where('a.user = :userId')
                ->andWhere('a.addressType = :addressType')
                ->andWhere('a.validFrom = :validFrom')
                ->setParameter('userId', $user->getId())
                ->setParameter('addressType', $addressType)
                ->setParameter('validFrom', $validFrom);

            $qb->getQuery()->execute();
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_users_list', [
            'id' => $user->getId(),
            'page' => self::DEFAULT_PAGE_INDEX
        ], Response::HTTP_SEE_OTHER);
    }
}
