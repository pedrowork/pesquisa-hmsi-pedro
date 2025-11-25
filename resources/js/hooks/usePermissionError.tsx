import { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';

interface PermissionError {
    open: boolean;
    permission?: string;
    message?: string;
}

export function usePermissionError() {
    const [error, setError] = useState<PermissionError>({
        open: false,
    });

    useEffect(() => {
        // Interceptar erros 403 nas requisições do Inertia
        const originalVisit = router.visit;

        router.visit = function (url: string, options: any = {}) {
            const originalOnError = options.onError;

            return originalVisit.call(this, url, {
                ...options,
                onError: (errors: any) => {
                    // Verificar se é erro 403
                    if (errors?.status === 403 || errors?.permission) {
                        setError({
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

        // Interceptar erros de resposta HTTP
        const handleError = (event: any) => {
            if (event.detail?.status === 403) {
                const errorData = event.detail.errors || {};
                setError({
                    open: true,
                    permission: errorData.permission,
                    message: errorData.message || 'Você não tem permissão para acessar esta página.',
                });
            }
        };

        window.addEventListener('inertia:error', handleError);

        return () => {
            window.removeEventListener('inertia:error', handleError);
        };
    }, []);

    return {
        error,
        setError,
    };
}

