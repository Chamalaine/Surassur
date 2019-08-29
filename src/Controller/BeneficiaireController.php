<?php


namespace App\Controller;

use App\Entity\Assure;
use App\Repository\AssureRepository;
use App\Entity\Beneficiaire;
use App\Form\BeneficiaireType;
use App\Repository\BeneficiaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @Route("/beneficiaire")
 */
class BeneficiaireController extends AbstractController
{
    /**
     * @Route("/", name="beneficiaire_index", methods={"GET"})
     */
    public function index(BeneficiaireRepository $beneficiaireRepository): Response
    {
        return $this->render('beneficiaire/index.html.twig', [
            'beneficiaires' => $beneficiaireRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}/new", name="beneficiaire_new", methods={"GET","POST"})
     */
    public function newBeneficiaire(Request $request, $id): Response
    {
        $beneficiaire = new Beneficiaire();
        $form = $this->createForm(BeneficiaireType::class, $beneficiaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($beneficiaire);
            $entityManager->flush();

            return $this->redirectToRoute('beneficiaire_index');
        }

        return $this->render('beneficiaire/new.html.twig', [
            'beneficiaire' => $beneficiaire,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="beneficiaire_show", methods={"GET"})
     */
    public function showBeneficiaire(Beneficiaire $beneficiaire): Response
    {
        return $this->render('beneficiaire/show.html.twig', [
            'beneficiaire' => $beneficiaire,
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

            return $this->redirectToRoute('beneficiaire_index');
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
            $entityManager->remove($beneficiaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('beneficiaire_index');
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
    
            if ($form->isSubmitted() && $form->isValid()) {
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
