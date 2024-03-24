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
                <li class="nav-item">
                    <a href="{{url('/')}}" class="nav-link">
                        <i class="icon-home4"></i>
                        <span>
                            Dashboard
                            <span class="d-block font-weight-normal opacity-50">No active orders</span>
                        </span>
                    </a>
                </li>

                <li class="nav-item nav-item-submenu <?=$TAG=='m_user'?'nav-item-open':''?>">
                    <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Manajemen User</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Master" <?=$TAG=='m_user'?'style="display: block;"':''?> >
                        <li class="nav-item"><a href="{{ route('mnjrole::mnjrole-index') }}" class="nav-link <?=$TAG2=='mnjrole'?'active':''?>">Roles</a></li>
                    </ul>
                    <ul class="nav nav-group-sub" data-submenu-title="Master" <?=$TAG=='m_user'?'style="display: block;"':''?> >
                        <li class="nav-item"><a href="{{ route('mnjuser::mnjuser-index') }}" class="nav-link <?=$TAG2=='mnjuser'?'active':''?>">Users</a></li>
                    </ul>
                    <ul class="nav nav-group-sub" data-submenu-title="Master" <?=$TAG=='m_user'?'style="display: block;"':''?> >
                        <li class="nav-item"><a href="{{ route('datarolemenus-link') }}" class="nav-link <?=$TAG2=='rolemenus'?'active':''?>">Role Menus</a></li>
                    </ul>
                    <ul class="nav nav-group-sub" data-submenu-title="Master" <?=$TAG=='m_user'?'style="display: block;"':''?> >
                        <li class="nav-item"><a href="{{ route('mnjmenu::mnjmenu-index') }}" class="nav-link <?=$TAG2=='menus'?'active':''?>">Menus</a></li>
                    </ul>
                </li>

                <li class="nav-item nav-item-submenu <?=$TAG=='aset'?'nav-item-open':''?>">
                    <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Aset</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Master" <?=$TAG=='aset'?'style="display: block;"':''?> >
                        <li class="nav-item"><a href="{{ url('aset/entri') }}" class="nav-link <?=$TAG2=='entri'?'active':''?>">Entri</a></li>
                        <li class="nav-item"><a href="{{ url('aset/data') }}" class="nav-link <?=$TAG2=='data'?'active':''?>">Data</a></li>
                        <li class="nav-item"><a href="{{ route('pemindahan::pemindahan-index') }}" class="nav-link <?=$TAG2=='pemindahan'?'active':''?>">Pemindahan</a></li>
                        <li class="nav-item"><a href="{{ route('peminjaman::peminjaman-index') }}" class="nav-link <?=$TAG2=='peminjaman'?'active':''?>">Peminjaman</a></li>
                        <li class="nav-item"><a href="{{ route('nonaktif::nonaktif-index') }}" class="nav-link <?=$TAG2=='nonaktif'?'active':''?>">Non Aktif</a></li>
                    </ul>
                </li>
                <li class="nav-item nav-item-submenu">
                    <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Master</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Master">
                        <li class="nav-item"><a href="{{ route('master::kondisi-link') }}" class="nav-link">Kondisi</a></li>
                        <li class="nav-item"><a href="{{ route('master::kategori-link') }}" class="nav-link">Kategori</a></li>
                        <li class="nav-item"><a href="{{ route('master::subkategori-link') }}" class="nav-link">Sub Kategori</a></li>
                        <li class="nav-item"><a href="{{ route('master::subsubkategori-link') }}" class="nav-link">Sub Sub Kategori</a></li>
                        <li class="nav-item"><a href="{{ route('master::instalasi-link') }}" class="nav-link">Instalasi</a></li>
                        <li class="nav-item"><a href="{{ route('master::lokasi-link') }}" class="nav-link">Lokasi</a></li>
                        <li class="nav-item"><a href="{{ route('master::ruangan-link') }}" class="nav-link">Ruangan</a></li>
                        <li class="nav-item"><a href="{{ route('master::spekitem-link') }}" class="nav-link">Spesifikasi Item</a></li>
                        <li class="nav-item"><a href="{{ route('master::spekgroup-link') }}" class="nav-link">Spesifikasi Group</a></li>
                        <li class="nav-item"><a href="{{ route('master::sistem-link') }}" class="nav-link">Sistem</a></li>
                    </ul>
                </li>
                <li class="nav-item nav-item-submenu">
                    <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Master MS</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Master">
                        <li class="nav-item"><a href="{{ route('master::kelompok-link') }}" class="nav-link">Kelompok</a></li>
                        <li class="nav-item"><a href="{{ route('master::kelompokdetail-link') }}" class="nav-link">Kelompok Detail</a></li>
                        <li class="nav-item"><a href="{{ route('master::template-link') }}" class="nav-link">Equipment</a></li>
                        <li class="nav-item"><a href="{{ route('master::komponen-link') }}" class="nav-link">Komponen</a></li>
                        <li class="nav-item"><a href="{{ route('master::komponendetail-link') }}" class="nav-link">Komponen Detail</a></li>
                    </ul>
                </li>
                <li class="nav-item nav-item-submenu">
                    <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Manajemen Strategi</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="MStrategi">
                        <li class="nav-item"><a href="{{ route('mstrategi::mstrategi-entri') }}" class="nav-link">Entri</a></li>
                        <li class="nav-item"><a href="{{ route('mstrategi::mstrategi-entripdm') }}" class="nav-link">Entri PdM</a></li>
                        <li class="nav-item"><a href="{{ route('mstrategi::mstrategi-entri52w') }}" class="nav-link">Entri 52W</a></li>
                        <li class="nav-item"><a href="{{ route('mstrategi::mstrategi-entri4w') }}" class="nav-link">Entri 4W</a></li>
                        <?php /*
                        <li class="nav-item"><a href="{{ route('mstrategi::mstrategi-entriPenugasan') }}" class="nav-link">Entri Penugasan</a></li>
                        */?>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{route('monitoring::monitoring-index')}}" class="nav-link">
                        <i class="icon-copy"></i>
                        <span>
                            Monitoring
                        </span>
                    </a>
                </li>

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

                <li class="nav-item">
                    <a href="{{route('jadwalkerja::jadwalkerja-index')}}" class="nav-link">
                        <i class="icon-copy"></i>
                        <span>
                            Jadwal Kerja Pompa
                        </span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{route('rpembobotan::rpembobotan-index')}}" class="nav-link">
                        <i class="icon-copy"></i>
                        <span>
                            Laporan Pembobotan
                        </span>
                    </a>
                </li>
				
                <li class="nav-item nav-item-submenu <?=$TAG=='m_user'?'nav-item-open':''?>">
                    <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Manajemen User</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Master" <?=$TAG=='m_user'?'style="display: block;"':''?> >
                        <li class="nav-item"><a href="{{ route('mnjrole::mnjrole-index') }}" class="nav-link <?=$TAG2=='mnjrole'?'active':''?>">Roles</a></li>
                    </ul>
                    <ul class="nav nav-group-sub" data-submenu-title="Master" <?=$TAG=='m_user'?'style="display: block;"':''?> >
                        <li class="nav-item"><a href="{{ route('mnjuser::mnjuser-index') }}" class="nav-link <?=$TAG2=='mnjuser'?'active':''?>">Users</a></li>
                    </ul>
                    <ul class="nav nav-group-sub" data-submenu-title="Master" <?=$TAG=='m_user'?'style="display: block;"':''?> >
                        <li class="nav-item"><a href="{{ route('datarolemenus-link') }}" class="nav-link <?=$TAG2=='rolemenus'?'active':''?>">Role Menus</a></li>
                    </ul>
                    <ul class="nav nav-group-sub" data-submenu-title="Master" <?=$TAG=='m_user'?'style="display: block;"':''?> >
                        <li class="nav-item"><a href="{{ route('mnjmenu::mnjmenu-index') }}" class="nav-link <?=$TAG2=='menus'?'active':''?>">Menus</a></li>
                    </ul>
                </li>
                
                <?php  
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