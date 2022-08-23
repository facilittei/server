@component('mail::layout')

@slot('header')
@component('mail::header', ['url' => config('app.website_url')])
Facilittei
@endcomponent
@endslot

# {{ trans('mail.course_welcome') }}, {{ $user->name }}!

{{ trans('mail.course_info') }}: **{{ $course->title }}**

@component('mail::button', ['url' => config('app.client_url') . '/course/' . $course->id])
{{ trans('ui.access') }}
@endcomponent

@slot('footer')
@component('mail::footer')
{{ trans('ui.thanks') }}, {{ config('app.name') }}
@endcomponent
@endslot

@endcomponent
