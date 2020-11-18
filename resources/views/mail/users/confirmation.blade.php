@component('mail::layout')

@slot('header')
@component('mail::header', ['url' => config('app.url')])
HEADER
@endcomponent
@endslot

# Introduction
{{ $user->name }}

The body of your message.

@slot('subcopy')
@component('mail::subcopy')
SUBCOPY
@endcomponent
@endslot

@component('mail::button', ['url' => ''])
Confirm
@endcomponent

@slot('footer')
@component('mail::footer')
Thanks, {{ config('app.name') }}
@endcomponent
@endslot

@endcomponent
