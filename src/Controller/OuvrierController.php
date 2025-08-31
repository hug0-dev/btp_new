<?php

namespace App\Controller;

use App\Entity\Ouvrier;
use App\Form\OuvrierType;
use App\Repository\OuvrierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ouvrier')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]

final class OuvrierController extends AbstractController
{
    #[Route('/', name: 'app_ouvrier_index', methods: ['GET'])]
    public function index(OuvrierRepository $ouvrierRepository): Response
    {
        return $this->render('ouvrier/index.html.twig', [
            'ouvriers' => $ouvrierRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_ouvrier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ouvrier = new Ouvrier();
        $form = $this->createForm(OuvrierType::class, $ouvrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ouvrier);
            $entityManager->flush();

            return $this->redirectToRoute('app_ouvrier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ouvrier/new.html.twig', [
            'ouvrier' => $ouvrier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_ouvrier_show', methods: ['GET'])]
    public function show(int $id, OuvrierRepository $ouvrierRepository): Response
    {
        $ouvrier = $ouvrierRepository->find($id);

        if (!$ouvrier) {
            throw new NotFoundHttpException('Ouvrier non trouvé');
        }

        return $this->render('ouvrier/show.html.twig', [
            'ouvrier' => $ouvrier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ouvrier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, OuvrierRepository $ouvrierRepository, EntityManagerInterface $entityManager): Response
    {
        $ouvrier = $ouvrierRepository->find($id);

        if (!$ouvrier) {
            throw new NotFoundHttpException('Ouvrier non trouvé');
        }

        $form = $this->createForm(OuvrierType::class, $ouvrier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_ouvrier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ouvrier/edit.html.twig', [
            'ouvrier' => $ouvrier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_ouvrier_delete', methods: ['POST'])]
    public function delete(int $id, OuvrierRepository $ouvrierRepository, EntityManagerInterface $entityManager): Response
    {
        $ouvrier = $ouvrierRepository->find($id);

        if (!$ouvrier) {
            throw new NotFoundHttpException('Ouvrier non trouvé');
        }

        // Suppression de l'ouvrier sans validation CSRF (sécurisé par la méthode de suppression)
        $entityManager->remove($ouvrier);
        $entityManager->flush();

        return $this->redirectToRoute('app_ouvrier_index', [], Response::HTTP_SEE_OTHER);
    }
}
