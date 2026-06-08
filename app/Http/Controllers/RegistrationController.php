<?php

namespace App\Http\Controllers;

use App\Actions\SyncMolliePaymentStatus;
use App\Enums\FormFieldType;
use App\Enums\RegistrationStates;
use App\Http\Requests\RegistrationStoreRequest;
use App\Models\Event;
use App\Models\FormField;
use App\Models\FormFieldOption;
use App\Models\FormSection;
use App\Models\Invite;
use App\Models\Registration;
use App\Models\RegistrationStates\PaymentPendingState;
use App\Models\RegistrationStates\WaitlistedState;
use App\Notifications\RegistrationSubmittedPaymentPendingNotification;
use App\Notifications\WaitlistedNotification;
use App\Services\MollieService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Mollie\Api\Http\Data\Money;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\PaymentMethod;
use Mollie\Laravel\Facades\Mollie;
use Throwable;

class RegistrationController extends Controller
{
    public function create(Event $event, Request $request): Response
    {
        // Invites may bypass opens_at, but never closes_at.
        if ($event->closes_at?->isNowOrPast()) {
            return Inertia::render('registration/closed', [
                'status' => 'closed',
                'event' => $this->mapEventForFrontend($event),
                'isIframe' => $request->has('iframe'),
            ]);
        }

        $invite = null;
        $inviteProps = null;

        if ($event->opens_at->isFuture()) {
            $invite = Invite::findValidForEvent(
                $event,
                $request->query('invite_id'),
                $request->query('invite_token'),
            );

            if (! $invite) {
                return Inertia::render('registration/closed', [
                    'status' => 'upcoming',
                    'event' => array_merge($this->mapEventForFrontend($event), [
                        // TODO: (string) is currently needed for wayfinder type inference; remove when fixed
                        'opensAt' => (string) $event->opens_at->toIso8601String(),
                    ]),
                    'isIframe' => $request->has('iframe'),
                ]);
            }

            $inviteProps = [
                'id' => $invite->id,
                'token' => (string) $request->query('invite_token'),
            ];
        }

        $capacityProps = null;
        if ($event->show_capacity_data) {
            $capacityProps = $event->capacitySnapshot();
        }

        return Inertia::render('registration/create', [
            'event' => $this->mapEventForFrontend($event),
            'form' => $this->mapFormForFrontend($event),
            'invite' => $inviteProps,
            'capacity' => $capacityProps,
            'isPreview' => false,
            'isIframe' => $request->has('iframe'),
        ]);
    }

    public function preview(Event $event, Request $request): Response
    {
        Gate::authorize('preview', $event);

        return Inertia::render('registration/create', [
            'event' => $this->mapEventForFrontend($event),
            'form' => $this->mapFormForFrontend($event),
            'capacity' => $event->show_capacity_data ? $event->capacitySnapshot() : null,
            'isPreview' => true,
            'isIframe' => $request->has('iframe'),
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     title: string,
     *     capacity: int,
     *     homeUrl: string|null,
     *     baseUrl: string|null,
     *     accentColorTitleAndButton: string|null,
     *     accentColorRequiredAndHover: string|null,
     *     accentColorLabelAndRadio: string|null,
     *     accentColorSectionTitle: string|null,
     * }
     */
    private function mapEventForFrontend(Event $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'capacity' => $event->capacity,
            'baseUrl' => $event->wordpressFormBaseUrl(),
            'homeUrl' => $event->home_url,
            'accentColorTitleAndButton' => $event->accent_color_title_and_button,
            'accentColorRequiredAndHover' => $event->accent_color_required_and_hover,
            'accentColorLabelAndRadio' => $event->accent_color_label_and_radio,
            'accentColorSectionTitle' => $event->accent_color_section_title,
        ];
    }

    /**
     * @return array{
     *     id: int,
     *     title: string,
     *     description: string|null,
     *     basePriceCents: int,
     *     sections: array<int, array{
     *         id: int,
     *         title: string,
     *         fields: array<int, array{
     *             id: int,
     *             label: string,
     *             placeholder: string|null,
     *             type: string,
     *             width: int,
     *             required: bool,
     *             defaultOptionValue: string|null,
     *             hideOptionPrice: bool,
     *             dependencyFieldId: int|null,
     *             dependencyOptionId: int|null,
     *             dependencyEquals: bool,
     *             options: array<int, array{
     *                 id: int,
     *                 label: string,
     *                 value: string,
     *                 priceCents: int
     *             }>
     *         }>
     *     }>
     * }|null
     */
    private function mapFormForFrontend(Event $event): ?array
    {
        $form = $event->form;

        if (! $form) {
            return null;
        }

        return [
            'id' => $form->id,
            'title' => $form->title,
            'description' => str($form->description)->sanitizeHtml()->toString(),
            'basePriceCents' => $form->base_price_cents,
            'sections' => $form->sections->map(fn (FormSection $section): array => [
                'id' => $section->id,
                'title' => $section->title,
                'fields' => $section->fields->map(fn (FormField $field): array => [
                    'id' => $field->id,
                    'label' => $field->label,
                    'placeholder' => $field->placeholder,
                    'type' => $field->type->value,
                    'width' => $field->width,
                    'required' => $field->required,
                    'defaultOptionValue' => $field->type === FormFieldType::Select
                        ? $field->options->firstWhere('id', $field->default_option_id)?->value
                        : null,
                    'hideOptionPrice' => (bool) $field->hide_option_price,
                    'dependencyFieldId' => $field->dependency_field_id,
                    'dependencyOptionId' => $field->dependency_option_id,
                    'dependencyEquals' => (bool) $field->dependency_equals,
                    'options' => $field->options->map(fn (FormFieldOption $option): array => [
                        'id' => $option->id,
                        'label' => $option->label,
                        'value' => $option->value,
                        'priceCents' => $option->price_cents,
                    ])->values()->all(),
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    public function status(Event $event, Registration $registration, Request $request, SyncMolliePaymentStatus $syncPaymentStatus): Response|RedirectResponse|JsonResponse
    {
        if (! $request->hasValidSignatureWhileIgnoring(['iframe'])) {
            abort(401);
        }

        // If the user just came from the checkout and the registration status we know is pending,
        // we make an request to Mollie to get the latest payment status, as in this case it is likely that
        // it just updated. Doing this prevents showing an outdated "payment pending" status to the user.
        if ($request->query('from_checkout') !== null && $registration->currentState instanceof PaymentPendingState) {
            $latestPayment = $registration->currentPaymentState()->first();

            if ($latestPayment) {
                try {
                    $syncPaymentStatus->execute($latestPayment);
                    $registration = $registration->refresh();
                } catch (Throwable $e) {
                    report($e);
                }
            }

            $redirect = $registration->signedStatusUrl();

            if ($request->has('iframe')) {
                return response()->json(['redirectUrl' => $redirect]);
            }

            return redirect()->to($redirect);
        }

        $isWaitlisted = $registration->currentState instanceof WaitlistedState;

        $waitlistPosition = ($isWaitlisted && $event->show_waitlist_position && $registration->currentState)
            ? (int) $event->registrations()
                ->waitlisted()
                ->where(function ($query) use ($registration): void {
                    $waitlistedAt = $registration->currentState?->created_at;

                    $query
                        ->whereHas('currentState', fn (Builder $query): Builder => $query
                            ->where('type', 'waitlisted')
                            ->where('created_at', '<', $waitlistedAt)
                        )
                        ->orWhere(fn (Builder $query) => $query
                            ->whereHas('currentState', fn ($query): Builder => $query
                                ->where('type', 'waitlisted')
                                ->where('created_at', '=', $waitlistedAt)
                            )
                            ->where('id', '<=', $registration->id)
                        );
                })
                ->count()
            : null;

        $checkoutUrl = null;
        $expiresAt = null;

        if ($registration->currentState instanceof PaymentPendingState && ! $registration->currentState->isExpired()) {
            $checkoutUrl = $registration->checkoutUrl($registration->currentState->expires_at);
            $expiresAt = (string) $registration->currentState->expires_at->toIso8601String();
        }

        return Inertia::render('registration/status', [
            'event' => $this->mapEventForFrontend($event),
            'status' => [
                'state' => $registration->currentState?->type ?? null,
                'waitlistPosition' => $waitlistPosition,
                'checkoutUrl' => $checkoutUrl,
                'expiresAt' => $expiresAt,
            ],
            'isIframe' => $request->has('iframe'),
        ]);
    }

    public function checkout(Event $event, Registration $registration, Request $request, MollieService $mollieService): RedirectResponse|HttpResponse|JsonResponse
    {
        if (! $request->hasValidSignatureWhileIgnoring(['iframe'])) {
            abort(401);
        }

        if (! ($registration->currentState instanceof PaymentPendingState) || $registration->currentState->isExpired()) {
            $redirect = $registration->signedStatusUrl();

            if ($request->has('iframe')) {
                return response()->json(['redirectUrl' => $redirect]);
            }

            return redirect()->to($redirect);
        }

        $lastPayment = $registration->currentPaymentState()->first();

        if ($lastPayment !== null) {
            $existingPayment = $mollieService->getPayment($registration->event, $lastPayment->mollie_payment_id);

            $checkoutUrl = $existingPayment->getCheckoutUrl();
            if ($existingPayment->isOpen() && $checkoutUrl) {
                return Inertia::location($checkoutUrl);
            }
        }

        $payment = $mollieService->createPayment($registration->event, [
            'description' => $registration->paymentDescription(),
            'amount' => new Money('EUR', number_format($registration->price_cents / 100, 2, '.', '')),
            'redirectUrl' => $registration->publicStatusUrl(['from_checkout' => '1']),
            'webhookUrl' => route('webhooks.mollie'),
            'method' => PaymentMethod::IDEAL,
        ]);

        $registration->payments()->create([
            'mollie_payment_id' => $payment->id,
            'status' => $payment->status,
            'occured_at' => Carbon::parse($payment->createdAt),
        ]);

        $redirect = $payment->getCheckoutUrl();

        // Redirect customer to Mollie checkout page.
        if ($request->has('iframe')) {
            return response()->json(['redirectUrl' => $redirect]);
        }

        return Inertia::location($redirect);
    }

    public function store(Event $event, RegistrationStoreRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $inviteId = Arr::get($validated, 'invite_id');
        $inviteToken = Arr::get($validated, 'invite_token');

        $expiresAt = now()->addMinutes((int) $event->registration_expiration_minutes);

        $form = $event->form;
        if (! $form) {
            throw ValidationException::withMessages([
                'event' => __('registration.validation.closed'),
            ]);
        }

        $formFields = $form->sections->flatMap->fields->all();
        $visibleFields = $this->filterVisibleDynamicFields($formFields, $validated['fields'] ?? []);

        /** @var array<string, mixed> $validatedDynamic */
        $validatedFields = Validator::make(
            $validated['fields'] ?? [], // When a form has no fields, there won't be a fields array
            $this->buildDynamicFieldsValidationRules($visibleFields),
            [],
            $this->buildDynamicFieldsAttributeNames($visibleFields)
        )->validate();

        $prepared = $this->prepareRegistrationSubmissionData($event, $validatedFields, $visibleFields);

        /** @var Registration $registration */
        [$registration, $waitlisted] = DB::transaction(function () use ($event, $prepared, $expiresAt, $inviteId, $inviteToken): array {
            // Lock event so that at most one registration can be created at a time,
            // preventing the possibility of exceeding capacity.
            $event = Event::query()
                ->lockForUpdate()
                ->findOrFail($event->id);

            if ($event->closes_at?->isNowOrPast()) {
                throw ValidationException::withMessages([
                    'event' => __('registration.validation.closed'),
                ]);
            }

            $invite = null;
            if ($event->opens_at->isFuture()) {
                $invite = Invite::findValidForEvent($event, $inviteId, $inviteToken, true);

                if (! $invite) {
                    throw ValidationException::withMessages([
                        'event' => __('registration.validation.upcoming'),
                    ]);
                }
            }

            $directPaymentAllowed = $event->directPaymentAllowed();

            /** @var Registration $registration */
            $registration = $event->registrations()->create([
                'price_cents' => $prepared['priceCents'],
            ]);

            $registration->registrationValues()->createMany($prepared['values']);

            if ($invite) {
                $invite->markUsedByRegistration($registration);
            }

            if ($directPaymentAllowed) {
                $registration->states()->create([
                    'type' => RegistrationStates::PaymentPending,
                    'expires_at' => $expiresAt,
                ]);
            } else {
                $registration->states()->create([
                    'type' => RegistrationStates::Waitlisted,
                ]);
            }

            $waitlisted = ! $directPaymentAllowed;

            return [$registration, $waitlisted];
        });

        try {
            if ($waitlisted) {
                $registration->notify(new WaitlistedNotification($registration));
            } else {
                $registration->notify(new RegistrationSubmittedPaymentPendingNotification($registration, $expiresAt));
            }
        } catch (Throwable $e) {
            report($e);
        }

        $isIframe = $request->has('iframe');

        $route = $registration->currentState instanceof PaymentPendingState
            ? $registration->checkoutUrl($expiresAt)
            : $registration->publicStatusUrl(isIframe: $isIframe);

        if ($isIframe) {
            return response()->json(['redirectUrl' => $route]);
        }

        return redirect()->to($route);
    }

    /**
     * @param  array<int, FormField>  $fields
     * @return array<string, mixed>
     */
    private function buildDynamicFieldsValidationRules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $key = "{$field->id}";

            $fieldRules = [];

            if ($field->required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            $fieldRules[] = 'string';

            switch ($field->type) {
                case FormFieldType::Email:
                    $fieldRules[] = 'email';
                    $fieldRules[] = 'max:255';
                    break;

                case FormFieldType::Date:
                    $fieldRules[] = 'date';
                    break;

                case FormFieldType::LongText:
                    $fieldRules[] = 'max:5000';
                    break;

                case FormFieldType::Radio:
                case FormFieldType::Select:
                    $fieldRules[] = Rule::in($field->options->pluck('value')->all());
                    break;

                case FormFieldType::Text:
                default:
                    $fieldRules[] = 'max:255';
                    break;
            }

            $rules[$key] = $fieldRules;
        }

        return $rules;
    }

    /**
     * @param  array<int, FormField>  $fields
     * @return array<string, string>
     */
    private function buildDynamicFieldsAttributeNames(array $fields): array
    {
        $attributes = [];

        foreach ($fields as $field) {
            $attributes[(string) $field->id] = $field->label;
        }

        return $attributes;
    }

    /**
     * @param  array<int|string, mixed>  $submittedFieldValues
     * @return array{priceCents: int, values: array<int, array<string, mixed>>}
     */
    private function prepareRegistrationSubmissionData(Event $event, array $submittedFieldValues, array $fields): array
    {
        $form = $event->form;
        abort_if($form === null, 422);

        $fields = collect($fields)->keyBy('id');

        $priceCents = (int) $form->base_price_cents;
        $registrationValues = [];

        foreach ($fields as $fieldId => $field) {
            $rawValue = $submittedFieldValues[$fieldId];

            // Note: even if a field was empty, we still store it so that it shows up
            // as empty in the registration details, instead of not showing up at all.

            $value = (string) $rawValue;
            $selectedOption = null;
            $optionPriceCents = 0;

            if ($value !== '' && $field->type->hasOptions()) {
                /** @var FormFieldOption $selectedOption */
                $selectedOption = $field->options->firstOrFail('value', $value);
                $optionPriceCents = $selectedOption->price_cents;
                $priceCents += $optionPriceCents;
            }

            $registrationValues[] = [
                'field_id' => $field->id,
                'option_id' => $selectedOption?->id,
                'option_price_cents' => $optionPriceCents,
                'value' => $value,
            ];
        }

        return [
            'priceCents' => $priceCents,
            'values' => $registrationValues,
        ];
    }

    /**
     * @param  array<int, FormField>  $fields
     * @param  array<int|string, mixed>  $submittedFieldValues
     * @return array<int, FormField>
     */
    private function filterVisibleDynamicFields(array $fields, array $submittedFieldValues): array
    {
        $fieldsById = collect($fields)->keyBy('id');

        return $fieldsById
            ->filter(fn (FormField $field): bool => $this->isVisibleDynamicField($field, $submittedFieldValues, $fieldsById))
            ->values()->all();
    }

    /**
     * @param  array<int|string, mixed>  $submittedFieldValues
     * @param  Collection<int, FormField>  $fieldsById
     */
    private function isVisibleDynamicField(FormField $field, array $submittedFieldValues, Collection $fieldsById): bool
    {
        if ($field->dependency_field_id === null || $field->dependency_option_id === null) {
            return true;
        }

        $dependencyField = $fieldsById->get($field->dependency_field_id);

        if (! $dependencyField instanceof FormField || ! $dependencyField->type?->hasOptions()) {
            return true;
        }

        $submittedValue = $submittedFieldValues[$dependencyField->id] ?? null;

        $selectedOption = $dependencyField->options->firstWhere('value', $submittedValue);

        if ($selectedOption === null) {
            return ! $field->dependency_equals;
        }

        $matches = $selectedOption->id === $field->dependency_option_id;

        return $field->dependency_equals === $matches;
    }
}
