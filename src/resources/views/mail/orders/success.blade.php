@component('mail::layout')

@slot('header')
@component('mail::header', ['url' => config('app.website_url')])
Facilittei
@endcomponent
@endslot

# {{ trans('ui.hi') }}, {{ $user->name }},

## {{ trans('mail.course_welcome') }}!

@component('mail::table')
| {{ trans('ui.title') }}  | {{ trans('ui.total') }}                                           |
| :----------------------- | -----------------------------------------------------------------:|
| {{ $course->title }}     | R$ {{ number_format(floatval($course->price), 2, '.', ',') }}     |
@endcomponent

@component('mail::table')
| {{ trans('messages.order_number') }}  |
| :------------------------------------ |
| {{ $order->id }}                      |
@endcomponent

@component('mail::button', ['url' => config('app.client_url') . '/course/' . $course->id])
{{ trans('ui.access') }}
@endcomponent

### {{ trans('messages.order_enjoy') }}

<a href="{{ config('app.client_url') . '/course/' . $course->id }}">{{ config('app.client_url') . '/course/' . $course->id }}</a>

@slot('footer')
@component('mail::footer')
{{ trans('ui.thanks') }}, {{ config('app.name') }}
@endcomponent
@endslot

@endcomponent
