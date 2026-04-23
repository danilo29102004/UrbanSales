<?php

namespace App\Service;

use App\Entity\Zapatilla;
use App\Entity\Categoria;
use App\Entity\Usuario;
use App\Repository\ZapatillaRepository;
use Doctrine\ORM\EntityManagerInterface;

class ZapatillaService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ZapatillaRepository $zapatillaRepository
    ) {}

    public function crearZapatilla(
        string $modelo,
        string $marca,
        string $talla,
        string $precio,
        int $stock,
        Categoria $categoria,
        Usuario $vendedor
    ): Zapatilla {
        $zapatilla = new Zapatilla();
        $zapatilla->setModelo($modelo);
        $zapatilla->setMarca($marca);
        $zapatilla->setTalla($talla);
        $zapatilla->setPrecio($precio);
        $zapatilla->setStock($stock);
        $zapatilla->setCategoria($categoria);
        $zapatilla->setVendedor($vendedor);

        $this->entityManager->persist($zapatilla);
        $this->entityManager->flush();

        return $zapatilla;
    }

    public function actualizarStock(Zapatilla $zapatilla, int $cantidad): void
    {
        $nuevoStock = $zapatilla->getStock() - $cantidad;
        
        if ($nuevoStock < 0) {
            throw new \Exception('Stock insuficiente');
        }

        $zapatilla->setStock($nuevoStock);
        $this->entityManager->flush();
    }

    public function obtenerPorCategoria(Categoria $categoria): array
    {
        return $this->zapatillaRepository->findBy(['categoria' => $categoria]);
    }

    public function obtenerPorVendedor(Usuario $vendedor): array
    {
        return $this->zapatillaRepository->findBy(['vendedor' => $vendedor]);
    }

    public function obtenerPorId(int $id): ?Zapatilla
    {
        return $this->zapatillaRepository->find($id);
    }

    public function obtenerTodas(): array
    {
        return $this->zapatillaRepository->findAll();
    }
}
