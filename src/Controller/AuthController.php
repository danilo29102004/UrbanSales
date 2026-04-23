<?php

namespace App\Controller;

use App\DTO\LoginDTO;
use App\DTO\RegistroDTO;
use App\Service\UsuarioService;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private UsuarioService $usuarioService,
        private AuthService $authService,
        private ValidatorInterface $validator
    ) {}

    #[Route('/login', name: 'app_auth_login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        // Solo procesar GET aquí, POST será interceptado por LoginAuthenticator
        if ($request->isMethod('POST')) {
            // Este método no debería llegar aquí si LoginAuthenticator está correctamente configurado
            // Pero lo dejamos como fallback
            return $this->login($request);
        }

        // Obtener errores de autenticación
        $authenticationError = null;
        $lastUsername = '';

        // Intentar obtener del atributo de request
        if ($request->attributes->has('_security.last_error')) {
            $authenticationError = $request->attributes->get('_security.last_error');
        }

        // O de la sesión
        if ($request->hasSession()) {
            $session = $request->getSession();
            if ($session->has('_security.auth_error')) {
                $authenticationError = $session->get('_security.auth_error');
                $session->remove('_security.auth_error');
            }
            if ($session->has('_security.last_username')) {
                $lastUsername = $session->get('_security.last_username');
            }
        }

        $errorMessage = null;
        if ($authenticationError) {
            $errorMessage = $authenticationError->getMessageKey() ?? 'Credenciales inválidas';
        }

        return $this->render('auth/login.html.twig', [
            'error' => $errorMessage,
            'last_email' => $lastUsername
        ]);
    }

    #[Route('/registro', name: 'app_auth_registro', methods: ['GET', 'POST'])]
    public function registro(Request $request): Response
    {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        if ($request->isMethod('GET')) {
            return $this->render('auth/registro.html.twig', [
                'error' => null
            ]);
        }

        try {
            $nombre = $request->request->get('nombre');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $terms = $request->request->get('terms');

            if (!$nombre || !$email || !$password || !$terms) {
                return $this->render('auth/registro.html.twig', [
                    'error' => 'Todos los campos son requeridos'
                ]);
            }

            // Validar datos
            $registroDTO = new RegistroDTO();
            $registroDTO->nombre = $nombre;
            $registroDTO->email = $email;
            $registroDTO->password = $password;

            $errors = $this->validator->validate($registroDTO);
            if (count($errors) > 0) {
                $mensajes = [];
                foreach ($errors as $error) {
                    $mensajes[] = $error->getMessage();
                }
                return $this->render('auth/registro.html.twig', [
                    'error' => implode(', ', $mensajes)
                ]);
            }

            // Registrar
            $usuario = $this->usuarioService->registrarUsuario($nombre, $email, $password);

            // Mostrar página de éxito
            return $this->render('auth/registro_exito.html.twig', [
                'usuario' => $usuario
            ]);

        } catch (\Exception $e) {
            return $this->render('auth/registro.html.twig', [
                'error' => $e->getMessage()
            ]);
        }
    }

    #[Route('/logout', name: 'app_auth_logout', methods: ['GET'])]
    public function logout(): Response
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
