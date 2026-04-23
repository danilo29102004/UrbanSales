<?php

namespace App\Command;

use App\Entity\Categoria;
use App\Entity\Usuario;
use App\Entity\Vendedor;
use App\Entity\Zapatilla;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:seed-data', description: 'Seed test data')]
class SeedDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Crear categorías
        $categorias = [];
        foreach (['Running', 'Basketball', 'Casual', 'Lifestyle'] as $name) {
            $cat = new Categoria();
            $cat->setNombre($name);
            $cat->setSlug(strtolower($name));
            $this->em->persist($cat);
            $categorias[$name] = $cat;
        }
        $this->em->flush();

        // Crear vendedor
        $usuario = $this->em->getRepository(Usuario::class)->findOneBy(['email' => 'juan.test@example.com']);
        if (!$usuario) {
            $usuario = new Usuario();
            $usuario->setEmail('juan.test@example.com');
            $usuario->setNombre('Juan Test');
            $usuario->setPassword($this->passwordHasher->hashPassword($usuario, 'password123'));
            $usuario->setRoles(['ROLE_USER']);
            $this->em->persist($usuario);
            $this->em->flush();
        }

        $vendedor = $this->em->getRepository(Vendedor::class)->findOneBy(['usuario' => $usuario]);
        if (!$vendedor) {
            $vendedor = new Vendedor();
            $vendedor->setUsuario($usuario);
            $vendedor->setEstado('APROBADO');
            $vendedor->setDni('12345678-A');
            $vendedor->setDocumentacion('documento.pdf');
            $vendedor->setFechaSolicitud(new \DateTime());
            $vendedor->setFechaAprobacion(new \DateTime());
            $this->em->persist($vendedor);
            $this->em->flush();
        }

        // Zapatillas de prueba
        $zapatillas = [
            ['Jordan 1 Retro High Virgil Abloh Archive Alaska', 'Air Jordan', 10, 364, 50, 'Casual'],
            ['Jordan 11 Retro Low University Blue (2026)', 'Air Jordan', 10.5, 155, 45, 'Basketball'],
            ['Nike Mind 001 Slide Black Chrome', 'Nike', 12, 159, 30, 'Casual'],
            ['Jordan 4 Retro Iced Carmine (Women\'s)', 'Air Jordan', 8, 199, 40, 'Lifestyle'],
            ['Nike Air Max 2017 en negro antracita', 'Nike', 11, 116, 35, 'Running'],
            ['Nike Air Max 2017 en negro antracita', 'Nike', 10.5, 115, 35, 'Running'],
            ['Louis Vuitton Nike Air Force 1 Low Monogram', 'Louis Vuitton', 9, 79578, 10, 'Lifestyle'],
            ['Nike Dunk Low LTD Wizard', 'Nike', 10.5, 99, 25, 'Basketball'],
            ['Nike Air Force 1 x Travis Scott', 'Nike', 11, 250, 20, 'Casual'],
            ['Air Jordan 1 Low OG SP Travis Scott Reverse Mocha', 'Air Jordan', 10, 450, 15, 'Basketball'],
            ['Nike Blazer Mid Vintage 77', 'Nike', 9, 85, 40, 'Lifestyle'],
            ['Adidas Yeezy Foam RNNR Sand', 'Adidas', 12, 320, 18, 'Casual'],
            ['Puma RS-X Millennium', 'Puma', 9.5, 95, 30, 'Lifestyle'],
            ['Nike SB Dunk Low Pro', 'Nike', 10, 125, 22, 'Casual'],
            ['Air Jordan 13 Retro He Got Game', 'Air Jordan', 10, 185, 35, 'Basketball'],
        ];

        foreach ($zapatillas as [$modelo, $marca, $talla, $precio, $stock, $categoria]) {
            $z = new Zapatilla();
            $z->setModelo($modelo);
            $z->setMarca($marca);
            $z->setTalla((string)$talla);
            $z->setPrecio((string)$precio);
            $z->setStock($stock);
            $z->setCategoria($categorias[$categoria]);
            $z->setVendedor($usuario);
            $this->em->persist($z);
        }

        $this->em->flush();
        $output->writeln('✅ Test data created successfully');
        return Command::SUCCESS;
    }
}
