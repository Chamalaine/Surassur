<?php

namespace App\Controller;

use App\Entity\Intermediaire;
use App\Form\ResetPasswordType;
use App\Form\ChangePasswordType;
use App\Form\RegistrationFormType;
use App\Form\ForgottenPasswordType;
use App\Security\LoginAuthenticator;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use ReCaptcha\ReCaptcha;
use Yectep\PhpSpreadsheetBundle\Factory;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginAuthenticator $authenticator): Response
    {


        $user = new Intermediaire();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        $recaptcha = new ReCaptcha("6LevPbwUAAAAALFU7QUz-1eTm6zlx4pcn1f2vblQ");
        $resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());

            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($resp->isSuccess()) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $user->setDateCreation( new \DateTime());
    
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
    
                
    
                $this->addFlash('success','Inscription réussie');
    
                return $this->redirectToRoute('Home');
            }

                    $this->addFlash('error', 'Veuillez valider le reCaptcha');

            return $this->render('registration/register.html.twig', [
                'registrationForm' => $form->createView(),
            ]);


        }
        

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

     /**
     * @Route("/connexion", name="login")
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
     * @Route("/deconnexion", name="logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');

    }



        /**
        * @Route("/motdepasse", name="change_password")
        */
        public function changePassword(Request $request, UserPasswordEncoderInterface $passwordEncoder)
        {
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $form = $this->createForm(ChangePasswordType::class, $user);
           

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

                $this->addFlash('success', 'Votre mot de passe à bien été changé !');
        
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
     * @Route("/oublimotdepasse", name="forgotten_password", methods="GET|POST")
     */
    public function forgottenPassword(Request $request, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator): Response
    {       
        $form = $this->createForm(ForgottenPasswordType::class);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()){
 
            $email =$form->get('email')->getData();

 
            $em = $this->getDoctrine()->getManager();
            $intermediaire = $em->getRepository(Intermediaire::class)->findOneByEmail($email);
 
            if ($intermediaire === null) {
                $this->addFlash('error', 'Email Inconnu, recommence !');
                return $this->redirectToRoute('forgotten_password');
            }
            
            /* Création du token à l'aide du TokenGeneratorInterface fournie nativement par Symfony */
            $token = $tokenGenerator->generateToken();
            $current = New \Datetime();
            
            /* On insère le token crée à notre utilisateur */
            $intermediaire->setResetToken($token);
            $intermediaire->setDateToken($current);
            $em->persist($intermediaire);
            $em->flush();
            
             /* On crée une url avec notre token */
            $url = $this->generateUrl('reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);
            
            /* Envoi de mail à l'aide de swift mailer */
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
 
            $this->addFlash('success', 'Mail envoyé, vérifier votre boîte email!');
 
            return $this->redirectToRoute('login');
        }
 
        return $this->render('security/forgotten_Password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

     /**
     * @Route("/resetPassword/{token}", name="reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $intermediaire = $em->getRepository(Intermediaire::class)->findOneByResetToken($token);
        $date=$intermediaire->getDateToken();
        $current = new \DateTime();
        $interval = $date->diff($current)->format('%i');

        if($interval>20){
            $this->addFlash('error', 'Lien expiré');
            return $this->redirectToRoute('Home');
        }
        //Reset avec le mail envoyé
        if ($form->isSubmitted() && $form->isValid()){            

            if ($intermediaire === null) {
                $this->addFlash('error', '');
                return $this->redirectToRoute('Home');
            }

            $password = $form->get('password')->getData();
            

            /* On remet notre Token de reinisialisation de mot de passe sur nule */
            $intermediaire->setResetToken(null);
            $intermediaire->setPassword($passwordEncoder->encodePassword($intermediaire, $password));
            $em->persist($intermediaire);
            $em->flush();
 
            $this->addFlash('success', 'Mot de passe mis à jour !');
 
            return $this->redirectToRoute('login');
        }
        else {
            return $this->render('security/resetPassword.html.twig', ['token' => $token,'form' => $form->createView(),]
        );
        }
 
    }





}

    
   

