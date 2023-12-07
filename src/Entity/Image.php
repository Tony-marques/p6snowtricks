<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\CustomTrait\TimestampableTrait;
use App\Repository\ImageRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[assert\NotNull(groups: ["creation"], message: "Veuillez mettre un fichier")]
    #[Assert\File(
        groups: ["creation", "edition"],
        mimeTypes: [
            'image/webp',
            'image/jpeg',
            'image/png',
            'image/gif'
        ],
        mimeTypesMessage: 'Le type du fichier n\'est pas supporté (webp, jpeg, png, gif).'
    )]
    private ?UploadedFile $file = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Trick $trick = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): static
    {
        $this->trick = $trick;

        return $this;
    }
}
