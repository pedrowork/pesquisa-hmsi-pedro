<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // HSTS (HTTP Strict Transport Security) - apenas em HTTPS
        if ($request->secure()) {
            $maxAge = config('security.hsts_max_age', 31536000); // 1 ano padrão
            $includeSubDomains = config('security.hsts_include_subdomains', true);

            $hstsHeader = "max-age={$maxAge}";
            if ($includeSubDomains) {
                $hstsHeader .= '; includeSubDomains';
            }

            $response->headers->set('Strict-Transport-Security', $hstsHeader);
        }

        // X-Frame-Options (proteção contra clickjacking)
        $xFrameOptions = config('security.x_frame_options', 'DENY');
        $response->headers->set('X-Frame-Options', $xFrameOptions);

        // X-Content-Type-Options (prevenir MIME sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-XSS-Protection (proteção XSS legacy)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy
        $referrerPolicy = config('security.referrer_policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Referrer-Policy', $referrerPolicy);

        // Permissions-Policy (anteriormente Feature-Policy)
        $permissionsPolicy = config('security.permissions_policy', 'geolocation=(), microphone=(), camera=()');
        $response->headers->set('Permissions-Policy', $permissionsPolicy);

        // Content Security Policy (CSP)
        $csp = $this->buildCspHeader();
        if ($csp) {
            $response->headers->set('Content-Security-Policy', $csp);
            // Para navegadores mais antigos
            $response->headers->set('X-Content-Security-Policy', $csp);
        }

        // Remove informações do servidor (segurança por obscuridade)
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }

    /**
     * Constrói o header Content-Security-Policy.
     */
    protected function buildCspHeader(): ?string
    {
        $csp = config('security.csp', null);

        // Se CSP está desabilitado, retornar null
        if ($csp === false) {
            return null;
        }

        // Se CSP é uma string, usar diretamente
        if (is_string($csp)) {
            return $csp;
        }

        // Construir CSP padrão se não especificado
        if ($csp === null) {
            return $this->getDefaultCsp();
        }

        return null;
    }

    /**
     * Retorna CSP padrão seguro.
     */
    protected function getDefaultCsp(): string
    {
        $isDevelopment = app()->environment('local', 'development');
        
        // Detectar desenvolvimento local mesmo com APP_ENV=production
        // Verifica se está rodando em localhost/127.0.0.1 (php artisan serve)
        $request = request();
        $isLocalHost = in_array($request->getHost(), ['localhost', '127.0.0.1', '::1'])
            || $request->getPort() == 8000;

        // Em desenvolvimento, usar CSP mais permissivo para Vite
        if ($isDevelopment || $isLocalHost) {
            // CSP mais permissivo para desenvolvimento com Vite
            // Vite está configurado para usar IPv4 (127.0.0.1), então não precisamos de IPv6
            // Usando wildcards para facilitar desenvolvimento
            return implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:* http://127.0.0.1:*",
                "style-src 'self' 'unsafe-inline' https://fonts.bunny.net http://localhost:* http://127.0.0.1:*",
                "img-src 'self' data: https:",
                "font-src 'self' data: https://fonts.bunny.net",
                "connect-src 'self' http://localhost:* http://127.0.0.1:* ws://localhost:* ws://127.0.0.1:*",
                "frame-ancestors 'none'",
                "base-uri 'self'",
                "form-action 'self'",
            ]);
        }

        // CSP padrão restritivo para produção
        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'", // unsafe-eval e unsafe-inline necessários para Inertia/Vue
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net", // unsafe-inline necessário para estilos inline
            "img-src 'self' data: https:",
            "font-src 'self' data: https://fonts.bunny.net",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "upgrade-insecure-requests",
        ];

        return implode('; ', $directives);
    }
}
