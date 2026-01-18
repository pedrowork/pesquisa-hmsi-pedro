<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Forçar HTTPS apenas em produção e quando não estiver usando php artisan serve
        // O php artisan serve não suporta HTTPS, então não força HTTPS em localhost/127.0.0.1
        $isLocalDevelopment = in_array($request->getHost(), ['localhost', '127.0.0.1', '::1']) 
            || app()->environment(['local', 'testing']);
        
        // Verificar se já está via HTTPS (via X-Forwarded-Proto quando atrás de proxy)
        $isSecure = $request->secure() || $request->header('X-Forwarded-Proto') === 'https';
        
        // Se estiver em desenvolvimento local, desabilitar cookies seguros temporariamente
        if ($isLocalDevelopment && !$isSecure) {
            config(['session.secure' => false]);
        }
        
        // Forçar HTTPS apenas se não estiver já seguro e não for desenvolvimento local
        if (app()->environment('production') && !$isSecure && !$isLocalDevelopment) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
