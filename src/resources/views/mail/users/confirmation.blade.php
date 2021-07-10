@component('mail::layout')

@slot('header')
@component('mail::header', ['url' => config('app.website_url')])
<img src="https://facilittei.com/facilittei.svg" width="225">
@endcomponent
@endslot

# {{ trans('messages.register_greeting') }}, {{ $user->name }}!

{{ trans('messages.register_thanks') }}

{{ trans('messages.register_confirm') }}

@slot('subcopy')
@component('mail::subcopy')
<span class="break-all">
    <a href="{{ config('app.client_url') . '/verify/' . $verification }}">{{ config('app.client_url') . '/verify/' . $verification }}</a>
</span>
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
