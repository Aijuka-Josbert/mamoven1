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

    // Improved selector to find the button that triggered this
    // Handles cases where quantity is implicit (onclick="addToCart(1)") or explicit
    let button = $(`[onclick="addToCart(${productId}, ${quantity})"]`);
    if (button.length === 0) {
        button = $(`[onclick="addToCart(${productId})"]`);
    }
    // Fallback for spacing differences
    if (button.length === 0) {
        button = $(`button[onclick*="addToCart(${productId}"]`);
    }

    const originalHtml = button.html();

    $.ajax({
        url: BASE_URL + '/api/add_to_cart.php',  // Use absolute URL
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
        error: function(xhr, status, error) {
            console.error('Cart error:', status, error);
            console.error('Response:', xhr.responseText);
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
        const $cartBadge = $('#cart-count');
        $cartBadge.text(count);
        if (count > 0) {
            $cartBadge.show();
        } else {
            $cartBadge.hide();
        }
        return;
    }

    if (!isLoggedIn()) return;

    $.ajax({
        url: BASE_URL + '/api/get_cart_count.php',
        method: 'GET',
        dataType: 'json',
        cache: false,
        timeout: 5000,
        success: function(response) {
            if (response && typeof response.count !== 'undefined') {
                const cartCount = parseInt(response.count) || 0;
                const $cartBadge = $('#cart-count');
                $cartBadge.text(cartCount);
                if (cartCount > 0) {
                    $cartBadge.show();
                } else {
                    $cartBadge.hide();
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Cart count error:', status, error, 'Response:', xhr.responseText);
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

// ===== NEW FEATURES =====

/**
 * Loads reviews for a product
 */
function loadProductReviews(productId) {
    $.ajax({
        url: BASE_URL + '/api/get_reviews.php',
        method: 'GET',
        data: { product_id: productId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayReviews(response);
            }
        },
        error: function() {
            $('#reviews-container').html('<p class="text-muted">Could not load reviews.</p>');
        }
    });
}

/**
 * Displays reviews in the reviews container
 */
function displayReviews(reviewData) {
    let html = '';

    if (reviewData.review_count === 0) {
        html = '<p class="text-muted text-center">No reviews yet. Be the first to review this product!</p>';
    } else {
        reviewData.reviews.forEach(function(review) {
            let deleteBtn = '';
            if (reviewData.is_admin) {
                deleteBtn = `<button class="btn btn-sm btn-danger float-end" onclick="deleteReview(${review.id})"><i class="fas fa-trash"></i></button>`;
            }
            let starsHtml = '';
            for (let i = 0; i < review.rating; i++) starsHtml += '★';
            for (let i = review.rating; i < 5; i++) starsHtml += '☆';
            
            html += `
                <div class="review-card mb-3 p-3 border rounded">
                    <div class="review-header d-flex justify-content-between align-items-center">
                        <div>
                            <div class="review-author fw-bold">${review.full_name}</div>
                            <div class="review-date text-muted small">${new Date(review.created_at).toLocaleDateString()}</div>
                        </div>
                        ${deleteBtn}
                    </div>
                    <div class="stars mb-2" style="color: #FFD700;">${starsHtml}</div>
                    <p class="mb-0">${review.comment || 'No comment provided.'}</p>
                </div>
            `;
        });
    }

    $('#reviews-container').html(html);
}

/**
 * Deletes a product review
 */
function deleteReview(reviewId) {
    if (confirm('Are you sure you want to delete this review?')) {
        $.ajax({
            url: BASE_URL + '/api/delete_review.php',
            method: 'POST',
            data: { review_id: reviewId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess('Review deleted.');
                    const productId = new URLSearchParams(window.location.search).get('id');
                    loadProductReviews(productId);
                } else {
                    showError(response.message || 'Could not delete review.');
                }
            }
        });
    }
}

/**
 * Submits a product review
 */
document.addEventListener('DOMContentLoaded', function() {
    // Interactive Stars setup
    const interactiveStars = document.querySelectorAll('.star-interactive');
    const ratingInput = document.getElementById('rating-input');
    
    if(interactiveStars.length > 0) {
        interactiveStars.forEach(star => {
            star.style.cursor = 'pointer';
            star.style.color = '#ccc';
            star.style.fontSize = '2rem';
            star.style.display = 'inline-block';
            star.style.transition = 'color 0.2s';
            
            star.addEventListener('mouseover', function() {
                let val = this.getAttribute('data-value');
                highlightStars(val);
            });
            
            star.addEventListener('mouseout', function() {
                let val = ratingInput.value;
                highlightStars(val);
            });
            
            star.addEventListener('click', function() {
                let val = this.getAttribute('data-value');
                ratingInput.value = val;
                highlightStars(val);
            });
        });
    }

    function highlightStars(val) {
        interactiveStars.forEach(star => {
            if (star.getAttribute('data-value') <= val) {
                star.style.color = '#FFD700'; // Gold
            } else {
                star.style.color = '#ccc'; // Gray
            }
        });
    }

    const reviewForm = document.getElementById('review-form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const productId = new URLSearchParams(window.location.search).get('id');
            const rating = ratingInput ? ratingInput.value : 0;
            const comment = document.getElementById('comment')?.value;

            if (!rating || rating == 0) {
                showError('Please select a rating');
                return;
            }

            $.ajax({
                url: BASE_URL + '/api/submit_review.php',
                method: 'POST',
                data: { 
                    product_id: productId, 
                    rating: rating, 
                    comment: comment 
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showSuccess(response.message);
                        reviewForm.reset();
                        // Reload reviews
                        loadProductReviews(productId);
                    } else {
                        showError(response.message);
                    }
                },
                error: function() {
                    showError('Could not submit review');
                }
            });
        });

        // Load reviews on page load
        const productId = new URLSearchParams(window.location.search).get('id');
        if (productId) {
            loadProductReviews(productId);
        }
    }
});

/**
 * Applies a promo code to the order
 */
function applyPromoCode() {
    const promoCode = document.getElementById('promo-code')?.value;
    const subtotalText = document.getElementById('subtotal-amount')?.textContent || '0';
    // Extract numeric value from "UGX 50,000" format
    const subtotal = parseFloat(subtotalText.replace(/[^\d]/g, '')) || 0;

    if (!promoCode) {
        showError('Please enter a promo code');
        return;
    }

    $.ajax({
        url: BASE_URL + '/api/apply_promo.php',
        method: 'POST',
        data: { 
            promo_code: promoCode,
            subtotal: subtotal
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showSuccess('Promo code applied!');
                // Update hidden field for form submission
                const hiddenPromoField = document.getElementById('promo-code-hidden');
                if (hiddenPromoField) {
                    hiddenPromoField.value = promoCode;
                }
                // Store the discount for use during checkout
                sessionStorage.setItem('promo_discount', response.discount);
                sessionStorage.setItem('promo_id', response.promo_id);
                // Update the UI to show discount
                updateOrderSummaryWithDiscount(response.discount);
            } else {
                showError(response.message);
            }
        },
        error: function() {
            showError('Could not apply promo code');
        }
    });
}

/**
 * Cancels an order
 */
function cancelOrder(orderId) {
    Swal.fire({
        title: 'Cancel Order?',
        text: 'Are you sure? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Cancel It',
        cancelButtonText: 'Keep Order',
        confirmButtonColor: '#dc3545',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + '/api/cancel_order.php',
                method: 'POST',
                data: { order_id: orderId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showSuccess(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        showError(response.message);
                    }
                },
                error: function() {
                    showError('Could not cancel order');
                }
            });
        }
    });
}

/**
 * Updates order summary with discount
 */
function updateOrderSummaryWithDiscount(discount) {
    const subtotalText = document.getElementById('subtotal-amount')?.textContent || '0';
    const deliveryFeeText = document.getElementById('delivery-fee')?.textContent || '0';
    
    const subtotal = parseFloat(subtotalText.replace(/[^\d]/g, '')) || 0;
    const deliveryFee = parseFloat(deliveryFeeText.replace(/[^\d]/g, '')) || 0;
    const newTotal = subtotal + deliveryFee - discount;

    // Update UI if elements exist
    const discountElement = document.getElementById('discount-amount');
    const discountRow = document.getElementById('discount-row');
    const totalElement = document.getElementById('total-amount');
    
    if (discountElement) {
        discountElement.textContent = 'UGX ' + discount.toLocaleString();
    }
    if (discountRow) {
        discountRow.style.display = 'flex';
    }
    if (totalElement) {
        totalElement.textContent = 'UGX ' + newTotal.toLocaleString();
    }
}
/**
 * Confirm order receipt
 */
function confirmReceipt(orderId) {
    Swal.fire({
        title: 'Confirm Receipt?',
        text: 'Are you sure you have received your order?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, I received it',
        cancelButtonText: 'Not yet',
        confirmButtonColor: '#28a745',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + '/api/confirm_receipt.php',
                method: 'POST',
                data: { order_id: orderId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showSuccess(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showError(response.message);
                    }
                },
                error: function() {
                    showError('Could not confirm receipt');
                }
            });
        }
    });
}
