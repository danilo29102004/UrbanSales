<?php

namespace App\Entity;

use App\Repository\VendedorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VendedorRepository::class)]
class Vendedor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'], inversedBy: 'vendedor')]
    private ?Usuario $usuario = null;

    #[ORM\Column(length: 20)]
    private ?string $dni = null;

    #[ORM\Column(length: 255)]
    private ?string $documentacion = null;

    #[ORM\Column(length: 255)]
    private ?string $estado = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $fechaSolicitud = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $fechaAprobacion = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(string $dni): static
    {
        $this->dni = $dni;

        return $this;
    }

    public function getDocumentacion(): ?string
    {
        return $this->documentacion;
    }

    public function setDocumentacion(string $documentacion): static
    {
        $this->documentacion = $documentacion;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getFechaSolicitud(): ?\DateTime
    {
        return $this->fechaSolicitud;
    }

    public function setFechaSolicitud(\DateTime $fechaSolicitud): static
    {
        $this->fechaSolicitud = $fechaSolicitud;

        return $this;
    }

    public function getFechaAprobacion(): ?\DateTime
    {
        return $this->fechaAprobacion;
    }

    public function setFechaAprobacion(?\DateTime $fechaAprobacion): static
    {
        $this->fechaAprobacion = $fechaAprobacion;

        return $this;
    }
}
