<?php

namespace App\Controller\Api;

use App\Service\ZapatillaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/zapatillas')]
class ZapatillaApiController extends AbstractController
{
    public function __construct(
        private ZapatillaService $zapatillaService
    ) {}

    #[Route('', name: 'api_zapatillas_listar', methods: ['GET'])]
    public function listar(Request $request): Response
    {
        try {
            $zapatillas = $this->zapatillaService->obtenerTodas();

            $resultado = [];
            foreach ($zapatillas as $zapatilla) {
                $resultado[] = [
                    'id' => $zapatilla->getId(),
                    'modelo' => $zapatilla->getModelo(),
                    'marca' => $zapatilla->getMarca(),
                    'talla' => $zapatilla->getTalla(),
                    'precio' => (float)$zapatilla->getPrecio(),
                    'stock' => $zapatilla->getStock(),
                    'categoria' => $zapatilla->getCategoria()?->getNombre(),
                    'vendedor' => $zapatilla->getVendedor()?->getNombre(),
                    'vendedor_id' => $zapatilla->getVendedor()?->getId(),
                    'imagen' => $zapatilla->getImagen(),
                ];
            }

            return $this->json(['zapatillas' => $resultado]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', name: 'api_zapatilla_obtener', methods: ['GET'])]
    public function obtener(int $id): Response
    {
        try {
            $zapatilla = $this->zapatillaService->obtenerPorId($id);
            if (!$zapatilla) {
                return $this->json(['error' => 'Zapatilla no encontrada'], 404);
            }

            return $this->json([
                'zapatilla' => [
                    'id' => $zapatilla->getId(),
                    'modelo' => $zapatilla->getModelo(),
                    'marca' => $zapatilla->getMarca(),
                    'talla' => $zapatilla->getTalla(),
                    'precio' => (float)$zapatilla->getPrecio(),
                    'stock' => $zapatilla->getStock(),
                    'categoria' => $zapatilla->getCategoria()?->getNombre(),
                    'vendedor' => $zapatilla->getVendedor()?->getNombre(),
                    'vendedor_id' => $zapatilla->getVendedor()?->getId(),
                    'imagen' => $zapatilla->getImagen(),
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
