@extends('layouts.app')

@section('title', 'Create currency pair')

@section('content')
  @include('partials.errors')

  {!! Form::open(['route' => 'store']) !!}

  <div class="form-group row">
    {!! Form::label('amount', 'Amount', ['class' => 'col-md-3 col-form-label']) !!}
    <div class="col-md-2">
      {!! Form::text('amount', null, ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="form-group row">
    {!! Form::label('base', 'Base currency', ['class' => 'col-md-3 col-form-label']) !!}
    <div class="col-md-2">
      {!! Form::select('base', $currencies, config('app.default_currencies')[0], ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="form-group row">
    {!! Form::label('target', 'Target currency', ['class' => 'col-md-3 col-form-label']) !!}
    <div class="col-md-2">
      {!! Form::select('target', $currencies, config('app.default_currencies')[1], ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="form-group row">
    {!! Form::label('duration', 'Duration (weeks)', ['class' => 'col-md-3 col-form-label']) !!}
    <div class="col-md-2">
      {!! Form::number('duration', config('app.default_duration'), ['class' => 'form-control', 'min' => config('app.min_duration'), 'max' => config('app.max_duration')]) !!}
    </div>
  </div>

  <div class="form-group row">
    {!! Form::label('favourite', 'Save as favourite', ['class' => 'col-md-3 col-form-label']) !!}
    <div class="col-md-2">
      {!! Form::checkbox('favourite', '1', true); !!}
    </div>
  </div>

  <div class="form-group row">
    <div class="col-md-3"></div>
    <div class="col-md-2">
      {!! Form::submit('Create', ['class' => 'btn btn-primary']) !!}
    </div>

  {!! Form::close() !!}
@endsection
