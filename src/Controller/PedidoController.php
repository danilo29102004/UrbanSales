<?php

namespace App\Controller;

use App\Service\PedidoService;
use App\Service\CarritoService;
use App\Service\UsuarioService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pedidos')]
class PedidoController extends AbstractController
{
    public function __construct(
        private PedidoService $pedidoService,
        private CarritoService $carritoService,
        private UsuarioService $usuarioService
    ) {}

    #[Route('/usuario/{usuarioId}', name: 'app_pedidos_usuario', methods: ['GET'])]
    public function obtenerPedidosUsuario(int $usuarioId): Response
    {
        try {
            $usuario = $this->usuarioService->obtenerPorId($usuarioId);
            if (!$usuario) {
                return $this->json(['error' => 'Usuario no encontrado'], 404);
            }

            $pedidos = $this->pedidoService->obtenerPedidosUsuario($usuario);

            $resultado = [];
            foreach ($pedidos as $pedido) {
                $items = [];
                foreach ($pedido->getDetallePedidos() as $detalle) {
                    $items[] = [
                        'zapatilla' => $detalle->getZapatilla()->getModelo(),
                        'cantidad' => $detalle->getCantidad(),
                        'precio' => $detalle->getPrecioMomento()
                    ];
                }

                $resultado[] = [
                    'id' => $pedido->getId(),
                    'fecha' => $pedido->getFecha()->format('Y-m-d H:i:s'),
                    'total' => $pedido->getTotal(),
                    'metodo_pago' => $pedido->getMetodoDePago(),
                    'direccion_envio' => $pedido->getDireccionDeEnvio(),
                    'items' => $items
                ];
            }

            return $this->json(['pedidos' => $resultado]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', name: 'app_pedido_obtener', methods: ['GET'])]
    public function obtener(int $id): Response
    {
        try {
            $pedido = $this->pedidoService->obtenerPorId($id);
            if (!$pedido) {
                return $this->json(['error' => 'Pedido no encontrado'], 404);
            }

            $items = [];
            foreach ($pedido->getDetallePedidos() as $detalle) {
                $items[] = [
                    'zapatilla' => $detalle->getZapatilla()->getModelo(),
                    'cantidad' => $detalle->getCantidad(),
                    'precio_momento' => $detalle->getPrecioMomento(),
                    'subtotal' => (float)$detalle->getPrecioMomento() * $detalle->getCantidad()
                ];
            }

            return $this->json([
                'pedido' => [
                    'id' => $pedido->getId(),
                    'fecha' => $pedido->getFecha()->format('Y-m-d H:i:s'),
                    'total' => $pedido->getTotal(),
                    'metodo_pago' => $pedido->getMetodoDePago(),
                    'direccion_envio' => $pedido->getDireccionDeEnvio(),
                    'usuario' => $pedido->getUsuario()->getNombre(),
                    'items' => $items
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/checkout', name: 'app_pedido_checkout', methods: ['POST'])]
    public function checkout(Request $request): Response
    {
        try {
            $usuarioId = $request->get('usuario_id');
            $metodoPago = $request->get('metodo_pago');
            $direccionEnvio = $request->get('direccion_envio');

            if (!$usuarioId || !$metodoPago || !$direccionEnvio) {
                return $this->json(['error' => 'Faltan campos obligatorios'], 400);
            }

            $usuario = $this->usuarioService->obtenerPorId($usuarioId);
            if (!$usuario) {
                return $this->json(['error' => 'Usuario no encontrado'], 404);
            }

            $carrito = $this->carritoService->obtenerCarrito($usuario);
            $pedido = $this->pedidoService->crearPedido(
                $usuario,
                $carrito,
                $metodoPago,
                $direccionEnvio
            );

            // Vaciar carrito después de crear pedido
            $this->carritoService->vaciarCarrito($carrito);

            return $this->json([
                'mensaje' => 'Pedido creado exitosamente',
                'pedido' => [
                    'id' => $pedido->getId(),
                    'total' => $pedido->getTotal(),
                    'fecha' => $pedido->getFecha()->format('Y-m-d H:i:s')
                ]
            ], 201);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('', name: 'app_pedidos_listar', methods: ['GET'])]
    public function listar(): Response
    {
        try {
            $pedidos = $this->pedidoService->obtenerTodos();

            $resultado = [];
            foreach ($pedidos as $pedido) {
                $resultado[] = [
                    'id' => $pedido->getId(),
                    'usuario' => $pedido->getUsuario()->getNombre(),
                    'fecha' => $pedido->getFecha()->format('Y-m-d H:i:s'),
                    'total' => $pedido->getTotal(),
                    'metodo_pago' => $pedido->getMetodoDePago()
                ];
            }

            return $this->json(['pedidos' => $resultado]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
