<div class="mr-0">
  <div class="navbar-text">Logged in as: <b>{{ Auth::user()->name }}</b></div>
  <a href="{{ route('logout') }}" class="btn btn-primary">Logout</a>
</div>
