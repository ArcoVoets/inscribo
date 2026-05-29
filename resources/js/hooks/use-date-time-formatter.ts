import { usePage } from '@inertiajs/react';
import { useMemo } from 'react';

import { Inertia } from '@/wayfinder/types';

export function useDateTimeFormatter() {
    const { locale, displayTimezone } = usePage<Inertia.SharedData>().props;

    const formatter = useMemo(
        () => {
            return new Intl.DateTimeFormat(locale, {
                dateStyle: 'medium',
                timeStyle: 'short',
                timeZone: displayTimezone,
            });
        },
        [locale, displayTimezone],
    );

    return {
        formatDateTime(iso: string): string {
            const date = new Date(iso);

            if (Number.isNaN(date.getTime())) {
                return iso;
            }

            return formatter.format(date);
        },
    };
}