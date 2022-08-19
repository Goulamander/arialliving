<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;
use App\Models\Role;
use Auth;

class ValidateRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $roles)
    {
        $allowed_roles = explode('|', $roles);

        if (auth()->check() && auth()->user()->hasRole($allowed_roles)) {
            return $next($request);
        }

        return redirect('login');
    }
}
