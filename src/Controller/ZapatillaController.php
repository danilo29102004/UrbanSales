<?php

namespace App\Controller;

use App\Service\ZapatillaService;
use App\Service\UsuarioService;
use App\Service\VendedorService;
use App\Repository\CategoriaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/zapatillas')]
class ZapatillaController extends AbstractController
{
    public function __construct(
        private ZapatillaService $zapatillaService,
        private UsuarioService $usuarioService,
        private VendedorService $vendedorService,
        private CategoriaRepository $categoriaRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_zapatillas_listar', methods: ['GET'])]
    #[Route('/api/zapatillas', name: 'app_zapatillas_api_listar', methods: ['GET'])]
    public function listar(Request $request): Response
    {
        try {
            $categoriaId = $request->query->get('categoria_id');
            $pagina = (int)$request->query->get('pagina', 1);
            $limit = (int)$request->query->get('limit', 12);

            if ($pagina < 1) $pagina = 1;
            if ($limit < 1 || $limit > 100) $limit = 12;

            if ($categoriaId) {
                $categoria = $this->categoriaRepository->find($categoriaId);
                if (!$categoria) {
                    return $this->json(['error' => 'Categoría no encontrada'], 404);
                }
                $datos = $this->zapatillaService->obtenerPorCategoriaConPaginacion($categoria, $pagina, $limit);
            } else {
                $datos = $this->zapatillaService->obtenerConPaginacion($pagina, $limit);
            }

            $zapatillas = $datos['zapatillas'];
            $resultado = [];
            foreach ($zapatillas as $zapatilla) {
                $resultado[] = [
                    'id' => $zapatilla->getId(),
                    'modelo' => $zapatilla->getModelo(),
                    'marca' => $zapatilla->getMarca(),
                    'talla' => $zapatilla->getTalla(),
                    'precio' => $zapatilla->getPrecio(),
                    'stock' => $zapatilla->getStock(),
                    'categoria' => $zapatilla->getCategoria()->getNombre(),
                    'vendedor' => $zapatilla->getVendedor()->getNombre()
                ];
            }

            return $this->json([
                'zapatillas' => $resultado,
                'paginacion' => [
                    'pagina_actual' => $datos['pagina_actual'],
                    'total_paginas' => $datos['total_paginas'],
                    'total_items' => $datos['total_items'],
                    'items_por_pagina' => $datos['items_por_pagina'],
                    'tiene_siguiente' => $datos['tiene_siguiente'],
                    'tiene_anterior' => $datos['tiene_anterior']
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', name: 'app_zapatilla_obtener', methods: ['GET'])]
    public function obtener(int $id): Response
    {
        try {
            $zapatilla = $this->zapatillaService->obtenerPorId($id);
            if (!$zapatilla) {
                throw $this->createNotFoundException('Zapatilla no encontrada');
            }

            return $this->render('zapatillas/show.html.twig', [
                'zapatilla' => $zapatilla
            ]);

        } catch (\Throwable $e) {
            throw $this->createNotFoundException('Zapatilla no encontrada');
        }
    }

    #[Route('/api/{id}', name: 'app_zapatilla_api_obtener', methods: ['GET'])]
    public function obtenerApi(int $id): Response
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
                    'precio' => $zapatilla->getPrecio(),
                    'stock' => $zapatilla->getStock(),
                    'categoria' => $zapatilla->getCategoria()->getNombre(),
                    'vendedor' => $zapatilla->getVendedor()->getNombre()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/crear', name: 'app_zapatilla_crear_formulario', methods: ['GET'])]
    public function crearFormulario(): Response
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            return $this->redirectToRoute('app_auth_login');
        }

        $vendedor = $this->vendedorService->obtenerPorUsuario($usuario);
        if (!$vendedor || $vendedor->getEstado() !== 'APROBADO') {
            return $this->redirectToRoute('app_vendedor_solicitar_formulario');
        }

        $categorias = $this->categoriaRepository->findAll();

        return $this->render('zapatillas/crear.html.twig', [
            'categorias' => $categorias
        ]);
    }

    #[Route('/crear', name: 'app_zapatilla_crear_api', methods: ['POST'])]
    public function crearApi(Request $request): Response
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            return $this->json(['error' => 'No autenticado'], 401);
        }

        $vendedor = $this->vendedorService->obtenerPorUsuario($usuario);
        if (!$vendedor || $vendedor->getEstado() !== 'APROBADO') {
            return $this->json(['error' => 'No eres un vendedor aprobado'], 403);
        }

        try {
            $modelo = $request->request->get('modelo');
            $marca = $request->request->get('marca');
            $talla = $request->request->get('talla');
            $precio = $request->request->get('precio');
            $stock = $request->request->get('stock');
            $categoriaId = $request->request->get('categoria_id');

            if (!$modelo || !$marca || !$talla || !$precio || !$stock || !$categoriaId) {
                return $this->json(['error' => 'Faltan campos obligatorios'], 400);
            }

            $categoria = $this->categoriaRepository->find($categoriaId);
            if (!$categoria) {
                return $this->json(['error' => 'Categoría no encontrada'], 404);
            }

            $zapatilla = $this->zapatillaService->crearZapatilla(
                $modelo,
                $marca,
                $talla,
                $precio,
                (int)$stock,
                $categoria,
                $usuario
            );

            return $this->json([
                'mensaje' => 'Zapatilla creada exitosamente',
                'zapatilla' => [
                    'id' => $zapatilla->getId(),
                    'modelo' => $zapatilla->getModelo(),
                    'marca' => $zapatilla->getMarca(),
                    'precio' => $zapatilla->getPrecio()
                ]
            ], 201);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}/editar', name: 'app_zapatilla_editar_formulario', methods: ['GET'])]
    public function editarFormulario(int $id): Response
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            return $this->redirectToRoute('app_auth_login');
        }

        $zapatilla = $this->zapatillaService->obtenerPorId($id);
        if (!$zapatilla) {
            return $this->redirectToRoute('app_zapatillas_list');
        }

        if ($zapatilla->getVendedor()->getId() !== $usuario->getId()) {
            return $this->redirectToRoute('app_zapatillas_list');
        }

        $categorias = $this->categoriaRepository->findAll();

        return $this->render('zapatillas/editar.html.twig', [
            'zapatilla' => $zapatilla,
            'categorias' => $categorias
        ]);
    }

    #[Route('/{id}/actualizar', name: 'app_zapatilla_actualizar', methods: ['POST'])]
    public function actualizar(int $id, Request $request): Response
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            return $this->json(['error' => 'No autenticado'], 401);
        }

        try {
            $zapatilla = $this->zapatillaService->obtenerPorId($id);
            if (!$zapatilla) {
                return $this->json(['error' => 'Zapatilla no encontrada'], 404);
            }

            if ($zapatilla->getVendedor()->getId() !== $usuario->getId()) {
                return $this->json(['error' => 'No tienes permiso para actualizar esta zapatilla'], 403);
            }

            $modelo = $request->request->get('modelo');
            $marca = $request->request->get('marca');
            $talla = $request->request->get('talla');
            $precio = $request->request->get('precio');
            $stock = $request->request->get('stock');
            $categoriaId = $request->request->get('categoria_id');

            if (!$modelo || !$marca || !$talla || !$precio || $stock === null || !$categoriaId) {
                return $this->json(['error' => 'Faltan campos obligatorios'], 400);
            }

            $categoria = $this->categoriaRepository->find($categoriaId);
            if (!$categoria) {
                return $this->json(['error' => 'Categoría no encontrada'], 404);
            }

            $zapatilla->setModelo($modelo);
            $zapatilla->setMarca($marca);
            $zapatilla->setTalla($talla);
            $zapatilla->setPrecio($precio);
            $zapatilla->setStock((int)$stock);
            $zapatilla->setCategoria($categoria);
            
            $this->entityManager->flush();

            return $this->json([
                'mensaje' => 'Zapatilla actualizada exitosamente',
                'zapatilla' => [
                    'id' => $zapatilla->getId(),
                    'modelo' => $zapatilla->getModelo(),
                    'marca' => $zapatilla->getMarca(),
                    'precio' => $zapatilla->getPrecio()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}/eliminar', name: 'app_zapatilla_eliminar', methods: ['POST'])]
    public function eliminar(int $id): Response
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            return $this->json(['error' => 'No autenticado'], 401);
        }

        try {
            $zapatilla = $this->zapatillaService->obtenerPorId($id);
            if (!$zapatilla) {
                return $this->json(['error' => 'Zapatilla no encontrada'], 404);
            }

            if ($zapatilla->getVendedor()->getId() !== $usuario->getId()) {
                return $this->json(['error' => 'No tienes permiso para eliminar esta zapatilla'], 403);
            }

            $this->entityManager->remove($zapatilla);
            $this->entityManager->flush();

            return $this->json(['mensaje' => 'Zapatilla eliminada exitosamente']);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/vendedor/{vendedorId}', name: 'app_zapatillas_vendedor', methods: ['GET'])]
    public function obtenerPorVendedor(int $vendedorId): Response
    {
        try {
            $vendedor = $this->usuarioService->obtenerPorId($vendedorId);
            if (!$vendedor) {
                return $this->json(['error' => 'Vendedor no encontrado'], 404);
            }

            $zapatillas = $this->zapatillaService->obtenerPorVendedor($vendedor);

            $resultado = [];
            foreach ($zapatillas as $zapatilla) {
                $resultado[] = [
                    'id' => $zapatilla->getId(),
                    'modelo' => $zapatilla->getModelo(),
                    'marca' => $zapatilla->getMarca(),
                    'precio' => $zapatilla->getPrecio(),
                    'stock' => $zapatilla->getStock()
                ];
            }

            return $this->json(['zapatillas' => $resultado]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
