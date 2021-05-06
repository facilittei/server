<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Facilittei')
<img src="https://facilittei.com/facilittei.svg" width="225">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
