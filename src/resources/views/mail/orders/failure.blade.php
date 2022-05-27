@component('mail::layout')

@slot('header')
@component('mail::header', ['url' => config('app.website_url')])
Facilittei
@endcomponent
@endslot

# {{ trans('ui.hi') }}, {{ $user->name }}!

{{ trans('messages.course_failure') }}: **{{ $course->title }}**

{{ trans('messages.order_number') }}:**{{ $order->id }}**

@slot('footer')
@component('mail::footer')
{{ trans('ui.thanks') }}, {{ config('app.name') }}
@endcomponent
@endslot

@endcomponent
