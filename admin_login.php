<?php
session_start();
require_once 'config.php';

if(isset($_SESSION['admin_loggedin'])) {
    header("Location: admin_dashboard.php");
    exit;
}

if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM admin WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);
        if(password_verify($password, $admin['password'])) {
            $_SESSION['admin_loggedin'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            header("Location: admin_dashboard.php");
            exit();
        }
    }
    
    $error = "Invalid username or password";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Access</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .form-container {
            transition: all 0.3s ease-in-out;
        }
        
        .slide-up {
            transform: translateY(-100%);
            opacity: 0;
            display: none;
        }
        
        .slide-down {
            transform: translateY(0);
            opacity: 1;
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert {
            animation: fadeIn 0.5s ease-in-out;
        }

        /* Animated Refresh Button Styles */
.refresh-btn {
    position: fixed;
    right: 30px;
    bottom: 30px;
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    z-index: 1000;
}

.refresh-icon {
    width: 24px;
    height: 24px;
    fill: white;
    transition: transform 0.5s ease;
}

.refresh-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.refresh-btn:hover .refresh-icon {
    animation: spin 1s ease infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.refresh-btn.clicked {
    animation: pulse 0.5s ease;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(0.9); }
    100% { transform: scale(1); }
}

.refresh-tooltip {
    position: absolute;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
    right: 70px;
    opacity: 0;
    transition: opacity 0.3s ease;
    white-space: nowrap;
}

.refresh-btn:hover .refresh-tooltip {
    opacity: 1;
}

/* Home Button Styles */
.home-btn-container {
    position: absolute;
    top: 20px;
    left: 20px;
    z-index: 100;
}

.home-btn {
    position: relative;
    padding: 12px 24px;
    background: linear-gradient(135deg, 
        rgba(59, 130, 246, 0.9), /* Blue */
        rgba(147, 51, 234, 0.9)  /* Purple */
    );
    border: none;
    border-radius: 105px;
    color: white;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 8px;
}

.home-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.4),
        transparent
    );
    transition: 0.5s;
}

.home-btn:hover::before {
    left: 100%;
}

.home-btn:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

.home-btn:active {
    transform: translateY(1px);
}

.home-icon {
    font-size: 20px;
    transition: transform 0.3s ease;
}

.home-btn:hover .home-icon {
    transform: scale(1.2);
}

/* Particle Effects */
.home-particles {
    position: absolute;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.home-particle {
    position: absolute;
    background: white;
    border-radius: 50%;
    pointer-events: none;
    opacity: 0;
    animation: particleAnimation 1s ease-out forwards;
}

@keyframes particleAnimation {
    0% {
        transform: translate(0, 0) scale(1);
        opacity: 0.8;
    }
    100% {
        transform: translate(var(--tx), var(--ty)) scale(0);
        opacity: 0;
    }
}

    </style>
</head>

<div class="home-btn-container">
    <button class="home-btn" onclick="goHome()">
        <i class="fas fa-home home-icon"></i>
        <span>Home</span>
    </button>
    <div class="home-particles"></div>
</div>


<body class="bg-gradient-to-r from-blue-500 to-purple-600 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-8 relative overflow-hidden">
        <!-- Toggle Buttons -->
        <div class="flex mb-8 bg-gray-100 rounded-lg p-1">
            <button id="loginToggle" 
                    onclick="toggleForm('login')"
                    class="flex-1 py-2 px-4 rounded-lg font-semibold transition-all duration-300 bg-blue-500 text-white">
                Login
            </button>
            <button id="signupToggle" 
                    onclick="toggleForm('signup')"
                    class="flex-1 py-2 px-4 rounded-lg font-semibold transition-all duration-300 text-gray-600">
                Sign Up
            </button>
        </div>


        <!-- Alert Messages -->
        <?php if(isset($_GET['error']) || isset($_GET['success'])): ?>
        <div class="alert mb-4 px-4 py-3 rounded relative <?php echo isset($_GET['error']) ? 'bg-red-100 text-red-700 border-red-400' : 'bg-green-100 text-green-700 border-green-400'; ?>">
            <?php 
            if(isset($_GET['error'])) {
                switch($_GET['error']) {
                    case 'invalid':
                        echo "Invalid credentials!";
                        break;
                    case 'empty':
                        echo "Please fill all fields!";
                        break;
                    case 'exists':
                        echo "Admin already exists!";
                        break;
                    default:
                        echo "An error occurred!";
                }
            }
            if(isset($_GET['success'])) {
                echo "Registration successful! Please login.";
            }
            ?>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <div id="loginForm" class="form-container slide-down">
            <form action="admin_login_handler.php" method="POST" class="space-y-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <input type="text" 
                           name="username" 
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter username"
                           required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" 
                           name="password" 
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter password"
                           required>
                </div>
                <button type="submit" 
                        class="w-full bg-blue-500 text-white py-3 rounded-lg font-semibold hover:bg-blue-600 transition-all duration-300">
                    Login
                </button>
            </form> <br>
            </button>
                     <button class="home-btn" onclick="goHome()">
                            <i class="fas fa-home home-icon"></i>
                             <span>Main - Menu</span>
                    </button>
                <div class="home-particles"></div>
                </div>
        </div>

        <!-- Signup Form -->
        <div id="signupForm" class="form-container slide-up">
            <form action="admin_signup_handler.php" method="POST" class="space-y-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <input type="text" 
                           name="username" 
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Choose username"
                           required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" 
                           name="password" 
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Choose password"
                           required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                    <input type="password" 
                           name="confirm_password" 
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Confirm password"
                           required>
                </div>
                <button type="submit" 
                        class="w-full bg-purple-500 text-white py-3 rounded-lg font-semibold hover:bg-purple-600 transition-all duration-300">
                    Sign Up
                </button>
            </form>
        </div>
    </div>

    <!-- Animated Refresh Button -->
<div class="refresh-btn" onclick="refreshPage()" id="refreshButton">
    <span class="refresh-tooltip">Refresh Page</span>
    <svg class="refresh-icon" viewBox="0 0 24 24">
        <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
    </svg>
</div>

<!-- Last Refresh Time Display -->
<div id="lastRefresh" class="fixed bottom-4 left-4 text-sm text-gray-600">
    Last refreshed: <span id="refreshTime"></span>
</div>


    <script>

        // Refresh functionality
function refreshPage() {
    const refreshBtn = document.getElementById('refreshButton');
    const refreshIcon = refreshBtn.querySelector('.refresh-icon');
    
    // Add clicked animation
    refreshBtn.classList.add('clicked');
    
    // Add spinning animation to icon
    refreshIcon.style.animation = 'spin 1s linear infinite';
    
    // Show loading state
    showToast('Refreshing page...', 'info');
    
    // Refresh the page after animation
    setTimeout(() => {
        location.reload();
    }, 1000);
}

// Update last refresh time
function updateLastRefreshTime() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });
    document.getElementById('refreshTime').textContent = timeStr;
}

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 p-4 rounded-lg text-white ${
        type === 'info' ? 'bg-blue-500' : 
        type === 'success' ? 'bg-green-500' : 
        'bg-red-500'
    } transition-opacity duration-300`;
    toast.style.zIndex = '1001';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Fade in
    setTimeout(() => {
        toast.style.opacity = '1';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Initialize last refresh time on page load
document.addEventListener('DOMContentLoaded', () => {
    updateLastRefreshTime();
});

// Add auto-refresh every 5 minutes (300000 milliseconds)
setInterval(() => {
    refreshPage();
}, 300000);

        function toggleForm(form) {
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            const loginToggle = document.getElementById('loginToggle');
            const signupToggle = document.getElementById('signupToggle');

            if(form === 'login') {
                loginForm.classList.remove('slide-up');
                loginForm.classList.add('slide-down');
                signupForm.classList.remove('slide-down');
                signupForm.classList.add('slide-up');
                loginToggle.classList.add('bg-blue-500', 'text-white');
                loginToggle.classList.remove('text-gray-600');
                signupToggle.classList.remove('bg-purple-500', 'text-white');
                signupToggle.classList.add('text-gray-600');
            } else {
                signupForm.classList.remove('slide-up');
                signupForm.classList.add('slide-down');
                loginForm.classList.remove('slide-down');
                loginForm.classList.add('slide-up');
                signupToggle.classList.add('bg-purple-500', 'text-white');
                signupToggle.classList.remove('text-gray-600');
                loginToggle.classList.remove('bg-blue-500', 'text-white');
                loginToggle.classList.add('text-gray-600');
            }
        }

        // Show appropriate form based on URL parameter
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if(urlParams.get('form') === 'signup') {
                toggleForm('signup');
            }
        }

        // Home button functionality
function goHome() {
    createHomeParticles();
    setTimeout(() => {
        window.location.href = 'index.html'; // Change this to your home page URL
    }, 500);
}

// Particle effect for home button
function createHomeParticles() {
    const particlesContainer = document.querySelector('.home-particles');
    const button = document.querySelector('.home-btn');
    const buttonRect = button.getBoundingClientRect();

    for(let i = 0; i < 15; i++) {
        const particle = document.createElement('div');
        particle.className = 'home-particle';
        
        // Random particle properties
        const size = Math.random() * 6 + 2;
        const tx = (Math.random() - 0.5) * 100; // Random x translation
        const ty = (Math.random() - 0.5) * 100; // Random y translation
        
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        particle.style.left = `${buttonRect.width / 2}px`;
        particle.style.top = `${buttonRect.height / 2}px`;
        particle.style.setProperty('--tx', `${tx}px`);
        particle.style.setProperty('--ty', `${ty}px`);
        
        particlesContainer.appendChild(particle);
        
        // Remove particle after animation
        setTimeout(() => particle.remove(), 1000);
    }
}

// Add hover sound effect (optional)
document.querySelector('.home-btn').addEventListener('mouseenter', () => {
    const hoverSound = new Audio('hover.mp3'); // Add your sound file
    hoverSound.volume = 0.2;
    hoverSound.play().catch(() => {}); // Catch and ignore errors if sound fails to play
});

    </script>
</body>
</html>
