import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';

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
        title: 'Novo Usuário',
        href: '/users/create',
    },
];

interface Role {
    id: number;
    name: string;
    slug: string;
    description: string | null;
}

interface UsersCreateProps {
    roles: Role[];
    isAdmin?: boolean;
}

export default function UsersCreate({ roles, isAdmin = false }: UsersCreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        status: '1',
        roles: [] as number[],
    });

    const handleRoleToggle = (roleId: number) => {
        const currentRoles = data.roles || [];
        if (currentRoles.includes(roleId)) {
            setData('roles', currentRoles.filter((id) => id !== roleId));
        } else {
            setData('roles', [...currentRoles, roleId]);
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/users', {
            preserveScroll: true,
        });
    };
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Novo Usuário" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center gap-4">
                    <Link href="/users">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Voltar
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold">Novo Usuário</h1>
                        <p className="text-muted-foreground mt-1">
                            Preencha os dados para criar um novo usuário
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Dados do Usuário</CardTitle>
                        <CardDescription>
                            Informe os dados necessários para criar o usuário
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name">
                                    Nome <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="name"
                                    name="name"
                                    type="text"
                                    required
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    autoComplete="name"
                                    placeholder="Nome completo"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    Email <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="email"
                                    name="email"
                                    type="email"
                                    required
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    autoComplete="username"
                                    placeholder="email@exemplo.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">
                                    Senha <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    autoComplete="new-password"
                                    placeholder="Mínimo 8 caracteres"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    Confirmar Senha{' '}
                                    <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    required
                                    value={data.password_confirmation}
                                    onChange={(e) =>
                                        setData('password_confirmation', e.target.value)
                                    }
                                    autoComplete="new-password"
                                    placeholder="Digite a senha novamente"
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="status">
                                    Status <span className="text-red-500">*</span>
                                </Label>
                                <select
                                    id="status"
                                    name="status"
                                    required
                                    value={data.status}
                                    onChange={(e) => setData('status', e.target.value)}
                                    className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                                <InputError message={errors.status} />
                            </div>

                            <div className="grid gap-2">
                                <Label>Roles (Grupos)</Label>
                                <p className="text-xs text-muted-foreground">
                                    Selecione as roles que este usuário terá. As permissões das roles serão aplicadas automaticamente.
                                </p>
                                <div className="rounded-lg border border-input p-4 max-h-64 overflow-y-auto">
                                    {roles.length === 0 ? (
                                        <p className="text-sm text-muted-foreground">
                                            Nenhuma role cadastrada. Crie roles primeiro.
                                        </p>
                                    ) : (
                                        <div className="space-y-2">
                                            {roles.map((role) => {
                                                const isAdminRole = role.slug === 'admin';
                                                const canSelectAdmin = isAdmin || !isAdminRole;
                                                
                                                return (
                                                    <div
                                                        key={role.id}
                                                        className={`flex items-center space-x-2 ${!canSelectAdmin ? 'opacity-50' : ''}`}
                                                    >
                                                        <Checkbox
                                                            id={`role-${role.id}`}
                                                            checked={(data.roles || []).includes(role.id)}
                                                            onCheckedChange={() =>
                                                                canSelectAdmin && handleRoleToggle(role.id)
                                                            }
                                                            disabled={!canSelectAdmin}
                                                        />
                                                        <label
                                                            htmlFor={`role-${role.id}`}
                                                            className={`text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 ${canSelectAdmin ? 'cursor-pointer' : 'cursor-not-allowed'}`}
                                                        >
                                                            {role.name}
                                                            {!canSelectAdmin && (
                                                                <span className="block text-xs text-yellow-600 dark:text-yellow-400">
                                                                    Apenas administradores podem criar usuários com este perfil
                                                                </span>
                                                            )}
                                                            {role.description && canSelectAdmin && (
                                                                <span className="block text-xs text-muted-foreground">
                                                                    {role.description}
                                                                </span>
                                                            )}
                                                        </label>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    )}
                                </div>
                                <InputError message={errors.roles} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Salvando...' : 'Criar Usuário'}
                                </Button>
                                <Link href="/users">
                                    <Button type="button" variant="outline">
                                        Cancelar
                                    </Button>
                                </Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
