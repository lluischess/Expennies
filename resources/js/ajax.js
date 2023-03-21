// Función principal de AJAX para realizar solicitudes al servidor
const ajax = (url, method = 'get', data = {}, domElement = null) => {
    method = method.toLowerCase()

    // Opciones predeterminadas para la solicitud AJAX
    let options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    }

    // Métodos que requieren un token CSRF
    const csrfMethods = new Set(['post', 'put', 'delete', 'patch'])

    // Si el método requiere un token CSRF
    if (csrfMethods.has(method)) {
        // Obtener los campos CSRF
        let additionalFields = {...getCsrfFields()}

        // Si el método no es POST, convertirlo a POST y agregar el método original como campo adicional
        if (method !== 'post') {
            options.method = 'post'

            additionalFields._METHOD = method.toUpperCase()
        }

        // Si los datos son de tipo FormData, agregar los campos adicionales y eliminar el encabezado 'Content-Type'
        if (data instanceof FormData) {
            for (const additionalField in additionalFields) {
                data.append(additionalField, additionalFields[additionalField])
            }

            delete options.headers['Content-Type'];

            options.body = data
        } else {
            // Si no, combinar los datos con los campos adicionales y convertirlos a una cadena JSON
            options.body = JSON.stringify({...data, ...additionalFields})
        }
    } else if (method === 'get') {
        // Si el método es GET, agregar los datos como parámetros de consulta en la URL
        url += '?' + (new URLSearchParams(data)).toString();
    }

    // Realizar la solicitud y procesar la respuesta
    return fetch(url, options).then(response => {
        // Si se proporciona un elemento DOM, limpiar los errores de validación
        if (domElement) {
            clearValidationErrors(domElement)
        }

        // Si la respuesta no es exitosa, manejar los errores de validación
        if (! response.ok) {
            if (response.status === 422) {
                response.json().then(errors => {
                    handleValidationErrors(errors, domElement)
                })
            }
        }

        return response
    })
}

// Funciones de acceso directo para diferentes métodos HTTP
const get  = (url, data) => ajax(url, 'get', data)
const post = (url, data, domElement) => ajax(url, 'post', data, domElement)
const del = (url, data) => ajax(url, 'delete', data)

// Función para manejar errores de validación y mostrarlos en el DOM
function handleValidationErrors(errors, domElement) {
    for (const name in errors) {
        const element = domElement.querySelector(`[name="${ name }"]`)

        element.classList.add('is-invalid')

        const errorDiv = document.createElement('div')

        errorDiv.classList.add('invalid-feedback')
        errorDiv.textContent = errors[name][0]

        element.parentNode.append(errorDiv)
    }
}

// Función para limpiar errores de validación en el DOM
function clearValidationErrors(domElement) {
    domElement.querySelectorAll('.is-invalid').forEach(function(element) {
        element.classList.remove('is-invalid')

        element.parentNode.querySelectorAll('.invalid-feedback').forEach(function(e) {
            e.remove()
        })
    })
}

// Función para obtener los campos CSRF del DOM
function getCsrfFields() {
    const csrfNameField  = document.querySelector('#csrfName')
    const csrfValueField = document.querySelector('#csrfValue')
    const csrfNameKey    = csrfNameField.getAttribute('name')
    const csrfName       = csrfNameField.content
    const csrfValueKey   = csrfValueField.getAttribute('name')
    const csrfValue      = csrfValueField.content

    return {
        [csrfNameKey]: csrfName,
        [csrfValueKey]: csrfValue
    }
}

export {
    ajax,
    get,
    post,
    del
}