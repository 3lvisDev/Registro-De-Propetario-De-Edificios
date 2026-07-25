<?php

namespace App\Providers;

use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\App;
use Native\Desktop\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /** @var resource|null */
    private static $instanceLock;

    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        self::$instanceLock = fopen(storage_path('app-instance.lock'), 'c+');

        if (! self::$instanceLock || ! flock(self::$instanceLock, LOCK_EX | LOCK_NB)) {
            App::quit();

            return;
        }

        Window::open()
            ->title('Registro de Propietarios')
            ->width(1280)
            ->height(820)
            ->minWidth(960)
            ->minHeight(640)
            ->center();

        App::focus();
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
            'memory_limit' => '256M',
            'display_errors' => 'Off',
        ];
    }
}
