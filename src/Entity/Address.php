<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
#[ORM\Table(name: "address", uniqueConstraints: [
    new ORM\UniqueConstraint(name: "unique_street_city", columns: ["street", "city"])
])]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150, nullable: false)]
    private ?string $name = null;

    #[Assert\NotBlank]
    #[ORM\Column(length: 150, nullable: false)]
    private ?string $street = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 3, nullable: false)]
    private ?string $lat = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 3, nullable: false)]
    private ?string $lng = null;

    #[Assert\NotBlank]
    #[ORM\Column(length: 50, nullable: false)]
    private ?string $city = null;

    #[ORM\Column(length: 5, nullable: false)]
    private ?string $zipCode = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'address')]
    private Collection $events;

    #[ORM\Column]
    private ?bool $isAllowed = null;

    public function __construct()
    {
        $this->events = new ArrayCollection();
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

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getLat(): ?string
    {
        return $this->lat;
    }

    public function setLat(string $lat): static
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLng(): ?string
    {
        return $this->lng;
    }

    public function setLng(string $lng): static
    {
        $this->lng = $lng;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setAddress($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getAddress() === $this) {
                $event->setAddress(null);
            }
        }

        return $this;
    }

    public function isAllowed(): ?bool
    {
        return $this->isAllowed;
    }

    public function setIsAllowed(bool $isAllowed): static
    {
        $this->isAllowed = $isAllowed;

        return $this;
    }
}
