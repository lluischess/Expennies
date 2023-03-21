// Importar las clases y funciones necesarias
import { Modal }     from "bootstrap"
import { get, post, del } from "./ajax"
import DataTable          from "datatables.net"

// Esperar a que el DOM esté completamente cargado
window.addEventListener('DOMContentLoaded', function () {
    // Crear una instancia de Modal para el modal de edición de categorías
    const editCategoryModal = new Modal(document.getElementById('editCategoryModal'))

    // Inicializar la tabla de categorías con DataTables
    const table = new DataTable('#categoriesTable', {
        serverSide: true,
        ajax: '/categories/load',
        orderMulti: false,
        columns: [
            {data: "name"},
            {data: "createdAt"},
            {data: "updatedAt"},
            {
                sortable: false,
                data: row => `
                    <div class="d-flex flex-">
                        <button type="submit" class="btn btn-outline-danger delete-category-btn" data-id="${ row.id }">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                        <button class="ms-2 btn btn-outline-primary edit-category-btn" data-id="${ row.id }">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                    </div>
                `
            }
        ]
    });

    // Escuchar eventos de clic en la tabla de categorías
    document.querySelector('#categoriesTable').addEventListener('click', function (event) {
        // Detectar si se hizo clic en un botón de editar o eliminar
        const editBtn   = event.target.closest('.edit-category-btn')
        const deleteBtn = event.target.closest('.delete-category-btn')

        // Si se hizo clic en un botón de editar
        if (editBtn) {
            const categoryId = editBtn.getAttribute('data-id')

            get(`/categories/${ categoryId }`)
                .then(response => response.json())
                .then(response => openEditCategoryModal(editCategoryModal, response))
        } else {
            // Si se hizo clic en un botón de eliminar
            const categoryId = deleteBtn.getAttribute('data-id')

            // Mostrar cuadro de confirmación y realizar una solicitud DELETE si se confirma
            if (confirm('Are you sure you want to delete this category?')) {
                del(`/categories/${ categoryId }`).then(() => {
                    if (response.ok) {
                        table.draw()
                    }
                })
            }
        }
    })

    // document.querySelectorAll('.edit-category-btn').forEach(button => {
    //     button.addEventListener('click', function (event) {
    //         const categoryId = event.currentTarget.getAttribute('data-id')
    //
    //         get(`/categories/${ categoryId }`)
    //             .then(response => response.json())
    //             .then(response => openEditCategoryModal(editCategoryModal, response))
    //     })
    // })

    // Escuchar eventos de clic en el botón "save-category-btn"
    document.querySelector('.save-category-btn').addEventListener('click', function (event) {
        // Obtener el ID de la categoría y realizar una solicitud POST para actualizar la categoría
        const categoryId = event.currentTarget.getAttribute('data-id')

        post(`/categories/${ categoryId }`, {
            name: editCategoryModal._element.querySelector('input[name="name"]').value
        }, editCategoryModal._element).then(response => {
            if (response.ok) {
                // Si la actualización fue exitosa, actualizar la tabla y ocultar el modal
                table.draw()
                editCategoryModal.hide()
            }
        })
    })
})

//     document.querySelector('.delete-category-btn').addEventListener('click', function (event) {
//         const categoryId = event.currentTarget.getAttribute('data-id')
//
//         if (confirm('Are you sure you want to delete this category?')) {
//             del(`/categories/${ categoryId }`)
//         }
//     })
// })

// Función para abrir el modal de edición de categorías
function openEditCategoryModal(modal, {id, name}) {
    const nameInput = modal._element.querySelector('input[name="name"]')

    nameInput.value = name

    modal._element.querySelector('.save-category-btn').setAttribute('data-id', id)

    modal.show()
}