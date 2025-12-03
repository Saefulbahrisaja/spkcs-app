<li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#!">Settings</a></li>
                        <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded transition duration-200" type="submit">Logout</button>
                            </form>         
                        </li>
                    </ul>
                </li>
                
