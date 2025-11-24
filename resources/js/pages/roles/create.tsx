import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, Form } from '@inertiajs/react';
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
import { useState } from 'react';

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
        title: 'Nova Role',
        href: '/roles/create',
    },
];

interface Permission {
    id: number;
    name: string;
    slug: string;
    description: string | null;
}

interface RolesCreateProps {
    permissions: Permission[];
}

export default function RolesCreate({ permissions }: RolesCreateProps) {
    const [selectedPermissions, setSelectedPermissions] = useState<number[]>([]);

    const handlePermissionToggle = (permissionId: number) => {
        setSelectedPermissions((prev) =>
            prev.includes(permissionId)
                ? prev.filter((id) => id !== permissionId)
                : [...prev, permissionId]
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Nova Role" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center gap-4">
                    <Link href="/roles">
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Voltar
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-3xl font-bold">Nova Role</h1>
                        <p className="text-muted-foreground mt-1">
                            Preencha os dados para criar uma nova role (grupo de usuários)
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Dados da Role</CardTitle>
                        <CardDescription>
                            Informe os dados necessários para criar a role
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            method="post"
                            action="/roles"
                            className="space-y-6"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">
                                            Nome <span className="text-red-500">*</span>
                                        </Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            type="text"
                                            required
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
                                                    Nenhuma permissão cadastrada. Crie permissões primeiro.
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
                                                                checked={selectedPermissions.includes(
                                                                    permission.id
                                                                )}
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
                                        {selectedPermissions.map((permissionId) => (
                                            <input
                                                key={permissionId}
                                                type="hidden"
                                                name={`permissions[]`}
                                                value={permissionId}
                                            />
                                        ))}
                                        <InputError message={errors.permissions} />
                                    </div>

                                    <div className="flex items-center gap-4">
                                        <Button type="submit" disabled={processing}>
                                            {processing ? 'Salvando...' : 'Criar Role'}
                                        </Button>
                                        <Link href="/roles">
                                            <Button type="button" variant="outline">
                                                Cancelar
                                            </Button>
                                        </Link>
                                    </div>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
