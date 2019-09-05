<?php

namespace App\Controller;

use Dompdf\Dompdf;
use App\Entity\Listing;
use App\Form\ListingType;
use App\Entity\Souscripteur;
use Swiftmailer\Swiftmailer;
use App\Repository\ListingRepository;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


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
        $listings = $listingRepository->findByIntermediaire($id);
        
        return $this->render('listing/index.html.twig', [
            'listings' => $listings,
        ]);
    }

    /**
     * @Route("/new", name="listing_new", methods={"GET","POST"})
     */
    public function newListing(Request $request): Response
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
    public function showListing(Listing $listing): Response
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
    public function editListing(Request $request, Listing $listing): Response
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
    public function deleteListing(Request $request, Listing $listing): Response
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
            'listing/pdf.html.twig',
            ['listing' => $listing]);
        $dompdf->loadHtml($html);

        // Orientation du PDF, ici en paysage et format A4
        $dompdf->setPaper('A4', 'landscape');

        // Conversion de l'HTML en PDF
        $dompdf->render();

        // Renvoi du PDF sur le navigateur 
        return $dompdf->stream($listing->getNom().".pdf");
    }

    /**
     * @Route("/{id}/envoyer}", name="listing_envoyer", methods={"GET"})
     */
    public function sendListing(Listing $listing, \Swift_Mailer $mailer)
    {
        /* Envoie de Listing par mail à l'aide de SwiftMailer */
        /* Insertion de la date d'envoi du listing dans la base de donnée */
        $listing->setDateEnvoi(new \DateTime());
        $tab = $listing->getassures();
        $nb = count($tab);
        $message = (new \Swift_Message($listing->getNom()))
            /* Adresse mail d'envoi */
            ->setFrom('surassur.amc@gmail.com')
            /* Adresse mail cible, récupéré dans la base de donnée */
            ->setTo($listing->getSouscripteur()->getEmail())
            ->setBody(
                $this->renderView(
                    /* Modèle twig du mail qui va être envoyé*/
                    'listing/envoie.html.twig',
                    ['listing' => $listing,
                    'nb' => $nb,]
                ),
                'text/html'
            )
        ;
        /* Envoie du mail */
        $mailer->send($message);
        
         /* Message d'Alerte */
        $this->addFlash('success','Listing envoyé avec succes');
                
        /* Retour vers les informations du listing */
        return $this->render('listing/show.html.twig', [
            'listing' => $listing,
            'nb' => $nb,
        ]);
    }

    /**
     * @Route("/excel", name="excel", methods={"GET"})
     */
    protected function createSpreadsheet()
    {
        $spreadsheet = new Spreadsheet();
        // Get active sheet - it is also possible to retrieve a specific sheet
        $sheet = $spreadsheet->getActiveSheet();
    
        // Set cell name and merge cells
        $sheet->setCellValue('A1', 'Browser characteristics')->mergeCells('A1:D1');
    
        // Set column names
        $columnNames = [
            'Browser',
            'Developper',
            'Release date',
            'Written in',
        ];
        $columnLetter = 'A';
        foreach ($columnNames as $columnName) {
            // Allow to access AA column if needed and more
            $columnLetter++;
            $sheet->setCellValue($columnLetter.'2', $columnName);
        }
    
        // Add data for each column
        $columnValues = [
            ['Google Chrome', 'Google Inc.', 'September 2, 2008', 'C++'],
            ['Firefox', 'Mozilla Foundation', 'September 23, 2002', 'C++, JavaScript, C, HTML, Rust'],
            ['Microsoft Edge', 'Microsoft', 'July 29, 2015', 'C++'],
            ['Safari', 'Apple', 'January 7, 2003', 'C++, Objective-C'],
            ['Opera', 'Opera Software', '1994', 'C++'],
            ['Maxthon', 'Maxthon International Ltd', 'July 23, 2007', 'C++'],
            ['Flock', 'Flock Inc.', '2005', 'C++, XML, XBL, JavaScript'],
        ];
    
        $i = 3; // Beginning row for active sheet
        foreach ($columnValues as $columnValue) {
            $columnLetter = 'A';
            foreach ($columnValue as $value) {
                $columnLetter++;
                $sheet->setCellValue($columnLetter.$i, $value);
            }
            $i++;
        }
    
        return $spreadsheet;
    }

          

   
}
