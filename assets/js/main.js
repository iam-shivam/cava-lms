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

    // 4. Security (disable inspect elements on specific pages)
    var path = window.location.pathname.toLowerCase();
    if (path.includes('course.php') || path.includes('course_play.php')) {
        document.addEventListener('contextmenu', event => event.preventDefault());
        document.onkeydown = function(e) {
            if (e.key === "F12") return false;
            if (e.ctrlKey && e.shiftKey && (e.key === "I" || e.key === "J" || e.key === "C")) return false;
            if (e.ctrlKey && e.key === "U") return false;
        };
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
});
