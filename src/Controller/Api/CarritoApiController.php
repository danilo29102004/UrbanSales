<?php

namespace App\Controller\Api;

use App\Service\CarritoService;
use App\Service\UsuarioService;
use App\Service\ZapatillaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/carrito')]
class CarritoApiController extends AbstractController
{
    public function __construct(
        private CarritoService $carritoService,
        private UsuarioService $usuarioService,
        private ZapatillaService $zapatillaService
    ) {}

    #[Route('', name: 'api_carrito_obtener', methods: ['GET'])]
    public function obtener(): Response
    {
        try {
            $usuario = $this->getUser();
            if (!$usuario) {
                return $this->json(['error' => 'No autenticado'], 401);
            }

            $carrito = $this->carritoService->obtenerCarrito($usuario);

            $detalles = [];
            foreach ($carrito->getDetalleCarritos() as $detalle) {
                $zapatilla = $detalle->getZapatilla();
                
                // Obtener imágenes de la zapatilla
                $imagenes = [];
                foreach ($zapatilla->getImagenes() as $img) {
                    $imagenes[] = $img->getRuta();
                }
                
                // Si no hay imágenes en la tabla, usar la imagen legacy
                if (empty($imagenes) && $zapatilla->getImagen()) {
                    $imagenes[] = $zapatilla->getImagen();
                }
                
                $detalles[] = [
                    'id' => $detalle->getId(),
                    'cantidad' => $detalle->getCantidad(),
                    'precioMomento' => (float)$detalle->getPrecioMomento(),
                    'zapatilla' => [
                        'id' => $zapatilla->getId(),
                        'modelo' => $zapatilla->getModelo(),
                        'marca' => $zapatilla->getMarca(),
                        'talla' => $zapatilla->getTalla(),
                        'precio' => (float)$zapatilla->getPrecio(),
                        'imagen' => $zapatilla->getImagen(),
                        'imagenes' => $imagenes
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

    #[Route('/agregar', name: 'api_carrito_agregar', methods: ['POST'])]
    public function agregarItem(Request $request): Response
    {
        try {
            $usuario = $this->getUser();
            if (!$usuario) {
                return $this->json(['error' => 'No autenticado'], 401);
            }

            $data = json_decode($request->getContent(), true);
            $zapatillaId = $data['zapatilla_id'] ?? null;
            $cantidad = $data['cantidad'] ?? 1;

            if (!$zapatillaId) {
                return $this->json(['error' => 'Falta zapatilla_id'], 400);
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

    #[Route('/eliminar/{detalleId}', name: 'api_carrito_eliminar', methods: ['DELETE'])]
    public function eliminarItem(int $detalleId): Response
    {
        try {
            $usuario = $this->getUser();
            if (!$usuario) {
                return $this->json(['error' => 'No autenticado'], 401);
            }

            $carrito = $this->carritoService->obtenerCarrito($usuario);
            $this->carritoService->eliminarItem($carrito, $detalleId);

            return $this->json([
                'mensaje' => 'Item eliminado',
                'total' => $this->carritoService->obtenerTotal($carrito),
                'cantidad_items' => count($carrito->getDetalleCarritos())
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/actualizar/{detalleId}', name: 'api_carrito_actualizar', methods: ['PATCH'])]
    public function actualizarItem(int $detalleId, Request $request): Response
    {
        try {
            $usuario = $this->getUser();
            if (!$usuario) {
                return $this->json(['error' => 'No autenticado'], 401);
            }

            $data = json_decode($request->getContent(), true);
            $cantidad = $data['cantidad'] ?? null;

            if ($cantidad === null) {
                return $this->json(['error' => 'Falta cantidad'], 400);
            }

            $carrito = $this->carritoService->obtenerCarrito($usuario);
            $this->carritoService->actualizarItem($carrito, $detalleId, (int)$cantidad);

            return $this->json([
                'mensaje' => 'Item actualizado',
                'total' => $this->carritoService->obtenerTotal($carrito),
                'cantidad_items' => count($carrito->getDetalleCarritos())
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
