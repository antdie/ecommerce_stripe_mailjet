<?php

namespace App\Controller;

use App\Form\ContactFormType;
use App\Service\MailjetManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailjetManager $mailjetManager): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('notice', 'Thanks for your message, we\'ll answer asap !');
            $mailjetManager->send(
                $this->getParameter('app.contact_email'),
                'Support best shop',
                'Contact from '.$form->get('firstname')->getData().' '.$form->get('lastname')->getData().' - '.$form->get('email')->getData(),
                $form->get('message')->getData()
            );
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
            'controller_name' => 'ContactController',
        ]);
    }
}
