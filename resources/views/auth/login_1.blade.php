<!DOCTYPE html>
<html>

    <head>
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <title>Login - Aplikasi Manajemen Asset</title>
        <meta name="keywords" content="HTML5 Bootstrap 3 Admin Template UI Theme" />
        <meta name="description" content="AdminDesigns - A Responsive HTML5 Admin UI Framework">
        <meta name="author" content="AdminDesigns">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- Font CSS (Via CDN) -->
        <!-- <link href="{{asset('font-awesome/css/font-awesome.css')}}" rel="stylesheet"> -->
        <link href="{{ asset('global_assets/css/icons/fontawesome/styles.min.css')}}" rel="stylesheet" type="text/css">

        <!-- Theme CSS -->
        <link rel="stylesheet" type="text/css" href="{{asset('assetlogin/default_skin/css/theme.css')}}">

        <!-- Admin Forms CSS -->
        <link rel="stylesheet" type="text/css" href="{{asset('assetlogin/admin-forms/css/admin-forms.css')}}">

        <!-- Favicon -->
        <link rel="shortcut icon" href="{{asset('assetlogin/img/favicon.ico')}}">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
       <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
       <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
       <![endif]
        <script type="text/javascript" src="{{asset('assetlogin/captcha/jquery-1.3.2.js')}}"></script>-->
        <style type="text/css">

            .button, .button:visited{
                float:right;
                background: #2daebf url(images/overlay.png) repeat-x; 
                font-weight:bold;
                display: inline-block; 
                padding: 5px 10px 6px; 
                color: #fff; 
                text-decoration: none;
                -moz-border-radius: 5px; 
                -webkit-border-radius: 5px;
                -moz-box-shadow: 0 1px 3px rgba(0,0,0,0.5);
                -webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.5);
                text-shadow: 0 -1px 1px rgba(0,0,0,0.25);
                border-bottom: 1px solid rgba(0,0,0,0.25);
                cursor: pointer;
                margin-top:1px;
                margin-right:15px;
            }
            .button:hover{
                background-color: #007d9a; 
            }
            #sortable {
                list-style-type: none;
                margin: 5px 0px 0px 16px;
                padding-left: 10px;
            }
            #sortable li {
                margin: 3px 3px 3px 0;
                padding: 1px;
                float: left;
                width: 50px;
                height: 35px;
                font-size: 20px;
                text-align: center;
                line-height:35px;
                cursor:pointer;
                -moz-border-radius:5px;
                -webkit-border-radius:5px;
                -moz-box-shadow: 0 1px 1px rgba(0,0,0,0.5);
                -webkit-box-shadow: 0 1px 1px rgba(0,0,0,0.5);
                text-shadow: 0 -1px 1px rgba(0,0,0,0.25);
                background:#0085FF url(images/overlay.png) repeat-x scroll 50% 50%;
                color:#fff;
                font-weight:normal;
            }
            .captcha_wrap{
                border:1px solid #fff;
                -moz-border-radius:10px;
                -webkit-border-radius:10px;
                -moz-box-shadow: 0 1px 3px rgba(0,0,0,0.5);
                -webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.5);
                float:left;
                height:120px;
                overflow:auto;
                width:150px;
                overflow:hidden;
                margin:0px 0px 0px 210px;
                background-color:#fff;
            }
            .captcha{
                -moz-border-radius:10px;
                -webkit-border-radius:10px;
                font-size:12px;
                color: #0085FF;
                text-align: center;
                border-bottom:1px solid #CCC;
                background-color:#fff;
            }
        </style>

    </head>

    <body class="external-page sb-l-c sb-r-c">

        <!-- Start: Settings Scripts -->
        <script>
            var boxtest = localStorage.getItem('boxed');

            if (boxtest === 'true') {
                document.body.className += ' boxed-layout';
            }
        </script>
        <!-- End: Settings Scripts -->

        <!-- Start: Main -->
        <div id="main" class="animated fadeIn">

            <!-- Start: Content -->
            <section id="content_wrapper">

                <!-- begin canvas animation bg -->
                <div id="canvas-wrapper">
                    <canvas id="demo-canvas"></canvas>
                </div>

                <!-- Begin: Content -->
                <section id="content">

                    <div class="admin-form theme-info" id="login1">

                        <div class="row mb15 table-layout">

                            <div class="col-xs-6 va-m pln">
                                <a href="dashboard.html" title="Return to Dashboard">
                                    <img src="{{asset('assetlogin/img/logo_login1.png')}}" title="AdminDesigns Logo" class="img-responsive w250">
                                </a>
                            </div>

                            <div class="col-xs-6 text-right va-b pr5">
                                <div class="login-links">
                                    <span class="text-white"> Aplikasi Manajemen Asset &copy; 2018</span>
                                </div>

                            </div>

                        </div>

                        <div class="panel panel-info mt8 br-n">

                            <div class="panel-heading heading-border bg-white">
                                <span class="panel-title hidden"><i class="fa fa-sign-in"></i>Register</span>
                                <div class="section row mn">
                                    <div class="col-sm-6">
                                        <a href="{{route('index')}}" class="button btn-social btn-system dark span-left mr5 btn-block">
                                            <span><i class="fa fa-home"></i>
                                            </span>Halaman Awal</a>
                                    </div>
                                    <div class="col-sm-6">
                                        <a href="" class="button btn-social btn-primary dark span-left mr5 btn-block">
                                            <span><i class="fa fa-dashboard"></i>
                                            </span>Web Portal</a>
                                    </div>
                                </div>
                            </div>

                            <!-- end .form-header section -->
                            <form method="post" action="{{URL::to('auth/login')}}" id="contact">
                                <div class="panel-body bg-light p30">
                                    @if (count($errors) > 0)
                                    <div class="row">
                                        <div class="col-sm-12 pr30">
                                            <div class="alert alert-sm alert-border-left alert-danger light alert-dismissable">
                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                                @foreach($errors->all() as $row)
                                                <i class="fa fa-info pr10"></i>{{$row}}<br>
                                                @endforeach                                              
                                            </div>                                            
                                        </div>                                        
                                    </div>
                                    @endif                                    
                                    <div class="row">
                                        <div class="col-sm-8 pr30">
                                            <div class="section">
                                                <label for="username" class="field-label text-muted fs18 mb10">Username</label>
                                                <label for="username" class="field prepend-icon">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}"> 
                                                    <input type="text" name="userid" id="username" class="gui-input" placeholder="Masukkan USERID">
                                                    <label for="username" class="field-icon"><i class="fa fa-user"></i>
                                                    </label>
                                                </label>
                                            </div>
                                            <!-- end section -->
                                            <div class="section">
                                                <label for="username" class="field-label text-muted fs18 mb10">Password</label>
                                                <label for="password" class="field prepend-icon">
                                                    <input type="password" name="password" id="password" class="gui-input" placeholder="Masukkan PASSWORD">
                                                    <label for="password" class="field-icon"><i class="fa fa-lock"></i>
                                                    </label>
                                                </label>
                                            </div>     
                                            <!-- end section -->
                                        </div>
                                        <div class="col-sm-4 br-l br-grey pl30">
                                            <div class="section">
                                                <br>                                               
                                            </div>
                                            <div class="captcha">
                                                <strong>CAPTCHA</strong>
                                            </div>                                           
                                            <div class="section text-center" style="">
                                                <img src="{{ captcha_src('flat') }}" style="padding: 10px;" id="captcha_image">
                                                <a href='javascript:void(0);' id="reload_captcha"><i class="fa fa-refresh"></i></a>
                                                <input type="text" class="gui-input text-center" name="captcha">                                               
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- end .form-body section -->
                                <div class="panel-footer clearfix p10 ph15">
                                    <button type="submit" id="subm" class="button btn-primary mr10 pull-right">Login</button>
                                </div>
                                <!-- end .form-footer section -->
                            </form>
                        </div>
                    </div>

                </section>
                <!-- End: Content -->

            </section>
            <!-- End: Content-Wrapper -->

        </div>
        <!-- End: Main -->

        <!-- BEGIN: PAGE SCRIPTS -->

        <!-- Google Map API 
        <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>

        <!-- jQuery -->
        <script type="text/javascript" src="{{asset('assetlogin/jquery/jquery-1.11.1.min.js')}}"></script>
        <script type="text/javascript" src="{{asset('assetlogin/jquery/jquery_ui/jquery-ui.min.js')}}"></script>
        <script type="text/javascript" src="{{asset('assetlogin/jquery/jquery.ui.touch-punch.min.js')}}"></script>

        <!-- Bootstrap -->
        <script type="text/javascript" src="{{asset('assetlogin/js/bootstrap/bootstrap.min.js')}}"></script>

        <!-- Page Plugins -->
        <script type="text/javascript" src="{{asset('assetlogin/js/login/EasePack.min.js')}}"></script>
        <script type="text/javascript" src="{{asset('assetlogin/js/login/rAF.js')}}"></script>
        <script type="text/javascript" src="{{asset('assetlogin/js/login/TweenLite.min.js')}}"></script>
        <script type="text/javascript" src="{{asset('assetlogin/js/login/login.js')}}"></script>

        <!-- Theme Javascript -->
        <script type="text/javascript" src="{{asset('assetlogin/js/utility.js')}}"></script>
        <script type="text/javascript" src="{{asset('assetlogin/js/main.js')}}"></script>
        <script type="text/javascript" src="{{asset('assetlogin/js/demo.js')}}"></script>

        <!-- Page Javascript -->
        <script type="text/javascript">
            jQuery(document).ready(function () {

                "use strict";

                // Init Theme Core      
                Core.init();

                // Init Demo JS
                //Demo.init();

                // Init CanvasBG and pass target starting location
                CanvasBG.init({
                    Loc: {
                        x: window.innerWidth / 2,
                        y: window.innerHeight / 3.3
                    },
                });
                $('#reload_captcha').click(function (event) {
                    $('#captcha_image').attr('src', $('#captcha_image').attr('src') + '{{ captcha_src() }}');
                });

            });
        </script>
<!--<script type="text/javascript" src="{{asset('assetlogin/captcha/ui.core.js')}}"></script>
<script type="text/javascript" src="{{asset('assetlogin/captcha/ui.sortable.js')}}"></script>-->
        

    </body>

</html>
