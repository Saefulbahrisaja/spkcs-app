<aside class="w-60 bg-white shadow h-screen">
    <ul class="p-4">

        @php
            $user = auth()->user();
        @endphp

        @if($user)
            @php
                $menu = config('menus.' . $user->role);
            @endphp

            @if($menu)
                <li class="font-bold mb-2">{{ $menu['title'] }}</li>

                @foreach($menu['menus'] as $item)
                    <li>
                        <a href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                    </li>
                @endforeach
            @endif

        @endif

    </ul>
</aside>
