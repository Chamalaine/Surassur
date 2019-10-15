<?php

namespace App\Controller;


use Dompdf\Dompdf;
use App\Entity\Listing;
use App\Entity\Assure;
use App\Form\ListingType;
use App\Entity\Souscripteur;
use Swiftmailer\Swiftmailer;
use App\Repository\ListingRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yectep\PhpSpreadsheetBundle\Factory;




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

        $dompdf = new Dompdf();

        $html = $this->renderView(
            'listing/pdf.html.twig',
            ['listing' => $listing]);
        $dompdf->loadHtml($html);

        // Orientation du PDF, ici en paysage et format A4
        $dompdf->setPaper('A4', 'landscape');

        // Conversion de l'HTML en PDF
        $dompdf->render();

        $pdf = $dompdf->output();
  
        $tab = $listing->getAssures();
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

        $attachmentPdf = new \Swift_Attachment($pdf, $listing->getNom().".pdf", 'application/pdf');


        $message->attach($attachmentPdf);
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
     * @Route("/{id}/excel", name="excel", methods={"GET"})
     */
    public function excelcrea(Listing $listing, Factory $factory)
    {
        $spreadsheet = $factory->createSpreadsheet();

        $spreadsheet->getProperties()->setCreator('Surassur')
        ->setLastModifiedBy('Surassur')
        ->setTitle('Scan result export')
        ->setSubject('Office 2007 XLSX Test Document')
        ->setDescription('Export of scan results with all vulnerabilities found.')
        ->setKeywords('office 2007 openxml php');

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(100);


        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Listing');
        $sheet->setCellValue('A1', 'Nom');
        $sheet->setCellValue('B1', 'Prenom');
        $sheet->setCellValue('C1', 'Date de Naissance');
        $sheet->setCellValue('D1', 'Adresse');
        $sheet->setCellValue('E1', 'Code Postal');
        $sheet->setCellValue('F1', 'Ville');
        $sheet->setCellValue('G1', 'Téléphone');
        $sheet->setCellValue('H1', 'Bénéficiaires');

        $assures = $listing->getAssures();

        $cell = 2;
        foreach($assures as $assure ){
            $sheet->setCellValue('A'.$cell, $assure->getNom());
            $sheet->setCellValue('B'.$cell, $assure->getPrenom());
            $sheet->setCellValue('C'.$cell, $assure->getDateNaissance());
            $sheet->setCellValue('D'.$cell, $assure->getNumero()." ".$assure->getLibelle()." ".$assure->getComplement());
            $sheet->setCellValue('E'.$cell, $assure->getCp());
            $sheet->setCellValue('F'.$cell, $assure->getVille());
            $sheet->setCellValue('G'.$cell, $assure->getTelephone());


            $beneficiaires=$assure->getBeneficier();
            foreach($beneficiaires as $beneficiaire){
            $i = $beneficiaire->getPrenom()." ".$beneficiaire->getNom()." ".$beneficiaire->getRelation();
            $info = $i . $i;
            }

            if(!empty($beneficiaires[1])){
                $sheet->setCellValue('H'.$cell, $info);
            }

            $cell++;
        }

        $response = $factory->createStreamedResponse($spreadsheet, 'Xls');


        // Redirect output to a client’s web browser (Xls)
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="Listing.xls"');
        $response->headers->set('Cache-Control','max-age=0');
       
        return $response;
 
    }

          

   
}
