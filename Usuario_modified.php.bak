<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_ADMIN')"),
    ]
)]
#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    /**
     * @var Collection<int, Zapatilla>
     */
    #[ORM\OneToMany(targetEntity: Zapatilla::class, mappedBy: 'vendedor')]
    private Collection $zapatillas;

    /**
     * @var Collection<int, Pedido>
     */
    #[ORM\OneToMany(targetEntity: Pedido::class, mappedBy: 'usuario')]
    private Collection $pedidos;

    /**
     * @var Collection<int, Carrito>
     */
    #[ORM\OneToMany(targetEntity: Carrito::class, mappedBy: 'usuario')]
    private Collection $carritos;

    #[ORM\OneToOne(targetEntity: Vendedor::class, mappedBy: 'usuario')]
    private ?Vendedor $vendedor = null;

    #[ORM\OneToOne(targetEntity: Comprador::class, mappedBy: 'usuario')]
    private ?Comprador $comprador = null;

    public function __construct()
    {
        $this->zapatillas = new ArrayCollection();
        $this->pedidos = new ArrayCollection();
        $this->carritos = new ArrayCollection();
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

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
            $this->zapatillas->add($zapatilla);
            $zapatilla->setVendedor($this);
        }

        return $this;
    }

    public function removeZapatilla(Zapatilla $zapatilla): static
    {
        if ($this->zapatillas->removeElement($zapatilla)) {
            // set the owning side to null (unless already changed)
            if ($zapatilla->getVendedor() === $this) {
                $zapatilla->setVendedor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Pedido>
     */
    public function getPedidos(): Collection
    {
        return $this->pedidos;
    }

    public function addPedido(Pedido $pedido): static
    {
        if (!$this->pedidos->contains($pedido)) {
            $this->pedidos->add($pedido);
            $pedido->setUsuario($this);
        }

        return $this;
    }

    public function removePedido(Pedido $pedido): static
    {
        if ($this->pedidos->removeElement($pedido)) {
            // set the owning side to null (unless already changed)
            if ($pedido->getUsuario() === $this) {
                $pedido->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Carrito>
     */
    public function getCarritos(): Collection
    {
        return $this->carritos;
    }

    public function addCarrito(Carrito $carrito): static
    {
        if (!$this->carritos->contains($carrito)) {
            $this->carritos->add($carrito);
            $carrito->setUsuario($this);
        }

        return $this;
    }

    public function removeCarrito(Carrito $carrito): static
    {
        if ($this->carritos->removeElement($carrito)) {
            // set the owning side to null (unless already changed)
            if ($carrito->getUsuario() === $this) {
                $carrito->setUsuario(null);
            }
        }

        return $this;
    }

    public function getVendedor(): ?Vendedor
    {
        return $this->vendedor;
    }

    public function setVendedor(?Vendedor $vendedor): static
    {
        $this->vendedor = $vendedor;

        return $this;
    }

    public function getComprador(): ?Comprador
    {
        return $this->comprador;
    }

    public function setComprador(?Comprador $comprador): static
    {
        $this->comprador = $comprador;

        return $this;
    }

    // UserInterface methods
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // No hace falta borrar credenciales sensibles
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }
}
