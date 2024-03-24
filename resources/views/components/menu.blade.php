<?php
if(!isset($TAG)) $TAG='';
if(!isset($TAG2)) $TAG2='';
?>
<div class="sidebar sidebar-light sidebar-main sidebar-expand-md">

    <!-- Sidebar mobile toggler -->
    <div class="sidebar-mobile-toggler text-center">
        <a href="#" class="sidebar-mobile-main-toggle">
            <i class="icon-arrow-left8"></i>
        </a>
        <span class="font-weight-semibold">Navigation</span>
        <a href="#" class="sidebar-mobile-expand">
            <i class="icon-screen-full"></i>
            <i class="icon-screen-normal"></i>
        </a>
    </div>
    <!-- /sidebar mobile toggler -->


    <!-- Sidebar content -->
    <div class="sidebar-content">

        <!-- User menu -->
        <div class="sidebar-user-material">
            <div class="sidebar-user-material-body">
                <div class="card-body text-center">
                    <a href="#">
                        <img src="{{ \Avatar::create(\Auth::user()->username)->toBase64() }}" class="img-fluid rounded-circle shadow-1 mb-3" width="80" height="80" alt="">
                        <?php /* {{ \Avatar::create('Joko Widodo')->toBase64() }} */ ?>
                    </a>
                    <h6 class="mb-0 text-white text-shadow-dark">{{(\Auth::check()) ? \Auth::user()->username : ' '}}</h6>
                    <h6 class="mb-0 text-white text-shadow-dark">{{(\Auth::check()) ? \Auth::user()->userid : ' '}}</h6>
                    <!-- <span class="font-size-sm text-white text-shadow-dark">Mojokerto, MJK</span> -->
                </div>
                                            
                <div class="sidebar-user-material-footer">
                    <a href="#user-nav" class="d-flex justify-content-between align-items-center text-shadow-dark dropdown-toggle" data-toggle="collapse"><span>My account</span></a>
                </div>
            </div>

            <div class="collapse" id="user-nav">
                <ul class="nav nav-sidebar">
                    <li class="nav-item">
                        <a data-toggle="modal" class="nav-link" data-target="#modal-pass" onclick="" >
                            <i class="icon-cog5"></i>
                            <span>Ganti Password</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{\URL::to('auth/logout')}}" class="nav-link">
                            <i class="icon-switch2"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- /user menu -->


        <!-- Main navigation -->
        <div class="card card-sidebar-mobile">
            <ul class="nav nav-sidebar" data-nav-type="accordion">

                <!-- Main -->
                <li class="nav-item-header"><div class="text-uppercase font-size-xs line-height-xs">Main</div> <i class="icon-menu" title="Main"></i></li>

                <li class="nav-item nav-item-submenu">
                    <a href="{{route('index')}}" class="nav-link"><i class="icon-home4"></i> <span>Dashboard</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Dashboard">
                        @if (namaRole() == "Super Administrator")
                            <li class="nav-item"><a href="{{ route('dashboard::link-prwrutin') }}" class="nav-link">Perawatan Rutin</a></li>
                            <li class="nav-item"><a href="{{ route('dashboard::link-aset') }}" class="nav-link">Asset</a></li>
                            <li class="nav-item"><a href="{{ route('depresiasi::index') }}" class="nav-link">Depresiasi</a></li>
                        @endif
                    </ul>
                </li>
                
                <!-- <li class="nav-item">
                    <a href="{{url('/')}}" class="nav-link">
                        <i class="icon-home4"></i>
                        <span>
                            Dashboard
                            <span class="d-block font-weight-normal opacity-50">No active orders</span>
                        </span>
                    </a>
                </li> -->
				
				{!! $menu !!} 
                <?php /* 
                <li class="nav-item">
                    <a href="{{route('perawatan::perawatan-index')}}" class="nav-link">
                        <i class="icon-copy"></i>
                        <span>
                            Perawatan
                        </span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{route('perbaikan::perbaikan-index')}}" class="nav-link">
                        <i class="icon-copy"></i>
                        <span>
                            Perbaikan
                        </span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{route('todolist::todolist-index')}}" class="nav-link">
                        <i class="icon-copy"></i>
                        <span>
                            Todo List Monitoring
                        </span>
                    </a>
                </li>
                */ ?>
                
                <?php  
                /*Jika nambah tipe form tabel di database (FM)*/
                /*
                <li class="nav-item">
                    <a href="{{route('temp::temp-index')}}" class="nav-link">
                        <i class="icon-copy"></i>
                        <span>
                            Temp Generate Form Table
                        </span>
                    </a>
                </li>
                */                
                 ?>
                 
                <!-- /page kits -->

            </ul>
        </div>
        <!-- /main navigation -->

    </div>
    <!-- /sidebar content -->
    
</div>