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
import { useEffect } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Roles',
        href: '/roles',
    },
    {
        title: 'Editar Role',
        href: '/roles/edit',
    },
];

interface Permission {
    id: number;
    name: string;
    slug: string;
    description: string | null;
}

interface Role {
    id: number;
    name: string;
    slug: string;
    description: string | null;
}

interface RolesEditProps {
    role: Role;
    permissions: Permission[];
    rolePermissions: number[];
}

export default function RolesEdit({
    role,
    permissions,
    rolePermissions,
}: RolesEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        name: role.name || '',
        slug: role.slug || '',
        description: role.description || '',
        permissions: rolePermissions || [],
    });

    useEffect(() => {
        if (rolePermissions && rolePermissions.length > 0) {
            setData('permissions', rolePermissions);
        }
    }, [rolePermissions, setData]);

    const handlePermissionToggle = (permissionId: number) => {
        const currentPermissions = data.permissions || [];
        if (currentPermissions.includes(permissionId)) {
            setData(
                'permissions',
                currentPermissions.filter((id) => id !== permissionId)
            );
        } else {
            setData('permissions', [...currentPermissions, permissionId]);
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/roles/${role.id}`, {
            preserveScroll: true,
            onError: (errors) => {
                console.error('Erros ao atualizar role:', errors);
            },
            onSuccess: () => {
                // Redirecionar para a lista de roles
                router.visit('/roles');
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Editar Role" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center gap-4">
                    <Link href="/roles">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Voltar
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold">Editar Role</h1>
                        <p className="text-muted-foreground mt-1">
                            Edite os dados da role {role.name}
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Dados da Role</CardTitle>
                        <CardDescription>
                            Atualize as informações da role
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
                                    onChange={(e) =>
                                        setData('name', e.target.value)
                                    }
                                    placeholder="Ex: Administrador"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="slug">
                                    Slug <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="slug"
                                    name="slug"
                                    type="text"
                                    required
                                    value={data.slug}
                                    onChange={(e) =>
                                        setData('slug', e.target.value)
                                    }
                                    placeholder="Ex: admin"
                                />
                                <p className="text-xs text-muted-foreground">
                                    Identificador único (sem espaços, use hífens ou underscores)
                                </p>
                                <InputError message={errors.slug} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="description">
                                    Descrição
                                </Label>
                                <textarea
                                    id="description"
                                    name="description"
                                    rows={3}
                                    value={data.description}
                                    onChange={(e) =>
                                        setData('description', e.target.value)
                                    }
                                    className="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                    placeholder="Descrição da role..."
                                />
                                <InputError message={errors.description} />
                            </div>

                            <div className="grid gap-2">
                                <Label>Permissões</Label>
                                <div className="rounded-lg border border-input p-4 max-h-64 overflow-y-auto">
                                    {permissions.length === 0 ? (
                                        <p className="text-sm text-muted-foreground">
                                            Nenhuma permissão cadastrada.
                                        </p>
                                    ) : (
                                        <div className="space-y-2">
                                            {permissions.map((permission) => (
                                                <div
                                                    key={permission.id}
                                                    className="flex items-center space-x-2"
                                                >
                                                    <Checkbox
                                                        id={`permission-${permission.id}`}
                                                        checked={(
                                                            data.permissions ||
                                                            []
                                                        ).includes(permission.id)}
                                                        onCheckedChange={() =>
                                                            handlePermissionToggle(
                                                                permission.id
                                                            )
                                                        }
                                                    />
                                                    <label
                                                        htmlFor={`permission-${permission.id}`}
                                                        className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer"
                                                    >
                                                        {permission.name}
                                                        {permission.description && (
                                                            <span className="block text-xs text-muted-foreground">
                                                                {permission.description}
                                                            </span>
                                                        )}
                                                    </label>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>
                                <InputError message={errors.permissions} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing
                                        ? 'Salvando...'
                                        : 'Atualizar Role'}
                                </Button>
                                <Link href="/roles">
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
