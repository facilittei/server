@component('mail::layout')

@slot('header')
@component('mail::header', ['url' => config('app.url')])
Welcome!
@endcomponent
@endslot

# {{ trans('messages.register_greeting') }}, {{ $user->name }}

{{ trans('messages.register_thanks') }}, facilittei.com!

{{ trans('messages.register_confirm') }}

@slot('subcopy')
@component('mail::subcopy')
<a href="{{ url('/verify/' . $verification) }}">{{ url('/verify/' . $verification) }}</a>
@endcomponent
@endslot

@component('mail::button', ['url' => url($verification)])
{{ trans('ui.confirm') }}
@endcomponent

@slot('footer')
@component('mail::footer')
Thanks, {{ config('app.name') }}
@endcomponent
@endslot

@endcomponent
