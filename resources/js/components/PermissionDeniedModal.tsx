import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { ShieldX, AlertCircle, Home } from 'lucide-react';
import { router } from '@inertiajs/react';

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
                    <div className="flex items-center gap-3 mb-2">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                            <ShieldX className="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                        <DialogTitle className="text-2xl">
                            Acesso Negado
                        </DialogTitle>
                    </div>
                    <DialogDescription className="text-base pt-2">
                        {message ||
                            'Você não tem permissão para acessar esta página.'}
                    </DialogDescription>
                </DialogHeader>

                <div className="py-4">
                    <div className="flex items-start gap-3 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4">
                        <AlertCircle className="h-5 w-5 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0" />
                        <div className="flex-1">
                            <p className="text-sm font-medium text-amber-900 dark:text-amber-100">
                                Permissão Necessária
                            </p>
                            {permission && (
                                <p className="text-xs text-amber-700 dark:text-amber-300 mt-1 font-mono">
                                    {permission}
                                </p>
                            )}
                            <p className="text-xs text-amber-700 dark:text-amber-300 mt-2">
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

