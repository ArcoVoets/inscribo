<div style="margin: 18px 0;">
    @if(count($priceRows) > 0)
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse: collapse; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
            <thead>
                <tr>
                    <th align="left" style="background: #f9fafb; padding: 10px 12px; font-size: 13px; font-weight: 700; color: #374151; border-bottom: 1px solid #e5e7eb;">
                        {{ __('mail.registration_completed.pricing.table.item') }}
                    </th>
                    <th align="left" style="background: #f9fafb; padding: 10px 12px; font-size: 13px; font-weight: 700; color: #374151; border-bottom: 1px solid #e5e7eb;">
                        {{ __('mail.registration_completed.pricing.table.price') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($priceRows as $row)
                    <tr>
                        <td valign="top" style="padding: 10px 12px; font-size: 14px; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #111827; width: 35%;">
                            {{ $row['label'] }}
                        </td>
                        <td valign="top" style="padding: 10px 12px; font-size: 14px; border-bottom: 1px solid #e5e7eb; color: #374151;">
                            {{ \Illuminate\Support\Number::currency($row['amountCents'] / 100, 'EUR', config('app.locale')) }}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td style="padding: 10px 12px; font-size: 14px; font-weight: 700; color: #111827; background: #f9fafb; border-top: 1px solid #e5e7eb;">
                        {{ __('mail.registration_completed.pricing.total') }}
                    </td>
                    <td style="padding: 10px 12px; font-size: 14px; font-weight: 700; color: #111827; background: #f9fafb; border-top: 1px solid #e5e7eb;">
                        {{ \Illuminate\Support\Number::currency($totalPriceCents / 100, 'EUR', config('app.locale')) }}
                    </td>
                </tr>
            </tbody>
        </table>
    @endif
</div>