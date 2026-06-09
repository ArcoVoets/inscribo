import { Head } from '@inertiajs/react';

import { useDateTimeFormatter } from '@/hooks/use-date-time-formatter';
import { translate } from '@/i18n/translate';
import { useLocaleStrings } from '@/i18n/use-locale-strings';
import { getFormThemeStyle, useIframeHeightSync } from '@/lib/utils';
import { Inertia } from '@/wayfinder/types';

export default function Closed({ event, status, isIframe }: Inertia.Pages.Registration.Closed) {
    const strings = useLocaleStrings().registration.closed;
    const { formatDateTime } = useDateTimeFormatter();

    useIframeHeightSync(isIframe, event.baseUrl);

    const formThemeStyle = getFormThemeStyle(event);

    const statusMessage =
        status === 'upcoming' && event.opensAt
            ? translate(strings.upcoming, { date: formatDateTime(event.opensAt) })
            : strings.closed;

    return (
        <>
            <Head title={strings.title} />

            <div className={"flex flex-col items-center justify-center bg-background p-6 md:p-14" + (isIframe ? '' : ' min-h-svh')} style={formThemeStyle}>
                <div className="w-full max-w-xl text-center">
                    {isIframe || <h1 className="page-hero" style={{ color: 'var(--accent-color-title-and-button)' }}>{event.title}</h1>}

                    <p className="text-2xl font-semibold text-muted-foreground" style={{ color: 'var(--accent-color-title-and-button)' }}>
                        {statusMessage}
                    </p>

                    {event.homeUrl && !isIframe && (
                        <div className="mt-6">
                            <a
                                href={event.homeUrl}
                                className="text-sm text-muted-foreground underline underline-offset-4"
                                style={{ color: 'var(--accent-color-title-and-button)' }}
                                rel="noopener noreferrer"
                            >
                                {strings.home}
                            </a>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
