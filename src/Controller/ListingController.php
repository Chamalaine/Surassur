<?php

namespace App\Controller;

use Swiftmailer\Swiftmailer;
use Dompdf\Dompdf;
use App\Entity\Souscripteur;
use App\Entity\Listing;
use App\Form\ListingType;
use App\Repository\ListingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile/listing")
 */
class ListingController extends AbstractController
{
    /**
     * @Route("/", name="listing_index", methods={"GET"})
     */
    public function index(ListingRepository $listingRepository): Response
    {
        $id = $this->getUser()->getId();
        return $this->render('listing/index.html.twig', [
            'listings' => $listingRepository->findByIntermediaire($id),
        ]);
    }

    /**
     * @Route("/new", name="listing_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $listing = new Listing();
        $form = $this->createForm(ListingType::class, $listing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $listing->setIntermediaire($this->getUser());
            $listing->setDateCreation(new \DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($listing);
            $entityManager->flush();

            $this->addFlash('success','Listing crée avec succes');

            return $this->redirectToRoute('assure_ajout',["id" => $listing->getId()]);
        }

        return $this->render('listing/new.html.twig', [
            'listing' => $listing,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="listing_show", methods={"GET"})
     */
    public function show(Listing $listing): Response
    {
        $tab = $listing->getassures();
        $nb = count($tab);
        return $this->render('listing/show.html.twig', [
            'listing' => $listing,
            'nb' => $nb,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="listing_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Listing $listing): Response
    {
        $form = $this->createForm(ListingType::class, $listing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success','Listing édité avec succes');

            return $this->redirectToRoute('listing_index', [
                'id' => $listing->getId(),
            ]);

        }

        $this->addFlash('error',"Erreur ! ");
        return $this->render('listing/edit.html.twig', [
            'listing' => $listing,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="listing_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Listing $listing): Response
    {
        if ($this->isCsrfTokenValid('delete'.$listing->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($listing);
            $entityManager->flush();
        }


        $this->addFlash('success','Listing supprimé avec succes');

        return $this->redirectToRoute('listing_index');
    }

     /**
     * @Route("/{id}/listing_pdf}", name="listing_pdf", methods={"GET"})
     */
    public function listingPdf(Listing $listing)
    {
        /* Création de listing à l'aide de dompdf */

        $dompdf = new Dompdf();

        $html = $this->renderView(
            // templates/emails/registration.html.twig
            'listing/envoie.html.twig',
            ['listing' => $listing]);
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        return $dompdf->stream($listing->getNom().".pdf");
    }

    /**
     * @Route("/{id}/envoyer}", name="listing_envoyer", methods={"GET"})
     */
    public function sendListing(Listing $listing, \Swift_Mailer $mailer)
    {

        /* Envoie de Listing par mail à l'aide de SwiftMailer */
        $listing->setDateEnvoi(new \DateTime());
        $tab = $listing->getassures();
        $nb = count($tab);
        $message = (new \Swift_Message($listing->getNom()))
            ->setFrom('surassur.amc@gmail.com')
            ->setTo($listing->getSouscripteur()->getEmail())
            ->setBody(
                $this->renderView(
  
                    'listing/envoie.html.twig',
                    ['listing' => $listing,
                    'nb' => $nb,]
                ),
                'text/html'
            )
        ;
    
        $mailer->send($message);

        $this->addFlash('success','Listing envoyé avec succes');
    
        return $this->render('listing/show.html.twig', [
            'listing' => $listing,
            'nb' => $nb,
        ]);
    }

   
}