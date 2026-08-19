    </main>
</div>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Custom Admin JS -->
<script>
    // General script for admin panel, e.g., confirming deletions.
    // Submits the matching hidden <form id="formId"> (POST + CSRF token)
    // rather than navigating to a GET URL — a GET-triggered delete can be
    // fired just by visiting a crafted link, bypassing this confirmation
    // entirely, so the actual delete must happen via POST.
    function confirmDelete(formId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }
</script>

</body>
</html>