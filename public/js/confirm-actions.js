document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (event) {
        const target = event.target.closest('[data-confirm]');
        if (!target) return;

        const message = target.dataset.confirm || 'Are you sure you want to continue?';
        if (!window.confirm(message)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }

        target.dataset.confirmAccepted = '1';
    }, true);

    document.addEventListener('submit', function (event) {
        const submitter = event.submitter;
        if (!submitter || !submitter.dataset.confirm) return;

        if (submitter.dataset.confirmAccepted === '1') {
            delete submitter.dataset.confirmAccepted;
            return;
        }

        const message = submitter.dataset.confirm || 'Are you sure you want to continue?';
        if (!window.confirm(message)) {
            event.preventDefault();
        }
    });

    document.querySelectorAll('select[data-confirm-change]').forEach(function (select) {
        select.dataset.previousValue = select.value;

        select.addEventListener('focus', function () {
            select.dataset.previousValue = select.value;
        });

        select.addEventListener('change', function () {
            const message = select.dataset.confirmChange || 'Are you sure you want to update this record?';
            if (!window.confirm(message)) {
                select.value = select.dataset.previousValue || select.value;
                return;
            }

            select.dataset.previousValue = select.value;
            if (select.form) {
                select.form.submit();
            }
        });
    });
});
