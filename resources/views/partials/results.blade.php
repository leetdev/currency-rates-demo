<ul class="parameters">
  <li>Amount: <b>{{ $parameters->amount }} {{ $parameters->base }}</b></li>
  <li>Currency: <b>{{ $parameters->target }}</b></li>
</ul>

<table class="table table-sm">
  <thead>
    <tr>
      <th>Week</th>
      <th class="text-right">Rate</th>
      <th class="text-right">Value</th>
      <th class="text-right">Profit/loss</th>
    </tr>
  </thead>
  @foreach ($weeks as $week)
    <tr>
      <td>{{ $week->year }}-W{{ sprintf('%02d', $week->week) }}</td>
      <td class="text-right">{{ $week->rate }}</td>
      <td class="text-right{{ $hilo[0] == $week->id ? ' text-success' : '' }}{{ $hilo[1] == $week->id ? ' text-danger' : '' }}">{{ $week->amount }} {{ $parameters->target }}</td>
      <td class="text-right{{ $week->profit > 0 ? ' text-success' : ($week->profit < 0 ? ' text-danger' : '') }}">{{ $week->profit }}</td>
    </tr>
  @endforeach
</table>
