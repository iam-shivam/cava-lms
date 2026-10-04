            </main>

            <footer class="studio-admin-footer">
                <span>&copy; <?php echo date('Y'); ?> CAVA LMS Portal</span>
                <span>Administration workspace</span>
            </footer>
        </div>
    </div>

    <!-- Global Confirmation Modal -->
    <div class="modal fade" id="globalConfirmModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="globalConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title h5" id="globalConfirmModalLabel">
                        <i class="fa-solid fa-circle-question text-warning me-2" aria-hidden="true"></i>Confirmation required
                    </h2>
                </div>
                <div class="modal-body text-dark" id="globalConfirmMessage">
                    Are you sure you want to proceed?
                </div>
                <div class="modal-footer border-0 pt-0">
                    <form id="globalConfirmForm" method="POST" action="" class="d-flex flex-wrap justify-content-end gap-2">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="confirmed_action" value="1">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="globalConfirmBtn" class="btn btn-primary">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap powers the responsive offcanvas navigation and existing widgets. -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
