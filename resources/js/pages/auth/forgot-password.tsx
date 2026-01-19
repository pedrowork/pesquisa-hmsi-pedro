import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useAppearance } from '@/hooks/use-appearance';
import { home, login } from '@/routes';
import { email } from '@/routes/password';
import { Form, Head, Link } from '@inertiajs/react';
import { Lock, Mail, Moon, Sun } from 'lucide-react';

interface ForgotPasswordProps {
    status?: string;
}

export default function ForgotPassword({ status }: ForgotPasswordProps) {
    const { appearance, updateAppearance } = useAppearance();

    const isDark =
        appearance === 'dark' ||
        (appearance === 'system' &&
            typeof window !== 'undefined' &&
            window.matchMedia('(prefers-color-scheme: dark)').matches);

    const toggleTheme = () => {
        updateAppearance(isDark ? 'light' : 'dark');
    };

    return (
        <>
            <Head title="Recuperar Senha" />
            <div className="relative min-h-screen overflow-hidden bg-gradient-to-br from-slate-50 via-teal-50 to-cyan-50 dark:from-slate-950 dark:via-slate-900 dark:to-teal-950">
                {/* Background Effects */}
                <div className="absolute inset-0 overflow-hidden">
                    <div className="absolute -top-40 -right-40 h-80 w-80 animate-pulse rounded-full bg-gradient-to-br from-teal-400/20 to-cyan-600/20 blur-3xl dark:from-teal-500/10 dark:to-cyan-500/10" />
                    <div className="absolute -bottom-40 -left-40 h-80 w-80 animate-pulse rounded-full bg-gradient-to-br from-emerald-400/20 to-teal-600/20 blur-3xl delay-1000 dark:from-emerald-500/10 dark:to-teal-500/10" />
                </div>

                {/* Grid Pattern */}
                <div className="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px] dark:bg-[linear-gradient(to_right,#80808008_1px,transparent_1px),linear-gradient(to_bottom,#80808008_1px,transparent_1px)]" />

                <div className="relative z-10 flex min-h-screen flex-col items-center justify-center p-6 sm:p-8">
                    {/* Header */}
                    <div className="absolute top-6 right-6 left-6 flex items-center justify-between sm:right-8 sm:left-8">
                        <Link
                            href={home()}
                            className="transition-transform hover:scale-105"
                        >
                            <img
                                src="/logomarca.png"
                                alt="Hospital e Maternidade Santa Isabel"
                                className="h-12 w-auto max-w-[200px] object-contain sm:h-14 sm:max-w-[240px]"
                                loading="eager"
                                onError={(e) => {
                                    const target = e.target as HTMLImageElement;
                                    target.style.display = 'none';
                                }}
                            />
                        </Link>
                        <button
                            onClick={toggleTheme}
                            className="group relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/50 bg-white/80 shadow-sm backdrop-blur-sm transition-all hover:bg-white hover:shadow-md dark:border-slate-700/50 dark:bg-slate-800/80 dark:hover:bg-slate-800"
                            aria-label="Alternar tema"
                        >
                            <Sun className="h-5 w-5 text-amber-500 transition-all group-hover:rotate-90 dark:hidden" />
                            <Moon className="hidden h-5 w-5 text-teal-400 transition-all group-hover:-rotate-12 dark:block" />
                        </button>
                    </div>

                    {/* Forgot Password Card */}
                    <div className="w-full max-w-md">
                        <div className="rounded-2xl border border-slate-200/50 bg-white/60 p-8 shadow-xl backdrop-blur-sm dark:border-slate-700/50 dark:bg-slate-800/60">
                            <div className="mb-8 text-center">
                                <div className="mb-4 flex justify-center">
                                    <div className="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-cyan-600 shadow-lg shadow-teal-500/25 dark:shadow-teal-500/10">
                                        <Lock className="h-8 w-8 text-white" />
                                    </div>
                                </div>
                                <h1 className="mb-2 text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    Recuperar Senha
                                </h1>
                                <p className="text-sm text-slate-600 dark:text-slate-400">
                                    Informe seu email para receber um link de
                                    redefinição de senha
                                </p>
                            </div>

                            {status && (
                                <div className="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-center text-sm font-medium text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
                                    {status}
                                </div>
                            )}

                            <Form
                                {...email.form()}
                                className="flex flex-col gap-6"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-6">
                                            <div className="grid gap-2">
                                                <Label
                                                    htmlFor="email"
                                                    className="text-sm font-semibold text-slate-700 dark:text-slate-300"
                                                >
                                                    Email
                                                </Label>
                                                <div className="relative">
                                                    <Mail className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-slate-400" />
                                                    <Input
                                                        id="email"
                                                        type="email"
                                                        name="email"
                                                        required
                                                        autoFocus
                                                        tabIndex={1}
                                                        autoComplete="email"
                                                        placeholder="seu@email.com"
                                                        className="h-11 border-slate-200 bg-white/80 pl-10 backdrop-blur-sm focus:border-teal-500 focus:ring-teal-500/20 dark:border-slate-700 dark:bg-slate-900/80 dark:focus:border-teal-400"
                                                    />
                                                </div>
                                                <InputError
                                                    message={errors.email}
                                                />
                                            </div>

                                            <Button
                                                type="submit"
                                                className="mt-2 h-11 w-full bg-gradient-to-r from-teal-600 to-cyan-600 text-white shadow-lg shadow-teal-500/25 transition-all hover:scale-[1.02] hover:shadow-xl hover:shadow-teal-500/30 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100 dark:shadow-teal-500/10"
                                                tabIndex={2}
                                                disabled={processing}
                                                data-test="email-password-reset-link-button"
                                            >
                                                {processing ? (
                                                    <Spinner className="mr-2" />
                                                ) : null}
                                                {processing
                                                    ? 'Enviando...'
                                                    : 'Enviar Link de Recuperação'}
                                            </Button>
                                        </div>

                                        <div className="text-center text-sm text-slate-600 dark:text-slate-400">
                                            <span>Lembrou sua senha? </span>
                                            <TextLink
                                                href={login()}
                                                className="font-semibold text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300"
                                                tabIndex={3}
                                            >
                                                Voltar ao login
                                            </TextLink>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
