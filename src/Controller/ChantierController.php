<?php
namespace App\Controller;

use App\Entity\Chantier;
use App\Form\ChantierType;
use App\Repository\ChantierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/chantier')]
class ChantierController extends AbstractController
{
    #[Route('/', name: 'app_chantier_index', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(ChantierRepository $chantierRepository): Response
    {
        $user = $this->getUser();
        
        if ($user->isAdmin()) {
            $chantiers = $chantierRepository->findAll();
        } else {
            $chantiers = [];
            if ($user->getEquipe()) {
                foreach ($user->getEquipe()->getAffectations() as $affectation) {
                    $chantier = $affectation->getChantier();
                    if ($chantier->getChantierPrerequis() === null) {
                        $chantier->setChantierPrerequis([]);
                    }
                    $chantiers[] = $chantier;
                }
            }
        }

        foreach ($chantiers as $chantier) {
            if ($chantier->getChantierPrerequis() === null) {
                $chantier->setChantierPrerequis([]);
            }
        }

        return $this->render('chantier/index.html.twig', [
            'chantiers' => $chantiers,
        ]);
    }

    #[Route('/new', name: 'app_chantier_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $chantier = new Chantier();
        $form = $this->createForm(ChantierType::class, $chantier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
                }

                $chantier->setImage($newFilename);
            }

            if ($chantier->getChantierPrerequis() === null) {
                $chantier->setChantierPrerequis([]);
            }

            $entityManager->persist($chantier);
            $entityManager->flush();

            $this->addFlash('success', 'Chantier créé avec succès !');
            return $this->redirectToRoute('app_chantier_index');
        }

        return $this->render('chantier/new.html.twig', [
            'chantier' => $chantier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_chantier_show', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(Chantier $chantier): Response
    {
        $user = $this->getUser();
        
        if (!$user->isAdmin()) {
            $hasAccess = false;
            if ($user->getEquipe()) {
                foreach ($user->getEquipe()->getAffectations() as $affectation) {
                    if ($affectation->getChantier() === $chantier) {
                        $hasAccess = true;
                        break;
                    }
                }
            }
            
            if (!$hasAccess) {
                throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce chantier.');
            }
        }

        if ($chantier->getChantierPrerequis() === null) {
            $chantier->setChantierPrerequis([]);
        }

        return $this->render('chantier/show.html.twig', [
            'chantier' => $chantier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_chantier_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Chantier $chantier, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ChantierType::class, $chantier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );

                    if ($chantier->getImage()) {
                        $oldImagePath = $this->getParameter('images_directory') . '/' . $chantier->getImage();
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }

                    $chantier->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            if ($chantier->getChantierPrerequis() === null) {
                $chantier->setChantierPrerequis([]);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Chantier mis à jour avec succès !');
            return $this->redirectToRoute('app_chantier_index');
        }

        return $this->render('chantier/edit.html.twig', [
            'chantier' => $chantier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_chantier_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Chantier $chantier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $chantier->getId(), $request->request->get('_token'))) {
            if ($chantier->getImage()) {
                $imagePath = $this->getParameter('images_directory') . '/' . $chantier->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($chantier);
            $entityManager->flush();

            $this->addFlash('success', 'Chantier supprimé avec succès !');
        }

        return $this->redirectToRoute('app_chantier_index');
    }
}