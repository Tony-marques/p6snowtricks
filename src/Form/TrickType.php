<?php

namespace App\Form;

use App\Entity\Trick;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

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
                "choice_label" => "name",
                "required" => false

            ])
            ->add("mainImage", FileType::class, [
                "required" => false,
                "label" => false,
            ])
            ->add("images", CollectionType::class, [
                "entry_type" => ImageType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                "label" => false,
                // "constraints" => [
                //     new File([
                //         "mimeTypes" => [
                //             'image/webp',
                //             'image/jpeg',
                //             'image/png',
                //             'image/gif'
                //         ],
                //         'mimeTypesMessage' => 'Le type du fichier n\'est pas supporté (webp, jpeg, png, gif).',
                //     ]),
                //     new NotNull(message: "Veuillez sélectionner un fichier.")
                // ]
            ])
            ->add("videos", CollectionType::class, [
                "entry_type" => VideoType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                "required" => false,
                "label" => false

            ])
            ->add('description', TextareaType::class, [
                "required" => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
            "validation_groups" => ["creation", "edition"]
        ]);
    }
}
