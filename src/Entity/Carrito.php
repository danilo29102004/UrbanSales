<?php

namespace App\Entity;

use App\Repository\CarritoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarritoRepository::class)]
class Carrito
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $fecha_creacion = null;

    #[ORM\ManyToOne(inversedBy: 'carritos')]
    private ?Usuario $usuario = null;

    /**
     * @var Collection<int, DetallePedido>
     */
    #[ORM\OneToMany(targetEntity: DetallePedido::class, mappedBy: 'carrito')]
    private Collection $detalleCarritos;

    public function __construct()
    {
        $this->detalleCarritos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechaCreacion(): ?\DateTime
    {
        return $this->fecha_creacion;
    }

    public function setFechaCreacion(\DateTime $fecha_creacion): static
    {
        $this->fecha_creacion = $fecha_creacion;

        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * @return Collection<int, DetallePedido>
     */
    public function getDetalleCarritos(): Collection
    {
        return $this->detalleCarritos;
    }

    public function addDetalleCarrito(DetallePedido $detalleCarrito): static
    {
        if (!$this->detalleCarritos->contains($detalleCarrito)) {
            $this->detalleCarritos->add($detalleCarrito);
            $detalleCarrito->setCarrito($this);
        }

        return $this;
    }

    public function removeDetalleCarrito(DetallePedido $detalleCarrito): static
    {
        if ($this->detalleCarritos->removeElement($detalleCarrito)) {
            // set the owning side to null (unless already changed)
            if ($detalleCarrito->getCarrito() === $this) {
                $detalleCarrito->setCarrito(null);
            }
        }

        return $this;
    }
}
