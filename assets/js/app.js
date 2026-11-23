// SmartRent client-side behaviour: form validation and small interface helpers.
// The server (PHP) re-checks everything, so this is for fast user feedback only.
document.addEventListener('DOMContentLoaded', function () {

    // Show or clear an error message under an input
    function setError(input, message) {
        var box = input.parentNode.querySelector('.field-error');
        if (!box) {
            box = document.createElement('span');
            box.className = 'field-error';
            input.parentNode.appendChild(box);
        }
        box.textContent = message;
        return message === '';
    }

    function field(form, name) {
        return form.querySelector('[name="' + name + '"]');
    }

    // Tenant registration: phone format and password length
    var tenantForm = field(document, 'phone_number') && field(document, 'password')
        ? field(document, 'phone_number').form : null;
    if (tenantForm) {
        tenantForm.addEventListener('submit', function (e) {
            var phone = field(tenantForm, 'phone_number');
            var pass = field(tenantForm, 'password');
            var ok = true;
            ok = setError(phone, /^0[17][0-9]{8}$/.test(phone.value.trim())
                ? '' : 'Phone must be 10 digits starting with 07 or 01.') && ok;
            ok = setError(pass, pass.value.length >= 8
                ? '' : 'Password must be at least 8 characters.') && ok;
            if (!ok) e.preventDefault();
        });
    }

    // Unit form: rent must be positive
    var rent = field(document, 'rent_per_month');
    if (rent && field(document, 'unit_number')) {
        rent.form.addEventListener('submit', function (e) {
            if (!setError(rent, parseFloat(rent.value) > 0 ? '' : 'Rent must be greater than zero.')) {
                e.preventDefault();
            }
        });
    }

    // Payment form: cash needs no reference; M-Pesa and bank do
    var method = field(document, 'payment_method');
    var reference = field(document, 'reference');
    var amount = field(document, 'amount');
    if (method && reference && amount) {
        function toggleReference() {
            var cash = method.value === 'cash';
            reference.disabled = cash;
            if (cash) { reference.value = ''; setError(reference, ''); }
        }
        method.addEventListener('change', toggleReference);
        toggleReference();

        method.form.addEventListener('submit', function (e) {
            var ok = true;
            ok = setError(amount, parseFloat(amount.value) > 0
                ? '' : 'Amount must be greater than zero.') && ok;
            if (method.value !== 'cash') {
                ok = setError(reference, reference.value.trim() !== ''
                    ? '' : 'Enter the M-Pesa code or bank slip number.') && ok;
            }
            if (!ok) e.preventDefault();
        });
    }

    // Ask before ending a tenancy
    var endLinks = document.querySelectorAll('a[href*="tenancy_end.php"]');
    endLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            if (!confirm('End this tenancy? The unit will become vacant.')) e.preventDefault();
        });
    });
});