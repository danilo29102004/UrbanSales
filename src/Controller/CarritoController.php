<?php

namespace App\Controller;

use App\Service\CarritoService;
use App\Service\UsuarioService;
use App\Service\ZapatillaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/carrito')]
#[Route('/api/carrito')]
class CarritoController extends AbstractController
{
    public function __construct(
        private CarritoService $carritoService,
        private UsuarioService $usuarioService,
        private ZapatillaService $zapatillaService
    ) {}

    #[Route('/{usuarioId}', name: 'app_carrito_obtener', methods: ['GET'])]
    public function obtener(int $usuarioId): Response
    {
        try {
            $usuario = $this->usuarioService->obtenerPorId($usuarioId);
            if (!$usuario) {
                return $this->json(['error' => 'Usuario no encontrado'], 404);
            }

            $carrito = $this->carritoService->obtenerCarrito($usuario);

            $detalles = [];
            foreach ($carrito->getDetalleCarritos() as $detalle) {
                $zapatilla = $detalle->getZapatilla();
                $detalles[] = [
                    'id' => $detalle->getId(),
                    'cantidad' => $detalle->getCantidad(),
                    'precioMomento' => (float)$detalle->getPrecioMomento(),
                    'zapatilla' => [
                        'id' => $zapatilla->getId(),
                        'modelo' => $zapatilla->getModelo(),
                        'marca' => $zapatilla->getMarca(),
                        'talla' => $zapatilla->getTalla(),
                        'precio' => (float)$zapatilla->getPrecio()
                    ]
                ];
            }

            $total = $this->carritoService->obtenerTotal($carrito);

            return $this->json([
                'carrito' => [
                    'id' => $carrito->getId(),
                    'fecha_creacion' => $carrito->getFechaCreacion()->format('Y-m-d H:i:s')
                ],
                'detalles' => $detalles,
                'total' => (float)$total,
                'cantidad_items' => count($detalles)
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/agregar', name: 'app_carrito_agregar', methods: ['POST'])]
    public function agregarItem(Request $request): Response
    {
        try {
            $usuarioId = $request->get('usuario_id');
            $zapatillaId = $request->get('zapatilla_id');
            $cantidad = $request->get('cantidad');

            if (!$usuarioId || !$zapatillaId || !$cantidad) {
                return $this->json(['error' => 'Faltan campos obligatorios'], 400);
            }

            $usuario = $this->usuarioService->obtenerPorId($usuarioId);
            if (!$usuario) {
                return $this->json(['error' => 'Usuario no encontrado'], 404);
            }

            $zapatilla = $this->zapatillaService->obtenerPorId($zapatillaId);
            if (!$zapatilla) {
                return $this->json(['error' => 'Zapatilla no encontrada'], 404);
            }

            $carrito = $this->carritoService->obtenerCarrito($usuario);
            $this->carritoService->agregarItem($carrito, $zapatilla, (int)$cantidad);

            return $this->json([
                'mensaje' => 'Item agregado al carrito',
                'carrito' => [
                    'total_items' => count($carrito->getDetalleCarritos()),
                    'total' => $this->carritoService->obtenerTotal($carrito)
                ]
            ], 201);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/eliminar/{detalleId}', name: 'app_carrito_eliminar', methods: ['DELETE'])]
    public function eliminarItem(int $detalleId, Request $request): Response
    {
        try {
            $usuarioId = $request->get('usuario_id');

            $usuario = $this->usuarioService->obtenerPorId($usuarioId);
            if (!$usuario) {
                return $this->json(['error' => 'Usuario no encontrado'], 404);
            }

            $carrito = $this->carritoService->obtenerCarrito($usuario);

            // Buscar el detalle en el carrito
            $detalle = null;
            foreach ($carrito->getDetalleCarritos() as $d) {
                if ($d->getId() === $detalleId) {
                    $detalle = $d;
                    break;
                }
            }

            if (!$detalle) {
                return $this->json(['error' => 'Item no encontrado en el carrito'], 404);
            }

            $this->carritoService->eliminarItem($carrito, $detalle);

            return $this->json([
                'mensaje' => 'Item eliminado del carrito',
                'carrito' => [
                    'total_items' => count($carrito->getDetalleCarritos()),
                    'total' => $this->carritoService->obtenerTotal($carrito)
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/vaciar/{usuarioId}', name: 'app_carrito_vaciar', methods: ['POST'])]
    public function vaciar(int $usuarioId): Response
    {
        try {
            $usuario = $this->usuarioService->obtenerPorId($usuarioId);
            if (!$usuario) {
                return $this->json(['error' => 'Usuario no encontrado'], 404);
            }

            $carrito = $this->carritoService->obtenerCarrito($usuario);
            $this->carritoService->vaciarCarrito($carrito);

            return $this->json(['mensaje' => 'Carrito vaciado']);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
