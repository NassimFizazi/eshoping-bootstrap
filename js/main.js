// Main JavaScript file

document.addEventListener('DOMContentLoaded', function() {
    // Update cart count in navbar
    updateCartCount();
    
    // Handle flash messages
    const flashMessage = document.querySelector('.flash-message');
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.classList.add('fade');
            setTimeout(() => {
                flashMessage.remove();
            }, 500);
        }, 3000);
    }
    
    // Activate Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Function to update cart count
function updateCartCount() {
    fetch('backend/api/cart.php')
        .then(response => {
            if(response.ok) {
                return response.json();
            }
            throw new Error('Network response was not ok.');
        })
        .then(data => {
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                if (data.items && data.items.length > 0) {
                    cartCountElement.textContent = data.items.length;
                    cartCountElement.classList.remove('d-none');
                } else {
                    cartCountElement.classList.add('d-none');
                }
            }
        })
        .catch(error => {
            // Silent fail for unauthorized users (not logged in)
            if (!error.message.includes('Unauthorized')) {
                console.error('Error updating cart count:', error);
            }
        });
}

// Function to add product to cart
function addToCart(productId, quantity = 1) {
    fetch('backend/api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        }
        return response.json().then(err => { throw new Error(err.message) });
    })
    .then(data => {
        // Show success message
        showAlert('Success', 'Product added to cart', 'success');
        
        // Update cart count in navbar
        updateCartCount();
    })
    .catch(error => {
        if (error.message.includes('Unauthorized')) {
            // Redirect to login page if not logged in
            window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
        } else {
            // Show error message
            showAlert('Error', error.message, 'danger');
        }
    });
}

// Function to remove item from cart
function removeFromCart(cartItemId) {
    if (confirm('Are you sure you want to remove this item from cart?')) {
        fetch(`backend/api/cart.php?id=${cartItemId}`, {
            method: 'DELETE'
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            return response.json().then(err => { throw new Error(err.message) });
        })
        .then(data => {
            // Reload the cart page
            window.location.reload();
        })
        .catch(error => {
            showAlert('Error', error.message, 'danger');
        });
    }
}

// Function to update cart item quantity
function updateCartQuantity(cartItemId, quantity) {
    if (quantity < 1) {
        return;
    }
    
    fetch('backend/api/cart.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: cartItemId,
            quantity: quantity
        })
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        }
        return response.json().then(err => { throw new Error(err.message) });
    })
    .then(data => {
        // Reload the cart page
        window.location.reload();
    })
    .catch(error => {
        showAlert('Error', error.message, 'danger');
    });
}

// Function to show alert
function showAlert(title, message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.style.zIndex = '9999';
    
    alertDiv.innerHTML = `
        <strong>${title}:</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto dismiss after 3 seconds
    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => {
            alertDiv.remove();
        }, 300);
    }, 3000);
}

// Handle search form submission
const searchForm = document.getElementById('search-form');
if (searchForm) {
    searchForm.addEventListener('submit', function(e) {
        const searchInput = document.getElementById('search-input');
        if (!searchInput.value.trim()) {
            e.preventDefault();
        }
    });
}
