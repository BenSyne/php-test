<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prescription;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    /**
     * Display the patient dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get dashboard statistics
        $activePrescriptions = Prescription::where('patient_id', $user->id)
            ->where('verification_status', 'verified')
            ->count();
            
        $pendingOrders = Order::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing', 'shipped'])
            ->count();
            
        $refillsDue = Prescription::where('patient_id', $user->id)
            ->where('verification_status', 'verified')
            ->where('refills_remaining', '>', 0)
            ->whereDate('next_refill_date', '<=', now()->addDays(7))
            ->count();
            
        $unreadMessages = 0; // Placeholder for future messaging system
        
        return view('dashboards.patient', compact(
            'activePrescriptions', 
            'pendingOrders', 
            'refillsDue', 
            'unreadMessages'
        ));
    }

    /**
     * Display patient prescriptions
     */
    public function prescriptions()
    {
        $user = Auth::user();
        
        $prescriptions = Prescription::where('patient_id', $user->id)
            ->with(['prescriber', 'product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('patient.prescriptions.index', compact('prescriptions'));
    }

    /**
     * Display prescription details
     */
    public function showPrescription(Prescription $prescription)
    {
        // Ensure the prescription belongs to the authenticated user
        if ($prescription->patient_id !== Auth::id()) {
            abort(403);
        }
        
        $prescription->load(['prescriber', 'product', 'auditLogs']);
        
        return view('patient.prescriptions.show', compact('prescription'));
    }

    /**
     * Display patient orders
     */
    public function orders()
    {
        $user = Auth::user();
        
        $orders = Order::where('user_id', $user->id)
            ->with(['items.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('patient.orders.index', compact('orders'));
    }

    /**
     * Display order details
     */
    public function showOrder(Order $order)
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }
        
        $order->load(['items.product', 'payment']);
        
        return view('patient.orders.show', compact('order'));
    }

    /**
     * Display refill requests
     */
    public function refills()
    {
        $user = Auth::user();
        
        // Get prescriptions eligible for refill
        $eligiblePrescriptions = Prescription::where('patient_id', $user->id)
            ->where('verification_status', 'verified')
            ->where('refills_remaining', '>', 0)
            ->with(['prescriber', 'product'])
            ->get();
            
        // Get recent refill requests
        $recentRefills = Order::where('user_id', $user->id)
            ->where('order_type', 'refill')
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        return view('patient.refills.index', compact('eligiblePrescriptions', 'recentRefills'));
    }

    /**
     * Request a refill for a prescription
     */
    public function requestRefill(Request $request, Prescription $prescription)
    {
        // Ensure the prescription belongs to the authenticated user
        if ($prescription->patient_id !== Auth::id()) {
            abort(403);
        }
        
        // Validate refill eligibility
        if ($prescription->verification_status !== 'verified' || $prescription->refills_remaining <= 0) {
            return back()->with('error', 'This prescription is not eligible for refill.');
        }
        
        // Create a refill order (simplified for demo)
        $user = Auth::user();
        $amount = 29.99; // Default prescription price for demo
        
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'REF' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
            'status' => 'pending',
            'order_type' => 'refill',
            'subtotal' => $amount,
            'total_amount' => $amount,
            'prescription_id' => $prescription->id,
            'customer_info' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->profile->phone ?? ''
            ],
            'billing_address' => [
                'line1' => $user->profile->address_line_1 ?? '',
                'line2' => $user->profile->address_line_2 ?? '',
                'city' => $user->profile->city ?? '',
                'state' => $user->profile->state ?? '',
                'zip' => $user->profile->zip_code ?? ''
            ],
            'shipping_address' => [
                'line1' => $user->profile->address_line_1 ?? '',
                'line2' => $user->profile->address_line_2 ?? '',
                'city' => $user->profile->city ?? '',
                'state' => $user->profile->state ?? '',
                'zip' => $user->profile->zip_code ?? ''
            ]
        ]);
        
        return redirect()->route('patient.orders.show', $order)
            ->with('success', 'Refill request submitted successfully. You will receive confirmation within 24 hours.');
    }

    /**
     * Display profile page
     */
    public function profile()
    {
        $user = Auth::user();
        $user->load('profile');
        
        return view('patient.profile.show', compact('user'));
    }

    /**
     * Show profile edit form
     */
    public function editProfile()
    {
        $user = Auth::user();
        $user->load('profile');
        
        return view('patient.profile.edit', compact('user'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
        ]);
        
        // Update user basic info
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        
        // Update or create profile
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
            ]
        );
        
        return redirect()->route('patient.profile')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Display prescription upload form
     */
    public function uploadPrescription()
    {
        return view('patient.upload-prescription');
    }

    /**
     * Handle prescription upload
     */
    public function storePrescription(Request $request)
    {
        $request->validate([
            'prescription_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Handle file upload (simplified for demo)
        $file = $request->file('prescription_file');
        $filename = 'prescription_' . Auth::id() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('prescriptions', $filename, 'private');
        
        // For demo purposes, we'll create a basic record
        // In production, this would integrate with prescription verification workflow
        
        return redirect()->route('patient.prescriptions')
            ->with('success', 'Prescription uploaded successfully. Our pharmacist will review it within 24 hours.');
    }

    /**
     * Display messages (placeholder)
     */
    public function messages()
    {
        return view('patient.messages.index');
    }

    /**
     * Display help page (placeholder)
     */
    public function help()
    {
        return view('patient.help.index');
    }
}