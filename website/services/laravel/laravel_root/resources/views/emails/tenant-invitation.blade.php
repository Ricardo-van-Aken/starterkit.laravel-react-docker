<x-mail::message>
# {{ __('emails.tenant_invitation.greeting', ['name' => $recipientName ?? $invitation->email]) }}

{{ __('emails.tenant_invitation.intro', ['tenant' => $invitation->tenant->name]) }}

@if($invitation->roles)
{{ __('emails.tenant_invitation.roles_intro') }}
@foreach($invitation->roles as $role)
- {{ $role }}
@endforeach
@endif

<x-mail::button :url="$acceptUrl">
{{ __('emails.tenant_invitation.accept_action') }}
</x-mail::button>

<x-mail::button :url="$declineUrl" color="error">
{{ __('emails.tenant_invitation.decline_action') }}
</x-mail::button>

{{ __('emails.tenant_invitation.outro') }}

{{ __('emails.tenant_invitation.salutation', ['name' => config('app.name')]) }}
</x-mail::message>
