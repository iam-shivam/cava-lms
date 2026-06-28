            </div> <!-- end main-content -->
        </div> <!-- end col-md-9 -->
    </div> <!-- end row -->
</div> <!-- end container-fluid -->

<!-- Global Confirmation Modal -->
<div class="modal fade" id="globalConfirmModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="globalConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="globalConfirmModalLabel"><i class="fa-solid fa-circle-question text-warning me-2"></i>Confirmation Required</h5>
            </div>
            <div class="modal-body fs-5 text-dark" id="globalConfirmMessage">
                Are you sure you want to proceed?
            </div>
            <div class="modal-footer border-0 pt-0">
                <form id="globalConfirmForm" method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="confirmed_action" value="1">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="globalConfirmBtn" class="btn btn-primary rounded-pill px-4">OK</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Global Script -->
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
