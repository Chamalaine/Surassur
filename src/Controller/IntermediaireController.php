<?php

namespace App\Controller;

use App\Entity\Intermediaire;
use App\Form\IntermediaireType;
use App\Repository\IntermediaireRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/profile/intermediaire")
 */
class IntermediaireController extends AbstractController
{
    /**
     * @Route("/", name="intermediaire_index", methods={"GET"})
     */
    public function index(IntermediaireRepository $intermediaireRepository): Response
    {
        return $this->render('intermediaire/index.html.twig', [
            'intermediaires' => $intermediaireRepository->findAll(),
        ]);
    }

   
    /**
     * @Route("/{id}", name="intermediaire_show", methods={"GET"})
     */
    public function show(Intermediaire $intermediaire): Response
    {
        return $this->render('intermediaire/show.html.twig', [
            'intermediaire' => $intermediaire,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="intermediaire_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Intermediaire $intermediaire): Response
    {
        $form = $this->createForm(IntermediaireType::class, $intermediaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $intermediaire->setPassword(
                $passwordEncoder->encodePassword(
                    $intermediaire,
                    $form->get('plainPassword')->getData()
                )
            );
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('intermediaire_index', [
                'id' => $intermediaire->getId(),
            ]);
        }

        return $this->render('intermediaire/edit.html.twig', [
            'intermediaire' => $intermediaire,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="intermediaire_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Intermediaire $intermediaire): Response
    {
        $id=$intermediaire->getId();
        $currentUserId = $this->getUser()->getId();

        /* Avant de pouvoir supprimer notre Utilisateur de la base de donnée, il faut d'abord le vider de la Session */
        if ($currentUserId == $id)
        {
          $session = $this->get('session');
          $session = new Session();
          $session->invalidate();
        }

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($intermediaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('Home');
    }


}
