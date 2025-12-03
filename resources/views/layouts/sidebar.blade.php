<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                    @php
                        $user = auth()->user();
                            @endphp
                        @if($user)
                            @php
                                $menu = config('menus.' . $user->role);
                                    @endphp
                            @if($menu)
                            <div class="sb-sidenav-menu-heading">{{ $menu['title'] }}</div>
                                @foreach($menu['menus'] as $item)
                                <a class="nav-link" href="{{ route($item['route']) }}">
                                    <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                                {{ $item['label'] }}
                                </a>
                                @endforeach
                            @endif
                         @endif
                            </div>
            </div>
            <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
                        {{ $menu['title'] }}
            </div>
    </nav>
</div>

