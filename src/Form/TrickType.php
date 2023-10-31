<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Trick;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                "required" => false,

            ])
            ->add("category", EntityType::class, [
                "placeholder" => "Sélectionner une catégorie",
                "class" => Category::class,
                "choice_label" => "name"
            ])
            ->add("mainImage", FileType::class, [
                // "constraints" => [
                //     new File([
                //         "mimeTypes" => [
                //             'image/webp',
                //             'image/jpeg',
                //             'image/png',
                //             'image/gif'
                //         ],
                //         'mimeTypesMessage' => 'Le type du fich',
                //     ])
                // ]
            ])
            ->add("images", CollectionType::class, [
                "entry_type" => ImageType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
            ])
            ->add('description', TextareaType::class, [
                "required" => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
