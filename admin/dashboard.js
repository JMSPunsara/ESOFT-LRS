// Global variables
let students = [];
const API_URL = 'api';

// Initial load
document.addEventListener('DOMContentLoaded', () => {
    loadStudents();
});

// Load students data
async function loadStudents() {
    try {
        const response = await fetch(`${API_URL}/get_students.php`);
        const data = await response.json();
        
        if (data.success) {
            students = data.data;
            renderStudentsTable();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error loading students data', 'error');
    }
}

// Render students table
function renderStudentsTable() {
    const tableBody = document.getElementById('studentsTableBody');
    tableBody.innerHTML = '';

    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const batchFilter = document.getElementById('batchFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;

    const filteredStudents = students.filter(student => {
        const matchesSearch = student.name.toLowerCase().includes(searchTerm) || 
                            student.id.toString().includes(searchTerm);
        const matchesBatch = !batchFilter || student.batch === batchFilter;
        const matchesStatus = !statusFilter || student.status === statusFilter;

        return matchesSearch && matchesBatch && matchesStatus;
    });

    filteredStudents.forEach(student => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${student.id}</td>
            <td>${student.name}</td>
            <td>${student.batch}</td>
            <td><span class="status status-${student.status}">${student.status}</span></td>
            <td>${student.last_login || 'Never'}</td>
            <td>
                ${student.status === 'active' 
                    ? `<button class="action-btn block-btn" onclick="showBlockModal(${student.id})">Block</button>`
                    : `<button class="action-btn unblock-btn" onclick="unblockStudent(${student.id})">Unblock</button>`
                }
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Search functionality
function searchStudents() {
    renderStudentsTable();
}

// Block student modal
function showBlockModal(studentId) {
    const modal = document.getElementById('blockModal');
    document.getElementById('studentId').value = studentId;
    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('blockModal');
    modal.style.display = 'none';
    document.getElementById('blockForm').reset();
}

// Handle block form submission
document.getElementById('blockForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const studentId = document.getElementById('studentId').value;
    const reason = document.getElementById('blockReason').value;
    const duration = document.getElementById('blockDuration').value;

    try {
        const response = await fetch(`${API_URL}/block_student.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                student_id: studentId,
                reason: reason,
                duration: duration
            })
        });

        const data = await response.json();
        
        if (data.success) {
            showNotification('Student blocked successfully', 'success');
            closeModal();
            loadStudents();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error blocking student', 'error');
    }
});

// Unblock student
async function unblockStudent(studentId) {
    if (!confirm('Are you sure you want to unblock this student?')) {
        return;
    }

    try {
        const response = await fetch(`${API_URL}/unblock_student.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                student_id: studentId
            })
        });

        const data = await response.json();
        
        if (data.success) {
            showNotification('Student unblocked successfully', 'success');
            loadStudents();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error unblocking student', 'error');
    }
}

// Notification handling
function showNotification(message, type) {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = `notification notification-${type}`;
    notification.style.display = 'block';

    setTimeout(() => {
        notification.style.display = 'none';
    }, 3000);
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('blockModal');
    if (event.target === modal) {
        closeModal();
    }
}