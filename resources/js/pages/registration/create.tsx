import { Form, Head, router } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useState, type CSSProperties, type ReactNode } from 'react';

import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Textarea } from '@/components/ui/textarea';
import { useLocaleStrings } from '@/i18n/use-locale-strings';
import { getFormThemeStyle } from '@/lib/utils';
import { getSendHeight } from '@/lib/utils';
import { store as registerForEvent } from '@/wayfinder/routes/events/register';
import { Inertia } from '@/wayfinder/types';

type RegistrationField = Inertia.Pages.Registration.Create['form']['sections'][number]['fields'][number];

function moneyEUR(cents: number) {
    return `€ ${(cents / 100).toFixed(2)}`;
}

export default function Create(props: Inertia.Pages.Registration.Create) {
    const { event, form, capacity, invite, isIframe } = props;

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

    // Note: this is a bit of a hack to handle redirects after form submission when the form is 
    // embedded in an iframe, since Inertia doesn't allow hooking into the Inertia::location redirect.
    // The server will therefore respond with a 200 status and a JSON body containing the redirect URL, 
    // and we listen for an HTTP exception (since this is not an Inertia response and thus an httpException 
    // event happens). Using this event we post a message to the parent window to perform the redirect.
    // Ideally we'd listen for this event only on the form submission, but there doesn't seem to exist
    // an hook for that, so instead we listen globally and check if the response is the one we expect.
    useEffect(() => {
        if (isIframe) {
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


    const strings = useLocaleStrings().registration.create;
    const formThemeStyle = getFormThemeStyle(event);

    const [selectedFieldValues, setSelectedFieldValues] = useState<Record<number, string>>({});

    const fieldsById = useMemo(() => {
        const map = new Map<number, RegistrationField>();

        if (form) {
            for (const section of form.sections) {
                for (const field of section.fields) {
                    map.set(field.id, field);
                }
            }
        }

        return map;
    }, [form]);

    const isFieldVisible = useCallback((field: RegistrationField): boolean => {
        if (!field.dependencyFieldId || !field.dependencyOptionId) {
            return true;
        }

        const dependencyField = fieldsById.get(field.dependencyFieldId);

        if (!dependencyField) {
            return true;
        }

        const dependencyValue = selectedFieldValues[dependencyField.id];

        const selectedOption = dependencyField.options.find((option) => option.value === dependencyValue);
        if (!selectedOption) {
            return !field.dependencyEquals;
        }

        const matches = selectedOption.id === field.dependencyOptionId;
        return field.dependencyEquals === matches;
    }, [fieldsById, selectedFieldValues]);

    const totalPriceCents = useMemo(() => {
        if (!form) {
            return 0;
        }

        let total = form.basePriceCents;

        for (const section of form.sections) {
            for (const field of section.fields) {
                if (!isFieldVisible(field)) {
                    continue;
                }

                if (field.type !== 'radio' && field.type !== 'select') {
                    continue;
                }

                const selectedValue = selectedFieldValues[field.id];
                if (!selectedValue) {
                    continue;
                }

                const selectedOption = field.options.find((option) => option.value === selectedValue);
                if (!selectedOption) {
                    continue;
                }

                total += selectedOption.priceCents;
            }
        }

        return total;
    }, [form, isFieldVisible, selectedFieldValues]);

    const showCapacity = capacity !== null;
    const showCapacityFullWarning = showCapacity && capacity.isCapacityFull;
    const showWaitlistWarning = showCapacity && !capacity.waitlistIsEmpty;

    const handleFieldValueChange = (fieldId: number, value: string) => {
        setSelectedFieldValues((current) => ({
            ...current,
            [fieldId]: value,
        }));
    };

    return (
        <>
            {props.isPreview && (
                <Alert className="mb-6 rounded-none fixed top-0 bg-gray-50" variant="destructive">
                    <AlertTitle>{strings.previewModeTitle}</AlertTitle>
                    <AlertDescription>{strings.previewModeDescription}</AlertDescription>
                </Alert>
            )}
            <Head title={strings.title} />

            <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-14" style={formThemeStyle}>
                <div className="w-full max-w-303">
                    {isIframe || <div className="mb-6">
                        <h1 className="page-hero" style={{ color: 'var(--accent-color-title-and-button)' }}>{form.title}</h1>
                        <p className="page-subtitle">{invite ? strings.inviteNotice : strings.openNotice}</p>
                    </div>}
                    {event.homeUrl && !isIframe && (
                        <div className="mb-4 text-center">
                            <a className="text-sm text-muted-foreground underline underline-offset-4" href={event.homeUrl} rel="noopener noreferrer">
                                {strings.home}
                            </a>
                        </div>
                    )}

                    {showCapacity && (
                        <div className="mb-6 rounded-none border p-4 text-sm">
                            <div className="flex flex-wrap gap-x-6 gap-y-2">
                                <div>
                                    <span className="text-muted-foreground">{strings.capacity}</span> <span className="font-medium">{event.capacity}</span>
                                </div>
                                <div>
                                    <span className="text-muted-foreground">{strings.available}</span> <span className="font-medium">{capacity.availableCapacity}</span>
                                </div>
                                <div>
                                    <span className="text-muted-foreground">{strings.reserved}</span> <span className="font-medium">{capacity.reservedCount}</span>
                                </div>
                                <div>
                                    <span className="text-muted-foreground">{strings.waitlist}</span> <span className="font-medium">{capacity.waitlistCount}</span>
                                </div>
                            </div>
                        </div>
                    )}

                    {showCapacityFullWarning && (
                        <Alert className="mb-6 rounded-none" variant="destructive">
                            <AlertTitle>{strings.capacityFullTitle}</AlertTitle>
                            <AlertDescription>{strings.capacityFullDescription}</AlertDescription>
                        </Alert>
                    )}

                    {showWaitlistWarning && !showCapacityFullWarning && (
                        <Alert className="mb-6 rounded-none">
                            <AlertTitle>{strings.waitlistActiveTitle}</AlertTitle>
                            <AlertDescription>{strings.waitlistActiveDescription}</AlertDescription>
                        </Alert>
                    )}

                    {form?.description && (
                        <div className="mb-6 rounded-none border bg-muted/30 p-4 text-sm text-muted-foreground">
                            {form.description}
                        </div>
                    )}

                    {!form && (
                        <Alert className="mb-6 rounded-none" variant="destructive">
                            <AlertDescription>{strings.formUnavailable}</AlertDescription>
                        </Alert>
                    )}

                    <Form
                        {...registerForEvent.form(event.id)}
                        disableWhileProcessing
                        className="grid gap-6"
                    >
                        {({ processing, errors }: { processing: boolean; errors: any }) => (
                            <>
                                {invite && (
                                    <>
                                        <input type="hidden" name="invite_id" value={invite.id} />
                                        <input type="hidden" name="invite_token" value={invite.token ?? ''} />
                                    </>
                                )}

                                {isIframe && <input type="hidden" name="iframe" value="1" />}

                                {errors.event && (
                                    <Alert className="mb-2 rounded-none" variant="destructive">
                                        <AlertTitle>{strings.cannotRegisterTitle}</AlertTitle>
                                        <AlertDescription>{errors.event}</AlertDescription>
                                    </Alert>
                                )}

                                {form?.sections.map((section) => (
                                    <Card key={section.id} className="gap-4 rounded-none border-0 py-2 shadow-none">
                                        <CardHeader className="px-0 pb-2">
                                            <CardTitle className="section-title pb-10 mb-0 mt-2" style={{ color: 'var(--accent-color-section-title)' }}>{section.title}</CardTitle>
                                        </CardHeader>

                                        <CardContent className="px-0">
                                            <div className="flex flex-col gap-4 sm:-mx-2 sm:flex-row sm:flex-wrap sm:gap-x-0 sm:gap-y-6">
                                                {section.fields.map((field) => {
                                                    if (!isFieldVisible(field)) {
                                                        return null;
                                                    }

                                                    const fieldError = errors[`fields.${field.id}`] ?? errors[`${field.id}`];
                                                    const fieldName = `fields[${field.id}]`;
                                                    const normalizedWidth = Math.min(100, Math.max(1, field.width || 100));
                                                    const fieldWidthStyle: CSSProperties = {
                                                        ['--field-width' as string]: `${normalizedWidth}%`,
                                                    };

                                                    let control: ReactNode;

                                                    switch (field.type) {
                                                        case 'long_text':
                                                            control = (
                                                                <Textarea
                                                                    id={`field-${field.id}`}
                                                                    name={fieldName}
                                                                    required={field.required}
                                                                    placeholder={field.placeholder ?? undefined}
                                                                    className="min-h-28 rounded-none bg-[#f4f6f7]"
                                                                />
                                                            );
                                                            break;

                                                        case 'select':
                                                            control = (
                                                                <NativeSelect
                                                                    id={`field-${field.id}`}
                                                                    name={fieldName}
                                                                    defaultValue={field.defaultOptionValue ?? undefined}
                                                                    onChange={(e) => handleFieldValueChange(field.id, e.target.value)}
                                                                    required={field.required}
                                                                    className="h-10.75 rounded-none bg-[#f4f6f7]"
                                                                >
                                                                    <NativeSelectOption value="">{strings.selectPlaceholder}</NativeSelectOption>
                                                                    {field.options.map((option) => (
                                                                        <NativeSelectOption key={option.value} value={option.value}>
                                                                            {option.label}
                                                                            {!field.hideOptionPrice && option.priceCents > 0 && ` (${moneyEUR(option.priceCents)})`}
                                                                        </NativeSelectOption>
                                                                    ))}
                                                                </NativeSelect>
                                                            );
                                                            break;

                                                        case 'radio':
                                                            control = (
                                                                <>
                                                                    <input
                                                                        type="hidden"
                                                                        name={fieldName}
                                                                        value={selectedFieldValues[field.id] ?? ''}
                                                                    />
                                                                    <RadioGroup
                                                                        name={fieldName}
                                                                        onValueChange={(value: string) => handleFieldValueChange(field.id, value)}
                                                                    >
                                                                        {field.options.map((option) => (
                                                                            <div key={option.value} className="flex items-center gap-1">
                                                                                <RadioGroupItem value={option.value} id={`field-${field.id}-${option.value}`} className="border-[var(--accent-color-label-and-radio)] text-[var(--accent-color-label-and-radio)]" />
                                                                                <Label htmlFor={`field-${field.id}-${option.value}`} className="font-normal mb-0" style={{ color: 'var(--accent-color-label-and-radio)' }}>
                                                                                    {option.label}
                                                                                    {!field.hideOptionPrice && option.priceCents > 0 && ` (${moneyEUR(option.priceCents)})`}
                                                                                </Label>
                                                                            </div>
                                                                        ))}
                                                                    </RadioGroup>
                                                                </>
                                                            );
                                                            break;

                                                        default:
                                                            control = (
                                                                <Input
                                                                    id={`field-${field.id}`}
                                                                    type={field.type === 'date' ? 'date' : field.type}
                                                                    name={fieldName}
                                                                    required={field.required}
                                                                    placeholder={field.placeholder ?? undefined}
                                                                    className="h-10.75 rounded-none bg-[#f4f6f7] dark:bg-[#0a0a0a]"
                                                                />
                                                            );
                                                            break;
                                                    }

                                                    return (
                                                        <div key={field.id} className="field-wrapper flex flex-col gap-2 sm:px-2" style={fieldWidthStyle}>
                                                            {field.type === 'radio' ? (
                                                                <Label style={{ color: 'var(--accent-color-label-and-radio)' }} className="font-semibold">
                                                                    {field.label}
                                                                    {field.required && <span style={{ color: 'var(--accent-color-required-asterisk)' }}> *</span>}
                                                                </Label>
                                                            ) : (
                                                                <Label htmlFor={`field-${field.id}`} style={{ color: 'var(--accent-color-label-and-radio)' }} className="font-semibold">
                                                                    {field.label}
                                                                    {field.required && <span style={{ color: 'var(--accent-color-required-asterisk)' }}> *</span>}
                                                                </Label>
                                                            )}
                                                            {control}
                                                            <InputError message={fieldError} />
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}

                                {form && (
                                    <div className="rounded-none border p-4 text-sm">
                                        <div className="font-medium">{strings.totalPriceLabel}</div>
                                        <div className="text-lg">{moneyEUR(totalPriceCents)}</div>
                                    </div>
                                )}

                                <Button
                                    type="submit"
                                    className="text-white dark:text-black bg-(--accent-color-title-and-button) hover:bg-(--accent-color-button-hover)"
                                    style={{ width: '230px', height: '40px', borderRadius: '3px', transition: 'all 0.3s ease 0s', transitionProperty: 'all', transitionDuration: '0.3s', transitionTimingFunction: 'ease', transitionDelay: '0s' }}
                                    disabled={processing || !form || props.isPreview}
                                >
                                    {processing ? strings.submittingButton : strings.submitButton}
                                </Button>
                            </>
                        )}
                    </Form>
                </div>
            </div>
        </>
    );
}
