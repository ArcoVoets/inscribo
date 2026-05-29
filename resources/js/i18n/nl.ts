import type { LocaleStrings } from '@/i18n/types';

export const nl = {
    registration: {
        create: {
            title: 'Inschrijving',
            inviteNotice: 'Voorinschrijving',
            openNotice: 'Inschrijving is open.',
            home: 'Home',
            capacity: 'Capaciteit',
            available: 'Beschikbaar',
            reserved: 'Gereserveerd',
            waitlist: 'Wachtlijst',
            capacityFullTitle: 'Capaciteit vol',
            capacityFullDescription:
                'Je kunt je nog steeds inschrijven, maar je wordt op de wachtlijst geplaatst.',
            waitlistActiveTitle: 'Wachtlijst actief',
            waitlistActiveDescription:
                'Er is momenteel een wachtlijst. Nieuwe inschrijvingen worden op de wachtlijst geplaatst.',
            cannotRegisterTitle: 'Kan niet inschrijven',
            nameLabel: 'Naam',
            namePlaceholder: 'Volledige naam',
            emailLabel: 'E-mailadres',
            emailPlaceholder: 'email@voorbeeld.com',
            cityLabel: 'Plaats',
            cityPlaceholder: 'Plaats',
            participantTypeLabel: 'Deelnemertype',
            participantTypePlaceholder: 'Selecteren…',
            formUnavailable: 'Inschrijfformulier is momenteel niet beschikbaar.',
            selectPlaceholder: 'Selecteren…',
            totalPriceLabel: 'Totale prijs',
            submitButton: 'Inschrijving indienen',
            submittingButton: 'Verzenden…',
            previewModeTitle: 'Preview modus',
            previewModeDescription: 'Dit is een preview van het inschrijfformulier. In deze modus kan het formulier niet worden verzonden.',
        },
        status: {
            title: 'Inschrijving',
            subtitle: 'Inschrijvingsstatus',
            home: 'Home',
            continueToPayment: 'Betalen',
            waitlistPosition: 'Je positie op de wachtlijst: :position',
            messages: {
                waitlisted: {
                    title: 'Je staat op de wachtlijst',
                    description: 'Je staat op de wachtlijst. We zullen contact met je opnemen als er plaats vrijkomt.',
                },
                payment_pending: {
                    title: 'Betaling in afwachting',
                    description: 'Je inschrijving is in afwachting van betaling. Voltooi de betaling voordat je inschrijving vervalt. Je hebt nog tot :datetime om te betalen.',
                },
                registered: {
                    title: 'Ingeschreven',
                    description: 'Je bent succesvol ingeschreven voor dit evenement. Een bevestiging is naar je e-mailadres verzonden.',
                },
                cancelled: {
                    title: 'Inschrijving geannuleerd',
                    description: 'Je inschrijving is geannuleerd.',
                },
                refunded: {
                    title: 'Terugbetaald',
                    description: 'Je betaling is terugbetaald.',
                },
                payment_expired: {
                    title: 'Betaling verlopen',
                    description: 'Je inschrijving is verlopen omdat de betaling niet op tijd is voltooid. Als je nog steeds wilt deelnemen, schrijf je dan opnieuw in.',
                }
            },
        },
        closed: {
            title: 'Inschrijving',
            closed: 'Inschrijving is gesloten.',
            upcoming: 'Inschrijving opent op :date',
            home: 'Home',
        },
    },
} satisfies LocaleStrings;
