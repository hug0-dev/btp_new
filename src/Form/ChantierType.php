<?php
namespace App\Form;

use App\Entity\Chantier;
use App\Entity\User;
use App\Entity\Competence;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class ChantierType extends AbstractType
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du Chantier',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Construction immeuble'],
                'required' => true
            ])
            ->add('chantier_prerequis', EntityType::class, [
                'class' => Competence::class,
                'choice_label' => 'nom',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.actif = 1')
                        ->orderBy('c.nom', 'ASC');
                },
                'multiple' => true,
                'expanded' => true,
                'label' => 'Compétences prérequises',
                'required' => false,
                'attr' => ['class' => 'form-check']
            ])
            ->add('effectif_requis', IntegerType::class, [
                'label' => 'Effectif Requis',
                'attr' => ['class' => 'form-control', 'min' => 1],
                'required' => true
            ])
            ->add('date_debut', DateType::class, [
                'label' => 'Date de Début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'required' => true
            ])
            ->add('date_fin', DateType::class, [
                'label' => 'Date de Fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'required' => true
            ])
            ->add('chefChantier', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $user) {
                    $label = $user->getNom();
                    if ($user->isAdmin()) {
                        $label .= ' (Admin)';
                    }
                    return $label;
                },
                'label' => 'Chef de Chantier',
                'placeholder' => 'Sélectionnez un chef',
                'required' => false,
                'attr' => ['class' => 'form-select'],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.nom', 'ASC');
                },
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image du Chantier',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG, WebP)',
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ]);

        // Événement CRUCIAL pour charger les compétences lors de l'édition
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $chantier = $event->getData();
            $form = $event->getForm();

            if ($chantier && $chantier->getId()) {
                $prerequis = $chantier->getChantierPrerequis();
                if ($prerequis && is_array($prerequis) && count($prerequis) > 0) {
                    // Récupérer les objets Competence depuis les noms stockés
                    $competences = $this->entityManager->getRepository(Competence::class)
                        ->createQueryBuilder('c')
                        ->where('c.nom IN (:noms)')
                        ->setParameter('noms', $prerequis)
                        ->getQuery()
                        ->getResult();
                    
                    // Définir les objets Competence dans le formulaire
                    $form->get('chantier_prerequis')->setData($competences);
                }
            }
        });

        // Événement pour convertir les objets Competence en noms lors de la soumission
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $chantier = $event->getData();
            $form = $event->getForm();

            $competences = $form->get('chantier_prerequis')->getData();
            if ($competences && count($competences) > 0) {
                $competenceNames = [];
                foreach ($competences as $competence) {
                    $competenceNames[] = $competence->getNom();
                }
                $chantier->setChantierPrerequis($competenceNames);
            } else {
                $chantier->setChantierPrerequis([]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chantier::class,
        ]);
    }
}