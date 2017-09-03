@if(session()->has('flash'))
    <div class="alert alert-{{ session()->has('alert') ? session()->get('alert') : 'danger' }} text-center" role="alert">
        {{ session()->get('flash') }}
    </div>
@endif
