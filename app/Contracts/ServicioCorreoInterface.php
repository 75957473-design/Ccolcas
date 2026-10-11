<?php

namespace App\Contracts;

interface ServicioCorreoInterface
{
    public function enviarCorreo(string $destinatario, string $mensaje): bool;
}