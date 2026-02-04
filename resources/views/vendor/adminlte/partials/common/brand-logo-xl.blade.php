{{-- Brand Logo (Large) --}}
@php
    $user = auth()->user();
    $initial = $user ? strtoupper(substr($user->nname ?? 'U', 0, 1)) : 'U';
    $dashboardUrl = $user ? match((int) $user->role_id) {
        1 => url('admin/dashboard'),
        2 => url('teamadmin/dashboard'),
        3 => url('user/dashboard'),
        default => url('login'),
    } : url('login');
@endphp

<a href="{{ $dashboardUrl }}" @if(config('adminlte.usermenu_enabled', true)) class="brand-link" @endif>

    {{-- Brand logo --}}
    <div class="brand-image img-circle elevation-3 d-flex align-items-center justify-content-center" 
         style="width: 33px; height: 33px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: bold; font-size: 16px; margin-left: 0.8rem; float: left;">
        {{ $initial }}
    </div>

    {{-- Brand text --}}
    <span class="brand-text font-weight-light">
        {!! config('adminlte.logo', '<b>Prime</b>FC') !!}
    </span>

</a>
