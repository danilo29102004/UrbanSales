<?php

namespace App\Service;

use App\Entity\Usuario;
use App\Entity\Vendedor;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsuarioService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private UsuarioRepository $usuarioRepository
    ) {}

    public function registrarUsuario(string $nombre, string $email, string $password): Usuario
    {
        // Validar que no exista
        $existente = $this->usuarioRepository->findOneBy(['email' => $email]);
        if ($existente) {
            throw new \Exception('El email ya está registrado');
        }

        $usuario = new Usuario();
        $usuario->setNombre($nombre);
        $usuario->setEmail($email);
        
        // Hasear contraseña
        $hashedPassword = $this->passwordHasher->hashPassword($usuario, $password);
        $usuario->setPassword($hashedPassword);

        $this->entityManager->persist($usuario);
        $this->entityManager->flush();

        return $usuario;
    }

    public function convertirAVendedor(Usuario $usuario, string $dni, string $documentoRuta): Vendedor
    {
        // Validar que no sea ya vendedor
        if ($usuario->getVendedor()) {
            throw new \Exception('El usuario ya es vendedor');
        }

        $vendedor = new Vendedor();
        $vendedor->setUsuario($usuario);
        $vendedor->setDni($dni);
        $vendedor->setDocumentacion($documentoRuta);
        $vendedor->setEstado('PENDIENTE');
        $vendedor->setFechaSolicitud(new \DateTime());

        $this->entityManager->persist($vendedor);
        $this->entityManager->flush();

        return $vendedor;
    }

    public function obtenerPorEmail(string $email): ?Usuario
    {
        return $this->usuarioRepository->findOneBy(['email' => $email]);
    }

    public function obtenerPorId(int $id): ?Usuario
    {
        return $this->usuarioRepository->find($id);
    }

    public function actualizarPerfil(Usuario $usuario, string $nombre, string $email, ?string $passwordActual = null, ?string $passwordNueva = null): Usuario
    {
        // Si hay contraseña nueva, verificar que la actual sea correcta
        if ($passwordNueva) {
            if (!$this->passwordHasher->isPasswordValid($usuario, $passwordActual)) {
                throw new \Exception('La contraseña actual es incorrecta');
            }
            
            $hashedPassword = $this->passwordHasher->hashPassword($usuario, $passwordNueva);
            $usuario->setPassword($hashedPassword);
        }

        $usuario->setNombre($nombre);
        $usuario->setEmail($email);

        $this->entityManager->flush();

        return $usuario;
    }
}
