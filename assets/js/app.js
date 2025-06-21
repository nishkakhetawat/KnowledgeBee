// Knowledge Bee JavaScript Application

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Password strength meter
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            updatePasswordStrengthMeter(strength);
        });
    }

    // Search autocomplete
    const searchInput = document.querySelector('input[name="q"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 300);
        });
    }

    // Content type switching in upload form
    const contentTypeSelect = document.getElementById('type');
    if (contentTypeSelect) {
        contentTypeSelect.addEventListener('change', function() {
            showContentFields(this.value);
        });
    }

    // Quiz functionality
    initializeQuizFeatures();
});

// Password strength calculation
function calculatePasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength += 1;
    if (password.match(/[a-z]/)) strength += 1;
    if (password.match(/[A-Z]/)) strength += 1;
    if (password.match(/[0-9]/)) strength += 1;
    if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
    
    return strength;
}

// Update password strength meter
function updatePasswordStrengthMeter(strength) {
    const meter = document.getElementById('password-strength');
    if (!meter) return;
    
    const strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    const strengthClass = ['danger', 'warning', 'info', 'success', 'success'];
    
    meter.className = `progress-bar bg-${strengthClass[strength - 1]}`;
    meter.style.width = `${(strength / 5) * 100}%`;
    meter.textContent = strengthText[strength - 1] || 'Very Weak';
}

// Search functionality
function performSearch(query) {
    if (query.length < 2) return;
    
    fetch(`../api/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data);
        })
        .catch(error => {
            console.error('Search error:', error);
        });
}

// Display search results
function displaySearchResults(results) {
    const resultsContainer = document.getElementById('search-results');
    if (!resultsContainer) return;
    
    if (results.length === 0) {
        resultsContainer.innerHTML = '<p class="text-muted">No results found</p>';
        return;
    }
    
    const html = results.map(item => `
        <div class="search-result-item p-2 border-bottom">
            <a href="skill.php?id=${item.skill_id}" class="text-decoration-none">
                <strong>${item.title}</strong>
                <br>
                <small class="text-muted">${item.skill_name} • ${item.type}</small>
            </a>
        </div>
    `).join('');
    
    resultsContainer.innerHTML = html;
}

// Content type field switching
function showContentFields(type) {
    // Hide all content type fields
    document.querySelectorAll('.content-type-fields').forEach(field => {
        field.style.display = 'none';
    });
    
    // Show relevant fields
    if (type === 'video') {
        document.getElementById('videoFields').style.display = 'block';
    } else if (type === 'blog') {
        document.getElementById('blogFields').style.display = 'block';
    } else if (type === 'quiz') {
        document.getElementById('quizFields').style.display = 'block';
    }
}

// Quiz functionality
function initializeQuizFeatures() {
    let questionCount = 1;
    
    // Add quiz question
    window.addQuizQuestion = function() {
        const container = document.getElementById('quizQuestions');
        if (!container) return;
        
        const newQuestion = document.createElement('div');
        newQuestion.className = 'quiz-question mb-3 p-3 border rounded';
        newQuestion.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label">Question ${questionCount + 1}</label>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeQuestion(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="mb-2">
                <input type="text" class="form-control" name="quiz_questions[${questionCount}][question]" placeholder="Enter your question">
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Option A</label>
                    <input type="text" class="form-control" name="quiz_questions[${questionCount}][options][a]" placeholder="Option A">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Option B</label>
                    <input type="text" class="form-control" name="quiz_questions[${questionCount}][options][b]" placeholder="Option B">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <label class="form-label">Option C</label>
                    <input type="text" class="form-control" name="quiz_questions[${questionCount}][options][c]" placeholder="Option C">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Option D</label>
                    <input type="text" class="form-control" name="quiz_questions[${questionCount}][options][d]" placeholder="Option D">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <label class="form-label">Correct Answer</label>
                    <select class="form-select" name="quiz_questions[${questionCount}][correct]">
                        <option value="a">A</option>
                        <option value="b">B</option>
                        <option value="c">C</option>
                        <option value="d">D</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Explanation (Optional)</label>
                    <input type="text" class="form-control" name="quiz_questions[${questionCount}][explanation]" placeholder="Why is this correct?">
                </div>
            </div>
        `;
        container.appendChild(newQuestion);
        questionCount++;
    };
    
    // Remove quiz question
    window.removeQuestion = function(button) {
        button.closest('.quiz-question').remove();
    };
}

// Voting functionality
function voteContent(contentId, voteType) {
    fetch('../pages/vote.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `content_id=${contentId}&vote_type=${voteType}&csrf_token=${getCSRFToken()}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            location.reload();
        } else {
            showAlert('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while voting.', 'danger');
    });
}

// Coin spending functionality
function spendCoins(amount, contentId) {
    if (confirm(`This will cost you ${amount} Buzz Coins. Continue?`)) {
        window.location.href = `spend-coins.php?content_id=${contentId}&amount=${amount}`;
    }
}

// Utility functions
function getCSRFToken() {
    const tokenInput = document.querySelector('input[name="csrf_token"]');
    return tokenInput ? tokenInput.value : '';
}

function showAlert(message, type = 'info') {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type} alert-dismissible fade show alert-custom`;
    alertContainer.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertContainer, container.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alertContainer);
            bsAlert.close();
        }, 5000);
    }
}

function confirmDelete(message = 'Are you sure you want to delete this?') {
    return confirm(message);
}

// Loading spinner
function showLoading() {
    const spinner = document.createElement('div');
    spinner.className = 'text-center py-4';
    spinner.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
    return spinner;
}

// Format numbers
function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Local storage utilities
const Storage = {
    set: function(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (e) {
            console.error('Error saving to localStorage:', e);
        }
    },
    
    get: function(key) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (e) {
            console.error('Error reading from localStorage:', e);
            return null;
        }
    },
    
    remove: function(key) {
        try {
            localStorage.removeItem(key);
        } catch (e) {
            console.error('Error removing from localStorage:', e);
        }
    }
};

// Export functions for global use
window.KnowledgeBee = {
    voteContent,
    spendCoins,
    showAlert,
    confirmDelete,
    formatNumber,
    Storage
}; 