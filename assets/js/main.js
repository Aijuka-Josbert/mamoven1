// Main JavaScript file for Mama's Oven
$(document).ready(function() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    $('.alert').delay(5000).fadeOut('slow');

    // Update cart count on page load if user is logged in
    if (typeof userLoggedIn !== 'undefined' && userLoggedIn) {
        updateCartCount();
    }

    // Check for order success in URL and show message
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('order_success')) {
        showOrderSuccess(urlParams.get('order_number'));
    }
});

/**
 * Checks if a user is logged in.
 * Relies on a global `userLoggedIn` variable set in PHP.
 * @returns {boolean}
 */
function isLoggedIn() {
    return typeof userLoggedIn !== 'undefined' && userLoggedIn;
}

/**
 * Shows a SweetAlert prompt to log in.
 */
function showLoginRequired() {
    Swal.fire({
        title: 'Login Required',
        text: 'Please log in to add items to your cart.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Login',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#8B4513',
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'auth/login.php?redirect=' + encodeURIComponent(window.location.pathname);
        }
    });
}

/**
 * Displays a success notification.
 * @param {string} message - The message to display.
 */
function showSuccess(message) {
    Swal.fire({
        title: 'Success!',
        text: message,
        icon: 'success',
        timer: 3000,
        showConfirmButton: false,
        timerProgressBar: true,
    });
}

/**
 * Displays an error notification.
 * @param {string} message - The message to display.
 */
function showError(message) {
    Swal.fire({
        title: 'Oops...',
        text: message,
        icon: 'error',
        confirmButtonColor: '#EFA7A7',
    });
}

/**
 * Shows order success message with order details.
 * @param {string} orderNumber - The order number to display.
 */
function showOrderSuccess(orderNumber) {
    Swal.fire({
        title: 'Order Placed Successfully!',
        html: `
            <div style="text-align: center;">
                <i class="fas fa-check-circle" style="color: #28a745; font-size: 3em; margin-bottom: 15px;"></i>
                <p><strong>Order Number:</strong> ${orderNumber}</p>
                <p>Thank you for your order! A confirmation email has been sent to your email address.</p>
                <p>You can track your order status in your order history.</p>
            </div>
        `,
        icon: 'success',
        confirmButtonText: 'View My Orders',
        showCancelButton: true,
        cancelButtonText: 'Continue Shopping',
        confirmButtonColor: '#8B4513',
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'orders.php';
        } else {
            window.location.href = 'products.php';
        }
    });
}

/**
 * Adds a product to the cart via AJAX.
 * @param {number} productId - The ID of the product to add.
 * @param {number} [quantity=1] - The quantity to add.
 */
function addToCart(productId, quantity = 1) {
    if (!isLoggedIn()) {
        showLoginRequired();
        return;
    }

    const button = $(`[onclick="addToCart(${productId}, ${quantity})"]`);
    const originalHtml = button.html();

    $.ajax({
        url: 'api/add_to_cart.php',
        method: 'POST',
        data: { product_id: productId, quantity: quantity },
        dataType: 'json',
        beforeSend: function() {
            button.html('<span class="loading"></span> Adding...').prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                updateCartCount(response.cart_count);
                showSuccess(response.message || 'Product added to cart!');
            } else {
                showError(response.message || 'Could not add product to cart.');
            }
        },
        error: function() {
            showError('An unexpected network error occurred. Please try again.');
        },
        complete: function() {
            button.html(originalHtml).prop('disabled', false);
        }
    });
}

/**
 * Fetches and updates the cart item count in the navbar.
 * @param {number} [count] - Optional count to set directly.
 */
function updateCartCount(count = null) {
    if (count !== null) {
        $('#cart-count').text(count);
        return;
    }

    if (!isLoggedIn()) return;

    $.ajax({
        url: 'api/get_cart_count.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#cart-count').text(response.count);
            }
        }
    });
}

/**
 * Previews an image selected in a file input.
 * @param {HTMLInputElement} input - The file input element.
 * @param {string} previewId - The ID of the img element to show the preview in.
 */
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#' + previewId).attr('src', e.target.result).show();
        };
        reader.readAsDataURL(input.files[0]);
    }
}