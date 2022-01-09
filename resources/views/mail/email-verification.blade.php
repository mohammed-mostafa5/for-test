@component('mail::message')

# Verification Mail

Welcome {{ $user->name }} ,

Use this code to verify your account:

<h2 style="text-align: center">{{ $user->verification_code }}</h2>

<small>Note: This code will be expired after 10 minutes</small>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
