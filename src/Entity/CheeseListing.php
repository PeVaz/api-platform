<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;


use App\Repository\CheeseListingRepository;
use Doctrine\ORM\Mapping as ORM;
use Carbon\Carbon;


/**
 *@ApiResource(
 *     collectionOperations={"get", "post"},
 *     itemOperations={
 *          "get"={
 *              "normalization_context"={"groups"={"cheese_listing:read", "cheese_listing:item:get"}},
 *          },
 *          "put"
 * },
 *     shortName="cheeses" ,
 *     normalizationContext={"groups"={"cheese_listing:read"}, "swagger_definition_name"="Read"},
 *     denormalizationContext={"groups"={"cheese_listing:write"}, "swagger_definition_name"="Write"},
 *     attributes={
 *          "pagination_items_per_page"=10,
 *          "formats"={"jsonld", "json", "html", "jsonhal", "csv"={"text/csv"}}
 *     }
 * )
 * 
 * @ApiFilter(BooleanFilter::class, properties={"isPublished"})
 * @ApiFilter(SearchFilter::class, properties={
 *     "title": "partial",
 *     "description": "partial",
 *     "owner": "exact",
 *     "owner.username": "partial"
 * })
 * @ApiFilter(RangeFilter::class, properties={"price"})
 * @ApiFilter(PropertyFilter::class)
 * 
 * @ORM\Entity(repositoryClass=CheeseListingRepository::class)
 */
class CheeseListing
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"cheese_listing:read", "cheese_listing:write", "user:read", "user:write"})
     * 
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     maxMessage="Describe your cheese in 50 chars or less"
     * )
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @Groups({"cheese_listing:read"})
     * 
     * @Assert\NotBlank()
     * 
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @Groups({"cheese_listing:read", "cheese_listing:write", "user:read", "user:write"})
     * 
     * @Assert\NotBlank()
     * 
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublished = false;

    /**
     * @Groups({"cheese_listing:read", "cheese_listing:write"})
     * 
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="cheeseListings")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Assert\Valid()
     */
    private $owner;

    public function __construct(string $title)
    {
        $this->title = $title;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }


    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @Groups("cheese_listing:read")
     */
    public function getShortDescription(): ?string
    {
        if (strlen($this->description) < 40) {
            return $this->description;
        }
        return substr($this->description, 0, 40).'...';
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

     /**
     * The description of the cheese as raw text.
     * 
     * @SerializedName("description")
     *
     * @Groups({"cheese_listing:write", "user:write"})
     */
    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);
        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * How long ago in text that this cheese listing was added.
     *
     * @Groups("cheese_listing:read")
     */
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
