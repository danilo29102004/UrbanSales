<?php

namespace App\Entity;

use App\Repository\DetallePedidoRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_USER')"),
        new Delete(security: "is_granted('ROLE_USER')"),
    ]
)]
#[ORM\Entity(repositoryClass: DetallePedidoRepository::class)]
class DetallePedido
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $cantidad = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $precioMomento = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $fecha = null;

    #[ORM\ManyToOne(inversedBy: 'detallePedidos')]
    private ?Pedido $pedido = null;

    #[ORM\ManyToOne(inversedBy: 'detallePedidos')]
    private ?Zapatilla $zapatilla = null;

    #[ORM\ManyToOne(inversedBy: 'detalleCarritos')]
    private ?Carrito $carrito = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): static
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getPrecioMomento(): ?string
    {
        return $this->precioMomento;
    }

    public function setPrecioMomento(string $precioMomento): static
    {
        $this->precioMomento = $precioMomento;

        return $this;
    }

    public function getFecha(): ?\DateTime
    {
        return $this->fecha;
    }

    public function setFecha(\DateTime $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getPedido(): ?Pedido
    {
        return $this->pedido;
    }

    public function setPedido(?Pedido $pedido): static
    {
        $this->pedido = $pedido;

        return $this;
    }

    public function getZapatilla(): ?Zapatilla
    {
        return $this->zapatilla;
    }

    public function setZapatilla(?Zapatilla $zapatilla): static
    {
        $this->zapatilla = $zapatilla;

        return $this;
    }

    public function getCarrito(): ?Carrito
    {
        return $this->carrito;
    }

    public function setCarrito(?Carrito $carrito): static
    {
        $this->carrito = $carrito;

        return $this;
    }
}
