<?php

namespace App\Controller;

use App\Entity\Souscripteur;
use App\Form\SouscripteurType;
use App\Repository\SouscripteurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile/souscripteur")
 */
class SouscripteurController extends AbstractController
{
    /**
     * @Route("/", name="souscripteur_index", methods={"GET"})
     */
    public function index(SouscripteurRepository $souscripteurRepository): Response
    {
        return $this->render('souscripteur/index.html.twig', [
            'souscripteurs' => $souscripteurRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="souscripteur_new", methods={"GET","POST"})
     */
    public function newSouscripteur(Request $request): Response
    {
        $souscripteur = new Souscripteur();
        $form = $this->createForm(SouscripteurType::class, $souscripteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($souscripteur);
            $entityManager->flush();

            $this->addFlash('success','Souscripteur ajouté avec succes');

            return $this->redirectToRoute('souscripteur_index');
        }

        return $this->render('souscripteur/new.html.twig', [
            'souscripteur' => $souscripteur,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="souscripteur_show", methods={"GET"})
     */
    public function showSouscripteur(Souscripteur $souscripteur): Response
    {
        return $this->render('souscripteur/show.html.twig', [
            'souscripteur' => $souscripteur,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="souscripteur_edit", methods={"GET","POST"})
     */
    public function editSouscripteur(Request $request, Souscripteur $souscripteur): Response
    {
        $form = $this->createForm(SouscripteurType::class, $souscripteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success','Souscripteur édité avec succes');

            return $this->redirectToRoute('souscripteur_index', [
                'id' => $souscripteur->getId(),
            ]);
        }

        return $this->render('souscripteur/edit.html.twig', [
            'souscripteur' => $souscripteur,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="souscripteur_delete", methods={"DELETE"})
     */
    public function deleteSouscripteur(Request $request, Souscripteur $souscripteur): Response
    {
        if ($this->isCsrfTokenValid('delete'.$souscripteur->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($souscripteur);
            $entityManager->flush();
        }

        $this->addFlash('success','Souscripteur supprimé avec succes');

        return $this->redirectToRoute('souscripteur_index');
    }
}
