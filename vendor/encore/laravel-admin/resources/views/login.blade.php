<!DOCTYPE html>
<html lang="en">
    <head>
        <title>{{config('admin.title')}} | {{ trans('admin.login') }}</title>
        <base href="{{asset('')}}">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
<!--===============================================================================================-->	
@if(!is_null($favicon = Admin::favicon()))
  <link rel="shortcut icon" href="{{$favicon}}">
  @endif
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/animsition/css/animsition.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="vendor/daterangepicker/daterangepicker.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">
<!--===============================================================================================-->
        <meta name="robots" content="noindex, follow">
    </head>
    <body style="background-color: #666666;">
        <div class="limiter">
            <div class="container-login100">
                <div class="wrap-login100">
                    <form class="login100-form validate-form" action="{{ admin_url('auth/login') }}" method="post">
                        <span class="login100-form-title p-b-43">
                        {{config('admin.name')}}
                        </span>
                        <div class="wrap-input100 validate-input" data-validate="Valid email is required: ex@abc.xyz">
                            <input class="input100" placeholder="{{ trans('admin.username') }}" name="username" value="{{ old('username') }}">
                            <span class="focus-input100"></span>
                            {{-- <span class="label-input100">Email</span> --}}
                        </div>

                        @if($errors->has('username'))
				          @foreach($errors->get('username') as $message)
				            <label style="    color: red;" class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
				          @endforeach
				        @endif

                        <div class="wrap-input100 validate-input" data-validate="Password is required">
                            <input class="input100" type="password" placeholder="{{ trans('admin.password') }}" name="password">
                            <span class="focus-input100"></span>
                        </div>

                        @if($errors->has('password'))
				          @foreach($errors->get('password') as $message)
				            <label style="    color: red;" class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label><br>
				          @endforeach
				        @endif

                        <div class="flex-sb-m w-full p-t-3 p-b-32">
                        	@if(config('admin.auth.remember'))
                        	<div class="contact100-form-checkbox">
                                <input class="input-checkbox100" type="checkbox" name="remember" value="1" {{ (!old('username') || old('remember')) ? 'checked' : '' }}>
                                <label class="label-checkbox100" for="ckb1">
                                {{ trans('admin.remember_me') }}
                                </label>
                            </div>
					        @endif
                        </div>
                        <div class="container-login100-form-btn">
                            <button type="submit" class="login100-form-btn">
                            {{ trans('admin.login') }}
                            </button>
                        </div>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                    <div id="carousel" class="carousel slide login100-more" data-ride="carousel" style="height: 100vh;">
					  <div class="carousel-inner h-100">
					    <div class="carousel-item active h-100">
					      <img class="d-block h-100" src="images/bg-01.jpg" alt="First slide">
					    </div>
					    {{-- <div class="carousel-item h-100">
					      <img class="d-block h-100" src="images/bg-01.jpg" alt="Second slide">
					    </div>
					    <div class="carousel-item h-100">
					      <img class="d-block h-100" src="images/bg-01.jpg" alt="Third slide">
					    </div> --}}
					  </div>
					  <a class="carousel-control-prev" href="#carousel" role="button" data-slide="prev">
					    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
					    <span class="sr-only">Previous</span>
					  </a>
					  <a class="carousel-control-next" href="#carousel" role="button" data-slide="next">
					    <span class="carousel-control-next-icon" aria-hidden="true"></span>
					    <span class="sr-only">Next</span>
					  </a>
					</div>
                    {{-- <div class="login100-more" style="background-image: url('images/bg-01.jpg');"></div> --}}
                </div>
            </div>
        </div>
        
        <!--===============================================================================================-->
	<script src="vendor/jquery/jquery-3.2.1.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/animsition/js/animsition.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/bootstrap/js/popper.js"></script>
	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/select2/select2.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/daterangepicker/moment.min.js"></script>
	<script src="vendor/daterangepicker/daterangepicker.js"></script>
<!--===============================================================================================-->
	<script src="vendor/countdowntime/countdowntime.js"></script>
<!--===============================================================================================-->
	<script src="js/main.js"></script>

        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            
            gtag('config', 'UA-23581568-13');
        </script>
    </body>
</html>