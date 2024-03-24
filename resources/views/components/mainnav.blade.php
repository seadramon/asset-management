<div class="navbar navbar-expand-md navbar-dark bg-indigo navbar-static">
    <div class="navbar-brand">
        <a href="{{ url('aset/data') }}" class="d-inline-block">
            <img src="{{asset('global_assets/images/logo_light.png')}}" alt="">
        </a>
    </div>

    <div class="d-md-none">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-mobile">
            <i class="icon-tree5"></i>
        </button>
        <button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
            <i class="icon-paragraph-justify3"></i>
        </button>
    </div>

    <div class="collapse navbar-collapse" id="navbar-mobile">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="#" class="navbar-nav-link sidebar-control sidebar-main-toggle d-none d-md-block">
                    <i class="icon-paragraph-justify3"></i>
                </a>
            </li>
        </ul>

        <span class="navbar-text ml-md-3 font-weight-semibold">
            <i class="icon-user mr-2"></i>
            {{\Auth::user()->role->jabatan->namajabatan}}
        </span>

        <ul class="navbar-nav ml-md-auto">
            <li class="nav-item">
                <a href="#" class="navbar-nav-link">
                    Asset Information System PDAM Surabaya  
                </a>
            </li>

            <li class="nav-item">
                <a href="{{\URL::to('auth/logout')}}" class="navbar-nav-link">
                    <i class="icon-switch2"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
</div>