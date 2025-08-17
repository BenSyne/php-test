@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Settings</h1>
    <p class="text-gray-500 mt-2">Manage your pharmacy system configuration</p>
</div>

<!-- Settings Navigation -->
<div class="glass rounded-2xl p-1 mb-8 inline-flex">
    <button class="px-4 py-2 bg-purple-600 text-white rounded-xl">General</button>
    <button class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-xl">Notifications</button>
    <button class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-xl">Security</button>
    <button class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-xl">Integrations</button>
    <button class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-xl">Billing</button>
</div>

<!-- General Settings -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Settings Form -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Business Information -->
        <div class="glass rounded-2xl p-6 border border-white/20">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Business Information</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pharmacy Name</label>
                    <input type="text" value="Direct Meds Pharmacy" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">DEA Number</label>
                        <input type="text" value="BM1234567" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NPI Number</label>
                        <input type="text" value="1234567890" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">License Number</label>
                    <input type="text" value="PH-2024-98765" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Business Address</label>
                    <textarea rows="3" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200">123 Medical Plaza
Suite 100
Los Angeles, CA 90210</textarea>
                </div>
            </div>
        </div>
        
        <!-- Contact Information -->
        <div class="glass rounded-2xl p-6 border border-white/20">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Contact Information</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" value="(555) 123-4567" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fax Number</label>
                        <input type="tel" value="(555) 123-4568" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" value="contact@directmeds.com" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Support Email</label>
                    <input type="email" value="support@directmeds.com" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200">
                </div>
            </div>
        </div>
        
        <!-- Operating Hours -->
        <div class="glass rounded-2xl p-6 border border-white/20">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Operating Hours</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Monday - Friday</span>
                    <div class="flex items-center space-x-2">
                        <input type="time" value="08:00" class="px-3 py-1 border border-gray-200 rounded-lg">
                        <span class="text-gray-500">to</span>
                        <input type="time" value="20:00" class="px-3 py-1 border border-gray-200 rounded-lg">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Saturday</span>
                    <div class="flex items-center space-x-2">
                        <input type="time" value="09:00" class="px-3 py-1 border border-gray-200 rounded-lg">
                        <span class="text-gray-500">to</span>
                        <input type="time" value="18:00" class="px-3 py-1 border border-gray-200 rounded-lg">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Sunday</span>
                    <div class="flex items-center space-x-2">
                        <input type="time" value="10:00" class="px-3 py-1 border border-gray-200 rounded-lg">
                        <span class="text-gray-500">to</span>
                        <input type="time" value="16:00" class="px-3 py-1 border border-gray-200 rounded-lg">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- System Preferences -->
        <div class="glass rounded-2xl p-6 border border-white/20">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">System Preferences</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">Automatic Refill Reminders</p>
                        <p class="text-sm text-gray-500">Send reminders to patients when refills are due</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">Drug Interaction Warnings</p>
                        <p class="text-sm text-gray-500">Alert pharmacists of potential drug interactions</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">Inventory Alerts</p>
                        <p class="text-sm text-gray-500">Notify when stock levels are low</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">HIPAA Audit Logging</p>
                        <p class="text-sm text-gray-500">Track all patient data access for compliance</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked disabled class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600 cursor-not-allowed"></div>
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Quick Stats -->
        <div class="glass rounded-2xl p-6 border border-white/20">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">System Status</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Version</span>
                    <span class="text-sm font-medium text-gray-900">v2.4.1</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Last Updated</span>
                    <span class="text-sm font-medium text-gray-900">Dec 15, 2024</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Database</span>
                    <span class="text-sm font-medium text-green-600">Connected</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">API Status</span>
                    <span class="text-sm font-medium text-green-600">Operational</span>
                </div>
            </div>
        </div>
        
        <!-- License Information -->
        <div class="glass rounded-2xl p-6 border border-white/20">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">License Information</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">Pharmacy License</p>
                    <p class="text-sm font-medium text-gray-900">Valid until Mar 31, 2025</p>
                    <div class="mt-2 w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-green-500 rounded-full" style="width: 75%"></div>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-600">DEA Registration</p>
                    <p class="text-sm font-medium text-gray-900">Valid until Jun 30, 2025</p>
                    <div class="mt-2 w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-green-500 rounded-full" style="width: 85%"></div>
                    </div>
                </div>
                <button class="w-full mt-4 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    Renew Licenses
                </button>
            </div>
        </div>
        
        <!-- Danger Zone -->
        <div class="glass rounded-2xl p-6 border border-red-200 bg-red-50/50">
            <h3 class="text-lg font-semibold text-red-900 mb-4">Danger Zone</h3>
            <div class="space-y-3">
                <button class="w-full px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-100">
                    Clear Cache
                </button>
                <button class="w-full px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-100">
                    Reset Settings
                </button>
                <button class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Export All Data
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Save Button -->
<div class="mt-8 flex justify-end space-x-3">
    <button class="px-6 py-2 border border-gray-300 rounded-xl hover:bg-gray-50">
        Cancel
    </button>
    <button class="px-6 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl hover:shadow-lg transform hover:-translate-y-0.5 transition-all">
        Save Changes
    </button>
</div>
@endsection