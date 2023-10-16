<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Trick;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                "required" => false,

            ])
            ->add("category", EntityType::class, [
                "placeholder" => "Selectionner une catégorie",
//                "mapped" => false,
                "class" => Category::class,
                "choice_label" => "name"
            ])
            ->add('description', TextareaType::class, [
                "required" => false
            ])
            // ->add('image')
            // ->add('video')
            // ->add('createdAt')
            // ->add('updatedAt')
            // ->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
