<?php

namespace App\Service;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService
{
    public function __construct(
        private UsuarioRepository $usuarioRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function login(string $email, string $password): ?Usuario
    {
        $usuario = $this->usuarioRepository->findOneBy(['email' => $email]);

        if (!$usuario) {
            throw new \Exception('Email o contraseña incorrectos');
        }

        if (!$this->passwordHasher->isPasswordValid($usuario, $password)) {
            throw new \Exception('Email o contraseña incorrectos');
        }

        return $usuario;
    }

    public function generarToken(Usuario $usuario): string
    {
        // Generar un token simple (en producción usar JWT)
        $token = bin2hex(random_bytes(32));
        // Aquí guardarías el token en BD o en cache
        return $token;
    }

    public function verificarToken(string $token): ?Usuario
    {
        // Aquí verificarías el token de la BD o cache
        // Por ahora retorna null
        return null;
    }
}
