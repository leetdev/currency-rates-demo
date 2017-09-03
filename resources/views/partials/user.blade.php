<div class="card">
  <div class="card-body">
    <h4>Welcome, {{ Auth::user()->name }}!</h4>
    <a href="{{ route('logout') }}" class="btn btn-primary">Logout</a>
  </div>
</div>
