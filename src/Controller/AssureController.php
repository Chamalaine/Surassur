<?php

namespace App\Controller;

use App\Entity\Assure;
use App\Entity\Listing;
use App\Form\AssureType;
use App\Entity\Beneficiaire;
use App\Form\BeneficiaireType;
use App\Form\NBeneficiaireType;
use App\Repository\AssureRepository;
use App\Repository\ListingRepository;
use App\Entity\BeneficiaireRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;




/**
 * @Route("/profile/assure")
 */
class AssureController extends AbstractController
{

  

    /**
     * @Route("/", name="assure_index", methods={"GET"})
     */
    public function index(assureRepository $assureRepository): Response
    {
        $id = $this->getUser()->getId();
        return $this->render('assure/index.html.twig', [
            'assures' => $assureRepository->findByIntermediaire($id),
        ]);
    }

    /**
     * @Route("/new", name="assure_new", methods={"GET","POST"})
     */
    public function newAssure(Request $request): Response
    {   

        $assure = new Assure();
        $form = $this->createForm(AssureType::class, $assure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $assure->setIntermediaire($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($assure);
            $entityManager->flush();

            $this->addFlash('success','Assuré ajouté avec succes');

            return $this->redirectToRoute('assure_index');
        }

        return $this->render('assure/new.html.twig', [
            'assure' => $assure,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="assure_show", methods={"GET"})
     */
    public function showAssure(Assure $assure): Response
    {
        return $this->render('assure/show.html.twig', [
            'assure' => $assure,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="assure_edit", methods={"GET","POST"})
     */
    public function editAssure(Request $request, Assure $assure): Response
    {
        $form = $this->createForm(assureType::class, $assure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success','Assuré édité avec succes');

            return $this->redirectToRoute('assure_index', [
                'id' => $assure->getId(),
            ]);
        }

        return $this->render('assure/edit.html.twig', [
            'assure' => $assure,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="assure_delete", methods={"DELETE"})
     */
    public function deleteAssure(Request $request, Assure $assure): Response
    {
        if ($this->isCsrfTokenValid('delete'.$assure->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($assure);
            $entityManager->flush();
        }
        
        $this->addFlash('success','Assuré supprimé avec succes');

        return $this->redirectToRoute('assure_index');
    }

    public function searchBar()
    {
        $form = $this->createFormBuilder(null)
        ->setAction($this->generateUrl("handleSearch"))
        ->add('Recherche', TextType::class)
        ->add('Rechercher', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-primary',
                'minlength' => 3
            ]
        ])

        ->getForm();

        return $this->render('search/searchBar.html.twig', [
        'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/handleSearch", name="handleSearch")
     */

    public function handleSearch(Request $request): Response
    {
        $id = $this->getUser()->getId();
        $query = $request->request->get('form')['Recherche'];
        if($query){
            $assures = $this->getDoctrine()->getRepository(Assure::class)->searchAssure($query,$id);

        }

        return $this->render('search/searchResults.html.twig', [
            'assures' => $assures
        ]);
    }


     /**
     * @Route("/{id}/ajout", name="assure_ajout", methods={"GET","POST"})
     */
    public function addAssure(request $request, ListingRepository $listingRepository, $id):Response
    {
        {   
            /* Ajout d'assuré */

            $listingRepository = $this->getDoctrine()->getRepository(Listing::class);
            $assure = new Assure();
            $form = $this->createForm(AssureType::class, $assure);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $assure->setIntermediaire($this->getUser());
                $listing=$listingRepository->find($id);
                /* Vérification si l'assuré est déjà présent dans la base de donnée */
                $doublon=$this->getDoctrine()->getRepository(Assure::class)->doublonAssure($assure);
                if($doublon === null){
                $assure->addListing($listing);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($assure);
                }
                else{
                /* Si l'assuré est déjà présent, alors on ajoute le listing présent dans la table de l'assuré */
                $doublon->addListing($listing);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($doublon);
                }

                $entityManager->flush();
                
                $this->addFlash('success','Assuré ajouté avec succes');
                /* Deux boutons de validations sont disponibles, ajouter un nouvel assuré ou alors ajouter des bénéficiaires à l'assuré ajouté */
                if ($form->getClickedButton() && 'beneficiaire' === $form->getClickedButton()->getName()) {

                    $id=$assure->getId();

                    return $this->redirectToRoute('beneficiaire_ajout',array('id'=>$id));
                }

                return $this->redirectToRoute('assure_ajout',array('id'=>$id));

            }
            
            return $this->render('assure/new.html.twig', [
                'assure' => $assure,
                'form' => $form->createView(),
            ]);
        }
    }


            /**
             * @Route("/{id}/new", name="beneficiaire_new", methods={"GET","POST"})
             */
            public function newBeneficiaire(Request $request, AssureRepository $assureRepository, $id): Response
            {
                $beneficiaire = new Beneficiaire();
                $form = $this->createForm(NBeneficiaireType::class, $beneficiaire);
                $form->handleRequest($request);
                $assureRepository = $this->getDoctrine()->getRepository(Assure::class);

                if ($form->isSubmitted() && $form->isValid()) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $assure=$assureRepository->find($id);
                    $beneficiaire->setAssure($assure);
                    $entityManager->persist($beneficiaire);
                    $entityManager->flush();

                    return $this->redirectToRoute('assure_show',array('id'=>$id));
                }

                return $this->render('beneficiaire/new.html.twig', [
                    'beneficiaire' => $beneficiaire,
                    'assure.id' => $id,
                    'form' => $form->createView(),
                ]);
            }


            /**
             * @Route("/{id}/edit", name="beneficiaire_edit", methods={"GET","POST"})
             */
            public function editBeneficiaire(Request $request, Beneficiaire $beneficiaire): Response
            {
                $form = $this->createForm(BeneficiaireType::class, $beneficiaire);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $this->getDoctrine()->getManager()->flush();

                    return $this->redirectToRoute('assure_show',array('id'=>$id));
                }

                return $this->render('beneficiaire/edit.html.twig', [
                    'beneficiaire' => $beneficiaire,
                    'form' => $form->createView(),
                ]);
            }

            /**
             * @Route("/{id}", name="beneficiaire_delete", methods={"DELETE"})
             */
            public function deleteBeneficiaire(Request $request, Beneficiaire $beneficiaire): Response
            {
                if ($this->isCsrfTokenValid('delete'.$beneficiaire->getId(), $request->request->get('_token'))) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $id=$beneficiare->getassure();
                    $entityManager->remove($beneficiaire);
                    $entityManager->flush();
                    
                }

                return $this->redirectToRoute('assure_show',array('id'=>$id) );
            }


            /**
             * @Route("/{id}/ajout", name="beneficiaire_ajout", methods={"GET","POST"})
             */
            public function ajouterBeneficiaire(request $request, AssureRepository $assureRepository, $id):Response
            {
                {   
                    $assureRepository = $this->getDoctrine()->getRepository(Assure::class);
                    $beneficiaire = new beneficiaire();
                    $form = $this->createForm(BeneficiaireType::class, $beneficiaire);
                    $form->handleRequest($request);
            
                    if ($form->isSubmitted() && $form->isValid()){
                        $assure=$assureRepository->find($id);
                        $beneficiaire->setAssure($assure);
                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($beneficiaire);
                        $entityManager->flush();

                        if ($form->getClickedButton() && 'assure' === $form->getClickedButton()->getName()) {
                            
                            $listing =$assure->getListings()->last();
                            $id=$listing->getId();
                            

                            return $this->redirectToRoute('assure_ajout',array('id'=>$id));
                        }
            
                        return $this->redirectToRoute('beneficiaire_ajout',array('id'=>$id) );
                    }
            
                    return $this->render('beneficiaire/new.html.twig', [

                        'beneficiaire' => $beneficiaire,
                        'form' => $form->createView(),
                    ]);
                }
            }

    
}
