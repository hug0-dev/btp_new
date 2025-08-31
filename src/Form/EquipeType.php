<?php
// src/Form/EquipeType.php - Version corrigée sans JSON_CONTAINS
namespace App\Form;

use App\Entity\Equipe;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class EquipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_equipe', TextType::class, [
                'label' => 'Nom de l\'équipe',
                'attr' => ['class' => 'form-control']
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom',
                'query_builder' => function (EntityRepository $er) {
                    // Récupérer tous les utilisateurs sans équipe ou avec des rôles USER/ADMIN
                    return $er->createQueryBuilder('u')
                        ->where('u.equipe IS NULL')
                        ->orderBy('u.nom', 'ASC');
                },
                'multiple' => true,
                'expanded' => true,
                'label' => 'Membres de l\'équipe',
                'required' => false,
            ])
            ->add('chefEquipe', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez un chef d\'équipe',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'query_builder' => function (EntityRepository $er) {
                    // Récupérer tous les utilisateurs (ils peuvent tous être chefs)
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.nom', 'ASC');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipe::class,
        ]);
    }
}