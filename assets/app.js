document.addEventListener('DOMContentLoaded', () => {
    const ingredients = document.querySelector('#ingredients');
    const addIngredient = document.querySelector('#add-ingredient');

    if (ingredients && addIngredient) {
        addIngredient.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'ingredient-row';
            const template = document.querySelector('.ingredient-row select');
            row.innerHTML = template.outerHTML.replace('name="ingredient_ids[]"', 'name="ingredient_ids[]"').replace(' selected', '');
            row.querySelector('select').value = '';
            row.innerHTML += '<button type="button" aria-label="Remove ingredient">&times;</button>';
            ingredients.appendChild(row);
            row.querySelector('select').focus();
        });

        ingredients.addEventListener('click', (event) => {
            if (event.target.tagName === 'BUTTON' && ingredients.children.length > 1) {
                event.target.parentElement.remove();
            }
        });
    }

    const saveIngredient = document.querySelector('#save-ingredient');
    if (saveIngredient) {
        saveIngredient.addEventListener('click', async () => {
            const input = document.querySelector('#new-ingredient');
            const name = input.value.trim();
            if (!name) return;
            const response = await fetch('ingredient_create.php', {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: new URLSearchParams({name, csrf: saveIngredient.dataset.csrf})});
            const result = await response.json();
            if (result.ingredient) {
                document.querySelectorAll('#ingredients select').forEach((select) => {
                    const option = new Option(result.ingredient.name, result.ingredient.id);
                    select.add(option);
                });
                let emptySelect = Array.from(document.querySelectorAll('#ingredients select')).find((select) => !select.value);
                if (!emptySelect && addIngredient) {
                    addIngredient.click();
                    emptySelect = document.querySelector('#ingredients select:last-of-type');
                }
                if (emptySelect) emptySelect.value = result.ingredient.id;
                input.value = '';
            } else if (result.error) alert(result.error);
        });
    }

    document.querySelectorAll('[data-favorite]').forEach((button) => {
        button.addEventListener('click', async () => {
            button.disabled = true;
            const response = await fetch('favorite.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({recipe_id: button.dataset.favorite, csrf: button.dataset.csrf})
            });
            const result = await response.json();
            button.textContent = result.saved ? 'Saved to favorites' : 'Save to favorites';
            button.classList.toggle('secondary', !result.saved);
            button.disabled = false;
        });
    });
});
