<?php
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
                    return $er->createQueryBuilder('u')
                        ->where('u.equipe IS NULL OR u.equipe = :currentEquipe')
                        ->andWhere('JSON_CONTAINS(u.roles, :userRole) = 1')
                        ->setParameter('userRole', '"ROLE_USER"')
                        ->setParameter('currentEquipe', null);
                },
                'multiple' => true,
                'expanded' => true,
                'label' => 'Membres de l\'équipe',
                'required' => false,
            ])
            ->add('chefEquipe', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('JSON_CONTAINS(u.roles, :adminRole) = 1 OR JSON_CONTAINS(u.roles, :userRole) = 1')
                        ->setParameter('adminRole', '"ROLE_ADMIN"')
                        ->setParameter('userRole', '"ROLE_USER"');
                },
                'placeholder' => 'Sélectionnez un chef d\'équipe',
                'required' => false,
                'attr' => ['class' => 'form-control']
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