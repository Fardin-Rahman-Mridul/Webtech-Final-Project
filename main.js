document.addEventListener('DOMContentLoaded', function () {

    // --- Mobile nav toggle ------------------------------------------------
    var toggle = document.getElementById('navToggle');
    var nav = document.getElementById('mainNav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var isOpen = nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    // --- Flash message auto-dismiss ----------------------------------------
    var alertBox = document.querySelector('.alert');
    if (alertBox) {
        setTimeout(function () {
            alertBox.style.transition = 'opacity 0.4s ease';
            alertBox.style.opacity = '0';
            setTimeout(function () { alertBox.remove(); }, 400);
        }, 4500);
    }

    // --- Interactive star rating picker (review.php) -----------------------
    var picker = document.querySelector('.star-picker');
    if (picker) {
        var stars = picker.querySelectorAll('.star');
        var input = document.getElementById('ratingInput');
        var label = document.getElementById('ratingLabel');
        var labels = { 1: 'Poor', 2: 'Below Average', 3: 'Average', 4: 'Good', 5: 'Excellent' };

        function paint(value) {
            stars.forEach(function (star) {
                star.classList.toggle('active', parseInt(star.dataset.value, 10) <= value);
            });
        }

        stars.forEach(function (star) {
            star.addEventListener('click', function () {
                var value = parseInt(star.dataset.value, 10);
                input.value = value;
                if (label) { label.textContent = labels[value]; }
                paint(value);
            });
            star.addEventListener('mouseenter', function () {
                paint(parseInt(star.dataset.value, 10));
            });
        });

        picker.addEventListener('mouseleave', function () {
            paint(parseInt(input.value || '0', 10));
        });

        var reviewForm = picker.closest('form');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function (e) {
                if (!input.value) {
                    e.preventDefault();
                    if (label) { label.textContent = 'Please select a rating before submitting.'; label.style.color = 'var(--danger)'; }
                }
            });
        }
    }

});
