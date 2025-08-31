<?php
// src/Controller/CompetenceController.php
namespace App\Controller;

use App\Entity\Competence;
use App\Repository\CompetenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/competence')]
#[IsGranted('ROLE_ADMIN')]
class CompetenceController extends AbstractController
{
    #[Route('/', name: 'app_competence_index', methods: ['GET'])]
    public function index(CompetenceRepository $competenceRepository): Response
    {
        return $this->render('competence/index.html.twig', [
            'competences' => $competenceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_competence_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $competence = new Competence();
        
        if ($request->isMethod('POST')) {
            $nom = $request->request->get('nom');
            $description = $request->request->get('description');
            
            if ($nom) {
                $competence->setNom($nom);
                $competence->setDescription($description);
                $competence->setActif(1);
                
                $entityManager->persist($competence);
                $entityManager->flush();
                
                $this->addFlash('success', 'Compétence créée avec succès !');
                return $this->redirectToRoute('app_competence_index');
            }
        }

        return $this->render('competence/new.html.twig', [
            'competence' => $competence,
        ]);
    }

    #[Route('/{id}/toggle', name: 'app_competence_toggle', methods: ['POST'])]
    public function toggle(Competence $competence, EntityManagerInterface $entityManager): Response
    {
        $competence->setActif($competence->getActif() ? 0 : 1);
        $entityManager->flush();
        
        $status = $competence->isActif() ? 'activée' : 'désactivée';
        $this->addFlash('success', 'Compétence ' . $status . ' avec succès !');
        
        return $this->redirectToRoute('app_competence_index');
    }

    #[Route('/{id}', name: 'app_competence_delete', methods: ['POST'])]
    public function delete(Request $request, Competence $competence, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$competence->getId(), $request->request->get('_token'))) {
            $entityManager->remove($competence);
            $entityManager->flush();
            $this->addFlash('success', 'Compétence supprimée avec succès !');
        }

        return $this->redirectToRoute('app_competence_index');
    }
}