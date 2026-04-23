<?php

namespace App\Service;

use App\Entity\Carrito;
use App\Entity\DetallePedido;
use App\Entity\Usuario;
use App\Entity\Zapatilla;
use App\Repository\CarritoRepository;
use Doctrine\ORM\EntityManagerInterface;

class CarritoService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CarritoRepository $carritoRepository
    ) {}

    public function obtenerCarrito(Usuario $usuario): Carrito
    {
        // Buscar carrito existente
        $carrito = $this->carritoRepository->findOneBy(['usuario' => $usuario]);

        // Si no existe, crear uno nuevo
        if (!$carrito) {
            $carrito = new Carrito();
            $carrito->setUsuario($usuario);
            $carrito->setFechaCreacion(new \DateTime());
            $this->entityManager->persist($carrito);
            $this->entityManager->flush();
        }

        return $carrito;
    }

    public function agregarItem(Carrito $carrito, Zapatilla $zapatilla, int $cantidad): DetallePedido
    {
        if ($cantidad <= 0) {
            throw new \Exception('La cantidad debe ser mayor a 0');
        }

        if ($zapatilla->getStock() < $cantidad) {
            throw new \Exception('Stock insuficiente');
        }

        // Verificar si ya existe en el carrito
        $detalleExistente = null;
        foreach ($carrito->getDetalleCarritos() as $detalle) {
            if ($detalle->getZapatilla()->getId() === $zapatilla->getId()) {
                $detalleExistente = $detalle;
                break;
            }
        }

        if ($detalleExistente) {
            $detalleExistente->setCantidad($detalleExistente->getCantidad() + $cantidad);
        } else {
            $detalle = new DetallePedido();
            $detalle->setCarrito($carrito);
            $detalle->setZapatilla($zapatilla);
            $detalle->setCantidad($cantidad);
            $detalle->setPrecioMomento($zapatilla->getPrecio());
            $detalle->setFecha(new \DateTime());
            
            $this->entityManager->persist($detalle);
            $detalleExistente = $detalle;
        }

        $this->entityManager->flush();
        return $detalleExistente;
    }

    public function eliminarItem(Carrito $carrito, $detalleId): void
    {
        // Si es un objeto, usar su ID; si es un ID, usarlo directamente
        $id = $detalleId instanceof DetallePedido ? $detalleId->getId() : $detalleId;
        
        $detalle = null;
        foreach ($carrito->getDetalleCarritos() as $d) {
            if ($d->getId() === $id) {
                $detalle = $d;
                break;
            }
        }

        if (!$detalle) {
            throw new \Exception('Item no encontrado en el carrito');
        }

        $this->entityManager->remove($detalle);
        $this->entityManager->flush();
    }

    public function actualizarItem(Carrito $carrito, int $detalleId, int $cantidad): void
    {
        if ($cantidad <= 0) {
            $this->eliminarItem($carrito, $detalleId);
            return;
        }

        $detalle = null;
        foreach ($carrito->getDetalleCarritos() as $d) {
            if ($d->getId() === $detalleId) {
                $detalle = $d;
                break;
            }
        }

        if (!$detalle) {
            throw new \Exception('Item no encontrado en el carrito');
        }

        if ($detalle->getZapatilla()->getStock() < $cantidad) {
            throw new \Exception('Stock insuficiente para esta cantidad');
        }

        $detalle->setCantidad($cantidad);
        $this->entityManager->flush();
    }

    public function vaciarCarrito(Carrito $carrito): void
    {
        foreach ($carrito->getDetalleCarritos() as $detalle) {
            $this->entityManager->remove($detalle);
        }
        $this->entityManager->flush();
    }

    public function obtenerTotal(Carrito $carrito): string
    {
        $total = 0;
        foreach ($carrito->getDetalleCarritos() as $detalle) {
            $subtotal = (float)$detalle->getPrecioMomento() * $detalle->getCantidad();
            $total += $subtotal;
        }
        return (string)$total;
    }
}
