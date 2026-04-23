<?php

namespace App\Entity;

use App\Repository\CategoriaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoriaRepository::class)]
class Categoria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Zapatilla>
     */
    #[ORM\OneToMany(targetEntity: Zapatilla::class, mappedBy: 'categoria')]
    private Collection $zapatillas;

    public function __construct()
    {
        $this->zapatillas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

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
     * @return Collection<int, Zapatilla>
     */
    public function getZapatillas(): Collection
    {
        return $this->zapatillas;
    }

    public function addZapatilla(Zapatilla $zapatilla): static
    {
        if (!$this->zapatillas->contains($zapatilla)) {
            $this->zapatillas  ->add($zapatilla);
            $zapatilla->setCategoria($this);
        }

        return $this;
    }

    public function removeZapatilla(Zapatilla $zapatilla): static
    {
        if ($this->zapatillas->removeElement($zapatilla)) {
            // set the owning side to null (unless already changed)
            if ($zapatilla->getCategoria() === $this) {
                $zapatilla->setCategoria(null);
            }
        }

        return $this;
    }
}
