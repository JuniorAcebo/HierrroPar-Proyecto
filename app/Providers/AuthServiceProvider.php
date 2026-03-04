<?php

namespace App\Providers;

use App\Models\Permiso;
use Illuminate\Support\Facades\Gate;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPolicies();

        $permisos = Permiso::all();

        foreach ($permisos as $permiso) {
            Gate::define($permiso->name, function ($user) use ($permiso) {
                return $user->hasPermission($permiso->name);
            });
        }
    }
}
