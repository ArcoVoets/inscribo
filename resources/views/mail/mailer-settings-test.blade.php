<h1>{{ __('admin.mailer_settings.test_email.subject') }}</h1>
<p>{{ __('admin.mailer_settings.test_email.intro') }}</p>
<ul>
    <li><strong>Host:</strong> {{ $mailerSettings->host }}</li>
    <li><strong>Port:</strong> {{ $mailerSettings->port }}</li>
    <li><strong>Username:</strong> {{ $mailerSettings->username }}</li>
    <li><strong>From:</strong> {{ $mailerSettings->from_address }}</li>
    @if($mailerSettings->reply_to_address)
        <li><strong>Reply-to:</strong> {{ $mailerSettings->reply_to_address }}</li>
    @endif
</ul>
