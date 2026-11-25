<?php
/**
 * inc/sesion.php · Sesión y cookies del Gremio
 * PARTE 3 (UD5) · CE 4.b (sesiones) · CE 4.c (cookies) · CE 4.e.1 (autenticación)
 */

declare(strict_types=1);

const COOKIE_TEMA    = 'gremio_tema';
const COOKIE_USUARIO = 'gremio_usuario';
const TEMAS          = ['claro', 'oscuro'];

/** Arranca la sesión con una cookie de sesión segura. (Dado) */
function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Devuelve el aventurero guardado en la sesión, o null. (Dado) */
function aventureroActual(): ?array
{
    return $_SESSION['aventurero'] ?? null;
}

/**
 * Protege una página: si no hay sesión, redirige a login.php?motivo=privado
 * y TERMINA el script. Si la hay, devuelve el aventurero.
 */
function requiereLogin(): array
{
    $aventurero = aventureroActual();
    if ($aventurero === null) {
        header('Location: login.php?motivo=privado');
        exit; // sin exit el resto de la página seguiría ejecutándose
    }
    return $aventurero;
}

/** Guarda un mensaje para mostrarlo UNA vez en la siguiente página. (Dado) */
function mensajeFlash(string $texto): void
{
    $_SESSION['flash'] = $texto;
}

/** Recupera y borra el mensaje flash. (Dado) */
function leerFlash(): ?string
{
    $texto = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $texto;
}

/**
 * Tema guardado en la cookie COOKIE_TEMA. Si no existe o su valor no está
 * en TEMAS (alguien puede haberla manipulado), devuelve 'claro'.
 */
function temaActual(): string
{
    $valor = $_COOKIE[COOKIE_TEMA] ?? null;
    if (is_string($valor) && in_array($valor, TEMAS, true)) {
        return $valor;
    }
    return 'claro';
}

/** Guarda el tema en la cookie COOKIE_TEMA durante un año. */
function guardarTema(string $tema): void
{
    setcookie(COOKIE_TEMA, $tema, [
        'expires'  => time() + 365 * 24 * 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/** Guarda el NOMBRE DE USUARIO (nunca la contraseña) 30 días en COOKIE_USUARIO. */
function recordarUsuario(string $usuario): void
{
    setcookie(COOKIE_USUARIO, $usuario, [
        'expires'  => time() + 30 * 24 * 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/** Borra la cookie COOKIE_USUARIO (caducidad en el pasado). */
function olvidarUsuario(): void
{
    setcookie(COOKIE_USUARIO, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/**
 * Comprueba las credenciales contra la tabla `aventureros`.
 * Devuelve id, usuario, nombre y clase (SIN el hash) o null si no son correctas.
 */
function autenticar(PDO $pdo, string $usuario, string $clave): ?array
{
    $consulta = $pdo->prepare(
        'SELECT id, usuario, nombre, clase, password_hash
           FROM aventureros
          WHERE usuario = :usuario'
    );
    $consulta->execute(['usuario' => $usuario]);
    $fila = $consulta->fetch();

    if ($fila === false) {
        return null; // el usuario no existe
    }
    if (!password_verify($clave, $fila['password_hash'])) {
        return null; // contraseña incorrecta
    }
    unset($fila['password_hash']); // el hash nunca sale de esta función
    return $fila;
}

/**
 * Cierra la sesión POR COMPLETO: vacía $_SESSION, caduca la cookie de sesión
 * y llama a session_destroy().
 */
function cerrarSesion(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => 'Lax',
        ]);
    }

    session_destroy();
}