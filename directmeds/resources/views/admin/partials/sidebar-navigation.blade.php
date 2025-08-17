<ul role="list" class="flex flex-1 flex-col gap-y-7">
    <li>
        <ul role="list" class="-mx-2 space-y-1">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('admin.dashboard') }}" 
                   class="@if(request()->routeIs('admin.dashboard')) bg-gray-50 text-blue-600 @else text-gray-700 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                    <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    Dashboard
                </a>
            </li>

            <!-- User Management -->
            <li x-data="{ open: {{ request()->routeIs('admin.users*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="@if(request()->routeIs('admin.users*')) bg-gray-50 text-blue-600 @else text-gray-700 hover:text-blue-600 hover:bg-gray-50 @endif group flex w-full gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                    <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    User Management
                    <svg :class="open ? 'rotate-90' : ''" class="ml-auto h-5 w-5 shrink-0 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                </button>
                <ul x-show="open" x-collapse class="mt-1 px-2">
                    <li>
                        <a href="{{ route('admin.users.index') }}" 
                           class="@if(request()->routeIs('admin.users.index')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            All Users
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.users.create') }}" 
                           class="@if(request()->routeIs('admin.users.create')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            Add User
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.users.roles') }}" 
                           class="@if(request()->routeIs('admin.users.roles')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            Roles & Permissions
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Inventory Management -->
            <li x-data="{ open: {{ request()->routeIs('admin.inventory*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="@if(request()->routeIs('admin.inventory*')) bg-gray-50 text-blue-600 @else text-gray-700 hover:text-blue-600 hover:bg-gray-50 @endif group flex w-full gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                    <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    Inventory
                    <svg :class="open ? 'rotate-90' : ''" class="ml-auto h-5 w-5 shrink-0 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                </button>
                <ul x-show="open" x-collapse class="mt-1 px-2">
                    <li>
                        <a href="{{ route('admin.inventory.index') }}" 
                           class="@if(request()->routeIs('admin.inventory.index')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            All Products
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.inventory.low-stock') }}" 
                           class="@if(request()->routeIs('admin.inventory.low-stock')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            Low Stock Alerts
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.inventory.create') }}" 
                           class="@if(request()->routeIs('admin.inventory.create')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            Add Product
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Order Management -->
            <li x-data="{ open: {{ request()->routeIs('admin.orders*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="@if(request()->routeIs('admin.orders*')) bg-gray-50 text-blue-600 @else text-gray-700 hover:text-blue-600 hover:bg-gray-50 @endif group flex w-full gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                    <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m8.25 4.5V16.5a1.125 1.125 0 011.125-1.125h2.25m-8.25 4.5l4.5-4.5m0 0V14.25m0 2.25l4.5 4.5M21 7.5v11.25m0 0a1.125 1.125 0 01-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V18.75m-2.25 0v-11.25m0 0a1.125 1.125 0 011.125-1.125h2.25a1.125 1.125 0 011.125 1.125v11.25" />
                    </svg>
                    Orders
                    <svg :class="open ? 'rotate-90' : ''" class="ml-auto h-5 w-5 shrink-0 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                </button>
                <ul x-show="open" x-collapse class="mt-1 px-2">
                    <li>
                        <a href="{{ route('admin.orders.index') }}" 
                           class="@if(request()->routeIs('admin.orders.index')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            All Orders
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.orders.pending') }}" 
                           class="@if(request()->routeIs('admin.orders.pending')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            Pending Orders
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.orders.fulfillment') }}" 
                           class="@if(request()->routeIs('admin.orders.fulfillment')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            Fulfillment Queue
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Prescriptions -->
            <li x-data="{ open: {{ request()->routeIs('admin.prescriptions*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="@if(request()->routeIs('admin.prescriptions*')) bg-gray-50 text-blue-600 @else text-gray-700 hover:text-blue-600 hover:bg-gray-50 @endif group flex w-full gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                    <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    Prescriptions
                    <svg :class="open ? 'rotate-90' : ''" class="ml-auto h-5 w-5 shrink-0 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                </button>
                <ul x-show="open" x-collapse class="mt-1 px-2">
                    <li>
                        <a href="{{ route('admin.prescriptions.pending') }}" 
                           class="@if(request()->routeIs('admin.prescriptions.pending')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            Pending Verification
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.prescriptions.index') }}" 
                           class="@if(request()->routeIs('admin.prescriptions.index')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            All Prescriptions
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Reports & Analytics -->
            <li x-data="{ open: {{ request()->routeIs('admin.reports*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="@if(request()->routeIs('admin.reports*')) bg-gray-50 text-blue-600 @else text-gray-700 hover:text-blue-600 hover:bg-gray-50 @endif group flex w-full gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                    <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                    </svg>
                    Reports
                    <svg :class="open ? 'rotate-90' : ''" class="ml-auto h-5 w-5 shrink-0 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                </button>
                <ul x-show="open" x-collapse class="mt-1 px-2">
                    <li>
                        <a href="{{ route('admin.reports.index') }}" 
                           class="@if(request()->routeIs('admin.reports.index')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            Analytics Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.sales') }}" 
                           class="@if(request()->routeIs('admin.reports.sales')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            Sales Reports
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.inventory') }}" 
                           class="@if(request()->routeIs('admin.reports.inventory')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            Inventory Reports
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Compliance -->
            <li>
                <a href="{{ route('compliance.dashboard') }}" 
                   class="@if(request()->routeIs('compliance.*')) bg-gray-50 text-blue-600 @else text-gray-700 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                    <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    Compliance
                </a>
            </li>

            <!-- System Settings -->
            <li x-data="{ open: {{ request()->routeIs('admin.settings*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="@if(request()->routeIs('admin.settings*')) bg-gray-50 text-blue-600 @else text-gray-700 hover:text-blue-600 hover:bg-gray-50 @endif group flex w-full gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                    <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                    <svg :class="open ? 'rotate-90' : ''" class="ml-auto h-5 w-5 shrink-0 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                </button>
                <ul x-show="open" x-collapse class="mt-1 px-2">
                    <li>
                        <a href="{{ route('admin.settings.general') }}" 
                           class="@if(request()->routeIs('admin.settings.general')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            General Settings
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.notifications') }}" 
                           class="@if(request()->routeIs('admin.settings.notifications')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            Notifications
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.settings.integrations') }}" 
                           class="@if(request()->routeIs('admin.settings.integrations')) bg-gray-50 text-blue-600 @else text-gray-600 hover:text-blue-600 hover:bg-gray-50 @endif group flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6">
                            Integrations
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </li>

    <!-- Footer section -->
    <li class="mt-auto">
        <div class="border-t border-gray-200 pt-4">
            <div class="flex items-center gap-x-3 px-2">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-xs font-medium text-gray-700">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ ucfirst(auth()->user()->user_type) }}</p>
                </div>
            </div>
        </div>
    </li>
</ul>