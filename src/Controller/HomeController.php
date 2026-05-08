<?php

namespace App\Controller;

use App\Repository\CategoriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        // Redirigir al perfil del usuario
        return $this->redirectToRoute('app_mi_perfil');
    }

    #[Route('/zapatillas', name: 'app_zapatillas_list', methods: ['GET'])]
    public function zapatillas(CategoriaRepository $categoriaRepository): Response
    {
        $categorias = $categoriaRepository->findAll();
        return $this->render('zapatillas/index.html.twig', [
            'categorias' => $categorias
        ]);
    }

    #[Route('/carrito', name: 'app_carrito', methods: ['GET'])]
    public function carrito(): Response
    {
        return $this->render('carrito/index.html.twig');
    }

    #[Route('/checkout', name: 'app_checkout', methods: ['GET'])]
    public function checkout(): Response
    {
        $usuario = $this->getUser();
        
        if (!$usuario) {
            return $this->redirectToRoute('app_auth_login');
        }

        return $this->render('checkout/index.html.twig');
    }

    #[Route('/perfil', name: 'app_perfil', methods: ['GET'])]
    public function perfil(): Response
    {
        $usuario = $this->getUser();
        
        if (!$usuario) {
            return $this->redirectToRoute('app_auth_login');
        }

        return $this->render('usuario/perfil.html.twig', [
            'usuario' => $usuario
        ]);
    }
}
