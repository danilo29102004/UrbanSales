<?php

namespace App\Service;

use App\Entity\Vendedor;
use App\Entity\Usuario;
use App\Repository\VendedorRepository;
use Doctrine\ORM\EntityManagerInterface;

class VendedorService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VendedorRepository $vendedorRepository
    ) {}

    public function solicitarVendedor(Usuario $usuario, string $dni, string $documentoRuta): Vendedor
    {
        // Validar que no tenga ya una solicitud pendiente
        $existente = $this->vendedorRepository->findOneBy(['usuario' => $usuario]);
        if ($existente) {
            throw new \Exception('Ya tienes una solicitud de vendedor pendiente');
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

    public function aprobarVendedor(Vendedor $vendedor): Vendedor
    {
        if ($vendedor->getEstado() !== 'PENDIENTE') {
            throw new \Exception('Solo se pueden aprobar solicitudes pendientes');
        }

        $vendedor->setEstado('APROBADO');
        $vendedor->setFechaAprobacion(new \DateTime());
        
        $this->entityManager->flush();
        return $vendedor;
    }

    public function rechazarVendedor(Vendedor $vendedor): Vendedor
    {
        if ($vendedor->getEstado() !== 'PENDIENTE') {
            throw new \Exception('Solo se pueden rechazar solicitudes pendientes');
        }

        $vendedor->setEstado('RECHAZADO');
        $this->entityManager->flush();

        return $vendedor;
    }

    public function obtenerSolicitudesPendientes(): array
    {
        return $this->vendedorRepository->findBy(['estado' => 'PENDIENTE']);
    }

    public function obtenerVendedoresAprobados(): array
    {
        return $this->vendedorRepository->findBy(['estado' => 'APROBADO']);
    }

    public function obtenerPorUsuario(Usuario $usuario): ?Vendedor
    {
        return $this->vendedorRepository->findOneBy(['usuario' => $usuario]);
    }

    public function obtenerPorId(int $id): ?Vendedor
    {
        return $this->vendedorRepository->find($id);
    }
}
