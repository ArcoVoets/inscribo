import type { LocaleStrings } from '@/i18n/types';

export const en = {
    registration: {
        create: {
            title: 'Registration',
            inviteNotice: 'Pre-registration',
            openNotice: 'Registration is open.',
            home: 'Home',
            capacity: 'Capacity',
            available: 'Available',
            reserved: 'Reserved',
            waitlist: 'Waitlist',
            capacityFullTitle: 'Capacity is full',
            capacityFullDescription:
                'You can still register, but you will be placed on the waitlist.',
            waitlistActiveTitle: 'Waitlist active',
            waitlistActiveDescription:
                'There is currently a waitlist. New registrations will be placed on the waitlist.',
            cannotRegisterTitle: 'Cannot register',
            formUnavailable: 'Registration form is currently unavailable.',
            selectPlaceholder: 'Select…',
            totalPriceLabel: 'Total price',
            submitButton: 'Submit registration',
            submittingButton: 'Submitting…',
            previewModeTitle: 'Preview mode',
            previewModeDescription: 'You are viewing a preview of the registration form. In preview mode, the form cannot be submitted.',
        },
        status: {
            title: 'Registration',
            subtitle: 'Registration status',
            home: 'Home',
            continueToPayment: 'Continue to payment',
            waitlistPosition: 'Your waitlist position: :position',
            messages: {
                waitlisted: {
                    title: 'You are on the waitlist',
                    description: 'You are on the waitlist. We will contact you if a place becomes available.',
                },
                payment_pending: {
                    title: 'Payment pending',
                    description: 'Your registration is pending payment. Please complete the payment before your registration expires. You have until :datetime to pay.',
                },
                registered: {
                    title: 'Registered',
                    description: 'You are successfully registered for :eventName. A confirmation has been sent to your email.',
                },
                cancelled: {
                    title: 'Registration cancelled',
                    description: 'Your registration has been cancelled.',
                },
                refunded: {
                    title: 'Refunded',
                    description: 'Your payment has been refunded.',
                },
                payment_expired: {
                    title: 'Payment expired',
                    description: 'Your registration has expired because the payment was not completed in time. If you still want to attend, please register again.',
                }
            },
        },
        closed: {
            title: 'Registration',
            closed: 'Registration is closed.',
            upcoming: 'Registration opens at :date',
            home: 'Home',
        },
    },
} satisfies LocaleStrings;

