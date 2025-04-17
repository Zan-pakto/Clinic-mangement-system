<?php
require_once 'config/database.php';
require_once 'config/auth.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClinicMS - Modern Healthcare Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .hero-pattern {
            background-image: url('https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .gradient-text {
            background: linear-gradient(45deg, #3B82F6, #10B981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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
            transform: scale(1.05);
        }
        .text-gradient {
            background: linear-gradient(45deg, #3B82F6, #10B981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full bg-white/80 backdrop-blur-md shadow-sm z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gradient">ClinicMS</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="login.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition duration-300">Login</a>
                    <a href="register.php" class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-md text-sm font-medium transition duration-300 hover:shadow-lg">Sign Up</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative hero-pattern min-h-screen">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 min-h-screen flex items-center pt-16">
            <div class="text-center md:text-left md:w-1/2" data-aos="fade-right">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight">
                    Transform Your Clinic with <span class="text-gradient">Modern Healthcare</span> Management
                </h1>
                <p class="text-xl text-gray-200 mb-8 max-w-2xl">
                    Streamline your clinic's operations with our comprehensive management solution. Designed for healthcare professionals who value efficiency and patient care.
                </p>
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 justify-center md:justify-start">
                    <a href="register.php" class="bg-blue-600 text-white hover:bg-blue-700 px-8 py-3 rounded-lg text-lg font-medium transition duration-300 hover:shadow-lg hover:scale-105">
                        Get Started Free
                    </a>
                    <a href="#features" class="glass-effect text-white hover:bg-white/20 px-8 py-3 rounded-lg text-lg font-medium transition duration-300 hover:scale-105">
                        Explore Features
                    </a>
                </div>
            </div>
            <div class="hidden md:block md:w-1/2" data-aos="fade-left">
                <img src="https://img.freepik.com/free-vector/medical-care-healthcare-concept-with-flat-design_23-2147854079.jpg" alt="Healthcare Illustration" class="floating w-full max-w-lg mx-auto">
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="bg-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-4xl font-bold text-blue-600 mb-2">1000+</div>
                    <div class="text-gray-600">Active Clinics</div>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-4xl font-bold text-green-600 mb-2">50K+</div>
                    <div class="text-gray-600">Patients Served</div>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-4xl font-bold text-yellow-600 mb-2">99%</div>
                    <div class="text-gray-600">Satisfaction Rate</div>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="400">
                    <div class="text-4xl font-bold text-purple-600 mb-2">24/7</div>
                    <div class="text-gray-600">Support Available</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Powerful Features</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Everything you need to manage your clinic efficiently and provide better patient care</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-sm card-hover" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-blue-600 mb-4">
                        <i class="fas fa-calendar-check text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Appointment Management</h3>
                    <p class="text-gray-600">Schedule and manage appointments with ease. Send automated reminders to patients.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm card-hover" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-green-600 mb-4">
                        <i class="fas fa-user-injured text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Patient Records</h3>
                    <p class="text-gray-600">Maintain comprehensive patient records. Track medical history and treatments.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm card-hover" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-yellow-600 mb-4">
                        <i class="fas fa-prescription text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Prescriptions & Lab Results</h3>
                    <p class="text-gray-600">Create and manage prescriptions. Track lab results and patient progress.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works Section -->
    <div class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">How It Works</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Get started with ClinicMS in three simple steps</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-blue-600">1</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Sign Up</h3>
                    <p class="text-gray-600">Create your account in minutes</p>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-green-600">2</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Configure</h3>
                    <p class="text-gray-600">Set up your clinic's profile</p>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                    <div class="bg-yellow-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-yellow-600">3</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Start Managing</h3>
                    <p class="text-gray-600">Begin managing your clinic efficiently</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonials Section -->
    <div class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">What Our Clients Say</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Trusted by healthcare professionals worldwide</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-sm card-hover" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex items-center mb-4">
                        <img src="https://ui-avatars.com/api/?name=Dr.+John+Smith&background=0D8ABC&color=fff" alt="Dr. John Smith" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-semibold text-gray-900">Dr. John Smith</h4>
                            <p class="text-gray-600">Family Clinic</p>
                        </div>
                    </div>
                    <p class="text-gray-600">"ClinicMS has transformed how we manage our practice. The appointment scheduling system alone has saved us hours of work."</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm card-hover" data-aos="fade-up" data-aos-delay="200">
                    <div class="flex items-center mb-4">
                        <img src="https://ui-avatars.com/api/?name=Dr.+Sarah+Johnson&background=0D8ABC&color=fff" alt="Dr. Sarah Johnson" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-semibold text-gray-900">Dr. Sarah Johnson</h4>
                            <p class="text-gray-600">Pediatric Clinic</p>
                        </div>
                    </div>
                    <p class="text-gray-600">"The patient management features are incredible. We can access medical histories instantly and provide better care."</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm card-hover" data-aos="fade-up" data-aos-delay="300">
                    <div class="flex items-center mb-4">
                        <img src="https://ui-avatars.com/api/?name=Dr.+Michael+Brown&background=0D8ABC&color=fff" alt="Dr. Michael Brown" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-semibold text-gray-900">Dr. Michael Brown</h4>
                            <p class="text-gray-600">Dental Practice</p>
                        </div>
                    </div>
                    <p class="text-gray-600">"The lab results integration has streamlined our workflow significantly. Highly recommended for modern clinics."</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center" data-aos="fade-up">
            <h2 class="text-3xl font-bold text-white mb-8">Ready to Transform Your Clinic?</h2>
            <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">Join thousands of healthcare professionals who trust ClinicMS for their practice management needs.</p>
            <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 justify-center">
                <a href="register.php" class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-3 rounded-lg text-lg font-medium transition duration-300 hover:shadow-lg hover:scale-105">
                    Start Free Trial
                </a>
                <a href="login.php" class="bg-transparent text-white border-2 border-white hover:bg-white hover:text-blue-600 px-8 py-3 rounded-lg text-lg font-medium transition duration-300 hover:shadow-lg hover:scale-105">
                    Schedule Demo
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold text-white mb-4">ClinicMS</h3>
                    <p class="text-gray-400">Modern healthcare management system for clinics of all sizes.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">Product</h4>
                    <ul class="space-y-2">
                        <li><a href="#features" class="hover:text-white transition duration-300">Features</a></li>
                        <li><a href="#" class="hover:text-white transition duration-300">Pricing</a></li>
                        <li><a href="#" class="hover:text-white transition duration-300">Integrations</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white transition duration-300">About Us</a></li>
                        <li><a href="#" class="hover:text-white transition duration-300">Contact</a></li>
                        <li><a href="#" class="hover:text-white transition duration-300">Careers</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">Connect</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-linkedin text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p>&copy; 2024 ClinicMS. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    </script>
</body>
</html> 