<?php
require 'vendor/autoload.php';
$kernel = new App\Kernel($_SERVER['APP_ENV'] ?? 'dev', $_SERVER['APP_DEBUG'] ?? false);
$kernel->boot();
$em = $kernel->getContainer()->get('doctrine')->getManager();

$repo = $em->getRepository(App\Entity\Zapatilla::class);
$zapatillas = $repo->findAll();

foreach ($zapatillas as $z) {
    if ($z->getVendedor() && $z->getVendedor()->getUsuario()->getEmail() === 'juan.test@example.com') {
        echo "Eliminando: " . $z->getModelo() . "\n";
        $em->remove($z);
    }
}

$em->flush();
echo "✅ Seed zapatillas eliminadas\n";
