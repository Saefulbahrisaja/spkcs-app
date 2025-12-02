<nav class="bg-blue-700 p-4 text-white flex justify-between items-center shadow-md">
    <div class="font-bold text-xl">Banten-SPABILITY</div>

    <div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded transition duration-200" type="submit">Logout</button>
        </form>
    </div>
</nav>
