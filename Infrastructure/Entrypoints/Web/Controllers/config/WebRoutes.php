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
            'students.create'   => ['method' => 'GET',  'action' => 'students.create'],
            'students.store'    => ['method' => 'POST', 'action' => 'students.store'],
            'students.index'    => ['method' => 'GET',  'action' => 'students.index'],
            'students.show'     => ['method' => 'GET',  'action' => 'students.show'],
            'students.edit'     => ['method' => 'GET',  'action' => 'students.edit'],
            'students.update'   => ['method' => 'POST', 'action' => 'students.update'],
            'students.delete'   => ['method' => 'POST', 'action' => 'students.delete'],
            'califications.create'   => ['method' => 'GET',  'action' => 'califications.create'],
            'califications.store'    => ['method' => 'POST', 'action' => 'califications.store'],
            'califications.index'    => ['method' => 'GET',  'action' => 'califications.index'],
            'califications.show'     => ['method' => 'GET',  'action' => 'califications.show'],
            'califications.edit'     => ['method' => 'GET',  'action' => 'califications.edit'],
            'califications.update'   => ['method' => 'POST', 'action' => 'califications.update'],
            'califications.delete'   => ['method' => 'POST', 'action' => 'califications.delete'],
            'auth.login'        => ['method' => 'GET',  'action' => 'login'],
            'auth.authenticate' => ['method' => 'POST', 'action' => 'authenticate'],
            'auth.logout'       => ['method' => 'GET',  'action' => 'logout'],
            'auth.forgot'       => ['method' => 'GET',  'action' => 'forgot'],
            'auth.forgot.send'  => ['method' => 'POST', 'action' => 'forgot.send'],
        ];
    }
}