import type { App } from '@/wayfinder/types';

export interface LocaleStrings {
    registration: {
        create: {
            title: string;
            inviteNotice: string;
            openNotice: string;
            home: string;
            capacity: string;
            available: string;
            reserved: string;
            waitlist: string;
            capacityFullTitle: string;
            capacityFullDescription: string;
            waitlistActiveTitle: string;
            waitlistActiveDescription: string;
            cannotRegisterTitle: string;
            formUnavailable: string;
            selectPlaceholder: string;
            totalPriceLabel: string;
            submitButton: string;
            submittingButton: string;
            previewModeTitle: string;
            previewModeDescription: string;
        };
        status: {
            title: string;
            subtitle: string;
            home: string;
            continueToPayment: string;
            waitlistPosition: string;
            messages: Record<App.Enums.RegistrationStates, { title: string; description: string }>;
        };
        closed: {
            title: string;
            closed: string;
            upcoming: string;
            home: string;
        };
    };
}
