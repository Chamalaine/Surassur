<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use ReCaptcha\ReCaptcha;


class HomeController extends AbstractController
{
    /**
     * @Route("/", name="Home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }


    /**
     * @Route("/", name="wrong_pass")
     */
    public function wrongPass()
    {
        return $this->render('security/forgotten_password.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }


    /**
     * @Route("/contact", name="contact")
     */

    public function contact(Request $request)
    {

        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        $recaptcha = new ReCaptcha("6LevPbwUAAAAALFU7QUz-1eTm6zlx4pcn1f2vblQ");
        $resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());

        if ($form->isSubmitted() && $form->isValid()) {
            if ($resp->isSuccess()) {

                $email=$form->get('email')->getData();
                $objet=$form->get('objet')->getData();
                $message=$form->get('message')->getData();
                $telephone=$form->get('telephone')->getData();

                $message = (new \Swift_Message('Oubli de mot de passe - Réinisialisation'))
                ->setFrom("surassur.amc@gmail.com")
                ->setTo("soufi.chamalaine@gmail.com")
                ->setBody(
                $this->renderView(
                    'home/MailContact.html.twig',
                    [
                        'email'=>$email,
                        'objet'=>$objet,
                        'message'=>$message,
                        'telephone'=>$telephone
                    ]
                ),
                    'text/html'
                );
            $mailer->send($message);


            }

            $this->addFlash('error', 'Veuillez valider le reCaptcha');

            return $this->render('home/contact.html.twig',[
                'contactForm' => $form->createView(),
            ]);

        }

        return $this->render('home/contact.html.twig',[
            'contactForm' => $form->createView(),
        ]);
    }
}
