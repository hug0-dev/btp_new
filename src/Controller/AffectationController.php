<?php
namespace App\Controller;

use App\Entity\Affectation;
use App\Form\AffectationType;
use App\Repository\AffectationRepository;
use App\Repository\ChantierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Chantier;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/affectation')]
class AffectationController extends AbstractController
{
    #[Route('/', name: 'app_affectation_index', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(AffectationRepository $affectationRepository, ChantierRepository $chantierRepository): Response
    {
        $user = $this->getUser();
        
        if ($user->isAdmin()) {
            $affectations = $affectationRepository->findAll();
            $chantiers = $chantierRepository->findAll();
        } else {
            $affectations = [];
            $chantiers = [];
            if ($user->getEquipe()) {
                $affectations = $user->getEquipe()->getAffectations()->toArray();
                foreach ($affectations as $affectation) {
                    $chantiers[] = $affectation->getChantier();
                }
            }
        }

        return $this->render('affectation/index.html.twig', [
            'affectations' => $affectations,
            'chantiers' => $chantiers,
        ]);
    }

    #[Route('/new/{id}', name: 'app_affectation_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
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

        $form = $this->createForm(AffectationType::class, $affectation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

    #[Route('/{id}', name: 'app_affectation_show', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(Affectation $affectation): Response
    {
        $user = $this->getUser();
        
        if (!$user->isAdmin() && $user->getEquipe() !== $affectation->getEquipe()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette affectation.');
        }

        return $this->render('affectation/show.html.twig', [
            'affectation' => $affectation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_affectation_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
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
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Affectation $affectation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$affectation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($affectation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_affectation_index', [], Response::HTTP_SEE_OTHER);
    }
}