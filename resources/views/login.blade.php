@extends('layouts.app')

@section('title', 'Login')

@section('content')
  <div class="row">
    <div class="col-lg-4 col-md-6 ml-auto mr-auto">
      <ul class="social-buttons">
        <li>
          <a href="{{ action('LoginController@login', ['provider' => 'google']) }}" class="btn btn-block btn-lg btn-primary text-center">
            <span class="fa fa-google"></span> Login with Google
          </a>
        </li>
      </ul>
    </div>
  </div>
@endsection
