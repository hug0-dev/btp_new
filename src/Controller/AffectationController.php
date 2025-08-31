<?php

namespace App\Controller;

use App\Entity\Affectation;
use App\Form\AffectationType;
use App\Repository\AffectationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Chantier;
use App\Repository\ChantierRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/affectation')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]

final class AffectationController extends AbstractController
{
    #[Route(name: 'app_affectation_index', methods: ['GET'])]
    public function index(AffectationRepository $affectationRepository, ChantierRepository $chantierRepository): Response
    {
        return $this->render('affectation/index.html.twig', [
            'affectations' => $affectationRepository->findAll(),
            'chantiers' => $chantierRepository->findAll(),
        ]);
    }
    

    #[Route('/new/{id}', name: 'app_affectation_new', methods: ['GET', 'POST'])]
        public function new(
            Request $request, 
            EntityManagerInterface $entityManager, 
            ChantierRepository $chantierRepository, 
            AffectationRepository $affectationRepository,
            ?Chantier $chantier
        ): Response {
            $chantierId = $request->attributes->get('id');
            $chantier = $chantierId ? $chantierRepository->find($chantierId) : null;

            $affectation = new Affectation();

            if ($chantier) {
                $affectation->setChantier($chantier);
            }

            $form = $this->createForm(\App\Form\AffectationType::class, $affectation);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $equipe = $affectation->getEquipe();
                $dateDebut = $affectation->getDateDebut();
                $dateFin = $affectation->getDateFin();

                $errors = [];
                
                // Vérifier pour chaque ouvrier de l'équipe s'il a déjà une affectation qui chevauche cette période
                foreach ($equipe->getOuvriers() as $ouvrier) {
                    $existingAffectations = $affectationRepository->findBy([
                        'equipe' => $equipe,
                        // On suppose ici que l'affectation s'applique à l'ensemble des ouvriers de l'équipe.
                    ]);
                    foreach ($existingAffectations as $existing) {
                        if ($this->datesOverlap($dateDebut, $dateFin, $existing->getDateDebut(), $existing->getDateFin())) {
                            $errors[] = sprintf(
                                "L'ouvrier %s est déjà affecté à un chantier du %s au %s.",
                                $ouvrier->getNomOuvrier(),
                                $existing->getDateDebut()->format('Y-m-d'),
                                $existing->getDateFin()->format('Y-m-d')
                            );
                        }
                    }
                }

                if (!empty($errors)) {
                    foreach ($errors as $error) {
                        $this->addFlash('error', $error);
                    }
                    return $this->redirectToRoute('app_affectation_new', ['id' => $chantier->getId()]);
                }

                // Aucune erreur détectée : persister l'affectation
                $entityManager->persist($affectation);
                $entityManager->flush();

                $this->addFlash('success', 'Affectation créée avec succès.');
                return $this->redirectToRoute('app_affectation_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('affectation/new.html.twig', [
                'affectation' => $affectation,
                'form' => $form->createView(),
            ]);
        }

        /**
         * Vérifie si deux périodes se chevauchent.
         */
        private function datesOverlap(\DateTimeInterface $start1, \DateTimeInterface $end1, \DateTimeInterface $start2, \DateTimeInterface $end2): bool
        {
            return $start1 <= $end2 && $start2 <= $end1;
        }


    #[Route('/{id}', name: 'app_affectation_show', methods: ['GET'])]
    public function show(Affectation $affectation): Response
    {
        return $this->render('affectation/show.html.twig', [
            'affectation' => $affectation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_affectation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Affectation $affectation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AffectationType::class, $affectation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_affectation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('affectation/edit.html.twig', [
            'affectation' => $affectation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_affectation_delete', methods: ['POST'])]
    public function delete(Request $request, Affectation $affectation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$affectation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($affectation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_affectation_index', [], Response::HTTP_SEE_OTHER);
    }
}
