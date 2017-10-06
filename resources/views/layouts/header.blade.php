<nav class="Header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-xs-4 col-sm-3 col-md-offset-1 col-lg-2">
        <a href="#" id="js-tray-trigger" class="Tray_TriggerContainer navbar-brand">
          <img alt="Sneekr" title="Sneekr" src="{{ secure_asset('images/tray.png')}}"
               class="Tray__Trigger js-tray-trigger">
        </a>
        <div class="Tray js-tray" id="js-tray">
          @if (Auth::check())
            <a href="{{ secure_url('history') }}" class="Tray__Item">History</a>
            <a href="{{ secure_url('devices/show') }}" class="Tray__Item">Devices</a>
            <a href="{{ secure_url('account/subscription') }}" class="Tray__Item">Subscription</a>
            <a href="{{ secure_url('account/settings') }}" class="Tray__Item">Account Settings</a>
            <a href="{{ secure_url('account/logout') }}" class="Tray__Item js-logout">Logout</a>
          @else
            <a href="{{ secure_url('/login') }}" class="Tray__Item">Login</a>
            <a href="{{ secure_url('/register') }}" class="Tray__Item">Registration</a>
          @endif
        </div>
        <a class="Header__LogoImageContainer" href="{{ secure_url('/') }}">
          <img alt="Sneekr" class="Header__LogoImage" title="Sneekr" src="{{ secure_asset('images/icon48.png') }}">
        </a>
        <a class="Header__Title" href="{{ secure_url('/') }}">
          {{ config('app.name', 'Sneekr') }}
        </a>
      </div>

      <div class="Header__AdContainer col-xs-8 col-sm-5 col-md-4 col-lg-6">
        <div style="width: 100%;">
          <ins class="adsbygoogle"
               style="display:block"
               data-ad-client="ca-pub-4770238595923264"
               data-ad-slot="8055149239"
               data-ad-format="horizontal">
          </ins>
        </div>
      </div>


      <div class="Header__DropdownContainer collapse in col-sm-4 col-md-3 col-lg-2">
        <ul class="nav navbar-nav pull-right">
          @if (Auth::guest())
            <li><a class="Header__Link" href="{{ secure_url('/login') }}">Login</a></li>
            <li><a class="Header__Link" href="{{ secure_url('/register') }}">Registration</a></li>
          @else
            <li class="dropdown">
              <a href="#" class="Header__Link dropdown-toggle Header__DropdownTrigger"
                 data-toggle="dropdown" role="button" aria-expanded="false">
                {{ Auth::user()->name }} <span class="caret"></span>
              </a>
              <ul class="dropdown-menu" role="menu">
                <li>
                  <a href="{{ secure_url('/logout') }}" class="js-logout">Logout</a>
                  <form class="Header__LogoutForm" id="js-logout-form" action="{{ secure_url('/logout') }}" method="POST">
                    {{ csrf_field() }}
                  </form>
                </li>
              </ul>
            </li>
          @endif
        </ul>
      </div>
    </div>
  </div>
</nav>