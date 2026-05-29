<div style="margin: 18px 0;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse: collapse; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
        <thead>
            <tr>
                <th align="left" style="background: #f9fafb; padding: 10px 12px; font-size: 13px; font-weight: 700; color: #374151; border-bottom: 1px solid #e5e7eb;">
                    {{ __('mail.registration_completed.table.field') }}
                </th>
                <th align="left" style="background: #f9fafb; padding: 10px 12px; font-size: 13px; font-weight: 700; color: #374151; border-bottom: 1px solid #e5e7eb;">
                    {{ __('mail.registration_completed.table.value') }}
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($fields as $field)
                <tr>
                    <td valign="top" style="padding: 10px 12px; font-size: 14px; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #111827; width: 35%;">
                        {{ $field->field->label }}
                    </td>
                    <td valign="top" style="padding: 10px 12px; font-size: 14px; border-bottom: 1px solid #e5e7eb; color: #374151;">
                        {{ $field->showValue() }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>