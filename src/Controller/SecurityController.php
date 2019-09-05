<?php

namespace App\Controller;

use App\Entity\Intermediaire;
use App\Form\ChangePasswordType;
use App\Form\RegistrationFormType;
use App\Form\ForgottenPasswordType;
use App\Security\LoginAuthenticator;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;





class SecurityController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginAuthenticator $authenticator): Response
    {
        $user = new Intermediaire();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setDateCreation( new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            $this->addFlash('success','Inscription réussie');

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

     /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //    $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();


        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }



        /**
        * @Route("/password", name="change_password")
        */
        public function changePassword(Request $request, UserPasswordEncoderInterface $passwordEncoder)
        {
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $form = $this->createForm(ChangePasswordType::class, $user);
            $ok = 1;

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                
                $oldPassword = $request->request->get('change_password')['oldPassword'];

                // Si l'ancien mot de passe est bon
                if ($passwordEncoder->isPasswordValid($user, $oldPassword)) {
                
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $em->persist($user);
                $em->flush();

                $this->addFlash('notice', 'Votre mot de passe à bien été changé !');
        
                return $this->render('home\index.html.twig');
                } 
                else {
                    $form->addError(new FormError('Ancien mot de passe incorrect'));
                }
        }

        return $this->render("security/change_password.html.twig", array(
                                'form' => $form->createView(),
        )); 
        
        }


        /**
     * @Route("/forgottenPassword", name="forgotten_password", methods="GET|POST")
     */
    public function forgottenPassword(Request $request, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator): Response
    {
        if ($request->isMethod('POST')) {
 
            $email = $request->request->get('email');
 
            $entityManager = $this->getDoctrine()->getManager();
            $intermediaire = $entityManager->getRepository(Intermediaire::class)->findOneByEmail($email);
 
            if ($intermediaire === null) {
                $this->addFlash('danger', 'Email Inconnu, recommence !');
                return $this->redirectToRoute('forgotten_password');
            }
            $token = $tokenGenerator->generateToken();
 
            try{
                $intermediaire->setResetToken($token);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('Home');
            }
 
            $url = $this->generateUrl('app_reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);
 
            $message = (new \Swift_Message('Oubli de mot de passe - Réinisialisation'))
                ->setFrom("surassur.amc@gmail.com")
                ->setTo($intermediaire->getEmail())
                ->setBody(
                $this->renderView(
                    'security/MailPassword.html.twig',
                    [
                        'intermediaire'=>$intermediaire,
                        'url'=>$url
                    ]
                ),
                    'text/html'
                );
            $mailer->send($message);
 
            $this->addFlash('notice', 'Mail envoyé, vérifier votre boîte email!');
 
            return $this->redirectToRoute('app_login');
        }
 
        return $this->render('security/forgotten_Password.html.twig');
    }

     /**** Réinisialiation du mot de passe par mail
     * @Route("/resetPassword/{token}", name="reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {
        //Reset avec le mail envoyé
        if ($request->isMethod('POST')) {
            $entityManager = $this->getDoctrine()->getManager();
 
            $intermediaire = $entityManager->getRepository(Intermediaire::class)->findOneByResetToken($token);
            /* @var $user User */
 
            if ($user === null) {
                $this->addFlash('danger', 'Mot de passe non reconnu');
                return $this->redirectToRoute('home');
            }
 
            $intermediaire->setResetToken(null);
            $intermediaire->setPassword($passwordEncoder->encodePassword($intermediaire, $request->request->get('password')));
            $entityManager->flush();
 
            $this->addFlash('notice', 'Mot de passe mis à jour !');
 
            return $this->redirectToRoute('security_login');
        }else {
 
            return $this->render('security/resetPassword.html.twig', ['token' => $token]);
        }
 
    }

    }

    
   

