

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Multban - Login</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css')}}">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="{{ asset('assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('assets/dist/css/multban.min.css')}}">

  <!--ICONE PAGINA-->
  <link rel="icon" type="image/png"
  href="{{ asset('assets/dist/img/logo-amarela-min.png')}}">

</head>
<body class="hold-transition login-page">
<div class="login-box">
  <!-- /.login-logo -->
   <!--COR DA BORDA DO CONTAINER-->
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <!--LOGO LOGIN-->
      <img src="{{ asset('assets/dist/img/logo-multban-completa.png')}}" alt="Logo multban"
      style="width: 220px;
      padding: 10px;
      padding-right: 9px;
      padding-left: 6px;
      -webkit-transform: scale(1.3);
      -ms-transform: scale(1.3);
      transform: scale(1.3);">
    </div>
    <div class="card-body">
      <p class="login-box-msg">Faça login para iniciar sua sessão</p>


      @if ($errors->any())
          <div>
              <div class="text-danger">
                  {{ __('Oops! Algo deu errado.') }}
              </div>

              <ul class="mt-3 list-disc list-inside text-sm text-danger">
                  @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                  @endforeach
              </ul>
          </div>
      @endif
      <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="input-group mb-3 input-group-sm">
          <input id="user_email" type="email" name="user_email" value="{{old('user_email')}}" required autofocus class="form-control  form-control-sm" placeholder="Email">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>

        <div class="input-group mb-3 input-group-sm">
          <input type="password" id="user_pass"
          class="form-control  form-control-sm"
          name="user_pass"
          required autocomplete="current-password"
          placeholder="{{__('Password')}}">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">

              <input type="checkbox" id="remember_me" name="remember">
              <label for="remember_me">
                {{ __('Remember me') }}
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-secundary-multban btn-block">{{ __('Log in') }}</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <p class="mb-1">
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}">
                {{ __('Forgot your password?') }}
            </a>
        @endif
      </p>
      {{--<p class="mb-0">
        <a href="{{ route('register') }}" class="text-center">{{ __('Register')}}</a>
      </p>--}}
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="{{asset('assets/plugins/jquery/jquery.min.js')}}"></script>
<!-- Bootstrap 4 -->
<script src="{{asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<!-- multban App -->
<script src="{{asset('assets/dist/js/multban.min.js')}}"></script>
</body>
</html>
