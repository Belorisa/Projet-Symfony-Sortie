<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\User;
use Doctrine\DBAL\Types\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('dateHeureDebut',DateTimeType::class, [
                'label' => 'Début de l\'évènement',
                'widget' => 'single_text',
                'required' => false,
                'constraints' => [
                    new NotNull(['message' => 'Merci de remplir le champ date de début']),
                ],
            ])
            ->add('dateHeureFin',DateTimeType::class, [
                'label' => 'Fin de l\'évènement',
                'widget' => 'single_text',
                'required' => false,
                'constraints' => [
                    new NotNull(['message' => 'Merci de remplir le champ date de fin']),
                ],
            ])
            ->add('dateLimiteInscription',DateTimeType::class,[
                'label' => 'Limite d\'inscription (en cas de modification elle sera effective dans l\'heure)',
                'widget' => 'single_text',
                'required' => false,
                'constraints' => [
                    new NotNull(['message' => 'Merci de remplir le champ date limite']),
                ],
            ])
            ->add('nbInscriptionMax')
            ->add('infoSortie')
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider',
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
