import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { StrictMode, useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import { initializeTheme } from './hooks/use-appearance';
import PermissionDeniedModal from './components/PermissionDeniedModal';
import { router } from '@inertiajs/react';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Variável global para controlar o modal de permissão
let setPermissionErrorGlobal: ((error: { open: boolean; permission?: string; message?: string }) => void) | null = null;

// Componente wrapper para interceptar erros
function AppWithErrorHandling({ Component, props }: any) {
    console.log('[DEBUG] AppWithErrorHandling: Componente iniciado', Component?.name || 'Unknown');
    
    const [permissionError, setPermissionError] = useState<{
        open: boolean;
        permission?: string;
        message?: string;
    }>({
        open: false,
    });
    
    console.log('[DEBUG] AppWithErrorHandling: useState chamado');

    useEffect(() => {
        console.log('[DEBUG] AppWithErrorHandling: useEffect iniciado');
        // Expor setPermissionError globalmente para o onError do Inertia
        setPermissionErrorGlobal = setPermissionError;

        // Interceptar onError do router do Inertia como fallback
        const originalVisit = router.visit;
        router.visit = function (url: string, options: any = {}) {
            const originalOnError = options.onError;

            return originalVisit.call(this, url, {
                ...options,
                onError: (errors: any) => {
                    // Verificar se é erro 403
                    if (errors?.status === 403 || errors?.permission) {
                        setPermissionError({
                            open: true,
                            permission: errors.permission,
                            message: errors.message || 'Você não tem permissão para acessar esta página.',
                        });
                        return;
                    }

                    if (originalOnError) {
                        originalOnError(errors);
                    }
                },
            });
        };

        return () => {
            setPermissionErrorGlobal = null;
        };
    }, []);

    return (
        <>
            <Component {...props} />
            <PermissionDeniedModal
                open={permissionError.open}
                onOpenChange={(open) =>
                    setPermissionError((prev) => ({ ...prev, open }))
                }
                permission={permissionError.permission}
                message={permissionError.message}
            />
        </>
    );
}

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob('./pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        console.log('[DEBUG] app.tsx setup: Iniciando renderização', App?.name || 'Unknown');
        const root = createRoot(el);

        console.log('[DEBUG] app.tsx setup: createRoot executado');
        root.render(
            <StrictMode>
                <AppWithErrorHandling Component={App} props={props} />
            </StrictMode>,
        );
        console.log('[DEBUG] app.tsx setup: root.render executado');
    },
    progress: {
        color: '#4B5563',
    },
    onError: (error) => {
        if (error.status === 403 && setPermissionErrorGlobal) {
            let permission: string | undefined;
            let message: string | undefined;

            // Tentar extrair informações do erro
            if (error.detail?.errors) {
                const errors = error.detail.errors;
                permission = errors.permission;
                message = errors.message;
            } else if (error.message) {
                try {
                    const parsed = JSON.parse(error.message);
                    permission = parsed.permission;
                    message = parsed.message;
                } catch {
                    message = error.message;
                }
            }

            setPermissionErrorGlobal({
                open: true,
                permission,
                message: message || 'Você não tem permissão para acessar esta página.',
            });
        }
    },
});

// Initialize theme only on client side (after DOM is ready)
if (typeof window !== 'undefined') {
    // Wait for DOM to be ready before initializing theme
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeTheme);
    } else {
        // DOM is already ready
        initializeTheme();
    }
}
