<?php

namespace App\Service;

use App\Entity\Pedido;
use App\Entity\DetallePedido;
use App\Entity\Usuario;
use App\Entity\Carrito;
use App\Repository\PedidoRepository;
use Doctrine\ORM\EntityManagerInterface;

class PedidoService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PedidoRepository $pedidoRepository,
        private ZapatillaService $zapatillaService
    ) {}

    public function crearPedido(
        Usuario $usuario,
        Carrito $carrito,
        string $metodoPago,
        string $direccionEnvio
    ): Pedido {
        if (count($carrito->getDetalleCarritos()) === 0) {
            throw new \Exception('El carrito está vacío');
        }

        $pedido = new Pedido();
        $pedido->setUsuario($usuario);
        $pedido->setFecha(new \DateTime());
        $pedido->setMetodoDePago($metodoPago);
        $pedido->setDireccionDeEnvio($direccionEnvio);

        // Copiar items del carrito al pedido
        $total = 0;
        foreach ($carrito->getDetalleCarritos() as $detalleCarrito) {
            $detallePedido = new DetallePedido();
            $detallePedido->setPedido($pedido);
            $detallePedido->setZapatilla($detalleCarrito->getZapatilla());
            $detallePedido->setCantidad($detalleCarrito->getCantidad());
            $detallePedido->setPrecioMomento($detalleCarrito->getPrecioMomento());
            $detallePedido->setFecha(new \DateTime());
            
            $this->entityManager->persist($detallePedido);
            
            // Actualizar stock
            $this->zapatillaService->actualizarStock(
                $detalleCarrito->getZapatilla(),
                $detalleCarrito->getCantidad()
            );

            $subtotal = (float)$detalleCarrito->getPrecioMomento() * $detalleCarrito->getCantidad();
            $total += $subtotal;
        }

        $pedido->setTotal((string)$total);
        $this->entityManager->persist($pedido);
        $this->entityManager->flush();

        return $pedido;
    }

    public function confirmarPedido(Pedido $pedido): Pedido
    {
        // Aquí iría validación de pago, etc
        $this->entityManager->flush();
        return $pedido;
    }

    public function obtenerPedidosUsuario(Usuario $usuario): array
    {
        return $this->pedidoRepository->findBy(
            ['usuario' => $usuario],
            ['fecha' => 'DESC']
        );
    }

    public function obtenerPorId(int $id): ?Pedido
    {
        return $this->pedidoRepository->find($id);
    }

    public function obtenerTodos(): array
    {
        return $this->pedidoRepository->findBy([], ['fecha' => 'DESC']);
    }
}
