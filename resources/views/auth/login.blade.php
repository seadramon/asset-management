<!DOCTYPE html>
<html>

    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>SEKRETARIAT | Login</title>

        <link href="{{asset('css/bootstrap.min.css')}}" rel="stylesheet">
        <link href="{{asset('font-awesome/css/font-awesome.css')}}" rel="stylesheet">

        <link href="{{asset('css/animate.css')}}" rel="stylesheet">
        <link href="{{asset('css/style.css')}}" rel="stylesheet">

    </head>

    <body class="gray-bg">

        <div class="middle-box text-center loginscreen animated fadeInDown">
            <div>
                <div>

                    <h1 class="logo-name">PDAM</h1>

                </div>
                <h3>Selamat Datang</h3>
                <p>Aplikasi Sekretariat
                    <!--Continually expanded and constantly improved Inspinia Admin Them (IN+)-->
                </p>
                <p>Login in.</p>
                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        {{var_dump($errors->all())}}
                    </ul>
                </div>
                @endif
                <form class="m-t" method="POST" role="form" action="{{URL::to('auth/login')}}">
                    <div class="form-group">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">  
                        <input type="text" name="userid" class="form-control" placeholder="Username" required="">
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="Password" required="">
                    </div>
                    <button type="submit" class="btn btn-primary block full-width m-b">Login</button>                    
                </form>
                <p class="m-t"> <small>TSI PDAM &copy; 2015</small> </p>
            </div>
        </div>

        <!-- Mainly scripts -->
        <script src="{{asset('js/jquery-2.1.1.js')}}"></script>
        <script src="{{asset('js/bootstrap.min.js')}}"></script>

    </body>

</html>
