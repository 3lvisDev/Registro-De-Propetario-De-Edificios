document.addEventListener('DOMContentLoaded', function () {
    // Get the modal element
    var detailModalElement = document.getElementById('copropietarioDetailModal');
    if (!detailModalElement) {
        console.error('Modal element #copropietarioDetailModal not found.');
        return;
    }
    var detailModal = new bootstrap.Modal(detailModalElement); // Ensure Bootstrap's Modal class is available

    // Using event delegation for dynamically potentially loaded content (though not strictly necessary here as it's server-rendered)
    // More robust if content were to be refreshed via AJAX without a full page reload.
    document.body.addEventListener('click', function(event) {
        // Check if the clicked element or its parent is the view details button
        var viewButton = event.target.closest('.view-copropietario-details');

        if (viewButton) {
            event.preventDefault(); // Prevent default anchor action

            var copropietarioId = viewButton.dataset.copropietarioId;
            var modalBody = detailModalElement.querySelector('.modal-body');
            
            // Set loading state in modal
            modalBody.innerHTML = '<p>Cargando detalles...</p>';
            // No need to manually show the modal here if data-bs-toggle is used, 
            // but we are preparing content *before* it might fully show or *if* we triggered manually.
            // Bootstrap's data attributes will handle the showing.

            // AJAX request to fetch copropietario details
            fetch('/copropietarios/details/' + copropietarioId)
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(function(data) {
                    // Populate modal with fetched data
                    // Adjust formatting as needed
                    var detailsHtml = '<dl class="row">'; // Using a definition list for nice formatting

                    detailsHtml += '<dt class="col-sm-4">ID:</dt><dd class="col-sm-8">' + (data.id || 'N/A') + '</dd>';
                    detailsHtml += '<dt class="col-sm-4">Nombre Completo:</dt><dd class="col-sm-8">' + (data.nombre_completo || 'N/A') + '</dd>';
                    detailsHtml += '<dt class="col-sm-4">Teléfono:</dt><dd class="col-sm-8">' + (data.telefono || 'N/A') + '</dd>';
                    detailsHtml += '<dt class="col-sm-4">Correo:</dt><dd class="col-sm-8">' + (data.correo || 'N/A') + '</dd>';
                    detailsHtml += '<dt class="col-sm-4">Tipo:</dt><dd class="col-sm-8">' + (data.tipo ? data.tipo.charAt(0).toUpperCase() + data.tipo.slice(1) : 'N/A') + '</dd>';
                    detailsHtml += '<dt class="col-sm-4">Patente:</dt><dd class="col-sm-8">' + (data.patente || 'N/A') + '</dd>';
                    detailsHtml += '<dt class="col-sm-4">Nº Departamento:</dt><dd class="col-sm-8">' + (data.numero_departamento || 'N/A') + '</dd>';
                    detailsHtml += '<dt class="col-sm-4">Estacionamiento:</dt><dd class="col-sm-8">' + (data.estacionamiento || 'N/A') + '</dd>';
                    detailsHtml += '<dt class="col-sm-4">Bodega:</dt><dd class="col-sm-8">' + (data.bodega || 'N/A') + '</dd>';
                    
                    // Timestamps if you want them
                    if (data.created_at) {
                        detailsHtml += '<dt class="col-sm-4">Registrado el:</dt><dd class="col-sm-8">' + new Date(data.created_at).toLocaleString() + '</dd>';
                    }
                    if (data.updated_at) {
                        detailsHtml += '<dt class="col-sm-4">Última Actualización:</dt><dd class="col-sm-8">' + new Date(data.updated_at).toLocaleString() + '</dd>';
                    }
                    
                    detailsHtml += '</dl>';
                    modalBody.innerHTML = detailsHtml;
                    
                    // If not using data-bs-toggle attributes, show modal manually:
                    // detailModal.show(); 
                })
                .catch(function(error) {
                    console.error('Error fetching copropietario details:', error);
                    modalBody.innerHTML = '<p class="text-danger">Error al cargar los detalles. Por favor, intente de nuevo.</p>';
                    // If not using data-bs-toggle attributes, show modal manually to display error:
                    // detailModal.show();
                });
        }
    });

    // Optional: Clear modal content when it's hidden to prevent showing old data briefly
    detailModalElement.addEventListener('hidden.bs.modal', function () {
        var modalBody = detailModalElement.querySelector('.modal-body');
        modalBody.innerHTML = '<p>Cargando detalles...</p>'; // Reset to loading state or empty
    });
});
