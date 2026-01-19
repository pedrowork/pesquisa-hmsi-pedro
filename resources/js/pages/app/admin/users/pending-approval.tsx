import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, CheckCircle, UserCheck, XCircle } from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Usuários',
        href: '/users',
    },
    {
        title: 'Aprovações Pendentes',
        href: '/admin/users/pending-approval',
    },
];

interface User {
    id: number;
    name: string;
    email: string;
    status: number;
    approval_status: string;
    created_at: string;
    department?: string | null;
    position?: string | null;
}

interface PaginatedUsers {
    data: User[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface PendingApprovalProps {
    users: PaginatedUsers;
}

export default function PendingApproval({ users }: PendingApprovalProps) {
    const [selectedUser, setSelectedUser] = useState<number | null>(null);
    const [action, setAction] = useState<'approve' | 'reject' | null>(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        approval_notes: '',
    });

    const handleApprove = (userId: number) => {
        setSelectedUser(userId);
        setAction('approve');
        reset();
    };

    const handleReject = (userId: number) => {
        setSelectedUser(userId);
        setAction('reject');
        reset();
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedUser || !action) return;

        const url =
            action === 'approve'
                ? `/admin/users/${selectedUser}/approve`
                : `/admin/users/${selectedUser}/reject`;

        post(url, {
            preserveScroll: true,
            onSuccess: () => {
                setSelectedUser(null);
                setAction(null);
                reset();
            },
        });
    };

    const handleCancel = () => {
        setSelectedUser(null);
        setAction(null);
        reset();
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Aprovações Pendentes" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center gap-4">
                    <Link href="/users">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Voltar
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold">
                            Aprovações Pendentes
                        </h1>
                        <p className="mt-1 text-muted-foreground">
                            Aprove ou rejeite usuários aguardando aprovação
                        </p>
                    </div>
                </div>

                {users.total === 0 ? (
                    <Card>
                        <CardContent className="py-12 text-center">
                            <UserCheck className="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                            <p className="text-muted-foreground">
                                Nenhum usuário aguardando aprovação
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <>
                        <Card>
                            <CardHeader>
                                <CardTitle>Usuários Pendentes</CardTitle>
                                <CardDescription>
                                    Total: {users.total} usuário(s) aguardando
                                    aprovação
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {users.data.map((user) => (
                                        <div
                                            key={user.id}
                                            className="flex items-center justify-between rounded-lg border p-4 transition-colors hover:bg-muted/50"
                                        >
                                            <div className="flex-1">
                                                <div className="flex items-center gap-3">
                                                    <div>
                                                        <h3 className="font-semibold">
                                                            {user.name}
                                                        </h3>
                                                        <p className="text-sm text-muted-foreground">
                                                            {user.email}
                                                        </p>
                                                        {user.department && (
                                                            <p className="mt-1 text-xs text-muted-foreground">
                                                                {
                                                                    user.department
                                                                }
                                                                {user.position &&
                                                                    ` • ${user.position}`}
                                                            </p>
                                                        )}
                                                        <p className="mt-1 text-xs text-muted-foreground">
                                                            Criado em:{' '}
                                                            {new Date(
                                                                user.created_at,
                                                            ).toLocaleDateString(
                                                                'pt-BR',
                                                                {
                                                                    day: '2-digit',
                                                                    month: '2-digit',
                                                                    year: 'numeric',
                                                                    hour: '2-digit',
                                                                    minute: '2-digit',
                                                                },
                                                            )}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Button
                                                    variant="default"
                                                    size="sm"
                                                    onClick={() =>
                                                        handleApprove(user.id)
                                                    }
                                                    disabled={
                                                        selectedUser ===
                                                            user.id &&
                                                        action === 'approve'
                                                    }
                                                >
                                                    <CheckCircle className="mr-2 h-4 w-4" />
                                                    Aprovar
                                                </Button>
                                                <Button
                                                    variant="destructive"
                                                    size="sm"
                                                    onClick={() =>
                                                        handleReject(user.id)
                                                    }
                                                    disabled={
                                                        selectedUser ===
                                                            user.id &&
                                                        action === 'reject'
                                                    }
                                                >
                                                    <XCircle className="mr-2 h-4 w-4" />
                                                    Rejeitar
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                {/* Paginação */}
                                {users.last_page > 1 && (
                                    <div className="mt-6 flex items-center justify-between">
                                        <div className="text-sm text-muted-foreground">
                                            Mostrando {users.data.length} de{' '}
                                            {users.total} usuário(s)
                                        </div>
                                        <div className="flex gap-2">
                                            {users.links.map((link, index) => (
                                                <button
                                                    key={index}
                                                    onClick={() => {
                                                        if (link.url) {
                                                            router.get(
                                                                link.url,
                                                            );
                                                        }
                                                    }}
                                                    disabled={
                                                        !link.url || link.active
                                                    }
                                                    className={`rounded-md px-3 py-1 text-sm ${
                                                        link.active
                                                            ? 'bg-primary text-primary-foreground'
                                                            : link.url
                                                              ? 'border bg-background hover:bg-muted'
                                                              : 'cursor-not-allowed bg-muted text-muted-foreground'
                                                    }`}
                                                    dangerouslySetInnerHTML={{
                                                        __html: link.label,
                                                    }}
                                                />
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Modal de Aprovação/Rejeição */}
                        {selectedUser && action && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>
                                        {action === 'approve'
                                            ? 'Aprovar Usuário'
                                            : 'Rejeitar Usuário'}
                                    </CardTitle>
                                    <CardDescription>
                                        {action === 'approve'
                                            ? 'Confirme a aprovação do usuário. Ele receberá a permissão de criar questionários automaticamente.'
                                            : 'Informe o motivo da rejeição. Este campo é obrigatório.'}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <form
                                        onSubmit={handleSubmit}
                                        className="space-y-4"
                                    >
                                        <div className="grid gap-2">
                                            <Label htmlFor="approval_notes">
                                                {action === 'approve'
                                                    ? 'Observações (opcional)'
                                                    : 'Motivo da Rejeição *'}
                                            </Label>
                                            <Textarea
                                                id="approval_notes"
                                                value={data.approval_notes}
                                                onChange={(e) =>
                                                    setData(
                                                        'approval_notes',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder={
                                                    action === 'approve'
                                                        ? 'Adicione observações sobre a aprovação...'
                                                        : 'Informe o motivo da rejeição...'
                                                }
                                                rows={4}
                                                required={action === 'reject'}
                                            />
                                            <InputError
                                                message={errors.approval_notes}
                                            />
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Button
                                                type="submit"
                                                variant={
                                                    action === 'approve'
                                                        ? 'default'
                                                        : 'destructive'
                                                }
                                                disabled={
                                                    processing ||
                                                    (action === 'reject' &&
                                                        !data.approval_notes.trim())
                                                }
                                            >
                                                {processing
                                                    ? 'Processando...'
                                                    : action === 'approve'
                                                      ? 'Confirmar Aprovação'
                                                      : 'Confirmar Rejeição'}
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={handleCancel}
                                                disabled={processing}
                                            >
                                                Cancelar
                                            </Button>
                                        </div>
                                    </form>
                                </CardContent>
                            </Card>
                        )}
                    </>
                )}
            </div>
        </AppLayout>
    );
}
