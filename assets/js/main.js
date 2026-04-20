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

    initGlobalRevealAnimations();
});

function initGlobalRevealAnimations() {
    const elements = document.querySelectorAll('.animate-on-scroll');
    if (!elements.length) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        elements.forEach((el) => el.classList.add('visible'));
        return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    elements.forEach((el) => observer.observe(el));
}

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
                <p>Thank you for your order! A confirmation email will be sent shortly when mail delivery is available.</p>
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
let latestReviewData = null;

function getProductIdFromUrl() {
    const productId = new URLSearchParams(window.location.search).get('id');
    return productId ? parseInt(productId, 10) : 0;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function renderStarsText(ratingValue) {
    const safeRating = Math.max(0, Math.min(5, Math.round(Number(ratingValue) || 0)));
    return '★'.repeat(safeRating) + '☆'.repeat(5 - safeRating);
}

function formatReviewComment(comment) {
    const clean = String(comment ?? '').trim();
    if (!clean) {
        return 'No comment provided.';
    }
    return escapeHtml(clean).replace(/\n/g, '<br>');
}

function formatReviewDate(dateValue) {
    const date = new Date(dateValue);
    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function reviewWasEdited(review) {
    if (!review || !review.updated_at || !review.created_at) {
        return false;
    }

    const updatedAt = new Date(review.updated_at).getTime();
    const createdAt = new Date(review.created_at).getTime();

    if (Number.isNaN(updatedAt) || Number.isNaN(createdAt)) {
        return false;
    }

    return updatedAt - createdAt > 3000;
}

function highlightStars(val) {
    const interactiveStars = document.querySelectorAll('.star-interactive');
    const safeValue = parseInt(val, 10) || 0;

    interactiveStars.forEach((star) => {
        const starValue = parseInt(star.getAttribute('data-value'), 10) || 0;
        star.style.color = starValue <= safeValue ? '#FFD700' : '#ccc';
    });
}

function updateReviewCharCounter() {
    const commentInput = document.getElementById('comment');
    const counter = document.getElementById('review-char-counter');
    if (!commentInput || !counter) {
        return;
    }

    counter.textContent = `${commentInput.value.length}/500`;
}

function resetReviewEditor(clearInputs) {
    const reviewIdInput = document.getElementById('review-id-input');
    const reviewTitle = document.getElementById('review-form-title');
    const submitBtn = document.getElementById('review-submit-btn');
    const cancelBtn = document.getElementById('review-cancel-btn');
    const ratingInput = document.getElementById('rating-input');
    const commentInput = document.getElementById('comment');

    if (reviewIdInput) {
        reviewIdInput.value = '';
    }

    if (reviewTitle) {
        reviewTitle.textContent = 'Leave a Review';
    }

    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Submit Review';
    }

    if (cancelBtn) {
        cancelBtn.classList.add('d-none');
    }

    if (clearInputs) {
        if (ratingInput) {
            ratingInput.value = '0';
        }
        if (commentInput) {
            commentInput.value = '';
        }
    }

    highlightStars(ratingInput ? ratingInput.value : 0);
    updateReviewCharCounter();
}

function beginEditReview(reviewId) {
    if (!latestReviewData || !Array.isArray(latestReviewData.reviews)) {
        showError('Review data is not ready yet. Please try again.');
        return;
    }

    const targetReview = latestReviewData.reviews.find((review) => {
        return Number(review.id) === Number(reviewId) && Number(review.can_edit) === 1;
    });

    if (!targetReview) {
        showError('You can only edit your own review.');
        return;
    }

    const reviewIdInput = document.getElementById('review-id-input');
    const reviewTitle = document.getElementById('review-form-title');
    const submitBtn = document.getElementById('review-submit-btn');
    const cancelBtn = document.getElementById('review-cancel-btn');
    const ratingInput = document.getElementById('rating-input');
    const commentInput = document.getElementById('comment');
    const reviewForm = document.getElementById('review-form');

    if (!ratingInput || !commentInput || !reviewForm) {
        showError('Review form is unavailable.');
        return;
    }

    if (reviewIdInput) {
        reviewIdInput.value = String(targetReview.id);
    }
    ratingInput.value = String(targetReview.rating || 0);
    commentInput.value = targetReview.comment || '';

    if (reviewTitle) {
        reviewTitle.textContent = 'Edit Your Review';
    }

    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-pen-to-square me-2"></i> Update Review';
    }

    if (cancelBtn) {
        cancelBtn.classList.remove('d-none');
    }

    highlightStars(ratingInput.value);
    updateReviewCharCounter();
    reviewForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function renderReviewsSummary(reviewData) {
    const summaryPanel = $('#reviews-summary');
    if (!summaryPanel.length) {
        return;
    }

    const totalReviews = Number(reviewData.review_count || 0);
    const avgRating = Number(reviewData.avg_rating || 0);
    const verifiedCount = Number(reviewData.verified_count || 0);

    if (totalReviews <= 0) {
        summaryPanel.addClass('d-none');
        return;
    }

    $('#summary-average-rating').text(avgRating.toFixed(1));
    $('#summary-stars').text(renderStarsText(avgRating));
    $('#summary-review-count').text(totalReviews);
    $('#summary-verified-count').text(verifiedCount);

    const breakdown = reviewData.rating_breakdown || {};
    let rows = '';

    for (let star = 5; star >= 1; star--) {
        const count = Number(breakdown[star] || 0);
        const percentage = totalReviews > 0 ? Math.round((count / totalReviews) * 100) : 0;

        rows += `
            <div class="rating-breakdown-row">
                <span class="rating-breakdown-label">${star}★</span>
                <div class="progress flex-grow-1" role="progressbar" aria-label="${star} star reviews" aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar" style="width: ${percentage}%"></div>
                </div>
                <span class="rating-breakdown-count">${count}</span>
            </div>
        `;
    }

    $('#rating-breakdown').html(rows);
    summaryPanel.removeClass('d-none');
}

function loadProductReviews(productId) {
    $.ajax({
        url: BASE_URL + '/api/get_reviews.php',
        method: 'GET',
        data: { product_id: productId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                latestReviewData = response;
                renderReviewsSummary(response);
                displayReviews(response);
            }
        },
        error: function(xhr) {
            let serverMessage = '';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                serverMessage = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    const parsed = JSON.parse(xhr.responseText);
                    serverMessage = parsed.message || '';
                } catch (e) {
                    serverMessage = '';
                }
            }

            $('#reviews-container').html('<p class="text-muted">' + (serverMessage || 'Could not load reviews.') + '</p>');
        }
    });
}

/**
 * Displays reviews in the reviews container
 */
function displayReviews(reviewData) {
    let html = '';

    if (Number(reviewData.review_count) === 0) {
        html = '<p class="text-muted text-center">No reviews yet. Be the first to review this product!</p>';
    } else {
        reviewData.reviews.forEach(function(review) {
            const canEdit = Number(review.can_edit) === 1;
            let actionButtons = '';

            if (canEdit) {
                actionButtons += `<button type="button" class="btn btn-sm btn-outline-primary" onclick="beginEditReview(${Number(review.id)})"><i class="fas fa-pen-to-square me-1"></i>Edit</button>`;
            }

            if (reviewData.is_admin) {
                actionButtons += ` <button class="btn btn-sm btn-danger" onclick="deleteReview(${Number(review.id)})"><i class="fas fa-trash me-1"></i>Delete</button>`;
            }

            const verifiedBadge = Number(review.is_verified_purchase) === 1
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle ms-2">Verified purchase</span>'
                : '';
            const ownBadge = canEdit ? '<span class="badge bg-info-subtle text-info border border-info-subtle ms-2 review-own-badge">Your review</span>' : '';
            const editedNote = reviewWasEdited(review) ? '<span class="review-edited-note">(edited)</span>' : '';
            const starsHtml = renderStarsText(review.rating);
            const reviewDate = formatReviewDate(review.created_at);
            const safeName = escapeHtml(review.full_name || 'Customer');
            const safeComment = formatReviewComment(review.comment);

            const actionsHtml = actionButtons.trim() !== ''
                ? `<div class="review-actions d-flex align-items-center gap-2">${actionButtons}</div>`
                : '';
            
            html += `
                <div class="review-card mb-3 p-3 border rounded">
                    <div class="review-header d-flex justify-content-between align-items-center">
                        <div>
                            <div class="review-author fw-bold">${safeName}${verifiedBadge}${ownBadge}</div>
                            <div class="review-date text-muted small">${reviewDate} ${editedNote}</div>
                        </div>
                        ${actionsHtml}
                    </div>
                    <div class="stars mb-2" style="color: #FFD700;">${starsHtml}</div>
                    <p class="mb-0 review-comment-body">${safeComment}</p>
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
    const commentInput = document.getElementById('comment');
    const cancelBtn = document.getElementById('review-cancel-btn');
    const reviewForm = document.getElementById('review-form');
    const productId = getProductIdFromUrl();
    
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
                let val = ratingInput ? ratingInput.value : 0;
                highlightStars(val);
            });
            
            star.addEventListener('click', function() {
                let val = this.getAttribute('data-value');
                if (ratingInput) {
                    ratingInput.value = val;
                }
                highlightStars(val);
            });
        });
    }

    if (commentInput) {
        commentInput.addEventListener('input', updateReviewCharCounter);
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            resetReviewEditor(true);
        });
    }

    updateReviewCharCounter();
    highlightStars(ratingInput ? ratingInput.value : 0);

    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const rating = ratingInput ? parseInt(ratingInput.value, 10) : 0;
            const comment = document.getElementById('comment')?.value;
            const reviewId = parseInt(document.getElementById('review-id-input')?.value || '0', 10);
            const submitBtn = document.getElementById('review-submit-btn');
            const originalSubmitHtml = submitBtn ? submitBtn.innerHTML : '';

            if (!rating || rating === 0) {
                showError('Please select a rating');
                return;
            }

            if (!productId) {
                showError('Missing product reference. Please reload and try again.');
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="loading"></span> Saving...';
            }

            $.ajax({
                url: BASE_URL + '/api/submit_review.php',
                method: 'POST',
                data: { 
                    product_id: productId, 
                    rating: rating, 
                    comment: comment,
                    review_id: reviewId > 0 ? reviewId : ''
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showSuccess(response.message);
                        resetReviewEditor(true);
                        // Reload reviews
                        loadProductReviews(productId);
                    } else {
                        showError(response.message);
                    }
                },
                error: function(xhr) {
                    let serverMessage = '';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        serverMessage = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            const parsed = JSON.parse(xhr.responseText);
                            serverMessage = parsed.message || '';
                        } catch (e) {
                            serverMessage = '';
                        }
                    }

                    showError(serverMessage || 'Could not submit review');
                },
                complete: function() {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalSubmitHtml;
                    }
                }
            });
        });
    }

    if (productId) {
        loadProductReviews(productId);
    }
});

/**
 * Applies a promo code to the order
 */
function applyPromoCode() {
    const promoInput = document.getElementById('promo-code');
    const promoCode = (promoInput?.value || '').trim().toUpperCase();
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
                if (promoInput) {
                    promoInput.value = promoCode;
                }
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
                const hiddenPromoField = document.getElementById('promo-code-hidden');
                if (hiddenPromoField) {
                    hiddenPromoField.value = '';
                }
                sessionStorage.removeItem('promo_discount');
                sessionStorage.removeItem('promo_id');
                if (typeof window.setCurrentDiscount === 'function') {
                    window.setCurrentDiscount(0);
                }
                showError(response.message);
            }
        },
        error: function() {
            const hiddenPromoField = document.getElementById('promo-code-hidden');
            if (hiddenPromoField) {
                hiddenPromoField.value = '';
            }
            sessionStorage.removeItem('promo_discount');
            sessionStorage.removeItem('promo_id');
            if (typeof window.setCurrentDiscount === 'function') {
                window.setCurrentDiscount(0);
            }
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

    const modalTotalElement = document.getElementById('modal-total-amount');
    if (modalTotalElement) {
        modalTotalElement.textContent = 'UGX ' + newTotal.toLocaleString();
    }

    if (typeof window.setCurrentDiscount === 'function') {
        window.setCurrentDiscount(discount);
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
