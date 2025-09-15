<?php
namespace App\Form;

use App\Entity\Affectation;
use App\Entity\Chantier;
use App\Entity\Equipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AffectationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('chantier', EntityType::class, [
                'class' => Chantier::class,
                'choice_label' => function (Chantier $chantier) {
                    return $chantier->getNom() . ' - Effectif requis : ' . $chantier->getEffectifRequis();
                },
                'attr' => ['class' => 'form-control'],
                'disabled' => true,
            ])
            ->add('equipe', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => function(Equipe $equipe) {
                    $competences = implode(', ', $equipe->getCompetences());
                    return $equipe->getNomEquipe() . ' - Compétences : ' . ($competences ?: 'Aucune') . ' - Membres : ' . $equipe->getNombre();
                },
                'multiple' => false,
                'expanded' => true,
                'constraints' => [
                    new Callback([$this, 'validateEquipe'])
                ]
            ]);
    }

    public function validateEquipe($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $chantier = $form->get('chantier')->getData();
        
        if ($chantier && $value) {
            $effectifRequis = $chantier->getEffectifRequis();
            $effectifEquipe = $value->getNombre();
            
            if ($effectifEquipe != $effectifRequis) {
                $context->buildViolation('Le nombre d\'effectif de l\'équipe (' . $effectifEquipe . ') doit être égal au nombre d\'effectif requis du chantier (' . $effectifRequis . ').')
                    ->atPath('equipe')
                    ->addViolation();
            }
            
            $competencesRequises = $chantier->getChantierPrerequis() ?? [];
            $competencesEquipe = $value->getCompetences();

            if (!empty($competencesRequises)) {
                if (empty($competencesEquipe) || empty(array_intersect($competencesRequises, $competencesEquipe))) {
                    $competencesRequisesStr = is_array($competencesRequises) 
                        ? implode(', ', $competencesRequises) 
                        : (string)$competencesRequises;
                    
                    $context->buildViolation('L\'équipe doit posséder au moins une des compétences requises : ' . $competencesRequisesStr)
                        ->atPath('equipe')
                        ->addViolation();
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Affectation::class,
        ]);
    }
}