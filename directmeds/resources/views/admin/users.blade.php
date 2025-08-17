@extends('layouts.admin')

@section('title', 'Users Management')

@section('content')
<!-- Page Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 space-y-4 sm:space-y-0">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Users Management</h1>
        <p class="text-gray-500 mt-2">Manage system users and permissions</p>
    </div>
    <div class="flex space-x-3">
        <button class="px-4 py-2 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            Import Users
        </button>
        <button class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl hover:shadow-lg transform hover:-translate-y-0.5 transition-all">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
            Add User
        </button>
    </div>
</div>

<!-- User Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="glass rounded-xl p-4 border border-white/20">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="text-2xl font-bold text-gray-900">1,429</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="glass rounded-xl p-4 border border-white/20">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Patients</p>
                <p class="text-2xl font-bold text-blue-600">1,245</p>
            </div>
            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">87.1%</span>
        </div>
    </div>
    
    <div class="glass rounded-xl p-4 border border-white/20">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Prescribers</p>
                <p class="text-2xl font-bold text-green-600">89</p>
            </div>
            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">6.2%</span>
        </div>
    </div>
    
    <div class="glass rounded-xl p-4 border border-white/20">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Pharmacists</p>
                <p class="text-2xl font-bold text-orange-600">45</p>
            </div>
            <span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">3.1%</span>
        </div>
    </div>
    
    <div class="glass rounded-xl p-4 border border-white/20">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Active Today</p>
                <p class="text-2xl font-bold text-purple-600">234</p>
            </div>
            <div class="flex -space-x-2">
                <div class="w-8 h-8 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full border-2 border-white"></div>
                <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-green-400 rounded-full border-2 border-white"></div>
                <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-red-400 rounded-full border-2 border-white"></div>
                <div class="w-8 h-8 bg-gray-200 rounded-full border-2 border-white flex items-center justify-center">
                    <span class="text-xs font-medium text-gray-600">+231</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="glass rounded-2xl p-4 mb-6 border border-white/20">
    <div class="flex flex-wrap items-center gap-3">
        <select class="px-4 py-2 rounded-lg border border-gray-200 focus:border-purple-500 focus:outline-none">
            <option>All Roles</option>
            <option>Patients</option>
            <option>Prescribers</option>
            <option>Pharmacists</option>
            <option>Admins</option>
        </select>
        <select class="px-4 py-2 rounded-lg border border-gray-200 focus:border-purple-500 focus:outline-none">
            <option>All Status</option>
            <option>Active</option>
            <option>Inactive</option>
            <option>Suspended</option>
            <option>Pending Verification</option>
        </select>
        <select class="px-4 py-2 rounded-lg border border-gray-200 focus:border-purple-500 focus:outline-none">
            <option>Registration Date</option>
            <option>Last 7 days</option>
            <option>Last 30 days</option>
            <option>Last 3 months</option>
        </select>
        <div class="ml-auto relative">
            <input type="text" placeholder="Search users..." class="pl-10 pr-4 py-2 w-64 rounded-lg border border-gray-200 focus:border-purple-500 focus:outline-none">
            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    </div>
</div>

<!-- Users Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Admin User Card -->
    <div class="glass rounded-2xl p-6 border border-purple-200 hover:shadow-xl transition-all hover:-translate-y-1">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                    <span class="text-white font-bold">AD</span>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Admin User</h3>
                    <p class="text-sm text-gray-500">admin@directmeds.com</p>
                </div>
            </div>
            <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-semibold rounded-full">Admin</span>
        </div>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Status:</span>
                <span class="text-green-600 font-medium">Active</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Joined:</span>
                <span class="text-gray-700">Jan 15, 2024</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Last Login:</span>
                <span class="text-gray-700">2 hours ago</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">2FA:</span>
                <span class="text-green-600">Enabled</span>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-200 flex space-x-2">
            <button class="flex-1 px-3 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm">
                View Details
            </button>
            <button class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Pharmacist Card -->
    <div class="glass rounded-2xl p-6 border border-white/20 hover:shadow-xl transition-all hover:-translate-y-1">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center">
                    <span class="text-white font-bold">JS</span>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">John Smith, RPh</h3>
                    <p class="text-sm text-gray-500">john.smith@directmeds.com</p>
                </div>
            </div>
            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">Pharmacist</span>
        </div>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Status:</span>
                <span class="text-green-600 font-medium">Active</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">License:</span>
                <span class="text-gray-700">RPH-123456</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Verifications:</span>
                <span class="text-gray-700">247 today</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Rating:</span>
                <div class="flex items-center">
                    <span class="text-yellow-500">★★★★★</span>
                    <span class="text-gray-600 ml-1">4.9</span>
                </div>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-200 flex space-x-2">
            <button class="flex-1 px-3 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm">
                View Details
            </button>
            <button class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Patient Card -->
    <div class="glass rounded-2xl p-6 border border-white/20 hover:shadow-xl transition-all hover:-translate-y-1">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-teal-500 rounded-full flex items-center justify-center">
                    <span class="text-white font-bold">MJ</span>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Maria Johnson</h3>
                    <p class="text-sm text-gray-500">maria.j@email.com</p>
                </div>
            </div>
            <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded-full">Patient</span>
        </div>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Status:</span>
                <span class="text-green-600 font-medium">Verified</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Member Since:</span>
                <span class="text-gray-700">Mar 2023</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Orders:</span>
                <span class="text-gray-700">23 total</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Insurance:</span>
                <span class="text-blue-600">BlueCross</span>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-200 flex space-x-2">
            <button class="flex-1 px-3 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm">
                View Details
            </button>
            <button class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Prescriber Card -->
    <div class="glass rounded-2xl p-6 border border-white/20 hover:shadow-xl transition-all hover:-translate-y-1">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center">
                    <span class="text-white font-bold">DC</span>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Dr. David Chen, MD</h3>
                    <p class="text-sm text-gray-500">dr.chen@medical.com</p>
                </div>
            </div>
            <span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-semibold rounded-full">Prescriber</span>
        </div>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Status:</span>
                <span class="text-green-600 font-medium">Verified</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">DEA:</span>
                <span class="text-gray-700">BC1234567</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">NPI:</span>
                <span class="text-gray-700">1234567890</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Prescriptions:</span>
                <span class="text-gray-700">342 this month</span>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-200 flex space-x-2">
            <button class="flex-1 px-3 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm">
                View Details
            </button>
            <button class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Suspended User Card -->
    <div class="glass rounded-2xl p-6 border border-red-200 hover:shadow-xl transition-all hover:-translate-y-1 opacity-75">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-gray-400 to-gray-600 rounded-full flex items-center justify-center">
                    <span class="text-white font-bold">RW</span>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Robert Wilson</h3>
                    <p class="text-sm text-gray-500">r.wilson@email.com</p>
                </div>
            </div>
            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">Suspended</span>
        </div>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Status:</span>
                <span class="text-red-600 font-medium">Suspended</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Reason:</span>
                <span class="text-gray-700">Policy violation</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Suspended:</span>
                <span class="text-gray-700">Nov 15, 2024</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Review Date:</span>
                <span class="text-gray-700">Dec 15, 2024</span>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-200 flex space-x-2">
            <button class="flex-1 px-3 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm">
                Review Case
            </button>
            <button class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- New User Card -->
    <div class="glass rounded-2xl p-6 border border-yellow-200 hover:shadow-xl transition-all hover:-translate-y-1">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-full flex items-center justify-center">
                    <span class="text-white font-bold">SL</span>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Susan Lee</h3>
                    <p class="text-sm text-gray-500">susan.lee@email.com</p>
                </div>
            </div>
            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full animate-pulse">New</span>
        </div>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Status:</span>
                <span class="text-yellow-600 font-medium">Pending Verification</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Registered:</span>
                <span class="text-gray-700">2 hours ago</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Documents:</span>
                <span class="text-orange-600">2 pending</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Type:</span>
                <span class="text-gray-700">Patient</span>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-200 flex space-x-2">
            <button class="flex-1 px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                Verify Now
            </button>
            <button class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Pagination -->
<div class="mt-8 flex items-center justify-center">
    <div class="flex items-center space-x-2">
        <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50" disabled>
            Previous
        </button>
        <button class="px-3 py-1 bg-purple-600 text-white rounded-lg">1</button>
        <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100">2</button>
        <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100">3</button>
        <span class="px-2">...</span>
        <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100">48</button>
        <button class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100">
            Next
        </button>
    </div>
</div>
@endsection