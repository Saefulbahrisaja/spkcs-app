<aside class="w-60 bg-white shadow h-screen overflow-y-auto">
    <ul class="p-4 space-y-2">

        @php
            $user = auth()->user();
        @endphp

        @if($user)
            @php
                $menu = config('menus.' . $user->role);
            @endphp

            @if($menu)
                <li class="font-bold mb-4 text-gray-800">{{ $menu['title'] }}</li>

                @foreach($menu['menus'] as $item)
                    <li>
                        <a href="{{ route($item['route']) }}" class="block px-3 py-2 text-gray-700 hover:bg-blue-100 hover:text-blue-800 rounded transition duration-200">{{ $item['label'] }}</a>
                    </li>
                @endforeach
            @endif

        @endif

    </ul>
</aside>
