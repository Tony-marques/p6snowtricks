<?php

namespace App\Form;

use App\Entity\Image;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\Image as ConstraintsImage;

class EditUserType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {

    $builder
      ->add('pseudo', TextType::class, [
        "required" => false
      ])
      ->add("age", IntegerType::class, [
        "required" => false,
      ])
      ->add("profileImageFile", FileType::class, [
        "mapped" => false,
        "required" => false,
        "constraints" => [
          new File([
            "mimeTypes" => [
              'image/webp',
              'image/jpeg',
              'image/png',
              'image/gif'
            ],
            'mimeTypesMessage' => 'Le type du fichier n\'est pas supporté (webp, jpeg, png, gif).',
          ])
        ]
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => User::class,
      "validation_groups" => ["user_edit"]
    ]);
  }
}
