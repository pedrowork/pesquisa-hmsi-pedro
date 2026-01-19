import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { router } from '@inertiajs/react';
import { AlertCircle, Home, ShieldX } from 'lucide-react';

interface PermissionDeniedModalProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    permission?: string;
    message?: string;
}

export default function PermissionDeniedModal({
    open,
    onOpenChange,
    permission,
    message,
}: PermissionDeniedModalProps) {
    const handleGoHome = () => {
        onOpenChange(false);
        router.visit('/dashboard');
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-[500px]">
                <DialogHeader>
                    <div className="mb-2 flex items-center gap-3">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                            <ShieldX className="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                        <DialogTitle className="text-2xl">
                            Acesso Negado
                        </DialogTitle>
                    </div>
                    <DialogDescription className="pt-2 text-base">
                        {message ||
                            'Você não tem permissão para acessar esta página.'}
                    </DialogDescription>
                </DialogHeader>

                <div className="py-4">
                    <div className="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                        <AlertCircle className="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" />
                        <div className="flex-1">
                            <p className="text-sm font-medium text-amber-900 dark:text-amber-100">
                                Permissão Necessária
                            </p>
                            {permission && (
                                <p className="mt-1 font-mono text-xs text-amber-700 dark:text-amber-300">
                                    {permission}
                                </p>
                            )}
                            <p className="mt-2 text-xs text-amber-700 dark:text-amber-300">
                                Entre em contato com o administrador do sistema
                                para solicitar acesso a esta funcionalidade.
                            </p>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        variant="outline"
                        onClick={() => onOpenChange(false)}
                    >
                        Fechar
                    </Button>
                    <Button onClick={handleGoHome}>
                        <Home className="mr-2 h-4 w-4" />
                        Ir para Dashboard
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
