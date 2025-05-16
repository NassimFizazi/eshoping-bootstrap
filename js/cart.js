document.addEventListener('DOMContentLoaded', function() {
    // Handle quantity increment/decrement buttons
    const quantityControls = document.querySelectorAll('.quantity-control');
    
    quantityControls.forEach(control => {
        const decrementBtn = control.querySelector('.decrement-btn');
        const incrementBtn = control.querySelector('.increment-btn');
        const quantityInput = control.querySelector('.quantity-input');
        const cartItemId = quantityInput.getAttribute('data-cart-item-id');
        
        if (decrementBtn && incrementBtn && quantityInput) {
            decrementBtn.addEventListener('click', function() {
                let value = parseInt(quantityInput.value);
                if (value > 1) {
                    value--;
                    quantityInput.value = value;
                    
                    if (cartItemId) {
                        updateCartQuantity(cartItemId, value);
                    }
                }
            });
            
            incrementBtn.addEventListener('click', function() {
                let value = parseInt(quantityInput.value);
                value++;
                quantityInput.value = value;
                
                if (cartItemId) {
                    updateCartQuantity(cartItemId, value);
                }
            });
            
            // Update cart when input value changes directly
            quantityInput.addEventListener('change', function() {
                let value = parseInt(quantityInput.value);
                if (isNaN(value) || value < 1) {
                    value = 1;
                }
                quantityInput.value = value;
                
                if (cartItemId) {
                    updateCartQuantity(cartItemId, value);
                }
            });
        }
    });
    
    // Handle add to cart form on product detail page
    const addToCartForm = document.getElementById('add-to-cart-form');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const productId = addToCartForm.getAttribute('data-product-id');
            const quantityInput = document.getElementById('product-quantity');
            const quantity = parseInt(quantityInput.value);
            
            if (productId && quantity && quantity > 0) {
                addToCart(productId, quantity);
            }
        });
    }
    
    // Handle checkout form submission
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = {
                shipping_address: document.getElementById('shipping-address').value,
                shipping_city: document.getElementById('shipping-city').value,
                shipping_zip_code: document.getElementById('shipping-zip-code').value,
                shipping_phone: document.getElementById('shipping-phone').value,
                payment_method: document.querySelector('input[name="payment_method"]:checked').value
            };
            
            // Submit order
            fetch('backend/api/order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                }
                return response.json().then(err => { 
                    if (err.errors) {
                        throw new Error(Object.values(err.errors).join('<br>'));
                    } else {
                        throw new Error(err.message || 'An error occurred');
                    }
                });
            })
            .then(data => {
                // Redirect to order confirmation page
                window.location.href = `orders.php?id=${data.order_id}&success=1`;
            })
            .catch(error => {
                // Show error message
                const errorDiv = document.getElementById('checkout-errors');
                errorDiv.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
                errorDiv.classList.remove('d-none');
                
                // Scroll to error message
                errorDiv.scrollIntoView({ behavior: 'smooth' });
            });
        });
    }
});
