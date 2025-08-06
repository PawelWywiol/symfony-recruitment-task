<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\UsersAddresses;
use App\Form\UsersType;
use App\Form\UsersAddressesType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UsersController extends AbstractController
{
    public const DEFAULT_PAGE_INDEX = 1;

    #[Route('/users/{page}', name: 'app_users_index', methods: ['GET'])]
    public function index(UsersRepository $usersRepository, int $page = self::DEFAULT_PAGE_INDEX): Response
    {
        if ($page < self::DEFAULT_PAGE_INDEX) {
            throw $this->createNotFoundException('Page not found');
        }

        return $this->render('users/index.html.twig', $usersRepository->paginate($page));
    }

    #[Route('/users/new', name: 'app_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new Users();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('users/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/user/{id}/{page}', name: 'app_users_show', methods: ['GET'])]
    public function show(Users $user, UsersRepository $usersRepository, int $page = 1): Response
    {
        $result = $usersRepository->getUserAddresses($user->getId(), $page);

        return $this->render('users/show.html.twig', [
            'user' => $user,
            'addresses' => $result['addresses'],
            'total_pages' => $result['total_pages'],
            'current_page' => $result['current_page'],
        ]);
    }

    #[Route('/user/{id}/edit', name: 'app_users_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Users $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('users/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/user/{id}', name: 'app_users_delete', methods: ['POST'])]
    public function delete(Request $request, Users $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/address/{userId}/{addressType}/{validFrom}/edit', name: 'app_address_edit', methods: ['GET', 'POST'])]
    public function editAddress(Request $request, int $userId, string $addressType, string $validFrom, EntityManagerInterface $entityManager): Response
    {
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
           ->setParameter('userId', $userId)
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

            return $this->redirectToRoute('app_users_show', ['id' => $address->getUser()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('users/edit_address_form.html.twig', [
            'address' => $address,
            'form' => $form,
        ]);
    }
}
