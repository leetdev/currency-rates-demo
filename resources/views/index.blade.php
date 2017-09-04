@extends('layouts.app')

@section('content')
  <table class="table">
    <thead class="thead-default">
      <tr>
        <th>Amount</th>
        <th>Base</th>
        <th>Target</th>
        <th>Weeks</th>
        <th>Actions</th>
      </tr>
    </thead>
    @foreach($calculations as $calculation)
      <tr{!! $calculation->id == $favourite->id ? ' class="table-success"' : '' !!}>
        <td class="align-middle">{{ $calculation->amount }}</td>
        <td class="align-middle">{{ $calculation->base }}</td>
        <td class="align-middle">{{ $calculation->target }}</td>
        <td class="align-middle">{{ $calculation->duration }}</td>
        <td>
          <a href="{{ route('show', $calculation->id) }}" class="btn btn-info btn-sm">
            <i class="fa fa-line-chart" aria-hidden="true"></i> Show
          </a>
          <a href="{{ route('edit', $calculation->id) }}" class="btn btn-primary btn-sm">
            <i class="fa fa-edit" aria-hidden="true"></i> Edit
          </a>
          <button data-calculation-id="{{ $calculation->id }}" data-toggle="modal" data-target="#confirmationModal" class="btn btn-danger btn-sm">
            <i class="fa fa-trash-o" aria-hidden="true"></i> Delete
          </button>
          @if ($calculation->id != $favourite->id)
            <a href="{{ route('favourite', $calculation->id) }}"  class="btn btn-success btn-sm">
              <i class="fa fa-thumbs-o-up" aria-hidden="true"></i> Favourite
            </a>
          @endif
        </td>
      </tr>
    @endforeach
  </table>
  <a href="{{ route('create') }}" class="btn btn-primary">
  <i class="fa fa-plus" aria-hidden="true"></i> Add currency pair
  </a>
@endsection

<!-- Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Confirm delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this currency pair?
      </div>
      <div class="modal-footer">
        {!! Form::open([
          'method' => 'DELETE',
          'url' => ''
        ]) !!}
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          {!! Form::submit('Confirm delete', ['class' => 'btn btn-danger']) !!}
        {!! Form::close() !!}
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
var urlTemplate = '{{ route('destroy', '%id%') }}';
</script>
