@component('mail::layout')

@slot('header')
@component('mail::header', ['url' => config('app.url')])
Howdy!
@endcomponent
@endslot

# {{ trans('mail.course_welcome') }}, {{ $user->name }}.

{{ trans('mail.course_info') }}: **{{ $course->title }}**

@slot('footer')
@component('mail::footer')
Thanks, {{ config('app.name') }}
@endcomponent
@endslot

@endcomponent
