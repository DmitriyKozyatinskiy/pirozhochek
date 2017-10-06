<div class="Sidebar">
  <a href="{{ secure_url('history') }}" class="Sidebar__Item">History</a>
  <a href="{{ secure_url('devices/show') }}" class="Sidebar__Item">Devices</a>
  <a href="{{ secure_url('account/subscription') }}" class="Sidebar__Item">Subscription</a>
  {{--<a href="{{ secure_url('account/billing') }}" class="Sidebar__Item">Billing</a>--}}
  <a href="{{ secure_url('account/settings') }}" class="Sidebar__Item">Account Settings</a>

  @if (Auth::check() && Auth::user()->isAdmin)
    <a href="{{ secure_url('admin/users') }}" class="Sidebar__Item">Admin Panel</a>
  @endif

  <div class="Sidebar__AdPanel">
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="ca-pub-4770238595923264"
         data-ad-slot="8055149239"
         data-ad-format="vertical">
    </ins>
  </div>
</div>

<script>
  (adsbygoogle = window.adsbygoogle || []).push({});
</script>
