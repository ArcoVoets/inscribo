import { InertiaLinkProps } from '@inertiajs/react';
import { type ClassValue, clsx } from 'clsx';
import type { CSSProperties } from 'react';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : String(url);
}

export function getSendHeight(baseUrl: string) {
    return function () {
        window.parent.postMessage(
            { type: 'resize', height: document.documentElement.scrollHeight },
            baseUrl,
        );
    }
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