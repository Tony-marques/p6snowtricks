<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\CustomTrait\TimestampableTrait;
use App\Repository\TrickRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TrickRepository::class)]
#[UniqueEntity(
    groups: ["creation", "edition"],
    fields: ["name"],
    message: "Le nom {{ value }} est déjà utilisé pour un autre trick, veuillez en choisir un autre."
)]
class Trick
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(groups: ["creation", "edition"], message: "Veuillez renseigner un titre")]
    #[Assert\Length(
        groups: ["creation", "edition"],
        min: 4,
        max: 255,
        minMessage: "Le titre doit faire au minimum {{ limit }} caractères, il fait actuellement {{ value_length }} caractère(s)",
        maxMessage: "Le titre doit faire au maximum {{ limit }} caractères, il fait actuellement {{ value_length }} caractère(s)"
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(
        groups: ["creation", "edition"],
        message: "Veuillez renseigner une description"
    )]
    #[Assert\Length(
        groups: ["creation", "edition"],
        min: 20,
        minMessage: "La description doit faire au minimum {{ limit }} caractères, elle fait actuellement {{ value_length }} caractères",
    )]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'tricks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[Assert\NotNull(groups: ["creation"], message: "Veuillez mettre un fichier")]
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
    private ?UploadedFile $mainImage = null;

    #[ORM\Column()]
    #[Assert\NotNull()]
    private $mainImageName = null;

    #[ORM\OneToMany(mappedBy: 'trick', targetEntity: Comment::class, orphanRemoval: true)]
    private Collection $comments;

    #[ORM\ManyToOne(inversedBy: 'tricks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(groups: ["creation", "edition"], message: "Veuillez séléctionner une catégorie.")]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'trick', targetEntity: Image::class, orphanRemoval: true, cascade: ["persist"])]
    #[Assert\Valid()]
    private Collection $images;

    #[ORM\OneToMany(mappedBy: 'trick', targetEntity: Video::class, orphanRemoval: true, cascade: ["persist"])]
    #[Assert\Valid()]
    private Collection $videos;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->videos = new ArrayCollection();
    }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setTrick($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTrick() === $this) {
                $comment->setTrick(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setTrick($this);
        }

        return $this;
    }

    public function removeImage(Image $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getTrick() === $this) {
                $image->setTrick(null);
            }
        }

        return $this;
    }

    /**
     * Get the value of mainImage
     */
    public function getMainImage(): ?UploadedFile
    {
        return $this->mainImage;
    }

    /**
     * Set the value of mainImage
     *
     * @return  self
     */
    public function setMainImage(?UploadedFile $mainImage)
    {
        $this->mainImage = $mainImage;

        return $this;
    }

    /**
     * Get the value of mainImageName
     */
    public function getMainImageName(): ?string
    {
        return $this->mainImageName;
    }

    /**
     * Set the value of mainImageName
     *
     * @return  self
     */
    public function setMainImageName(?string $mainImageName)
    {
        $this->mainImageName = $mainImageName;

        return $this;
    }

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): static
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setTrick($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): static
    {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getTrick() === $this) {
                $video->setTrick(null);
            }
        }

        return $this;
    }
}
