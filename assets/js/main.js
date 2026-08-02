// CAVA LMS Global JavaScript Helper

// Global functions for admin video syllabus builder
window.toggleSectionEdit = function(id, event) {
    if (event) event.stopPropagation();
    let viewDiv = document.getElementById('view_sec_' + id);
    let editDiv = document.getElementById('edit_sec_' + id);
    let inputEl = document.getElementById('input_sec_' + id);
    if (!viewDiv || !editDiv || !inputEl) return;
    
    if (viewDiv.classList.contains('d-none')) {
        viewDiv.classList.remove('d-none');
        viewDiv.classList.add('d-flex');
        editDiv.classList.remove('d-flex');
        editDiv.classList.add('d-none');
        inputEl.value = document.getElementById('text_sec_' + id).innerText;
    } else {
        viewDiv.classList.remove('d-flex');
        viewDiv.classList.add('d-none');
        editDiv.classList.remove('d-none');
        editDiv.classList.add('d-flex');
        inputEl.focus();
    }
};

window.saveSection = function(id, event) {
    if (event) event.stopPropagation();
    let newTitle = document.getElementById('input_sec_' + id).value;
    if (newTitle.trim() !== "") {
        document.getElementById('edit_section_id').value = id;
        document.getElementById('edit_section_title').value = newTitle;
        document.getElementById('edit_section_form').submit();
    }
};

// Global functions for Course Play OTP logic
window.sendVideoOtp = function(videoId) {
    document.getElementById('otp-request-block').style.display = 'none';
    document.getElementById('otp-verify-block').style.display = 'block';
    document.getElementById('otp_message').innerText = "Sending OTP...";
    document.getElementById('otp_message').className = "text-info d-block mt-2";
    
    fetch('api/video_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=send&video_id=' + videoId
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('otp_message').innerText = data.message;
        if(!data.success) {
            document.getElementById('otp_message').className = "text-danger d-block mt-2";
            document.getElementById('otp-request-block').style.display = 'block';
            document.getElementById('otp-verify-block').style.display = 'none';
        } else {
            document.getElementById('otp_message').className = "text-success d-block mt-2";
        }
    })
    .catch(e => {
        document.getElementById('otp_message').innerText = "Error sending OTP.";
        document.getElementById('otp_message').className = "text-danger d-block mt-2";
    });
};

window.verifyVideoOtp = function(videoId) {
    let otp = document.getElementById('video_otp_input').value;
    if(!otp) return;
    
    document.getElementById('otp_message').innerText = "Verifying...";
    document.getElementById('otp_message').className = "text-info d-block mt-2";
    
    fetch('api/video_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=verify&video_id=' + videoId + '&otp=' + otp
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            document.getElementById('otp_message').innerText = "Success! Reloading video...";
            document.getElementById('otp_message').className = "text-success d-block mt-2";
            window.location.reload();
        } else {
            document.getElementById('otp_message').innerText = data.message;
            document.getElementById('otp_message').className = "text-danger d-block mt-2";
        }
    })
    .catch(e => {
        document.getElementById('otp_message').innerText = "Error verifying OTP.";
        document.getElementById('otp_message').className = "text-danger d-block mt-2";
    });
};
// Global function for dynamic confirmation modal
window.confirmAction = function(event, message, url) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    document.getElementById('globalConfirmMessage').innerText = message;
    document.getElementById('globalConfirmForm').setAttribute('action', url);
    var myModal = new bootstrap.Modal(document.getElementById('globalConfirmModal'));
    myModal.show();
};
document.addEventListener('DOMContentLoaded', function () {
    // 1. Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // 2. Alerts Auto Fade
    var alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // 3. Toasts initialization (from config.php)
    var toastEl = document.getElementById("flashToast");
    if (toastEl && typeof bootstrap !== 'undefined') {
        var toast = new bootstrap.Toast(toastEl, {delay: 5000});
        toast.show();
    }

    // 4. Security (disable context menu & inspect shortcuts on Courses, Webinars, Events, and Document Viewers)
    var path = window.location.pathname.toLowerCase();
    if (path.includes('course.php') || path.includes('course_play.php') || path.includes('webinars.php') || path.includes('webinar') || path.includes('events.php') || path.includes('event') || path.includes('document_viewer.php')) {
        document.addEventListener('contextmenu', function(event) {
            event.preventDefault();
            return false;
        });
        document.addEventListener('keydown', function(e) {
            // Block F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C, Ctrl+U, Ctrl+S, Ctrl+P
            if (e.key === "F12") { e.preventDefault(); return false; }
            if (e.ctrlKey && e.shiftKey && (e.key === "I" || e.key === "i" || e.key === "J" || e.key === "j" || e.key === "C" || e.key === "c")) { e.preventDefault(); return false; }
            if (e.ctrlKey && (e.key === "U" || e.key === "u" || e.key === "S" || e.key === "s" || e.key === "P" || e.key === "p")) { e.preventDefault(); return false; }
        });
    }

    // 5. Admin Course Form Partial Payment toggle
    var partialPaymentCb = document.getElementById('allow_partial_payment');
    var minInstallmentContainer = document.getElementById('min_installment_container');
    if (partialPaymentCb && minInstallmentContainer) {
        partialPaymentCb.addEventListener('change', function() {
            minInstallmentContainer.style.display = this.checked ? 'block' : 'none';
        });
    }

    // 6. Razorpay Checkout Initialization
    var rzpData = document.getElementById('razorpay-data');
    if (rzpData && typeof Razorpay !== 'undefined') {
        var options = {
            "key": rzpData.getAttribute('data-key'),
            "amount": rzpData.getAttribute('data-amount'),
            "currency": "INR",
            "name": "CAVA LMS Portal",
            "description": rzpData.getAttribute('data-title'),
            "order_id": rzpData.getAttribute('data-orderid'),
            "handler": function (response){
                window.location.href = "payment_callback.php?razorpay_payment_id=" + response.razorpay_payment_id + 
                                       "&razorpay_order_id=" + response.razorpay_order_id + 
                                       "&razorpay_signature=" + response.razorpay_signature;
            },
            "prefill": {
                "name": rzpData.getAttribute('data-name'),
                "email": rzpData.getAttribute('data-email')
            },
            "theme": {
                "color": "#6f42c1"
            },
            "modal": {
                "ondismiss": function(){
                    window.location.href = "index.php";
                }
            }
        };
        
        var rzp1 = new Razorpay(options);
        rzp1.on('payment.failed', function (response){
            window.location.href = "payment_callback.php?error=payment_failed&razorpay_order_id=" + response.error.metadata.order_id;
        });
        
        rzp1.open();
    }

    // 7. AJAX Search and Search Clear Button
    var searchInput = document.getElementById('search');
    var clearBtn = document.getElementById('search-clear');
    
    if (searchInput) {
        var debounceTimer;
        
        // Detect which grid page we're on
        var gridId = '';
        if (document.getElementById('courses-grid')) gridId = 'courses-grid';
        else if (document.getElementById('webinars-grid')) gridId = 'webinars-grid';
        else if (document.getElementById('events-grid')) gridId = 'events-grid';
        
        // After AJAX search, update all filter tab links and sort options to include the new search term
        // This ensures clicking category tabs or changing sort after searching still works correctly
        function syncFilterLinks(searchValue) {
            // Sync .filter-link tab hrefs
            document.querySelectorAll('a.filter-link').forEach(function(link) {
                try {
                    var url = new URL(link.href);
                    if (searchValue && searchValue.trim() !== '') {
                        url.searchParams.set('search', searchValue.trim());
                    } else {
                        url.searchParams.delete('search');
                    }
                    link.href = url.toString();
                } catch(e) {}
            });
            
            // Sync .sort-select option values
            document.querySelectorAll('select.sort-select option').forEach(function(option) {
                try {
                    var url = new URL(option.value);
                    if (searchValue && searchValue.trim() !== '') {
                        url.searchParams.set('search', searchValue.trim());
                    } else {
                        url.searchParams.delete('search');
                    }
                    option.value = url.toString();
                } catch(e) {}
            });
        }
        
        var triggerSearch = function() {
            var form = searchInput.form;
            var url = new URL(form.action);
            var formData = new FormData(form);
            var searchVal = searchInput.value.trim();
            
            // Build URL from form data (includes hidden inputs like category/sort)
            for (var pair of formData.entries()) {
                var key = pair[0];
                var val = pair[1].trim();
                if (val !== '') {
                    url.searchParams.set(key, val);
                } else {
                    url.searchParams.delete(key);
                }
            }
            
            // Show / hide clear button
            if (clearBtn) {
                clearBtn.style.display = searchVal !== '' ? 'block' : 'none';
            }
            
            if (gridId) {
                fetch(url.toString())
                .then(function(res) { return res.text(); })
                .then(function(html) {
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    var newGrid = doc.getElementById(gridId);
                    var currentGrid = document.getElementById(gridId);
                    
                    if (currentGrid && newGrid) {
                        currentGrid.innerHTML = newGrid.innerHTML;
                    }
                    
                    // Push new URL to browser history
                    window.history.pushState({}, '', url.toString());
                    
                    // Sync all filter links and sort options to include current search value
                    syncFilterLinks(searchVal);
                })
                .catch(function(err) {
                    console.error('AJAX search error:', err);
                });
            } else {
                form.submit();
            }
        };

        // Trigger search on input (debounced 300ms)
        searchInput.addEventListener('input', function() {
            if (clearBtn) {
                clearBtn.style.display = searchInput.value.trim() !== '' ? 'block' : 'none';
            }
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(triggerSearch, 300);
        });

        // Intercept form submit
        searchInput.form.addEventListener('submit', function(e) {
            e.preventDefault();
            clearTimeout(debounceTimer);
            triggerSearch();
        });

        // Clear button: do a clean full-page redirect to current URL without search param
        // (avoids stale state from repeated AJAX calls)
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                var url = new URL(window.location.href);
                url.searchParams.delete('search');
                window.location.href = url.toString();
            });
        }
    }

    // 8. Global Form Submit Loader
    // Automatically attach a spinner and disable submit buttons to prevent double-submissions
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            // Use setTimeout to allow other submit listeners (like AJAX search) to run first and call preventDefault()
            setTimeout(function() {
                if (e.defaultPrevented) return;
                
                // If the form is already submitting, prevent duplicate submission
                if (form.classList.contains('is-submitting')) {
                    return;
                }
                
                // Mark the form as submitting
                form.classList.add('is-submitting');
                
                // Prevent all other user inputs on the form
                form.style.pointerEvents = 'none';
                form.style.opacity = '0.8';

                // Create a full page visual overlay to lock the screen
                var overlay = document.createElement('div');
                overlay.id = 'submit-block-overlay';
                overlay.style.position = 'fixed';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.width = '100vw';
                overlay.style.height = '100vh';
                overlay.style.zIndex = '9999999';
                overlay.style.cursor = 'wait';
                overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.6)';
                overlay.style.backdropFilter = 'blur(3px)';
                overlay.style.display = 'flex';
                overlay.style.flexDirection = 'column';
                overlay.style.justifyContent = 'center';
                overlay.style.alignItems = 'center';
                
                // Add a large spinner in the center of the screen
                overlay.innerHTML = '<i class="comet-spinner" style="font-size: 3rem; color: #6f42c1;"></i><h5 class="mt-3 text-dark fw-bold">Please wait...</h5>';
                document.body.appendChild(overlay);
                
                // Block keyboard interactions (Tab, Enter, etc.) while processing
                document.addEventListener('keydown', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }, { capture: true });
                
                // Find the submit button for this form
                var submitBtn = form.querySelector('button[type="submit"], input[type="submit"], button:not([type="button"]):not([type="reset"])');
                
                if (submitBtn && !submitBtn.hasAttribute('data-no-loader')) {
                    if (submitBtn.tagName.toLowerCase() === 'input') {
                        submitBtn.value = 'Processing...';
                    } else {
                        // If it doesn't already have the comet-spinner
                        if (submitBtn.innerHTML.indexOf('comet-spinner') === -1 && submitBtn.innerHTML.indexOf('fa-spinner') === -1) {
                            var originalText = submitBtn.innerHTML;
                            submitBtn.setAttribute('data-original-text', originalText);
                            
                            var loadText = "Processing...";
                            var lowerText = originalText.toLowerCase();
                            if (lowerText.includes("upload") || lowerText.includes("add video") || lowerText.includes("import")) {
                                loadText = "Uploading...";
                            } else if (lowerText.includes("save") || lowerText.includes("update") || lowerText.includes("create") || lowerText.includes("add")) {
                                loadText = "Saving...";
                            }
                            
                            // Using the user-requested comet-spinner class
                            submitBtn.innerHTML = '<i class="comet-spinner me-2"></i>' + loadText;
                        }
                    }
                    submitBtn.classList.add('disabled');
                }
            }, 10);
        });
    });
});
