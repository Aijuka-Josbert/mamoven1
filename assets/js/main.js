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
        text: 'You need to be logged in to perform this action.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#A7D7C5',
        cancelButtonColor: '#EFA7A7',
        confirmButtonText: 'Login Now',
    }).then((result) => {
        if (result.isConfirmed) {
            // Assumes there's a global BASE_URL variable for consistent paths
            window.location.href = `${BASE_URL}/auth/login.php`;
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
        url: `${BASE_URL}/api/add_to_cart.php`,
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
        $('#cart-count').text(count).show();
        return;
    }

    $.ajax({
        url: `${BASE_URL}/api/get_cart_count.php`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response && typeof response.count !== 'undefined') {
                if(response.count > 0) {
                   $('#cart-count').text(response.count).show();
                } else {
                   $('#cart-count').hide();
                }
            }
        },
        error: function() {
            console.error("Could not fetch cart count.");
        }
    });
}

/**
 * Previews an image selected in a file input.
 * @param {HTMLInputElement} input - The file input element.
 * @param {string} previewId - The ID of the img element to show the preview in.
 */
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}