<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le pseudo est obligatoire',
                    ]),
                ]
            ])
            ->add('nom')
            ->add('prenom')
            ->add('telephone', null, [
                    'label' => 'Téléphone',
                    'constraints' => [
                        new NotBlank(),
                        new Regex([
                            'pattern' => '/^(?:\+33|0)[1-9](?:[ .-]?\d{2}){4}$/',
                            'message' => 'Veuillez entrer un numéro de téléphone français valide'
                        ])
                    ]
                ])
            ->add("photo", FileType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Email([
                        'message' => 'Veuillez saisir une adresse email valide',
                    ]),
                    new NotBlank([
                        'message' => 'Veuillez saisir une adresse email',
                    ]),
                    new Regex([
                        'pattern' => '/@campus-eni\.fr$/',
                        'message' => 'L\'adresse email doit se terminer par @campus-eni.fr',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'example@campus-eni.fr'
                ]
            ])
            ->add('campus',EntityType::class, [
               'class' => Site::class,
                'choice_label' => function (Site $site) {
                return $site->getNom();
                }
            ])
            ->add('agreeTerms', HiddenType::class, [
                'mapped' => false,
                'data' => true,
                'required' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'label' => 'Mot de passe',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
