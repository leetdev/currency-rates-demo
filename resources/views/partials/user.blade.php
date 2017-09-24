<div class="mr-0">
  <div class="navbar-text">Logged in as: <b>{{ Auth::user()->name }}</b></div>
  <a href="{{ route('logout') }}" class="btn btn-primary">
    <i class="fa fa-sign-out" aria-hidden="true"></i> Logout
  </a>
</div>
