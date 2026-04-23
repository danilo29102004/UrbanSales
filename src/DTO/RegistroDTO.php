<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RegistroDTO
{
    #[Assert\NotBlank(message: 'El nombre es obligatorio')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'El nombre debe tener al menos 3 caracteres')]
    public string $nombre = '';

    #[Assert\NotBlank(message: 'El email es obligatorio')]
    #[Assert\Email(message: 'El email no es válido')]
    public string $email = '';

    #[Assert\NotBlank(message: 'La contraseña es obligatoria')]
    #[Assert\Length(min: 6, minMessage: 'La contraseña debe tener al menos 6 caracteres')]
    public string $password = '';
}
