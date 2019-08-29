<?php

namespace App\Controller;

use App\Entity\Intermediaire;
use App\Form\RegistrationFormType;
use App\Security\LoginAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Form\ChangePasswordType;




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
        * @Route("/password", name="reset_password")
        */
        public function resetPassword(Request $request)
        {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

        $passwordEncoder = $this->get('security.password_encoder');
        dump($request->request);die();
        $oldPassword = $request->request->get('change_password')['oldPassword'];

        // Si l'ancien mot de passe est bon
        if ($passwordEncoder->isPasswordValid($user, $oldPassword)) {
        
        $newEncodedPassword = $passwordEncoder->encodePassword($user, $user->getPassword());
        $user->setPassword($newEncodedPassword);

        $em->persist($user);
        $em->flush();

        $this->addFlash('notice', 'Votre mot de passe à bien été changé !');
 
        return $this->render('home\index.html.twig');
        } else {
        $form->addError(new FormError('Ancien mot de passe incorrect'));
        }
        }

        return $this->render("home/index.html.twig"); 
        
        }

        /**
         * @Route("/changepassword", name="change_password")
         */
        public function changePassword(Request $request)
        {
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $form = $this->createForm(ChangePasswordType::class, $user);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()){
                $encoded= $passwordEncoder->encodePassword(
                    $user,
                    $form->get('oldPassword')->getData());

                $old=$user->getpassword();
                    
                if($encoded=$old){
                    $user->setPassword(
                        $passwordEncoder->encodePassword(
                            $user,
                            $form->get('Password')->getData()
                        ));

                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($user);
                        $entityManager->flush();
            
                        $this->addFlash('success','Mot de passe modifié');

                        return $this->render("home/index.html.twig"); 

                }
                return $this->render("home/index.html.twig"); 
            }
            return $this->render('security/reset_password.html.twig', [
                'ChangePasswordType' => $form->createView(),
            ]);
        }

    }

    
   

