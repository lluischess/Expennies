<?php

declare(strict_types=1);

namespace App;

use App\Contracts\SessionInterface;
use App\Exception\SessionException;

class Session implements SessionInterface
{

    public function __construct()
    {
    }

    public function start(): void
    {
        // Si la session ya existe tenemos un problema ya que no es un comportamiento normal
        //if (session_status() === PHP_SESSION_ACTIVE){
        if ($this->isActive()){
            throw new SessionException('Session has already been started');
        }

        // Revisamos que los headers se han enviado y si ese es el caso manejamos el error
        if (headers_sent($filename,$line)){
            throw new SessionException('Headers have already sent by'. $filename . ' line:'. $line);
        }

        // Iniciamos la session, para poder usarlas.
        // Pero antes añadimos algunos parametros de seguridad a la sessión:
        //session_set_cookie_params(['domain' => 'expennies.test', 'path' => '/','secure' => true, 'httponly' => true, 'samesite' => 'lax']);
        //var_dump(session_get_cookie_params());

        if(!session_start()){
            throw new SessionException('Unable to start the session');
        }
    }

    public function save(): void
    {
        session_write_close();
    }

    public function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public function get(string $sessionName, mixed $default = null): mixed
    {
        // Array_key_exists revisa si no existe o si es null por eso es mejor que el isset
        return array_key_exists($sessionName, $_SESSION) ? $_SESSION[$sessionName] : $default;
    }

    public function put(string $sessionName, mixed $getId): void
    {
        $_SESSION[$sessionName] = $getId;
    }

    public function regenerate(): bool
    {
        return session_regenerate_id();
    }

    public function forget(string $sessionName): void
    {
        unset($_SESSION[$sessionName]);
    }

    public function flash(string $sessionkey, array $messages): void
    {
        $_SESSION['flash'][$sessionkey] = $messages;
    }

    public function getFlash(string $sessionkey): array
    {
        $message = $_SESSION['flash'][$sessionkey] ?? [];

        unset($_SESSION['flash'][$sessionkey]);

        return $message;
    }
}