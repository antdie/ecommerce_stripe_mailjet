<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Order;
use App\Form\AddressFormType;
use App\Form\EditPasswordFormType;
use App\Service\CartManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/account')]
class AccountController extends AbstractController
{
    #[Route('', name: 'app_account')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig', [
            'controller_name' => 'AccountController',
        ]);
    }

    #[Route('/password', name: 'app_account_password')]
    public function password(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $notification = null;

        $user = $this->getUser();
        $form = $this->createForm(EditPasswordFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $form->get('oldPassword')->getData();
            if ($userPasswordHasher->isPasswordValid($user, $oldPassword)) {
                $newPassword = $form->get('newPassword')->getData();
                $password = $userPasswordHasher->hashPassword($user, $newPassword);

                $user->setPassword($password);
                $entityManager->flush();
                $notification = 'Password updated.';
            } else {
                $notification = 'Your old password isn\'t correct.';
            }
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification,
            'controller_name' => 'AccountPasswordController',
        ]);
    }

    #[Route('/addresses', name: 'app_account_addresses')]
    public function addresses(): Response
    {
        return $this->render('account/addresses.html.twig', [
            'controller_name' => 'AccountAddressesController',
        ]);
    }

    #[Route('/address/add', name: 'app_account_address_add')]
    public function addressAdd(Request $request, EntityManagerInterface $entityManager, CartManager $cartManager): Response
    {
        $address = new Address();
        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setCustomer($this->getUser());
            $entityManager->persist($address);
            $entityManager->flush();

            if ($cartManager->get()) {
                return $this->redirectToRoute('app_order');
            } else {
                return $this->redirectToRoute('app_account_addresses');
            }
        }

        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView(),
            'controller_name' => 'AccountAddressFormController',
        ]);
    }

    #[Route('/address/edit/{id}', name: 'app_account_address_edit')]
    public function addressEdit($id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $address = $entityManager->getRepository(Address::class)->findOneById($id);

        if (!$address || $address->getCustomer() !== $this->getUser()) {
            throw $this->createNotFoundException('Address not found');
        }

        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_account_addresses');
        }

        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView(),
            'controller_name' => 'AccountAddressFormController',
        ]);
    }

    #[Route('/address/delete/{id}', name: 'app_account_address_delete')]
    public function addressDelete($id, EntityManagerInterface $entityManager): Response
    {
        $address = $entityManager->getRepository(Address::class)->findOneById($id);

        if ($address && $address->getCustomer() === $this->getUser()) {
            $entityManager->remove($address);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_account_addresses');
    }

    #[Route('/orders', name: 'app_account_orders')]
    public function orders(EntityManagerInterface $entityManager): Response
    {
        $orders = $entityManager->getRepository(Order::class)->findBy(
            ['customer' => $this->getUser(), 'state' => ['paid', 'assigned', 'delivered']],
            ['id' => 'DESC'],
        );

        return $this->render('account/orders.html.twig', [
            'orders' => $orders,
            'controller_name' => 'OrdersController',
        ]);
    }

    #[Route('/order/{id}', name: 'app_account_order')]
    public function order($id, EntityManagerInterface $entityManager): Response
    {
        $order = $entityManager->getRepository(Order::class)->findOneById($id);

        if (!$order || $order->getCustomer() !== $this->getUser()) {
            throw $this->createNotFoundException('Order not found');
        }

        return $this->render('account/order.html.twig', [
            'order' => $order,
            'controller_name' => 'OrdersController',
        ]);
    }
}
