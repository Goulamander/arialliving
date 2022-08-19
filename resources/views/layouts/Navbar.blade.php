
<nav class="top_navbar {{$is_front_layout? '' : 'active'}} {{isAdminEnd() ?  '' : '_resident'}}">
    <div class="container">
        <div class="row clearfix">
            <div class="col-12">
                <div class="navbar-logo">
                    <a href="javascript:void(0);" class="bars"></a>
                    <a class="navbar-brand" href="@if( $is_resident ) / @else /admin @endif">
                        <img src="/img/logo.png" width="150" alt="Aria Living">
                    </a>
                </div>

                @if( ! isAdminEnd() ) 
                <ul class="list">
                    <li{!!setActive('/')!!}>
                        <a href="/" class="waves-effect waves-block">
                            <i class="icon-home"></i><span>Home</span>
                        </a>
                    </li>
                    @if( ! $is_preview )
                    <li{!!setActive('/my-bookings')!!}>
                        <a href="/my-bookings" class="waves-effect waves-block myBookings">
                            <i class="icon-calendar"></i><span>My Bookings</span>
                            @php $active_bookings = Auth::user()->countActiveBookings() @endphp
                            @if($active_bookings > 0)
                            <span class="badge">{{$active_bookings}}</span>
                            @endif
                        </a>
                    </li>
                    @endif
                    <li{!!setActive('/my-building')!!}>
                        <a href="/my-building" class="waves-effect waves-block">
                            <i class="icon-pointer"></i><span>My Building</span>
                        </a>
                    </li>
                    <li{!!setActive('/retail-deals')!!}>
                        <a href="/retail-deals" class="waves-effect waves-block">
                            <i class="icon-present"></i><span>Retail Deals</span>
                        </a>
                    </li>
                </ul>
                @endif
                <ul class="nav navbar-nav">
                    <li class="dropdown profile">
                        <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button">
                            <span class="initials">{{ initials(Auth::user()->first_name.' '.Auth::user()->last_name) }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li>
                                <div class="user-info">
                                    <h6 class="user-name mb-0">{{Auth::user()->first_name}} {{Auth::user()->last_name}}</h6>
                                    <p class="user-position mb-0">{{ Auth::user()->role->display_name }}</p>
                                    @if( $is_resident || Auth::user()->isBuildingManager() )
                                    <p class="user-building light">{{ Auth::user()->building->first()->name ?? '' }}</p>
                                    @endif
                                    <hr>
                                </div>
                            </li>                            
                            <li>
                                @if( $is_resident )
                                <a href="/profile">
                                    <i class="icon-user m-r-10"></i><span>My Profile</span>
                                </a>
                                @else
                                <a href="/admin/profile">
                                    <i class="icon-user m-r-10"></i><span>My Profile</span>
                                </a>
                                @endif
                            </li>   
                            @if( Auth::user()->isSuperAdmin() )
                            <li>
                                <a href="/admin/settings">
                                    <i class="icon-settings m-r-10"></i><span>Settings</span>
                                </a>
                            </li> 
                            @endif                      
                            <li>
                                <a href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="icon-power m-r-10"></i><span>Sign Out</span>
                                </a>
                                <form autocomplete="off" id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none">{{ csrf_field() }}</form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>        
    </div>
</nav>