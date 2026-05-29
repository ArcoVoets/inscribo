import type { LocaleStrings } from '@/i18n/types';

import { en } from '@/i18n/en';
import { nl } from '@/i18n/nl';

export const locales = {
    en,
    nl,
} satisfies Record<string, LocaleStrings>;

export type SupportedLocale = keyof typeof locales;

export function getLocaleStrings(locale: string): LocaleStrings {
    return locales[locale as SupportedLocale] ?? locales.en;
}
