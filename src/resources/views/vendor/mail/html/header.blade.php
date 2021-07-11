<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Facilittei')
<img src="https://s3.amazonaws.com/facilittei-dev/facilittei.png" width="225">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
