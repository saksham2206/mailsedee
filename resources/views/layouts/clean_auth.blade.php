<!DOCTYPE html>
<html lang="en">
<head>
  <title>@yield('title') - {{ \Acelle\Model\Setting::get("site_name") }}</title>
  @include('layouts._favicon')
  @include('layouts._head')
  <!-- Global stylesheets -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lato:wght@700&display=swap" rel="stylesheet">
  <link href="{{ URL::asset('assetsnew/css/config/modern/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
  <link href="{{ URL::asset('assetsnew/css/config/modern/app.min.css') }}" rel="stylesheet" type="text/css">
  <link href="{{ URL::asset('assetsnew/css/icons.min.css') }}" rel="stylesheet" type="text/css">
  <!-- <link href="{{ URL::asset('css/app.css') }}?v={{ app_version() }}" rel="stylesheet" type="text/css"> -->
  <!-- /global stylesheets -->
  <!-- Core JS files -->
  <script type="text/javascript" src="{{ URL::asset('assetsnew/js/vendor.min.js') }}"></script>
  <script type="text/javascript" src="{{ URL::asset('assetsnew/js/app.min.js') }}"></script>
      <!-- <script type="text/javascript" src="{{ URL::asset('assetsnew/js/core/libraries/bootstrap.min.js') }}"></script>
         <script type="text/javascript" src="{{ URL::asset('assetsnew/js/plugins/loaders/blockui.min.js') }}"></script>
         <script type="text/javascript" src="{{ URL::asset('assetsnew/js/plugins/ui/nicescroll.min.js') }}"></script>
         <script type="text/javascript" src="{{ URL::asset('assetsnew/js/plugins/ui/drilldown.js') }}"></script>
         <script type="text/javascript" src="{{ URL::asset('assetsnew/js/plugins/notifications/sweet_alert.min.js') }}"></script> -->
         <!-- /core JS files -->
         <!-- Theme JS files -->
      <!--    <script type="text/javascript" src="{{ URL::asset('assetsnew/js/plugins/forms/styling/uniform.min.js') }}"></script>
       <script type="text/javascript" src="{{ URL::asset('assetsnew/js/core/app.js') }}"></script> -->
       <!-- /theme JS files -->
      <!-- <script>
         $(function() {
         
             // Style checkboxes and radios
             $('.styled').uniform();
         
         });
     </script> -->
     <link href="{{ URL::asset('assets/css/components.css') }}" rel="stylesheet" type="text/css">
     <link href="{{ URL::asset('css/mc.css') }}?v={{ app_version() }}" rel="stylesheet" type="text/css">
     <link href="{{ URL::asset('assetsnew/css/newAuth.css') }}?v={{ app_version() }}" rel="stylesheet" type="text/css">
     <!-- display flash message -->
     @include('common.flash')
 </head>
 <body class="loading auth-fluid-pages pb-0">
  <div class="auth-fluid">
   <!--Auth fluid left content -->
   <div class="auth-fluid-form-box scroller_sideform">
    <div class="login_home d-flex h-100">
     <div class="card-body">
      <!-- Logo -->
      <div class="auth-brand text-center text-lg-start">
       <div class="auth-logo">
        <a href="{{url('/')}}" class="logo logo-dark text-center">
         <span class="logo-lg">
          <!--    <img src="{{ URL::asset('assetsnew/images/logo-dark.png') }}" alt="" height="22"> -->
          <img src="https://seed.stagingwebsite.space/wp-content/uploads/2021/12/logo-black.png" alt="" >
      </span>
  </a>
  <a href="{{url('/')}}" class="logo logo-light text-center">
    <span class="logo-lg">
        <img src="https://seed.stagingwebsite.space/wp-content/uploads/2021/12/logo-black.png" alt="" >
    </span>
</a>
</div>
</div>
@yield('content')
</div>
<!-- end .card-body -->
</div>
<!-- end .align-items-center.d-flex.h-100-->
</div>
<!-- end auth-fluid-form-box-->
<!-- Auth fluid right content -->
<div class="home_login_box">
<div class="auth-fluid-right text-center" style="background-color:#fff;">
    <div class="auth-user-testimonial">
     <h1>Reach your next lead with SENDE!</h1>
               <!-- 
                  <div class="testimonial_wrap">
                      <h3>The most comprehensive <br> cold email tool, ever!</h3>
                  </div>
              -->
          </div>
          <!-- end auth-user-testimonial-->
          <!-- Swiper -->

          <!-- Slider main container -->
          <div class="slider_preview">
            <div class="slider_image"><img class="img img-responsive" src="{{url('/images/Saly-3.png')}}" style="max-width: 100%;height: 500px;"></div>
            <!-- <h2 class="quotes">Automated Scheduling</h2>
            <h2 class="quotes">Powerful Analytics </h2>
            <h2 class="quotes">One-click Scheduling</h2> -->
          </div>

      </div>   
        </div>
      <script>
        function addButtonLoadingEffect(button) {
            button.addClass('button-loading');
            button.append('<div class="loader"></div>');
        }

        function removeButtonLoadingEffect(button) {
            button.removeClass('button-loading');
            button.find('.loader').remove();
        }
        $('.login-button').on('click', function(e) {
            e.preventDefault();
            
            $(this).html('{{ trans('messages.login.please_wait') }}');
            
            $(this).closest('form').addClass('loading');
            
            addButtonLoadingEffect($(this));
            
            $(this).closest('form').submit();
        });

        @if (request()->session()->get('user-not-activated'))
        @php
        $uid = request()->session()->get('user-not-activated');
        @endphp
        $(document).ready(function() {
                    // Success alert
                    swal({
                        title: "You Have Not Activated Your Account Please activate account first",
                        text: "<a href='{{ action('UserController@resendActivationEmail', ['uid' => $uid]) }}' class='btn bg-teal-800'>{{ trans('messages.resend_activation_email') }}</a>",
                        confirmButtonColor: "#00695C",
                        type: "warning",
                        allowOutsideClick: true,
                        confirmButtonText: "{{ trans('messages.ok') }}",
                        customClass: "swl-warning",
                        html:true
                    });

                });
        <?php request()->session()->forget('user-not-activated'); ?>
        @endif    
    </script>
    <!-- Text Animation -->
    <script>
        (function() {

  var quotes = $(".quotes");
  var quoteIndex = -1;

  function showNextQuote() {
    ++quoteIndex;
    quotes.eq(quoteIndex % quotes.length)
      .fadeIn(500)
      .delay(500)
      .fadeOut(500, showNextQuote);
  }

  showNextQuote();

})();
    </script>
    <!-- Text Animation End  -->      
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</div>
@include('layouts._js')
</body>
</html>
