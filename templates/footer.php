    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="bi bi-bee"></i> Knowledge Bee</h5>
                    <p class="mb-0">A social learning platform where knowledge sharing meets community.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Knowledge Bee. All rights reserved.</p>
                    <small class="text-muted">Built with ❤️ for the learning community</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="../assets/js/app.js"></script>
    
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Confirm delete actions
        function confirmDelete(message = 'Are you sure you want to delete this?') {
            return confirm(message);
        }

        // Handle coin spending
        function spendCoins(amount, contentId) {
            if (confirm(`This will cost you ${amount} Buzz Coins. Continue?`)) {
                // You can implement AJAX here or redirect to a handler
                window.location.href = `spend-coins.php?content_id=${contentId}&amount=${amount}`;
            }
        }

        // Handle voting
        function voteContent(contentId, voteType) {
            fetch('vote.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `content_id=${contentId}&vote_type=${voteType}&csrf_token=<?php echo $_SESSION['csrf_token'] ?? ''; ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while voting.');
            });
        }
    </script>
</body>
</html> 