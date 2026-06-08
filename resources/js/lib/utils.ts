import { InertiaLinkProps } from '@inertiajs/react';
import { type ClassValue, clsx } from 'clsx';
import { useEffect, type CSSProperties } from 'react';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : String(url);
}

export function getFormThemeStyle(event: Record<string, any>): CSSProperties {
    const titleAndButtonColor = event.accentColorTitleAndButton ?? 'var(--primary)';
    const requiredAndHoverColor = event.accentColorRequiredAndHover ?? titleAndButtonColor;
    const requiredAsteriskColor = event.accentColorRequiredAndHover ?? '#ff0000';
    const labelAndRadioColor = event.accentColorLabelAndRadio ?? 'var(--foreground)';
    const sectionTitleColor = event.accentColorSectionTitle ?? 'var(--foreground)';

    return {
        ['--accent-color-title-and-button' as string]: titleAndButtonColor,
        ['--accent-color-button-hover' as string]: requiredAndHoverColor,
        ['--accent-color-required-and-hover' as string]: requiredAndHoverColor,
        ['--accent-color-required-asterisk' as string]: requiredAsteriskColor,
        ['--accent-color-label-and-radio' as string]: labelAndRadioColor,
        ['--accent-color-section-title' as string]: sectionTitleColor,
    };
}

export function useIframeHeightSync(isIframe: boolean, baseUrl: string | null) {
    useEffect(() => {
        if (isIframe && baseUrl) {

            let lastHeight = 0;

            const sendHeight = () => {
                requestAnimationFrame(() => {
                    const app = document.getElementById('app');
                    const height =
                        app?.scrollHeight ||
                        document.documentElement.scrollHeight ||
                        600;

                    if (height === lastHeight) {
                        return;
                    }
                    window.parent.postMessage(
                        { type: 'resize', height },
                        baseUrl
                    );

                    lastHeight = height;
                });
            };

            sendHeight();

            const observableElement = document.getElementById('app') || document.documentElement;
            const ro = new ResizeObserver(sendHeight);
            ro.observe(observableElement);

            window.addEventListener('resize', sendHeight);
            document.addEventListener('inertia:finish', sendHeight);

            return () => {
                ro.disconnect();
                window.removeEventListener('resize', sendHeight);
                document.removeEventListener('inertia:finish', sendHeight);
            };
        }
    }, [isIframe, baseUrl]);
}
