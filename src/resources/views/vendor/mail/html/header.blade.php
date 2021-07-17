<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Facilittei')
<img src="{{ config('app.assets_url') }}/facilittei.png" width="225">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
