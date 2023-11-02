<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType as TypeTextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShowUserType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {

    $builder
      ->add('pseudo', TypeTextType::class, [

      ])
      ->add("age")
      ->add("profileImage", FileType::class, [
        "required" => false
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => User::class,
    ]);
  }
}
