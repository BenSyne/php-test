<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class HomeController extends Controller
{
    /**
     * Display the homepage
     */
    public function index()
    {
        // Temporarily use simpler view to test
        return view('test');
    }

    /**
     * Display the about page
     */
    public function about()
    {
        return view('home.about');
    }

    /**
     * Display the services page
     */
    public function services()
    {
        return view('home.services');
    }

    /**
     * Display the contact page
     */
    public function contact()
    {
        return view('home.contact');
    }

    /**
     * Handle contact form submission
     */
    public function submitContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // Here you would typically send an email or store the contact message
        // For now, we'll just return a success message
        
        return back()->with('success', 'Thank you for your message. We will get back to you soon!');
    }

    /**
     * Display the privacy policy
     */
    public function privacy()
    {
        return view('home.privacy');
    }

    /**
     * Display the terms of service
     */
    public function terms()
    {
        return view('home.terms');
    }

    /**
     * Display the FAQ page
     */
    public function faq()
    {
        return view('home.faq');
    }
}