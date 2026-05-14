<?php

namespace App\Command;

use App\Entity\Zapatilla;
use App\Entity\DetallePedido;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:delete-seed-data', description: 'Delete seed test data')]
class DeleteSeedDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repo = $this->em->getRepository(Zapatilla::class);
        $zapatillas = $repo->findAll();

        $detalleRepo = $this->em->getRepository(DetallePedido::class);
        $count = 0;

        foreach ($zapatillas as $z) {
            if ($z->getVendedor() && $z->getVendedor()->getEmail() === 'juan.test@example.com') {
                // Primero eliminar los detalles de pedidos que referencian esta zapatilla
                $detalles = $detalleRepo->findBy(['zapatilla' => $z]);
                foreach ($detalles as $detalle) {
                    $this->em->remove($detalle);
                }
                
                $output->writeln("Eliminando: " . $z->getModelo());
                $this->em->remove($z);
                $count++;
            }
        }

        $this->em->flush();
        $output->writeln("✅ Se eliminaron $count zapatillas de prueba");
        return Command::SUCCESS;
    }
}

