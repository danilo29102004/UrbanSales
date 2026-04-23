<?php

namespace App\Controller;

use App\Service\UsuarioService;
use App\Service\VendedorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('')]
class UsuarioController extends AbstractController
{
    public function __construct(
        private UsuarioService $usuarioService,
        private VendedorService $vendedorService
    ) {}

    #[Route('/registro', name: 'app_registro', methods: ['POST'])]
    public function registro(Request $request): Response
    {
        try {
            $nombre = $request->get('nombre');
            $email = $request->get('email');
            $password = $request->get('password');

            if (!$nombre || !$email || !$password) {
                return $this->json(['error' => 'Faltan campos obligatorios'], 400);
            }

            // Validar formato email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->json(['error' => 'El email no es válido'], 400);
            }

            // Validar longitud password
            if (strlen($password) < 6) {
                return $this->json(['error' => 'La contraseña debe tener al menos 6 caracteres'], 400);
            }

            $usuario = $this->usuarioService->registrarUsuario($nombre, $email, $password);

            return $this->json([
                'mensaje' => 'Usuario registrado exitosamente',
                'usuario' => [
                    'id' => $usuario->getId(),
                    'nombre' => $usuario->getNombre(),
                    'email' => $usuario->getEmail()
                ]
            ], 201);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/convertir-vendedor', name: 'app_convertir_vendedor', methods: ['POST'])]
    public function convertirVendedor(Request $request): Response
    {
        try {
            $usuarioId = $request->get('usuario_id');
            $dni = $request->get('dni');
            $documento = $request->get('documento'); // Ruta del archivo

            if (!$usuarioId || !$dni || !$documento) {
                return $this->json(['error' => 'Faltan campos obligatorios'], 400);
            }

            $usuario = $this->usuarioService->obtenerPorId($usuarioId);
            if (!$usuario) {
                return $this->json(['error' => 'Usuario no encontrado'], 404);
            }

            $vendedor = $this->usuarioService->convertirAVendedor($usuario, $dni, $documento);

            return $this->json([
                'mensaje' => 'Solicitud de vendedor creada',
                'vendedor' => [
                    'id' => $vendedor->getId(),
                    'estado' => $vendedor->getEstado(),
                    'fecha_solicitud' => $vendedor->getFechaSolicitud()->format('Y-m-d')
                ]
            ], 201);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/usuario/{id}', name: 'app_usuario_obtener', methods: ['GET'])]
    public function obtenerUsuario(int $id): Response
    {
        try {
            $usuario = $this->usuarioService->obtenerPorId($id);
            if (!$usuario) {
                return $this->json(['error' => 'Usuario no encontrado'], 404);
            }

            $vendedor = $this->vendedorService->obtenerPorUsuario($usuario);

            return $this->json([
                'usuario' => [
                    'id' => $usuario->getId(),
                    'nombre' => $usuario->getNombre(),
                    'email' => $usuario->getEmail(),
                    'es_vendedor' => $vendedor ? true : false,
                    'estado_vendedor' => $vendedor ? $vendedor->getEstado() : null
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/mi-perfil', name: 'app_mi_perfil', methods: ['GET'])]
    public function miPerfil(): Response
    {
        $usuario = $this->getUser();
        
        if (!$usuario) {
            return $this->redirectToRoute('app_auth_login');
        }

        $vendedor = $this->vendedorService->obtenerPorUsuario($usuario);

        return $this->render('usuario/perfil.html.twig', [
            'usuario' => $usuario,
            'vendedor' => $vendedor
        ]);
    }

    #[Route('/mi-perfil/editar', name: 'app_mi_perfil_editar', methods: ['GET'])]
    public function miPerfilEditar(): Response
    {
        $usuario = $this->getUser();
        
        if (!$usuario) {
            return $this->redirectToRoute('app_auth_login');
        }

        return $this->render('usuario/editar_perfil.html.twig', [
            'usuario' => $usuario
        ]);
    }

    #[Route('/mi-perfil/actualizar', name: 'app_mi_perfil_actualizar', methods: ['POST'])]
    public function actualizarPerfil(Request $request): Response
    {
        $usuario = $this->getUser();
        
        if (!$usuario) {
            return $this->json(['error' => 'No autenticado'], 401);
        }

        try {
            $nombre = $request->request->get('nombre');
            $email = $request->request->get('email');
            $passwordActual = $request->request->get('password_actual');
            $passwordNueva = $request->request->get('password_nueva');

            // Si intenta cambiar email, validar que sea único
            if ($email !== $usuario->getEmail()) {
                $existente = $this->usuarioService->obtenerPorEmail($email);
                if ($existente) {
                    return $this->json(['error' => 'El email ya está registrado'], 400);
                }
            }

            // Si intenta cambiar contraseña, validar contraseña actual
            if ($passwordNueva) {
                if (!$passwordActual) {
                    return $this->json(['error' => 'Debes ingresar tu contraseña actual'], 400);
                }

                if (strlen($passwordNueva) < 6) {
                    return $this->json(['error' => 'La nueva contraseña debe tener al menos 6 caracteres'], 400);
                }

                $this->usuarioService->actualizarPerfil($usuario, $nombre, $email, $passwordActual, $passwordNueva);
            } else {
                $this->usuarioService->actualizarPerfil($usuario, $nombre, $email);
            }

            return $this->json([
                'mensaje' => 'Perfil actualizado exitosamente',
                'usuario' => [
                    'nombre' => $usuario->getNombre(),
                    'email' => $usuario->getEmail()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}

