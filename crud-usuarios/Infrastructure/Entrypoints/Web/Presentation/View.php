<?php

declare(strict_types=1);

namespace App\crud_usuarios\Infrastructure\Entrypoints\Web\Presentation;

final class View
{
    /** @param array<string, mixed> $data */
    public static function render(string $template, array $data = []): void
    {
        $file = __DIR__ . '/Views/' . $template . '.php';

        if (!file_exists($file)) {
            throw new \RuntimeException(
                sprintf('Vista no encontrada: "%s" en %s', $template, $file)
            );
        }

        extract($data, EXTR_SKIP);
        require $file;
    }

    public static function redirect(string $route): never
    {
        header('Location: ?route=' . urlencode($route));
        exit;
    }
}