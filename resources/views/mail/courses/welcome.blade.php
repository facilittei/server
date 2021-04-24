@component('mail::layout')

@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="https://facilittei.com/facilittei.svg" width="225">
@endcomponent
@endslot

# {{ trans('mail.course_welcome') }}, {{ $user->name }}!

{{ trans('mail.course_info') }}: **{{ $course->title }}**

@slot('footer')
@component('mail::footer')
{{ trans('ui.thanks') }}, {{ config('app.name') }}
@endcomponent
@endslot

@endcomponent
