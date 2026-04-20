<x-mail::message>
# {{ __('emails.claim_account.greeting') }}

{{ __('emails.claim_account.intro', ['name' => config('app.name')]) }}

<x-mail::button :url="$url">
{{ __('emails.claim_account.action') }}
</x-mail::button>

{{ __('emails.claim_account.outro') }}

{{ __('emails.claim_account.salutation', ['name' => config('app.name')]) }}
</x-mail::message>
