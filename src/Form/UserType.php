<?php
namespace App\Form;

use App\Entity\User;
use App\Entity\Competence;
use App\Entity\Equipe;
use App\Entity\UserCompetence;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Doctrine\ORM\EntityManagerInterface;

class UserType extends AbstractType
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control']
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control']
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'help' => 'Laissez vide pour ne pas modifier le mot de passe'
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
                'attr' => ['class' => 'form-check']
            ])
            ->add('competences', EntityType::class, [
                'class' => Competence::class,
                'choice_label' => 'nom',
                'query_builder' => function($repository) {
                    return $repository->createQueryBuilder('c')
                        ->where('c.actif = 1')
                        ->orderBy('c.nom', 'ASC');
                },
                'multiple' => true,
                'expanded' => false,
                'mapped' => false,
                'label' => 'Compétences',
                'attr' => [
                    'class' => 'form-select',
                    'size' => '8',
                    'style' => 'height: auto;'
                ],
                'help' => 'Maintenez Ctrl (Cmd sur Mac) pour sélectionner plusieurs compétences'
            ])
            ->add('equipe', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => 'nom_equipe',
                'placeholder' => 'Sélectionnez une équipe',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            if ($user && $user->getId()) {
                $competences = [];
                foreach ($user->getUserCompetences() as $userCompetence) {
                    $competences[] = $userCompetence->getCompetence();
                }
                
                $form->get('competences')->setData($competences);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            if ($user && $form->isValid()) {
                $selectedCompetences = $form->get('competences')->getData();
                
                foreach ($user->getUserCompetences() as $userCompetence) {
                    $this->entityManager->remove($userCompetence);
                }
                $user->getUserCompetences()->clear();

                foreach ($selectedCompetences as $competence) {
                    $userCompetence = new UserCompetence();
                    $userCompetence->setUser($user);
                    $userCompetence->setCompetence($competence);
                    
                    $user->addUserCompetence($userCompetence);
                    $this->entityManager->persist($userCompetence);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}