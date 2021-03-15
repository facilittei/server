@component('mail::layout')

@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ trans('ui.welcome') }}!
@endcomponent
@endslot

# {{ trans('messages.register_greeting') }}, {{ $user->name }}

{{ trans('messages.register_thanks') }}, facilittei.com!

{{ trans('messages.register_confirm') }}

@slot('subcopy')
@component('mail::subcopy')
<a href="{{ config('app.client_url') . '/verify/' . $verification }}">{{ config('app.client_url') . '/verify/' . $verification }}</a>
@endcomponent
@endslot

@component('mail::button', ['url' => config('app.client_url') . '/verify/' . $verification])
{{ trans('ui.confirm') }}
@endcomponent

@slot('footer')
@component('mail::footer')
{{ trans('ui.thanks') }}, {{ config('app.name') }}
@endcomponent
@endslot

@endcomponent
