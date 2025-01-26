<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .modal-overlay {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        .modal-overlay.active {
            opacity: 1;
        }
        .modal-content {
            transform: scale(0.95);
            transition: transform 0.3s ease-in-out;
        }
        .modal-overlay.active .modal-content {
            transform: scale(1);
        }
        .toast {
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }
        .toast.show {
            transform: translateX(0);
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .animate-spin {
            animation: spin 1s linear infinite;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-4 right-4 z-50"></div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Student Management System</h1>
                <button 
                    onclick="openAddModal()"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
                >
                    <i class="fas fa-plus"></i>
                    Add New Student
                </button>
            </div>

            <!-- Search Form -->
            <form id="searchForm" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input 
                        type="text" 
                        name="search_name" 
                        placeholder="Search by name"
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
                <div>
                    <input 
                        type="text" 
                        name="search_reg" 
                        placeholder="Registration number"
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
                <div>
                    <select 
                        name="search_batch" 
                        class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Select Batch</option>
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                        <option value="2027">2027</option>
                        <option value="2028">2028</option>
                        <option value="2029">2029</option>
                        <option value="2030">2030</option>
                        <option value="2031">2031</option>
                    </select>
                </div>
                <div>
                    <button 
                        type="submit"
                        class="w-full bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg transition-colors"
                    >
                        Search
                    </button>
                </div>
            </form>

            <!-- Results Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg overflow-hidden">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">Reg. Number</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Batch</th>
                            <th class="px-4 py-3 text-left">Contact</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody">
                        <!-- Table content will be dynamically loaded -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Student Modal -->
    <div id="studentModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-40">
        <div class="modal-content bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
            <div class="flex justify-between items-center p-6 border-b">
                <h2 id="modalTitle" class="text-2xl font-bold text-gray-800"></h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="studentForm" class="p-6">
                <input type="hidden" id="studentId" name="id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                        <input 
                            type="text" 
                            id="firstName" 
                            name="first_name" 
                            required
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                        <input 
                            type="text" 
                            id="lastName" 
                            name="last_name" 
                            required
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Registration Number</label>
                        <input 
                            type="text" 
                            id="regNumber" 
                            name="registration_number" 
                            required
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Batch Name</label>
                        <input 
                            type="text" 
                            id="batchName" 
                            name="batch_name" 
                            required
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Batch Year</label>
                        <input 
                            type="number" 
                            id="batchYear" 
                            name="batch_year" 
                            required
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contact Number</label>
                        <input 
                            type="text" 
                            id="contactNumber" 
                            name="contact_number"
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea 
                            id="address" 
                            name="address" 
                            rows="3"
                            class="w-full px-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500"
                        ></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-4 mt-6">
                    <button 
                        type="button" 
                        onclick="closeModal()"
                        class="px-4 py-2 text-gray-600 border rounded-lg hover:bg-gray-100 transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-40">
        <div class="modal-content bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Confirm Delete</h3>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this student? This action cannot be undone.</p>
            <div class="flex justify-end gap-4">
                <button 
                    onclick="closeDeleteModal()"
                    class="px-4 py-2 text-gray-600 border rounded-lg hover:bg-gray-100 transition-colors"
                >
                    Cancel
                </button>
                <button 
                    id="confirmDelete"
                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
                >
                    Delete
                </button>
            </div>
        </div>
    </div>

    <script>
    // Toast notification system
    function showToast(message, type = 'success', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast bg-white shadow-lg rounded-lg p-4 mb-4 flex items-center ${
            type === 'success' ? 'border-l-4 border-green-500' :
            type === 'error' ? 'border-l-4 border-red-500' :
            'border-l-4 border-yellow-500'
        }`;
        
        const icon = document.createElement('div');
        icon.className = 'flex-shrink-0 mr-3';
        icon.innerHTML = type === 'success' ? 
            '<i class="fas fa-check-circle text-green-500"></i>' :
            type === 'error' ? 
            '<i class="fas fa-exclamation-circle text-red-500"></i>' :
            '<i class="fas fa-exclamation-triangle text-yellow-500"></i>';
        
        const text = document.createElement('div');
        text.className = 'flex-1 text-sm';
        text.textContent = message;
        
        toast.appendChild(icon);
        toast.appendChild(text);
        
        const container = document.getElementById('toastContainer');
        container.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Remove toast after duration
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    // Form validation
    function validateForm(formData) {
        const errors = [];
        
        // Required fields
        const requiredFields = ['first_name', 'last_name', 'email', 'registration_number', 'batch_name', 'batch_year'];
        requiredFields.forEach(field => {
            if (!formData.get(field)?.trim()) {
                errors.push(`${field.replace('_', ' ')} is required`);
            }
        });
        
        // Email validation
        const email = formData.get('email');
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.push('Invalid email format');
        }
        
        // Registration number format
        const regNumber = formData.get('registration_number');
        if (regNumber && !/^[A-Z0-9]+$/.test(regNumber)) {
            errors.push('Registration number should contain only uppercase letters and numbers');
        }
        
        // Batch year validation
        const batchYear = formData.get('batch_year');
        if (batchYear) {
            const year = parseInt(batchYear);
            const currentYear = new Date().getFullYear();
            if (year < 2000 || year > currentYear + 4) {
                errors.push('Invalid batch year');
            }
        }
        
        return errors;
    }

    // Modal functions
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Student';
        document.getElementById('studentForm').reset();
        document.getElementById('studentId').value = '';
        openModal();
    }

    function openEditModal(student) {
        document.getElementById('modalTitle').textContent = 'Edit Student';
        document.getElementById('studentId').value = student.id;
        document.getElementById('firstName').value = student.first_name;
        document.getElementById('lastName').value = student.last_name;
        document.getElementById('regNumber').value = student.registration_number;
        document.getElementById('email').value = student.email;
        document.getElementById('batchName').value = student.batch_name;
        document.getElementById('batchYear').value = student.batch_year;
        document.getElementById('contactNumber').value = student.contact_number || '';
        document.getElementById('address').value = student.address || '';
        openModal();
    }

    function openModal() {
        const modal = document.getElementById('studentModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => modal.classList.add('active'), 10);
    }

    function closeModal() {
        const modal = document.getElementById('studentModal');
        modal.classList.remove('active');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    function openDeleteModal(studentId) {
        const modal = document.getElementById('deleteModal');
        document.getElementById('confirmDelete').onclick = () => deleteStudent(studentId);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => modal.classList.add('active'), 10);
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('active');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    // CRUD Operations
    document.getElementById('studentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const errors = validateForm(formData);
        
        if (errors.length > 0) {
            errors.forEach(error => showToast(error, 'error'));
            return;
        }
        
        const isEdit = formData.get('id');
        const url = isEdit ? 'admin/update_student.php' : 'admin/add_student.php';
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(
                    isEdit ? 'Student updated successfully' : 'Student added successfully',
                    'success'
                );
                closeModal();
                refreshStudentList();
            } else {
                showToast(data.message || 'Operation failed', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    });

    function deleteStudent(studentId) {
        fetch('admin/delete_student.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: studentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Student deleted successfully', 'success');
                closeDeleteModal();
                refreshStudentList();
            } else {
                showToast(data.message || 'Delete operation failed', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }

    function refreshStudentList() {
        const searchForm = document.getElementById('searchForm');
        searchForm.dispatchEvent(new Event('submit'));
    }

    // Search functionality
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const tbody = document.getElementById('studentsTableBody');
        
        // Show loading state
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="flex justify-center items-center">
                        <svg class="animate-spin h-6 w-6 text-gray-500 mr-3" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Loading...
                    </div>
                </td>
            </tr>
        `;
        
        fetch('admin/search_students.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = data.students.map(student => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">${student.first_name} ${student.last_name}</td>
                        <td class="px-4 py-3">${student.registration_number}</td>
                        <td class="px-4 py-3">${student.email}</td>
                        <td class="px-4 py-3">${student.batch_name} (${student.batch_year})</td>
                        <td class="px-4 py-3">${student.contact_number || '-'}</td>
                        <td class="px-4 py-3 text-center">
                            <button 
                                onclick="openEditModal(${JSON.stringify(student).replace(/"/g, '&quot;')})"
                                class="text-blue-500 hover:text-blue-700 mx-1"
                                title="Edit"
                            >
                                <i class="fas fa-edit"></i>
                            </button>
                            <button 
                                onclick="openDeleteModal(${student.id})"
                                class="text-red-500 hover:text-red-700 mx-1"
                                title="Delete"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('') || `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-gray-500">
                            No students found
                        </td>
                    </tr>
                `;
            } else {
                showToast(data.message || 'Error loading students', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while loading students', 'error');
        });
    });

    // Initial load
    document.getElementById('searchForm').dispatchEvent(new Event('submit'));
    </script>
</body>
</html>