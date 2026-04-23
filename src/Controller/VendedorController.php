<?php

namespace App\Controller;

use App\Service\VendedorService;
use App\Service\ZapatillaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/vendedor')]
class VendedorController extends AbstractController
{
    public function __construct(
        private VendedorService $vendedorService,
        private ZapatillaService $zapatillaService
    ) {}

    #[Route('/solicitudes-pendientes', name: 'app_vendedor_solicitudes_pendientes', methods: ['GET'])]
    public function solicitudesPendientes(): Response
    {
        try {
            $solicitudes = $this->vendedorService->obtenerSolicitudesPendientes();

            $resultado = [];
            foreach ($solicitudes as $vendedor) {
                $resultado[] = [
                    'id' => $vendedor->getId(),
                    'usuario' => $vendedor->getUsuario()->getNombre(),
                    'email' => $vendedor->getUsuario()->getEmail(),
                    'dni' => $vendedor->getDni(),
                    'documento' => $vendedor->getDocumentacion(),
                    'estado' => $vendedor->getEstado(),
                    'fecha_solicitud' => $vendedor->getFechaSolicitud()->format('Y-m-d H:i:s')
                ];
            }

            return $this->json(['solicitudes' => $resultado]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/admin/{id}/aprobar', name: 'app_vendedor_aprobar', methods: ['POST'])]
    public function aprobar(int $id): Response
    {
        try {
            $vendedor = $this->vendedorService->obtenerPorId($id);
            if (!$vendedor) {
                return $this->json(['error' => 'Vendedor no encontrado'], 404);
            }

            $vendedor = $this->vendedorService->aprobarVendedor($vendedor);

            return $this->json([
                'mensaje' => 'Vendedor aprobado exitosamente',
                'vendedor' => [
                    'id' => $vendedor->getId(),
                    'usuario' => $vendedor->getUsuario()->getNombre(),
                    'estado' => $vendedor->getEstado(),
                    'fecha_aprobacion' => $vendedor->getFechaAprobacion()->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/admin/{id}/rechazar', name: 'app_vendedor_rechazar', methods: ['POST'])]
    public function rechazar(int $id): Response
    {
        try {
            $vendedor = $this->vendedorService->obtenerPorId($id);
            if (!$vendedor) {
                return $this->json(['error' => 'Vendedor no encontrado'], 404);
            }

            $vendedor = $this->vendedorService->rechazarVendedor($vendedor);

            return $this->json([
                'mensaje' => 'Vendedor rechazado',
                'vendedor' => [
                    'id' => $vendedor->getId(),
                    'usuario' => $vendedor->getUsuario()->getNombre(),
                    'estado' => $vendedor->getEstado()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/solicitar', name: 'app_vendedor_solicitar_formulario', methods: ['GET'])]
    public function solicitarFormulario(): Response
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            return $this->redirectToRoute('app_auth_login');
        }

        $vendedorExistente = $this->vendedorService->obtenerPorUsuario($usuario);
        
        return $this->render('vendedor/solicitar.html.twig', [
            'vendedor' => $vendedorExistente
        ]);
    }

    #[Route('/solicitar', name: 'app_vendedor_solicitar', methods: ['POST'])]
    public function solicitar(Request $request): Response
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            return $this->json(['error' => 'No autenticado'], 401);
        }

        try {
            $dni = $request->request->get('dni');
            
            // Manejar archivo de documento
            $documento = $request->files->get('documento');
            $documentoRuta = null;
            
            if ($documento) {
                $nombreArchivo = uniqid('vendedor_') . '.' . $documento->guessExtension();
                $documento->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/documentos',
                    $nombreArchivo
                );
                $documentoRuta = '/uploads/documentos/' . $nombreArchivo;
            }

            if (!$dni || !$documentoRuta) {
                return $this->json(['error' => 'DNI y documento son obligatorios'], 400);
            }

            $vendedor = $this->vendedorService->solicitarVendedor($usuario, $dni, $documentoRuta);

            return $this->json([
                'mensaje' => 'Solicitud de vendedor enviada exitosamente',
                'vendedor' => [
                    'id' => $vendedor->getId(),
                    'estado' => $vendedor->getEstado(),
                    'fecha_solicitud' => $vendedor->getFechaSolicitud()->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/panel', name: 'app_vendedor_panel', methods: ['GET'])]
    public function panel(): Response
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            return $this->redirectToRoute('app_auth_login');
        }

        $vendedor = $this->vendedorService->obtenerPorUsuario($usuario);
        
        if (!$vendedor) {
            return $this->redirectToRoute('app_vendedor_solicitar_formulario');
        }

        $zapatillas = $this->zapatillaService->obtenerPorVendedor($usuario);

        return $this->render('vendedor/panel.html.twig', [
            'vendedor' => $vendedor,
            'zapatillas' => $zapatillas
        ]);
    }

    #[Route('/admin/aprobados', name: 'app_vendedores_aprobados', methods: ['GET'])]
    public function obtenerAprobados(): Response
    {
        try {
            $vendedores = $this->vendedorService->obtenerVendedoresAprobados();

            $resultado = [];
            foreach ($vendedores as $vendedor) {
                $resultado[] = [
                    'id' => $vendedor->getId(),
                    'usuario' => $vendedor->getUsuario()->getNombre(),
                    'email' => $vendedor->getUsuario()->getEmail(),
                    'dni' => $vendedor->getDni(),
                    'fecha_aprobacion' => $vendedor->getFechaAprobacion()?->format('Y-m-d H:i:s')
                ];
            }

            return $this->json(['vendedores' => $resultado]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/admin/{id}', name: 'app_vendedor_obtener', methods: ['GET'])]
    public function obtener(int $id): Response
    {
        try {
            $vendedor = $this->vendedorService->obtenerPorId($id);
            if (!$vendedor) {
                return $this->json(['error' => 'Vendedor no encontrado'], 404);
            }

            return $this->json([
                'vendedor' => [
                    'id' => $vendedor->getId(),
                    'usuario' => $vendedor->getUsuario()->getNombre(),
                    'email' => $vendedor->getUsuario()->getEmail(),
                    'dni' => $vendedor->getDni(),
                    'documento' => $vendedor->getDocumentacion(),
                    'estado' => $vendedor->getEstado(),
                    'fecha_solicitud' => $vendedor->getFechaSolicitud()->format('Y-m-d H:i:s'),
                    'fecha_aprobacion' => $vendedor->getFechaAprobacion()?->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
