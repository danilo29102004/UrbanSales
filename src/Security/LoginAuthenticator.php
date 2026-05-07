<?php

namespace App\Security;

use App\Entity\Usuario;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Doctrine\ORM\EntityManagerInterface;

class LoginAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function supports(Request $request): bool
    {
        return $request->getPathInfo() === '/auth/login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $password = $request->request->get('password', '');

        if (!$email || !$password) {
            throw new AuthenticationException('Email y contraseña requeridos');
        }

        return new Passport(
            new UserBadge($email, function($userIdentifier) {
                $usuario = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $userIdentifier]);
                if (!$usuario) {
                    throw new AuthenticationException('Usuario no encontrado');
                }
                return $usuario;
            }),
            new PasswordCredentials($password)
            // Temporarily disabled CSRF validation for testing
            // [
            //     new CsrfTokenBadge('authenticate', '_csrf_token'),
            // ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('app_mi_perfil'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // Guardar el email en la sesión
        $email = $request->request->get('email', '');
        $request->getSession()->set('_security.last_username', $email);

        // Guardar el error en la sesión
        $request->getSession()->set('_security.auth_error', $exception);

        return new RedirectResponse($this->urlGenerator->generate('app_auth_login'));
    }
}
