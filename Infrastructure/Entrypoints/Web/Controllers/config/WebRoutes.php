<?php

declare(strict_types=1);

namespace App\Infrastructure\Entrypoints\Web\Controllers\Config;

final class WebRoutes
{
    /**
     * @return array<string, array{method: string, action: string}>
     */
    public static function routes(): array
    {
        return [
            'home'              => ['method' => 'GET',  'action' => 'home'],
            'users.create'      => ['method' => 'GET',  'action' => 'create'],
            'users.store'       => ['method' => 'POST', 'action' => 'store'],
            'users.index'       => ['method' => 'GET',  'action' => 'index'],
            'users.show'        => ['method' => 'GET',  'action' => 'show'],
            'users.edit'        => ['method' => 'GET',  'action' => 'edit'],
            'users.update'      => ['method' => 'POST', 'action' => 'update'],
            'users.delete'      => ['method' => 'POST', 'action' => 'delete'],
            'auth.login'        => ['method' => 'GET',  'action' => 'login'],
            'auth.authenticate' => ['method' => 'POST', 'action' => 'authenticate'],
            'auth.logout'       => ['method' => 'GET',  'action' => 'logout'],
            'auth.forgot'       => ['method' => 'GET',  'action' => 'forgot'],
            'auth.forgot.send'  => ['method' => 'POST', 'action' => 'forgot.send'],
        ];
    }
}