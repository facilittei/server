@component('mail::layout')

@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="https://facilittei.com/facilittei.svg" width="225">
@endcomponent
@endslot

# TODO: message content

{{ $invite->token }}


@slot('footer')
@component('mail::footer')
{{ trans('ui.thanks') }}, {{ config('app.name') }}
@endcomponent
@endslot

@endcomponent
