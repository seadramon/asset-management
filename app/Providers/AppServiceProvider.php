<?php

namespace Asset\Providers;

use Illuminate\Support\ServiceProvider;
use Asset\Models\Menu;
use Request;
use Auth;
use Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Sftp\SftpAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('sftp', function ($app, $config) {
            return new Filesystem(new SftpAdapter($config));
        });
        
        view()->composer('components.menu', function ($view) {

            $aa = Menu::whereHas('roles.roleuser', function($sql){
                        $sql->where('user_id', Auth::user()->userid);
                    })
					//->where('tipe', '>', 0)
                    ->orderBy('urut')->get();
            //$aa = Menu::orderBy('urut')->get();
            
            $html = '';
            //menu default
            $i = 0;
            //menu sesuai role
            $bef = '';
            // dd($aa);
            foreach($aa as $row){     
                $style = "";

                if($row->tipe == 0){
                    if($bef == 2){
                        $html .= '</ul></li>';
                    }
                    $html .= '<li class="nav-item">
								<a href="' .route($row->url) . '" class="nav-link">
									<i class="'. $row->icon .'"></i>
									<span>
										' . $row->nama . '
									</span>
								</a>
							</li>';
                    $bef = $row->tipe;
                }elseif($row->tipe == 1){
                    $tag = $this->cekTag($row->nama);

                    if ($tag) {
                        $style = 'style="display: block;"';
                    }

                    if($bef == 4){
                        $html .= '</div></li></div></li>';
                    }
                    if($bef == 2){
                        $html .= '</ul></li>';
                    }
                    $html .= '<li class="nav-item nav-item-submenu">
								<a href="#" class="nav-link"><i class="'. $row->icon .'"></i> 
									<span>' . $row->nama . '</span>
								</a>
								<ul class="nav nav-group-sub" data-submenu-title="' . $row->nama . '" '.$style.'>';
                    $bef = $row->tipe;

                // }elseif($row->tipe == 3){
                    // if($bef == 4){
                        // $html .= '</div></li>';
                    // }
                    // $html .= '<li class="m-menu__item  m-menu__item--submenu"  m-menu-submenu-toggle="hover" m-menu-link-redirect="1" aria-haspopup="true">
                                // <a  href="javascript:;" class="m-menu__link m-menu__toggle">
                                    // <i class="m-menu__link-icon '.$row->icon.'"></i>
                                    // <span class="m-menu__link-text">
                                        // ' . $row->nama . '
                                    // </span>
                                    // <i class="m-menu__hor-arrow la la-angle-right"></i>
                                    // <i class="m-menu__ver-arrow la la-angle-right"></i>
                                // </a>
                                // <div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--right">
                                    // <span class="m-menu__arrow "></span>
                                    // <ul class="m-menu__subnav">';
                    // $bef = $row->tipe;

                }elseif($row->tipe == 2){
                    // $tag2 = $this->cekTag2($row->url);

                    if($bef == 4){
                        $html .= '</ul></li>';
                    }
                    $html .= '<li class="nav-item"><a href="' .route($row->url) . '" class="nav-link">' . $row->nama . '</a></li>';
                    $bef = $row->tipe;
                // }elseif($row->tipe == 4){
                    // $html .= '<li class="m-menu__item "  aria-haspopup="true">
                                    // <a  href="' . route($row->url) . '" class="m-menu__link">
                                        // <i class="m-menu__link-icon '.$row->icon.'"></i>
                                        // <span class="m-menu__link-title">
                                            // <span class="m-menu__link-wrap">
                                                // <span class="m-menu__link-text">
                                                    // ' . $row->nama . '
                                                // </span>
                                            // </span>
                                        // </span>
                                    // </a>
                                // </li>';
                    // $bef = $row->tipe;
                }
            }
            if($bef == 2){
                $html .= '</ul></li>';
            }
            $view->with('menu', $html);
        });
    }

    private function cekTag($url)
    {        
        $ret = false;
        $url = strtolower($url);
        if ($url=='manajemen strategi') $url = 'mstrategi';

        $segmen = Request::segment(1);
        $segmen2 = Request::segment(2);

        $except = [
            'Kelompok',
            'KelompokDetail',
            'Template',
            'Komponen',
            'KomponenDetail'
        ];        
        if (in_array($segmen2, $except) && $url=='master') {
            $segmen = 'masterms';
            $url = 'masterms';

            $ret = true;
        }

        if ($segmen == $url) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
