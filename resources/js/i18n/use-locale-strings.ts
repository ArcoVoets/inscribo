import { usePage } from '@inertiajs/react';

import { getLocaleStrings } from '@/i18n/locales';
import { Inertia } from '@/wayfinder/types';

export function useLocaleStrings() {
    const { locale } = usePage<Inertia.SharedData>().props;

    return getLocaleStrings(locale);
}
