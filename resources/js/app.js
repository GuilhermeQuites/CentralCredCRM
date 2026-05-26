document.querySelectorAll('[data-select-search]').forEach((input) => {
    const select = document.getElementById(input.dataset.selectSearch);

    if (!select) {
        return;
    }

    const options = Array.from(select.options).map((option) => ({
        option,
        text: option.textContent.toLowerCase(),
    }));

    input.addEventListener('input', () => {
        const search = input.value.trim().toLowerCase();

        options.forEach(({ option, text }) => {
            option.hidden = option.value !== '' && search !== '' && !text.includes(search);
        });
    });
});
