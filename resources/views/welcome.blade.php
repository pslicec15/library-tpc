@include('partials.header')
<style>
        body {
            background-image: url('https://scontent.xx.fbcdn.net/v/t1.15752-9/459538900_861411086139340_3399122342212788212_n.jpg?_nc_cat=101&ccb=1-7&_nc_sid=0024fc&_nc_eui2=AeEn3dKJhHjhpV6nE-xF68PBEb3WH6j7BfQRvdYfqPsF9MCZmjMNKXz26h_sCGNzBRjexrL0kEaFfRsm-PY6OHr9&_nc_ohc=qfTMpAd_IXcQ7kNvgHmuZHc&_nc_ad=z-m&_nc_cid=0&_nc_ht=scontent.xx&_nc_gid=AM0pXSRcNbvraRf-cxw6dKu&oh=03_Q7cD1QGZTQd5RIQp-THqCfz1WqbvhhW5nRQ4Z-Vq6lh2Vxl0Mw&oe=6720EC4D');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
</style>
<body class="hold-transition login-page">
    <div class="login-box">
        <!-- /.login-logo -->
        <div class="card card-outline card-success">
            <div class="card-header">
                <div class="row justify-content-center align-items-center">
                    <div class="col-3">
                        <img src="../../dist/img/tpc_logo.png" class="img-fluid" alt="TPC Logo">
                    </div>
                    <div class="col-8 pt-3">
                        <p class="text-md font-weight-bold"><span class="font-weight-light text-sm">Talibon Polytechnic
                                College</span><br>Library Management System</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <p class="login-box-msg">Sign in to your account</p>
                <form action="/login/process" method="post" id="login-form">
                    @csrf
                    <div class="form-group">
                        <div class="input-group mb-3">
                            <input type="text" name="username" class="form-control" placeholder="Username">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group mb-3">
                            <input type="password" name="password" class="form-control" placeholder="Password">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-2 mb-3" id="sign-in-button">
                        <button type="submit" class="btn btn-block btn-success">Sign in</a>
                    </div>
                    <a href="mailto:razelhunt@gmail.com" target=self>
                        Contact Us
                    </a>
                </form>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.login-box -->
        @include('partials.footer')
