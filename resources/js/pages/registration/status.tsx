import { Head, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';


import { Button } from '@/components/ui/button';
import { useDateTimeFormatter } from '@/hooks/use-date-time-formatter';
import { translate } from '@/i18n/translate';
import { useLocaleStrings } from '@/i18n/use-locale-strings';
import { getFormThemeStyle } from '@/lib/utils';
import { getSendHeight } from '@/lib/utils';
import type { Inertia } from '@/wayfinder/types';


export default function Status({
    event,
    status,
    isIframe,
}: Inertia.Pages.Registration.Status) {
    const strings = useLocaleStrings().registration.status;

    const formThemeStyle = getFormThemeStyle(event);

    const { formatDateTime } = useDateTimeFormatter();

    const [isLoading, setIsLoading] = useState(false);

    const stateMessages = status.state ? strings.messages[status.state] : null;

    const handleCheckoutClick = (e: React.MouseEvent<HTMLAnchorElement>) => {
        setIsLoading(true);

        if (isIframe && event.baseUrl) {
            e.preventDefault();
            window.parent.postMessage(
                { type: 'redirect', url: status.checkoutUrl },
                event.baseUrl,
            );
        }
    };

    useEffect(() => {
        if (isIframe && event.baseUrl) {
            const sendHeight = getSendHeight(event.baseUrl);
            sendHeight();

            window.addEventListener('resize', sendHeight);
            document.addEventListener('inertia:finish', sendHeight);

            return () => {
                window.removeEventListener('resize', sendHeight);
                document.removeEventListener('inertia:finish', sendHeight);
            };
        }
    }, [event.baseUrl, isIframe]);

    // For explanation: see comment in create.tsx
    useEffect(() => {
        if (isIframe && event.baseUrl) {
            router.on('httpException', (exception) => {
                if (exception.detail.response.status === 200) {
                    const url = exception.detail.response.data?.redirectUrl;
                    if (url && event.baseUrl) {
                        window.parent.postMessage(
                            { type: 'redirect', url: url },
                            event.baseUrl,
                        );
                        return false; // do not propagate this error further since we handled it
                    }
                }
            });
        }
    }, [isIframe, event.baseUrl]);


    return (
        <>
            <Head title={strings.title} />

            <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-14" style={formThemeStyle}>
                <div className="w-full max-w-xl">
                    {isIframe || <div className="mb-6">
                        <h1 className="page-hero" style={{ color: 'var(--accent-color-title-and-button)' }}>{event.title}</h1>
                        <p className="page-subtitle">{strings.subtitle}</p>
                    </div>}

                    {status.state && stateMessages && (
                        <>
                            <div className="mb-6">
                                <h2 className="text-2xl font-semibold mb-4" style={{ color: 'var(--accent-color-section-title)' }}>{stateMessages.title}</h2>
                                <div className="space-y-3">
                                    <p className="text-lg text-muted-foreground">
                                        {translate(stateMessages.description, {
                                            datetime: status.expiresAt ? formatDateTime(status.expiresAt) : null,
                                        })}
                                    </p>
                                    {status.waitlistPosition != null && (
                                        <p className="text-lg text-muted-foreground">
                                            {translate(strings.waitlistPosition, {
                                                position: status.waitlistPosition,
                                            })}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div className="mt-6 flex flex-col gap-3">
                                {status.state === 'payment_pending' && status.checkoutUrl && (
                                    <Button
                                        asChild
                                        disabled={isLoading}
                                        className="text-white dark:text-black bg-(--accent-color-title-and-button) hover:bg-(--accent-color-button-hover)"
                                        style={{ width: '230px', height: '40px', borderRadius: '3px', transition: 'all 0.3s ease 0s', transitionProperty: 'all', transitionDuration: '0.3s', transitionTimingFunction: 'ease', transitionDelay: '0s' }}
                                    >
                                        <a 
                                            href={status.checkoutUrl} 
                                            rel="noopener noreferrer"
                                            onClick={handleCheckoutClick}
                                        >
                                            {isLoading && (
                                                <svg className="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            )}
                                            {strings.continueToPayment}
                                        </a>
                                    </Button>
                                )}

                                {event.homeUrl && !isIframe && (
                                    <Button
                                        asChild
                                        variant="outline"
                                        className="border-(--accent-color-title-and-button) text-(--accent-color-title-and-button) hover:bg-(--accent-color-title-and-button) hover:text-white dark:hover:text-black"
                                        style={{ height: '40px', borderRadius: '3px', transition: 'all 0.3s ease 0s', transitionProperty: 'all', transitionDuration: '0.3s', transitionTimingFunction: 'ease', transitionDelay: '0s' }}
                                    >
                                        <a href={event.homeUrl} rel="noopener noreferrer">
                                            {strings.home}
                                        </a>
                                    </Button>
                                )}
                            </div>
                        </>
                    )}
                </div>
            </div>
        </>
    );
}
