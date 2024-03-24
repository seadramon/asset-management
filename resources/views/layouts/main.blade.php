<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title')</title>

    <!-- Global stylesheets -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="{{ asset('global_assets/css/icons/icomoon/styles.css')}}" rel="stylesheet" type="text/css">
    <link href="{{ asset('global_assets/css/icons/fontawesome/styles.min.css')}}" rel="stylesheet" type="text/css">

    <!-- original -->
    <!-- <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('assets/css/bootstrap_limitless.min.css')}}" rel="stylesheet" type="text/css"> -->
    <!-- ./Original -->

    <!-- Limitless -->
    <link href="{{asset('assets/css/limitless/bootstrap.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('assets/css/limitless/bootstrap_limitless.min.css')}}" rel="stylesheet" type="text/css">
    <!-- ./Limitless -->

    <link href="{{asset('assets/css/layout.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('assets/css/components.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('assets/css/colors.min.css')}}" rel="stylesheet" type="text/css">

    <link href="{{asset('global_assets/css/main.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('global_assets/plugins/sweetalerts/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
    <!-- /global stylesheets -->
    @yield('css')
    <!-- Core JS files -->
    <script src="{{asset('global_assets/js/main/jquery.min.js')}}"></script>
    <script src="{{asset('global_assets/js/main/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('global_assets/js/plugins/loaders/blockui.min.js')}}"></script>
    <script src="{{asset('global_assets/js/plugins/ui/ripple.min.js')}}"></script>
    <script src="{{asset('global_assets/plugins/sweetalerts/sweetalert2.all.min.js')}}"></script>
    <!-- /core JS files -->

    <!-- Theme JS files -->
    <script src="{{asset('global_assets/js/plugins/forms/styling/uniform.min.js')}}"></script>

    <script src="{{asset('assets/js/app.js')}}"></script>
    <!-- /theme JS files -->
    <style type="text/css">
        .font-red{
            color: red;
        }
    </style>
</head>

<body>

    <!-- Main navbar -->
    @include('components.mainnav')
    <!-- /main navbar -->


    <!-- Page content -->
    <div class="page-content">

        <!-- Main sidebar -->
        @include('components.menu')
        <!-- /main sidebar -->

        <!-- Main content -->
        <div class="content-wrapper">

            <!-- Page header -->
            <div class="page-header page-header-light">
                <div class="page-header-content header-elements-md-inline">
                    <div class="d-flex page-title">
                        <h4><span class="font-weight-semibold">@yield('pagetitle')</span></h4>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <!-- /page header -->

            <!-- Content area -->
            <div class="content">
                @yield('content')
            </div>
            <!-- /content area -->

            <!-- Footer -->
            @include('components.footer')
            <!-- /footer -->
        </div>
        <!-- /main content -->
    </div>
    <!-- /page content -->
    @yield('js')
    
    <script type="text/javascript">
        function ConfirmDelete() {
            var x = confirm("Apakah anda yakin akan menghapus data?");
            if (x)
                return true;
            else
                return false;
        }
    </script>
</body>
</html>
