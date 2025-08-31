<?php

namespace App\Form;

use App\Entity\Ouvrier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class OuvrierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_ouvrier') // Champ pour le nom de l'ouvrier
            ->add('competences', ChoiceType::class, [
                'choices' => [
                    'Maçon' => 'Maçon',
                    'Coffreur' => 'Coffreur',
                    'Ferrailleur' => 'Ferrailleur',
                    'Terrassier' => 'Terrassier',
                    'Conducteur d’engins' => 'Conducteur d’engins',
                    'Manœuvre de chantier' => 'Manœuvre de chantier',
                    'Grutier' => 'Grutier',
                    'Plombier' => 'Plombier',
                    'Électricien' => 'Électricien',
                    'Peintre en bâtiment' => 'Peintre en bâtiment',
                    'Plâtrier' => 'Plâtrier',
                    'Carreleur' => 'Carreleur',
                    'Menuisier' => 'Menuisier',
                    'Parqueteur' => 'Parqueteur',
                    'Serrurier-métallier' => 'Serrurier-métallier',
                    'Chauffagiste' => 'Chauffagiste',
                    'Enduiseur' => 'Enduiseur',
                    'Vitrificateur' => 'Vitrificateur',
                    'Solier-moquettiste' => 'Solier-moquettiste',
                    'Staffeur-Ornemaniste' => 'Staffeur-Ornemaniste',
                ],
                'multiple' => true, // Permet de cocher plusieurs choix
                'expanded' => true, // Affiche les options sous forme de cases à cocher
                'placeholder' => 'Sélectionnez les compétences prérequises',
                'required' => true,
                'attr' => ['class' => 'form-check']
                ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Ouvrier' => 'Ouvrier',
                    'Chef' => 'Chef',
                ],
                'expanded' => false, // Menu déroulant
                'multiple' => false, // Un seul choix possible
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Choisissez un rôle',
            ])
;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ouvrier::class,
        ]);
    }
}
