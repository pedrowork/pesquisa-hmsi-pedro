import { dashboard, login, home } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { useAppearance } from '@/hooks/use-appearance';
import { Moon, Sun, ClipboardList, BarChart3, Shield, Zap } from 'lucide-react';

export default function Welcome() {
    console.log('[DEBUG] Welcome: Componente iniciado');

    console.log('[DEBUG] Welcome: Antes de usePage');
    const { auth } = usePage<SharedData>().props;
    console.log('[DEBUG] Welcome: usePage executado');

    console.log('[DEBUG] Welcome: Antes de useAppearance');
    const { appearance, updateAppearance } = useAppearance();
    console.log('[DEBUG] Welcome: useAppearance executado');

    const isDark =
        appearance === 'dark' ||
        (appearance === 'system' &&
            typeof window !== 'undefined' &&
            window.matchMedia('(prefers-color-scheme: dark)').matches);

    const toggleTheme = () => {
        updateAppearance(isDark ? 'light' : 'dark');
    };

    const features = [
        {
            icon: ClipboardList,
            title: 'Questionários Inteligentes',
            description: 'Crie e gerencie pesquisas de satisfação de forma intuitiva e eficiente',
        },
        {
            icon: BarChart3,
            title: 'Análise em Tempo Real',
            description: 'Visualize métricas e insights instantâneos sobre a satisfação dos pacientes',
        },
        {
            icon: Shield,
            title: 'Segurança Total',
            description: 'Seus dados protegidos com criptografia de ponta e conformidade LGPD',
        },
        {
            icon: Zap,
            title: 'Respostas Rápidas',
            description: 'Interface otimizada para coleta rápida e precisa de feedback',
        },
    ];

    return (
        <>
            <Head title="Sistema de Pesquisa de Satisfação">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=inter:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>
            <div className="relative min-h-screen overflow-hidden bg-gradient-to-br from-slate-50 via-teal-50 to-cyan-50 dark:from-slate-950 dark:via-slate-900 dark:to-teal-950">
                {/* Background Effects */}
                <div className="absolute inset-0 overflow-hidden">
                    <div className="absolute -top-40 -right-40 h-80 w-80 rounded-full bg-gradient-to-br from-teal-400/20 to-cyan-600/20 blur-3xl dark:from-teal-500/10 dark:to-cyan-500/10 animate-pulse" />
                    <div className="absolute -bottom-40 -left-40 h-80 w-80 rounded-full bg-gradient-to-br from-emerald-400/20 to-teal-600/20 blur-3xl dark:from-emerald-500/10 dark:to-teal-500/10 animate-pulse delay-1000" />
                    <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-96 w-96 rounded-full bg-gradient-to-br from-teal-400/10 to-cyan-600/10 blur-3xl dark:from-teal-500/5 dark:to-cyan-500/5" />
                </div>

                {/* Grid Pattern */}
                <div className="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px] dark:bg-[linear-gradient(to_right,#80808008_1px,transparent_1px),linear-gradient(to_bottom,#80808008_1px,transparent_1px)]" />

                <div className="relative z-10 flex min-h-screen flex-col">
                    {/* Header */}
                    <header className="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
                        <nav className="flex items-center justify-between">
                            <Link
                                href={home()}
                                className="flex items-center gap-3 transition-transform hover:scale-105"
                            >
                                <img
                                    src="/logomarca.png"
                                    alt="Hospital e Maternidade Santa Isabel"
                                    className="h-16 w-auto max-w-[600px] object-contain sm:h-35 lg:h-24"
                                    loading="eager"
                                    onError={(e) => {
                                        const target = e.target as HTMLImageElement;
                                        target.style.display = 'none';
                                    }}
                                />
                                <span className="text-xl font-bold bg-gradient-to-r from-teal-600 to-cyan-600 bg-clip-text text-transparent dark:from-teal-400 dark:to-cyan-400">
                                    Pesquisa de Satisfação
                                </span>
                            </Link>
                            <div className="flex items-center gap-4">
                                {/* Theme Toggle */}
                                <button
                                    onClick={toggleTheme}
                                        className="group relative flex h-10 w-10 items-center justify-center rounded-xl bg-white/80 backdrop-blur-sm border border-slate-200/50 shadow-sm transition-all hover:bg-white hover:shadow-md dark:bg-slate-800/80 dark:border-slate-700/50 dark:hover:bg-slate-800"
                                        aria-label="Alternar tema"
                                    >
                                        <Sun className="h-5 w-5 text-amber-500 transition-all group-hover:rotate-90 dark:hidden" />
                                        <Moon className="hidden h-5 w-5 text-teal-400 transition-all group-hover:-rotate-12 dark:block" />
                                </button>
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                        className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-teal-500/25 transition-all hover:shadow-xl hover:shadow-teal-500/30 hover:scale-105 dark:shadow-teal-500/10"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <Link
                                href={login()}
                                        className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-teal-500/25 transition-all hover:shadow-xl hover:shadow-teal-500/30 hover:scale-105 dark:shadow-teal-500/10"
                            >
                                        Entrar
                            </Link>
                        )}
                            </div>
                    </nav>
                </header>

                    {/* Main Content */}
                    <main className="container mx-auto flex flex-1 flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
                        <div className="w-full max-w-6xl">
                            {/* Hero Section */}
                            <div className="text-center mb-16">
                                <div className="mb-6 inline-flex items-center gap-2 rounded-full bg-teal-500/10 px-4 py-1.5 text-sm font-medium text-teal-600 dark:bg-teal-500/20 dark:text-teal-400">
                                    <Zap className="h-4 w-4" />
                                    <span>Sistema de Pesquisa de Satisfação</span>
                                </div>
                                <h1 className="mb-6 text-5xl font-bold tracking-tight sm:text-6xl lg:text-7xl">
                                    <span className="bg-gradient-to-r from-slate-900 via-teal-800 to-cyan-800 bg-clip-text text-transparent dark:from-slate-100 dark:via-teal-200 dark:to-cyan-200">
                                        Transforme Feedback
                                    </span>
                                    <br />
                                    <span className="bg-gradient-to-r from-teal-600 via-cyan-600 to-emerald-600 bg-clip-text text-transparent dark:from-teal-400 dark:via-cyan-400 dark:to-emerald-400">
                                        em Melhorias Reais
                                    </span>
                                </h1>
                                <p className="mx-auto mb-8 max-w-2xl text-lg text-slate-600 dark:text-slate-300 sm:text-xl">
                                    Colete, analise e aja sobre o feedback dos pacientes com nossa plataforma
                                    inteligente de pesquisa de satisfação hospitalar.
                                </p>
                                <div className="flex flex-col items-center justify-center gap-4 sm:flex-row">
                                    <Link
                                        href={auth.user ? dashboard() : login()}
                                        className="group relative inline-flex items-center gap-2 overflow-hidden rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 px-8 py-4 text-base font-semibold text-white shadow-lg shadow-teal-500/25 transition-all hover:shadow-xl hover:shadow-teal-500/30 hover:scale-105 dark:shadow-teal-500/10"
                                    >
                                        <span className="relative z-10">Começar Agora</span>
                                        <div className="absolute inset-0 bg-gradient-to-r from-cyan-600 to-emerald-600 opacity-0 transition-opacity group-hover:opacity-100" />
                                    </Link>
                                </div>
                            </div>

                            {/* Features Grid */}
                            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                                {features.map((feature, index) => {
                                    const Icon = feature.icon;
                                    return (
                                        <div
                                            key={index}
                                            className="group relative overflow-hidden rounded-2xl bg-white/60 backdrop-blur-sm border border-slate-200/50 p-6 shadow-sm transition-all hover:bg-white/80 hover:shadow-lg hover:scale-105 dark:bg-slate-800/60 dark:border-slate-700/50 dark:hover:bg-slate-800/80"
                                        >
                                            <div className="absolute inset-0 bg-gradient-to-br from-blue-500/0 via-indigo-500/0 to-purple-500/0 transition-all group-hover:from-blue-500/5 group-hover:via-indigo-500/5 group-hover:to-purple-500/5" />
                                            <div className="relative z-10">
                                                <div className="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-cyan-600 shadow-lg shadow-teal-500/25 dark:shadow-teal-500/10">
                                                    <Icon className="h-6 w-6 text-white" />
                                                </div>
                                                <h3 className="mb-2 text-lg font-semibold text-slate-900 dark:text-slate-100">
                                                    {feature.title}
                                                </h3>
                                                <p className="text-sm text-slate-600 dark:text-slate-400">
                                                    {feature.description}
                                                </p>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>

                            {/* Stats Section */}
                            <div className="mt-16 rounded-2xl bg-gradient-to-br from-teal-600/10 via-cyan-600/10 to-emerald-600/10 backdrop-blur-sm border border-teal-200/20 p-8 dark:from-teal-500/10 dark:via-cyan-500/10 dark:to-emerald-500/10 dark:border-teal-500/20">
                                <div className="grid gap-8 sm:grid-cols-3">
                                    <div className="text-center">
                                        <div className="mb-2 text-4xl font-bold text-teal-600 dark:text-teal-400">
                                            100%
                                        </div>
                                        <div className="text-sm font-medium text-slate-600 dark:text-slate-400">
                                            Seguro e Confiável
                                        </div>
                                    </div>
                                    <div className="text-center">
                                        <div className="mb-2 text-4xl font-bold text-cyan-600 dark:text-cyan-400">
                                            24/7
                                        </div>
                                        <div className="text-sm font-medium text-slate-600 dark:text-slate-400">
                                            Disponível Sempre
                                        </div>
                                    </div>
                                    <div className="text-center">
                                        <div className="mb-2 text-4xl font-bold text-emerald-600 dark:text-emerald-400">
                                            ∞
                                        </div>
                                        <div className="text-sm font-medium text-slate-600 dark:text-slate-400">
                                            Questionários Ilimitados
                                        </div>
                                    </div>
                                </div>
                        </div>
                        </div>
                    </main>

                    {/* Footer */}
                    <footer className="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
                        <div className="text-center text-sm text-slate-500 dark:text-slate-400">
                            <p><a href="https://www.linkedin.com/in/pedrohrsdev/" target="_blank" rel="noopener noreferrer">© {new Date().getFullYear()} Pedro H. R. S. Todos os direitos reservados.</a></p>
                        </div>
                    </footer>
                </div>
            </div>
        </>
    );
}
