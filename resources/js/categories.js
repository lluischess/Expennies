// Este código agrega un event listener a todos los elementos de la página que tienen la clase CSS .edit-category-btn.
// Cuando se hace clic en uno de estos elementos, se obtiene el valor del atributo data-id.
window.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-category-btn').forEach(button => {
        button.addEventListener('click', function (event) {
            const categoryId = event.currentTarget.getAttribute('data-id')

            // TODO
            console.log(categoryId)
        })
    })
})