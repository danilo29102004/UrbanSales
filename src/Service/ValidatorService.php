<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorService
{
    public function __construct(
        private ValidatorInterface $validator
    ) {}

    public function validar(object $objeto): array
    {
        $errors = $this->validator->validate($objeto);
        $mensajes = [];

        foreach ($errors as $error) {
            $mensajes[$error->getPropertyPath()] = $error->getMessage();
        }

        return $mensajes;
    }

    public function validarEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function validarPassword(string $password): bool
    {
        return strlen($password) >= 6;
    }

    public function validarNombre(string $nombre): bool
    {
        return strlen($nombre) >= 3 && strlen($nombre) <= 255;
    }

    public function validarDni(string $dni): bool
    {
        // DNI debe ser formato: 12345678A o 12345678
        return preg_match('/^[0-9]{8}[A-Z]?$/', $dni) === 1;
    }

    public function validarTelefono(string $telefono): bool
    {
        // Validar teléfono simple
        return preg_match('/^[0-9]{9,15}$/', str_replace([' ', '-', '+'], '', $telefono)) === 1;
    }

    public function validarDireccion(string $direccion): bool
    {
        return strlen($direccion) >= 5 && strlen($direccion) <= 255;
    }
}
