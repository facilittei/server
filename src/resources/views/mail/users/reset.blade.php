@component('mail::layout')

@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="https://facilittei.com/facilittei.svg" width="225">
@endcomponent
@endslot

# {{ trans('messages.register_greeting') }}, {{ $user->name }}!

{{ trans('messages.password_reset_why') }}

@component('mail::button', ['url' => $link])
{{ trans('messages.password_reset_button') }}
@endcomponent

{{ trans('messages.password_reset_link_expiration') }}

{{ trans('messages.password_reset_link_noaction') }}

@slot('footer')
@component('mail::footer')
{{ trans('ui.thanks') }}, {{ config('app.name') }}
@endcomponent
@endslot

@slot('subcopy')
{{ trans('messages.password_reset_link_trouble') }}
<span class="break-all"><a href="{{ $link }}">{{ $link }}</a></span>
@endslot

@endcomponent
