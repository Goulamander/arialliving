@if( isAdminEnd() ) 
<aside id="leftsidebar" class="sidebar h_menu">
    <div class="container">
        
        <div class="row clearfix">
            <div class="col-12">
                <div class="menu">
                    <ul class="list">

                        {{-- Calendar --}}
                        <li{!!setActive('/admin')!!}>
                            <a href="/admin">
                                <span>Calendar</span>
                            </a>
                        </li>
                        {{-- Bookings --}}
                        <li{!!setActive(['bookings', 'booking'])!!}>
                            <a href="/admin/bookings/active">
                                <span>Bookings</span>
                            </a>
                        </li>

                        {{-- Bookable Items --}}
                        <li{!!setActive(['items', 'item'])!!}>  
                            <a href="/admin/items">
                                <span>Bookable Items</span>
                            </a>
                        </li>

                        {{-- Stores --}}
                        @if( Auth::user()->hasRole(['super-admin', 'admin']) )
                        <li{!!setActive(['stores', 'store'])!!}>
                            <a href="/admin/retail-stores">
                                <span>Retail Stores/Deals</span>
                            </a>
                        </li>
                        @endif

                        {{-- Buildings --}}
                        @if( Auth::user()->hasRole(['super-admin', 'admin']) )
                        <li{!!setActive(['buildings', 'building'])!!}>
                            <a href="/admin/buildings">
                                <span>Buildings</span>
                            </a>
                        </li>
                        @endif

                        {{-- Residents --}}
                        @if( ! Auth::user()->isExternal() )
                        <li{!!setActive(['residents', 'resident'])!!}>
                            <a href="/admin/residents">
                                <span>Residents</span>
                            </a>
                        </li> 
                        @endif

                        {{-- Users (Back-End Users) --}}
                        @if( Auth::user()->hasRole(['super-admin', 'admin']) )
                        <li{!!setActive(['users', 'user'])!!}>
                            <a href="/admin/users">
                                <span>Users</span>
                            </a>
                        </li>
                        @endif

                        {{-- Users (Back-End Users) --}}
                        @if( Auth::user()->hasRole(['super-admin', 'admin']) )
                        <li>
                            <a href="/admin/qr-codes">
                                <span>More...</span>
                            </a>
                            {{-- Users (Back-End Users) --}}
                            @if( Auth::user()->hasRole(['super-admin', 'admin']) )
                            <ul class="ml-menu">
                                {{-- Users (Back-End Users) --}}
                                @if( Auth::user()->hasRole(['super-admin', 'admin']) )
                                <li{!!setActive(['qr-codes', 'qr-code'])!!}>
                                    <a href="/admin/qr-codes">
                                        <span>QR Codes</span>
                                    </a>
                                </li>
                                @endif

                                {{-- Media Management (Back-End Users) --}}
                                @if( Auth::user()->hasRole(['super-admin', 'admin']) )
                                <li{!!setActive(['media-management', 'media-management'])!!}>
                                    <a href="/admin/media-management">
                                        <span>Media Management</span>
                                    </a>
                                </li>
                                @endif

                                {{-- Marketing Communications (Back-End Users) --}}
                                @if( Auth::user()->hasRole(['super-admin', 'admin']) )
                                <li{!!setActive(['marketing-communications'])!!}>
                                    <a href="/admin/marketing-communications">
                                        <span>Marketing Communications</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                            @endif
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</aside>
@endif