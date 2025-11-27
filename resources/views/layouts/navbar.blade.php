<nav class="bg-blue-700 p-4 text-white flex justify-between">
    <div class="font-bold text-xl">Banten-SPABILITY</div>

    <div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="bg-red-600 px-3 py-1 rounded">Logout</button>
        </form>
    </div>
</nav>
