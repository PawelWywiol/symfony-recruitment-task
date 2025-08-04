<?php

namespace App\Controller;

use App\Entity\UsersAddresses;
use App\Form\UsersAddressesType;
use App\Repository\UsersAddressesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users/addresses')]
final class UsersAddressesController extends AbstractController
{
    #[Route(name: 'app_users_addresses_index', methods: ['GET'])]
    public function index(UsersAddressesRepository $usersAddressesRepository): Response
    {
        return $this->render('users_addresses/index.html.twig', [
            'users_addresses' => $usersAddressesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_users_addresses_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $usersAddress = new UsersAddresses();
        $form = $this->createForm(UsersAddressesType::class, $usersAddress);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($usersAddress);
            $entityManager->flush();

            return $this->redirectToRoute('app_users_addresses_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('users_addresses/new.html.twig', [
            'users_address' => $usersAddress,
            'form' => $form,
        ]);
    }

    #[Route('/{user}', name: 'app_users_addresses_show', methods: ['GET'])]
    public function show(UsersAddresses $usersAddress): Response
    {
        return $this->render('users_addresses/show.html.twig', [
            'users_address' => $usersAddress,
        ]);
    }

    #[Route('/{user}/edit', name: 'app_users_addresses_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UsersAddresses $usersAddress, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UsersAddressesType::class, $usersAddress);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_users_addresses_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('users_addresses/edit.html.twig', [
            'users_address' => $usersAddress,
            'form' => $form,
        ]);
    }

    #[Route('/{user}', name: 'app_users_addresses_delete', methods: ['POST'])]
    public function delete(Request $request, UsersAddresses $usersAddress, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$usersAddress->getUser(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($usersAddress);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_users_addresses_index', [], Response::HTTP_SEE_OTHER);
    }
}
