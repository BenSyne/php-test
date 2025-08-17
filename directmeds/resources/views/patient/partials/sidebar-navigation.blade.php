<ul class="flex flex-1 flex-col gap-y-7">
    <li>
        <ul class="-mx-2 space-y-1">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('patient.dashboard') }}" class="{{ request()->routeIs('patient.dashboard') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                    <svg class="{{ request()->routeIs('patient.dashboard') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Dashboard
                </a>
            </li>

            <!-- Prescriptions -->
            <li>
                <a href="{{ route('patient.prescriptions') }}" class="{{ request()->routeIs('patient.prescriptions*') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                    <svg class="{{ request()->routeIs('patient.prescriptions*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z" />
                    </svg>
                    My Prescriptions
                </a>
            </li>

            <!-- Orders -->
            <li>
                <a href="{{ route('patient.orders') }}" class="{{ request()->routeIs('patient.orders*') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                    <svg class="{{ request()->routeIs('patient.orders*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    Order History
                </a>
            </li>

            <!-- Refill Requests -->
            <li>
                <a href="{{ route('patient.refills') }}" class="{{ request()->routeIs('patient.refills*') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                    <svg class="{{ request()->routeIs('patient.refills*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Refill Requests
                    @if(isset($pendingRefills) && $pendingRefills > 0)
                        <span class="ml-auto w-5 h-5 text-xs bg-red-600 text-white rounded-full flex items-center justify-center">{{ $pendingRefills }}</span>
                    @endif
                </a>
            </li>

            <!-- Profile -->
            <li>
                <a href="{{ route('patient.profile') }}" class="{{ request()->routeIs('patient.profile*') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                    <svg class="{{ request()->routeIs('patient.profile*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    My Profile
                </a>
            </li>

            <!-- Upload Prescription -->
            <li>
                <a href="{{ route('patient.upload-prescription') }}" class="{{ request()->routeIs('patient.upload-prescription*') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                    <svg class="{{ request()->routeIs('patient.upload-prescription*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    Upload Prescription
                </a>
            </li>
        </ul>
    </li>

    <!-- Second group -->
    <li>
        <div class="text-xs font-semibold leading-6 text-gray-400">Support</div>
        <ul class="-mx-2 mt-2 space-y-1">
            <!-- Messages -->
            <li>
                <a href="{{ route('patient.messages') }}" class="{{ request()->routeIs('patient.messages*') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                    <svg class="{{ request()->routeIs('patient.messages*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                    </svg>
                    Messages
                    @if(isset($unreadMessages) && $unreadMessages > 0)
                        <span class="ml-auto w-5 h-5 text-xs bg-red-600 text-white rounded-full flex items-center justify-center">{{ $unreadMessages }}</span>
                    @endif
                </a>
            </li>

            <!-- Help & Support -->
            <li>
                <a href="{{ route('patient.help') }}" class="{{ request()->routeIs('patient.help*') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                    <svg class="{{ request()->routeIs('patient.help*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c0-1.297.82-2.43 1.96-2.43s1.96 1.133 1.96 2.43c0 .826-.384 1.562-.962 2.036L12 10.5l-.837-.945c-.578-.474-.962-1.21-.962-2.036zm2.879 5.981v3a.75.75 0 01-1.5 0v-3a.75.75 0 011.5 0zm0 0V12a.75.75 0 00-.75-.75h-2.5a.75.75 0 100 1.5h1.75z" />
                    </svg>
                    Help & Support
                </a>
            </li>
        </ul>
    </li>
</ul>