<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Plus36 School Management System - Complete School Management System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .hover-scale {
            transition: transform 0.3s ease;
        }
        .hover-scale:hover {
            transform: translateY(-10px);
        }
        .pricing-card {
            transition: all 0.3s ease;
        }
        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .popular-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                            </svg>
                        </div>
                        <span class="ml-2 text-xl font-bold text-gray-800">Plus36 Networks SMS</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="#features" class="text-gray-700 hover:text-indigo-600 transition">Features</a>
                    <a href="#pricing" class="text-gray-700 hover:text-indigo-600 transition">Pricing</a>
                    {{-- <a href="#contact" class="text-gray-700 hover:text-indigo-600 transition">Contact</a> --}}
                    <a href="{{ route('login') }}" class="px-4 py-2 text-indigo-600 border border-indigo-600 rounded-lg hover:bg-indigo-50 transition">Login</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-md">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-24 pb-16 gradient-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="text-white">
                    <h1 class="text-5xl font-bold mb-6 leading-tight">
                        Modern School Management System for the Digital Age
                    </h1>
                    <p class="text-xl mb-8 opacity-90">
                        Streamline your school operations with our comprehensive SAAS platform. Manage students, teachers, results, and payments all in one place.
                    </p>
                    <div class="flex space-x-4">
                        <a href="{{ route('register') }}" class="px-8 py-3 bg-white text-indigo-600 rounded-lg font-semibold hover:bg-gray-100 transition shadow-lg">
                            Start Free Trial
                        </a>
                        <a href="#features" class="px-8 py-3 border-2 border-white text-white rounded-lg font-semibold hover:bg-white hover:text-indigo-600 transition">
                            Learn More
                        </a>
                    </div>
                    <div class="mt-8 flex items-center space-x-6">
                        <div class="flex -space-x-2">
                            <div class="w-10 h-10 rounded-full bg-white bg-opacity-30 flex items-center justify-center text-white font-bold">✓</div>
                            <div class="w-10 h-10 rounded-full bg-white bg-opacity-30 flex items-center justify-center text-white font-bold">✓</div>
                            <div class="w-10 h-10 rounded-full bg-white bg-opacity-30 flex items-center justify-center text-white font-bold">✓</div>
                        </div>
                        <span class="text-sm">Trusted by 500+ schools</span>
                    </div>
                </div>
                <div class="relative">
                    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                        <div class="bg-gray-800 px-4 py-2 flex items-center space-x-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        </div>
                        <img src="https://placehold.co/600x400/6366f1/white?text=Dashboard+Preview" alt="Dashboard Preview" class="w-full">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Powerful Features for Modern Education</h2>
                <p class="text-xl text-gray-600">Everything you need to manage your school efficiently</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="p-6 bg-gray-50 rounded-xl hover-scale shadow-md">
                    <div class="w-16 h-16 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Result Management</h3>
                    <p class="text-gray-600">Teachers can easily upload and manage test and exam scores for each subject and class.</p>
                </div>
                <div class="p-6 bg-gray-50 rounded-xl hover-scale shadow-md">
                    <div class="w-16 h-16 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm0 0v4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Fee Management</h3>
                    <p class="text-gray-600">Parents can pay school fees online with multiple payment methods and track payment history.</p>
                </div>
                <div class="p-6 bg-gray-50 rounded-xl hover-scale shadow-md">
                    <div class="w-16 h-16 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Parent Portal</h3>
                    <p class="text-gray-600">Parents get real-time access to their children's academic progress and performance.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 bg-gradient-to-br from-gray-50 to-indigo-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Simple, Transparent Pricing</h2>
                <p class="text-xl text-gray-600">Choose the perfect plan for your school</p>
                <div class="mt-4 inline-flex items-center space-x-2 bg-white rounded-lg p-1 shadow-sm">
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium">Monthly</button>
                    <button class="px-4 py-2 text-gray-600 hover:text-indigo-600 rounded-md text-sm font-medium">Yearly <span class="text-green-500 text-xs">Save 20%</span></button>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Basic Plan -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden pricing-card relative">
                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Basic</h3>
                        <p class="text-gray-500 mb-4">Perfect for small schools</p>
                        <div class="mb-6">
                            <span class="text-4xl font-bold text-gray-900">$49</span>
                            <span class="text-gray-500">/month</span>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Up to 200 students
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Up to 10 teachers
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Basic result management
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Email support
                            </li>
                            <li class="flex items-center text-gray-400">
                                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Advanced analytics
                            </li>
                            <li class="flex items-center text-gray-400">
                                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                API access
                            </li>
                        </ul>
                        <a href="{{ route('register') }}" class="block text-center px-6 py-3 border-2 border-indigo-600 text-indigo-600 rounded-lg font-semibold hover:bg-indigo-50 transition">
                            Get Started
                        </a>
                    </div>
                </div>

                <!-- Professional Plan (Popular) -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden pricing-card relative transform scale-105 border-2 border-indigo-500">
                    <div class="popular-badge">
                        <span class="bg-indigo-600 text-white px-4 py-1 rounded-full text-sm font-semibold">Most Popular</span>
                    </div>
                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Professional</h3>
                        <p class="text-gray-500 mb-4">Best for growing schools</p>
                        <div class="mb-6">
                            <span class="text-4xl font-bold text-gray-900">$99</span>
                            <span class="text-gray-500">/month</span>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Up to 500 students
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Up to 30 teachers
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Advanced result management
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Payment integration
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Advanced analytics & reports
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Priority email & phone support
                            </li>
                        </ul>
                        <a href="{{ route('register') }}" class="block text-center px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition shadow-md">
                            Get Started
                        </a>
                    </div>
                </div>

                <!-- Enterprise Plan -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden pricing-card relative">
                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Enterprise</h3>
                        <p class="text-gray-500 mb-4">For large institutions</p>
                        <div class="mb-6">
                            <span class="text-4xl font-bold text-gray-900">$199</span>
                            <span class="text-gray-500">/month</span>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Unlimited students
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Unlimited teachers
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Custom branding
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Full API access
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Dedicated account manager
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                24/7 priority support
                            </li>
                        </ul>
                        <a href="{{ route('register') }}" class="block text-center px-6 py-3 border-2 border-indigo-600 text-indigo-600 rounded-lg font-semibold hover:bg-indigo-50 transition">
                            Contact Sales
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional Pricing Info -->
            <div class="mt-16 text-center">
                <p class="text-gray-600 mb-4">All plans include:</p>
                <div class="flex flex-wrap justify-center gap-6">
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        30-day money-back guarantee
                    </div>
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        SSL security
                    </div>
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Automatic backups
                    </div>
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Regular updates
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="mt-20">
                <h3 class="text-2xl font-bold text-center text-gray-900 mb-8">Frequently Asked Questions</h3>
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-lg p-6 shadow-sm">
                        <h4 class="font-semibold text-lg mb-2">Can I change plans later?</h4>
                        <p class="text-gray-600">Yes, you can upgrade or downgrade your plan at any time. Changes will be reflected in your next billing cycle.</p>
                    </div>
                    <div class="bg-white rounded-lg p-6 shadow-sm">
                        <h4 class="font-semibold text-lg mb-2">Is there a setup fee?</h4>
                        <p class="text-gray-600">No, there are no hidden fees or setup costs. You only pay the monthly subscription fee.</p>
                    </div>
                    <div class="bg-white rounded-lg p-6 shadow-sm">
                        <h4 class="font-semibold text-lg mb-2">Do you offer discounts for non-profits?</h4>
                        <p class="text-gray-600">Yes, we offer special pricing for non-profit educational institutions. Please contact our sales team.</p>
                    </div>
                    <div class="bg-white rounded-lg p-6 shadow-sm">
                        <h4 class="font-semibold text-lg mb-2">What payment methods do you accept?</h4>
                        <p class="text-gray-600">We accept all major credit cards, PayPal, and bank transfers for annual plans.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Ready to Get Started?</h2>
                <p class="text-xl text-gray-600">Join thousands of schools using Plus36 Networks SMS</p>
            </div>
            <div class="max-w-2xl mx-auto bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl shadow-xl p-8 text-white text-center">
                <h3 class="text-2xl font-bold mb-4">Start your 14-day free trial today</h3>
                <p class="mb-6 opacity-90">No credit card required. Cancel anytime.</p>
                <a href="{{ route('register') }}" class="inline-block px-8 py-3 bg-white text-indigo-600 rounded-lg font-semibold hover:bg-gray-100 transition shadow-lg">
                    Start Free Trial
                </a>
                <p class="mt-4 text-sm opacity-75">Or contact us at <a href="mailto:sales@plus36networks.com" class="underline">sales@plus36networks.com</a></p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                            </svg>
                        </div>
                        <span class="ml-2 text-xl font-bold">Plus36 Networks SMS</span>
                    </div>
                    <p class="text-gray-400">Modern school management solution for the digital age.</p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Product</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#features" class="hover:text-white transition">Features</a></li>
                        <li><a href="#pricing" class="hover:text-white transition">Pricing</a></li>
                        <li><a href="#" class="hover:text-white transition">Demo</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Company</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition">About Us</a></li>
                        <li><a href="#contact" class="hover:text-white transition">Contact</a></li>
                        <li><a href="#" class="hover:text-white transition">Blog</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Legal</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white transition">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2024 Plus36 Networks SMS. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Simple pricing toggle (monthly/yearly)
        let isMonthly = true;
        
        function updatePrices() {
            const prices = {
                basic: isMonthly ? 49 : 39,
                professional: isMonthly ? 99 : 79,
                enterprise: isMonthly ? 199 : 159
            };
            
            document.querySelectorAll('.pricing-card .text-4xl').forEach((el, index) => {
                const key = index === 0 ? 'basic' : index === 1 ? 'professional' : 'enterprise';
                el.textContent = `$${prices[key]}`;
            });
        }
        
        document.querySelectorAll('#pricing button').forEach((btn, idx) => {
            btn.addEventListener('click', () => {
                isMonthly = idx === 0;
                updatePrices();
                
                // Update active button styles
                document.querySelectorAll('#pricing button').forEach(b => {
                    b.classList.remove('bg-indigo-600', 'text-white');
                    b.classList.add('text-gray-600');
                });
                btn.classList.remove('text-gray-600');
                btn.classList.add('bg-indigo-600', 'text-white');
            });
        });
    </script>
</body>
</html>
