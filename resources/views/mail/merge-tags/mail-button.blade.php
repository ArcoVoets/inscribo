@props(['url', 'label'])

<div style="margin: 18px 0;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">
        <tr>
            <td align="center">
                <a href="{{ $url }}" style="display: inline-block; background-color: #111827; border-radius: 6px; color: #ffffff; font-size: 14px; font-weight: 700; line-height: 1.5; padding: 12px 24px; text-decoration: none;">
                    {{ $label }}
                </a>
            </td>
        </tr>
    </table>
</div>
