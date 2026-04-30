<?php

namespace App\Controller;

use App\Entity\Zapatilla;
use App\Entity\Categoria;
use App\Service\ZapatillaUploadService;
use App\Repository\CategoriaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/vendedor/zapatillas')]
#[IsGranted('ROLE_USER')]
class ZapatillaVendedorController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private CategoriaRepository $categoriaRepository,
        private ZapatillaUploadService $uploadService
    ) {}

    #[Route('', name: 'app_vendedor_zapatillas_listar', methods: ['GET'])]
    public function listar(): Response
    {
        $usuario = $this->getUser();
        
        if (!$usuario->getVendedor() || $usuario->getVendedor()->getEstado() !== 'APROBADO') {
            $this->addFlash('error', 'No eres un vendedor aprobado');
            return $this->redirectToRoute('app_home');
        }

        $zapatillas = $usuario->getZapatillas();

        return $this->render('vendedor/zapatillas/lista.html.twig', [
            'zapatillas' => $zapatillas
        ]);
    }

    #[Route('/crear', name: 'app_vendedor_zapatillas_crear', methods: ['GET', 'POST'])]
    public function crear(Request $request): Response
    {
        $usuario = $this->getUser();
        
        // Verificar que el usuario sea vendedor aprobado
        if (!$usuario->getVendedor() || $usuario->getVendedor()->getEstado() !== 'APROBADO') {
            $this->addFlash('error', 'No eres un vendedor aprobado');
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            try {
                // Obtener datos del formulario
                $modelo = $request->request->get('modelo');
                $marca = $request->request->get('marca');
                $talla = $request->request->get('talla');
                $precio = $request->request->get('precio');
                $stock = $request->request->get('stock');
                $categoriaId = $request->request->get('categoria_id');

                // Validaciones básicas
                if (!$modelo || !$marca || !$talla || !$precio || !$stock || !$categoriaId) {
                    $this->addFlash('error', 'Todos los campos son obligatorios');
                    return $this->redirectToRoute('app_vendedor_zapatillas_crear');
                }

                // Obtener categoría
                $categoria = $this->categoriaRepository->find($categoriaId);
                if (!$categoria) {
                    $this->addFlash('error', 'Categoría no válida');
                    return $this->redirectToRoute('app_vendedor_zapatillas_crear');
                }

                // Crear zapatilla
                $zapatilla = new Zapatilla();
                $zapatilla->setModelo($modelo);
                $zapatilla->setMarca($marca);
                $zapatilla->setTalla((string)$talla);
                $zapatilla->setPrecio((string)$precio);
                $zapatilla->setStock((int)$stock);
                $zapatilla->setCategoria($categoria);
                $zapatilla->setVendedor($usuario);

                // Procesar imagen si existe
                $uploadedFile = $request->files->get('imagen');
                if ($uploadedFile) {
                    // Validar que sea una imagen
                    $mimeType = $uploadedFile->getMimeType();
                    if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                        $this->addFlash('error', 'El archivo debe ser una imagen válida (JPG, PNG, GIF, WebP)');
                        return $this->redirectToRoute('app_vendedor_zapatillas_crear');
                    }

                    // Validar tamaño (máximo 5MB)
                    if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
                        $this->addFlash('error', 'La imagen no debe superar 5MB');
                        return $this->redirectToRoute('app_vendedor_zapatillas_crear');
                    }

                    $imagenPath = $this->uploadService->uploadImage($uploadedFile);
                    $zapatilla->setImagen($imagenPath);
                }

                // Guardar en BD
                $this->em->persist($zapatilla);
                $this->em->flush();

                $this->addFlash('success', 'Zapatilla creada exitosamente');
                return $this->redirectToRoute('app_vendedor_zapatillas_listar');

            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al crear la zapatilla: ' . $e->getMessage());
                return $this->redirectToRoute('app_vendedor_zapatillas_crear');
            }
        }

        $categorias = $this->categoriaRepository->findAll();

        return $this->render('vendedor/zapatillas/crear.html.twig', [
            'categorias' => $categorias
        ]);
    }

    #[Route('/{id}/editar', name: 'app_vendedor_zapatillas_editar', methods: ['GET', 'POST'])]
    public function editar(int $id, Request $request): Response
    {
        $usuario = $this->getUser();
        $zapatilla = $this->em->getRepository(Zapatilla::class)->find($id);

        // Verificar que la zapatilla existe y pertenece al vendedor
        if (!$zapatilla || $zapatilla->getVendedor() !== $usuario) {
            throw $this->createNotFoundException('Zapatilla no encontrada');
        }

        if ($request->isMethod('POST')) {
            try {
                $zapatilla->setModelo($request->request->get('modelo'));
                $zapatilla->setMarca($request->request->get('marca'));
                $zapatilla->setTalla($request->request->get('talla'));
                $zapatilla->setPrecio($request->request->get('precio'));
                $zapatilla->setStock((int)$request->request->get('stock'));

                $categoriaId = $request->request->get('categoria_id');
                $categoria = $this->categoriaRepository->find($categoriaId);
                if ($categoria) {
                    $zapatilla->setCategoria($categoria);
                }

                // Procesar nueva imagen si existe
                $uploadedFile = $request->files->get('imagen');
                if ($uploadedFile) {
                    $mimeType = $uploadedFile->getMimeType();
                    if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                        $this->addFlash('error', 'El archivo debe ser una imagen válida');
                        return $this->redirectToRoute('app_vendedor_zapatillas_editar', ['id' => $id]);
                    }

                    if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
                        $this->addFlash('error', 'La imagen no debe superar 5MB');
                        return $this->redirectToRoute('app_vendedor_zapatillas_editar', ['id' => $id]);
                    }

                    // Eliminar imagen anterior si existe
                    if ($zapatilla->getImagen()) {
                        $this->uploadService->deleteImage($zapatilla->getImagen());
                    }

                    $imagenPath = $this->uploadService->uploadImage($uploadedFile);
                    $zapatilla->setImagen($imagenPath);
                }

                $this->em->flush();
                $this->addFlash('success', 'Zapatilla actualizada exitosamente');
                return $this->redirectToRoute('app_vendedor_zapatillas_listar');

            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al actualizar: ' . $e->getMessage());
            }
        }

        $categorias = $this->categoriaRepository->findAll();

        return $this->render('vendedor/zapatillas/editar.html.twig', [
            'zapatilla' => $zapatilla,
            'categorias' => $categorias
        ]);
    }

    #[Route('/{id}/eliminar', name: 'app_vendedor_zapatillas_eliminar', methods: ['POST'])]
    public function eliminar(int $id, Request $request): Response
    {
        $usuario = $this->getUser();
        $zapatilla = $this->em->getRepository(Zapatilla::class)->find($id);

        if (!$zapatilla || $zapatilla->getVendedor() !== $usuario) {
            throw $this->createNotFoundException('Zapatilla no encontrada');
        }

        try {
            // Eliminar imagen si existe
            if ($zapatilla->getImagen()) {
                $this->uploadService->deleteImage($zapatilla->getImagen());
            }

            $this->em->remove($zapatilla);
            $this->em->flush();

            $this->addFlash('success', 'Zapatilla eliminada exitosamente');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al eliminar: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_vendedor_zapatillas_listar');
    }
}
