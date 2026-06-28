document.addEventListener("DOMContentLoaded", function() {
    const inputs = document.querySelectorAll('.otp-input');
    const hiddenOtp = document.getElementById('final_otp');
    if (!inputs.length || !hiddenOtp) return;
    const form = inputs[0].closest('form');

    inputs.forEach((input, index) => {
        // Handle input change
        input.addEventListener('input', function(e) {
            // Remove non-numeric chars
            this.value = this.value.replace(/[^0-9]/g, '');
            
            if (this.value) {
                // Move to next input
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            }
            updateHiddenOTP();
        });

        // Handle keydown for backspace
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value) {
                if (index > 0) {
                    inputs[index - 1].focus();
                }
            }
        });

        // Handle paste
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, inputs.length);
            for (let i = 0; i < pastedData.length; i++) {
                if (index + i < inputs.length) {
                    inputs[index + i].value = pastedData[i];
                    if (index + i < inputs.length - 1) {
                        inputs[index + i + 1].focus();
                    } else {
                        inputs[index + i].focus();
                    }
                }
            }
            updateHiddenOTP();
        });
    });

    function updateHiddenOTP() {
        let otpValue = '';
        inputs.forEach(inp => otpValue += inp.value);
        hiddenOtp.value = otpValue;
    }
    
    // Check required fields correctly for modern form validation
    if (form) {
        form.addEventListener('submit', function(e) {
            updateHiddenOTP();
            if (hiddenOtp.value.length !== inputs.length) {
                e.preventDefault();
                alert("Please enter the complete 6-digit OTP.");
            }
        });
    }

    // Focus first input on load
    if (inputs.length > 0) inputs[0].focus();
});
