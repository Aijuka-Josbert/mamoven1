// Main JavaScript file for Mama's Oven
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if( target.length ) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });

    // Auto-hide alerts after 5 seconds
    $('.alert').delay(5000).fadeOut();
});

// Cart Functions
function addToCart(productId, quantity = 1) {
    if (!isLoggedIn()) {
        showLoginRequired();
        return;
    }

    $.ajax({
        url: 'api/add_to_cart.php',
        method: 'POST',
        data: {
            product_id: productId,
            quantity: quantity
        },
        dataType: 'json',
        beforeSend: function() {
            $(`[data-product-id="${productId}"]`).find('.btn').html('<span class="loading"></span> Adding...');
        },
        success: function(response) {
            if (response.success) {
                updateCartCount();
                showSuccess('Product added to cart!');
                $(`[data-product-id="${productId}"]`).find('.btn').html('<i class="fas fa-cart-plus"></i> Add to Cart');
            } else {
                showError(response.message || 'Failed to add product to cart');
                $(`[data-product-id="${productId}"]`).find('.btn').html('<i class="fas fa-cart-plus"></i> Add to Cart');
            }
        },
        error: function() {
            showError('Network error. Please try again.');
            $(`[data-product-id="${productId}"]`).find('.btn').html('<i class="fas fa-cart-plus"></i> Add to Cart');
        }
    });
}

function updateCartQuantity(cartId, quantity) {
    if (quantity < 1) {
        removeFromCart(cartId);
        return;
    }

    $.ajax({
        url: 'api/update_cart.php',
        method: 'POST',
        data: {
            cart_id: cartId,
            quantity: quantity
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload(); // Reload to update totals
            } else {
                showError(response.message || 'Failed to update cart');
            }
        },
        error: function() {
            showError('Network error. Please try again.');
        }
    });
}

function removeFromCart(cartId) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }

    $.ajax({
        url: 'api/remove_from_cart.php',
        method: 'POST',
        data: {
            cart_id: cartId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $(`[data-cart-id="${cartId}"]`).fadeOut(300, function() {
                    $(this).remove();
                    updateCartCount();
                    updateCartTotals();
                });
                showSuccess('Item removed from cart');
            } else {
                showError(response.message || 'Failed to remove item');
            }
        },
        error: function() {
            showError('Network error. Please try again.');
        }
    });
}

function updateCartCount() {
    $.ajax({
        url: 'api/get_cart_count.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#cart-count').text(response.count);
        }
    });
}

function updateCartTotals() {
    $.ajax({
        url: 'api/get_cart_totals.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#subtotal').text('UGX ' + response.subtotal.toLocaleString());
            $('#total').text('UGX ' + response.total.toLocaleString());
        }
    });
}

// Order Functions
function placeOrder() {
    if (!isLoggedIn()) {
        showLoginRequired();
        return;
    }

    const deliveryAddress = $('#delivery_address').val();
    const deliveryPhone = $('#delivery_phone').val();
    const specialInstructions = $('#special_instructions').val();

    if (!deliveryAddress || !deliveryPhone) {
        showError('Please fill in all required delivery information');
        return;
    }

    $.ajax({
        url: 'api/place_order.php',
        method: 'POST',
        data: {
            delivery_address: deliveryAddress,
            delivery_phone: deliveryPhone,
            special_instructions: specialInstructions
        },
        dataType: 'json',
        beforeSend: function() {
            $('#place-order-btn').html('<span class="loading"></span> Placing Order...').prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                showSuccess('Order placed successfully!');
                setTimeout(function() {
                    window.location.href = 'order-confirmation.php?order=' + response.order_number;
                }, 2000);
            } else {
                showError(response.message || 'Failed to place order');
                $('#place-order-btn').html('Place Order').prop('disabled', false);
            }
        },
        error: function() {
            showError('Network error. Please try again.');
            $('#place-order-btn').html('Place Order').prop('disabled', false);
        }
    });
}

// Product Functions
function loadProducts(category = '', search = '', page = 1) {
    $.ajax({
        url: 'api/get_products.php',
        method: 'GET',
        data: {
            category: category,
            search: search,
            page: page
        },
        dataType: 'json',
        beforeSend: function() {
            $('#products-container').html('<div class="text-center"><div class="loading"></div> Loading products...</div>');
        },
        success: function(response) {
            displayProducts(response.products);
            displayPagination(response.pagination);
        },
        error: function() {
            $('#products-container').html('<div class="alert alert-danger">Failed to load products</div>');
        }
    });
}

function displayProducts(products) {
    let html = '';
    if (products.length === 0) {
        html = '<div class="col-12"><div class="alert alert-info">No products found</div></div>';
    } else {
        products.forEach(function(product) {
            html += `
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="product-card" data-product-id="${product.id}">
                        <img src="${product.image || 'assets/images/placeholder.jpg'}" alt="${product.name}" class="product-image">
                        <div class="product-info">
                            <h5 class="product-name">${product.name}</h5>
                            <p class="product-price">UGX ${product.price.toLocaleString()}</p>
                            <p class="product-description">${product.description || ''}</p>
                            ${product.flavours ? `<p class="text-muted"><strong>Flavours:</strong> ${product.flavours}</p>` : ''}
                            <div class="d-flex gap-2">
                                <a href="product-details.php?id=${product.id}" class="btn btn-outline-primary btn-sm">View Details</a>
                                <button onclick="addToCart(${product.id})" class="btn btn-primary btn-sm">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    $('#products-container').html(html);
}

function displayPagination(pagination) {
    if (pagination.total_pages <= 1) {
        $('#pagination-container').html('');
        return;
    }

    let html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous page
    if (pagination.current_page > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadProducts('', '', ${pagination.current_page - 1})">Previous</a></li>`;
    }
    
    // Page numbers
    for (let i = 1; i <= pagination.total_pages; i++) {
        if (i === pagination.current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadProducts('', '', ${i})">${i}</a></li>`;
        }
    }
    
    // Next page
    if (pagination.current_page < pagination.total_pages) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadProducts('', '', ${pagination.current_page + 1})">Next</a></li>`;
    }
    
    html += '</ul></nav>';
    $('#pagination-container').html(html);
}

// Utility Functions
function isLoggedIn() {
    return typeof userLoggedIn !== 'undefined' && userLoggedIn;
}

function showLoginRequired() {
    Swal.fire({
        title: 'Login Required',
        text: 'Please login to add items to your cart',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Login',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'auth/login.php';
        }
    });
}

function showSuccess(message) {
    Swal.fire({
        title: 'Success!',
        text: message,
        icon: 'success',
        timer: 3000,
        showConfirmButton: false
    });
}

function showError(message) {
    Swal.fire({
        title: 'Error!',
        text: message,
        icon: 'error',
        confirmButtonText: 'OK'
    });
}

function showInfo(message) {
    Swal.fire({
        title: 'Info',
        text: message,
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

function formatCurrency(amount) {
    return 'UGX ' + amount.toLocaleString();
}

// Image preview function
function previewImage(input, targetId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#' + targetId).attr('src', e.target.result).show();
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Form validation
function validateEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    var re = /^[\+]?[0-9\s\-\(\)]{10,}$/;
    return re.test(phone);
}

// Print function for order receipts
function printOrder() {
    window.print();
}
