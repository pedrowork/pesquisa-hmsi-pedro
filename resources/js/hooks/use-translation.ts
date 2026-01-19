import { usePage } from '@inertiajs/react';
import { useMemo } from 'react';
import en from '../locales/en.json';
import ptBR from '../locales/pt-BR.json';

type TranslationKey = string;
type Translations = typeof ptBR;

const translations = {
    'pt-BR': ptBR as Translations,
    en: en as Translations,
};

export type Locale = keyof typeof translations;

interface SharedData {
    locale?: Locale;
    [key: string]: unknown;
}

export function useTranslation() {
    const page = usePage<SharedData>();
    const locale = (page.props.locale as Locale) || 'pt-BR';

    const t = useMemo(() => {
        const currentTranslations =
            translations[locale] || translations['pt-BR'];

        return (
            key: string,
            params?: Record<string, string | number>,
        ): string => {
            const keys = key.split('.');
            let value: unknown = currentTranslations;

            for (const k of keys) {
                if (typeof value === 'object' && value !== null && k in value) {
                    value = (value as Record<string, unknown>)[k];
                } else {
                    // Fallback para pt-BR se não encontrar
                    let fallbackValue: unknown = translations['pt-BR'];
                    for (const fallbackKey of keys) {
                        if (
                            typeof fallbackValue === 'object' &&
                            fallbackValue !== null &&
                            fallbackKey in fallbackValue
                        ) {
                            fallbackValue = (
                                fallbackValue as Record<string, unknown>
                            )[fallbackKey];
                        } else {
                            return key; // Retorna a chave se não encontrar tradução
                        }
                    }
                    value = fallbackValue;
                }
            }

            if (typeof value === 'string') {
                // Substituir parâmetros {param}
                if (params) {
                    return value.replace(/\{(\w+)\}/g, (match, paramKey) => {
                        return params[paramKey]?.toString() || match;
                    });
                }
                return value;
            }

            return key; // Retorna a chave se não for string
        };
    }, [locale]);

    return { t, locale };
}
