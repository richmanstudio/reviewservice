'use strict';

(function initRating() {
    const row = document.querySelector('.rating-row');
    if (!row) return;

    const labels = row.querySelectorAll('.rating-label');
    const inputs = row.querySelectorAll('.rating-input');

    function updateVisual(selectedValue) {
        labels.forEach((label) => {
            const btn   = label.querySelector('.rating-btn');
            const input = label.querySelector('.rating-input');
            const val   = parseInt(input.value, 10);
            btn.classList.toggle('is-selected', val <= selectedValue);
        });
    }

    inputs.forEach((input) => {
        input.addEventListener('change', () => {
            updateVisual(parseInt(input.value, 10));
        });
    });

    const checked = row.querySelector('.rating-input:checked');
    if (checked) {
        updateVisual(parseInt(checked.value, 10));
    }

    labels.forEach((label) => {
        const input = label.querySelector('.rating-input');
        const hoverVal = parseInt(input.value, 10);

        label.addEventListener('mouseenter', () => {
            const currentChecked = row.querySelector('.rating-input:checked');
            if (!currentChecked) {
                labels.forEach((l) => {
                    const btn = l.querySelector('.rating-btn');
                    const v   = parseInt(l.querySelector('.rating-input').value, 10);
                    btn.classList.toggle('is-selected', v <= hoverVal);
                });
            }
        });

        label.addEventListener('mouseleave', () => {
            const currentChecked = row.querySelector('.rating-input:checked');
            if (!currentChecked) {
                labels.forEach((l) => {
                    l.querySelector('.rating-btn').classList.remove('is-selected');
                });
            }
        });
    });
})();

(function initCharCounter() {
    const textarea = document.getElementById('comment');
    const counter  = document.getElementById('comment-counter');
    if (!textarea || !counter) return;

    const max = parseInt(textarea.getAttribute('maxlength'), 10) || 2000;

    function update() {
        const len = textarea.value.length;
        counter.textContent = len + ' / ' + max;
        counter.style.color = len >= max * 0.9 ? '#e05555' : '';
    }

    textarea.addEventListener('input', update);
    update();
})();
