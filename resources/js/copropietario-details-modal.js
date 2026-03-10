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
                    // Populate modal with fetched data using safe DOM manipulation
                    // This prevents XSS by using textContent instead of innerHTML concatenation
                    var dl = document.createElement('dl');
                    dl.className = 'row';
                    
                    // Helper function to safely add a field to the definition list
                    function addField(label, value) {
                        var dt = document.createElement('dt');
                        dt.className = 'col-sm-4';
                        dt.textContent = label + ':';
                        
                        var dd = document.createElement('dd');
                        dd.className = 'col-sm-8';
                        dd.textContent = value || 'N/A';
                        
                        dl.appendChild(dt);
                        dl.appendChild(dd);
                    }
                    
                    // Add all fields safely - textContent automatically escapes HTML
                    addField('ID', data.id);
                    addField('Nombre Completo', data.nombre_completo);
                    addField('Teléfono', data.telefono);
                    addField('Correo', data.correo);
                    addField('Tipo', data.tipo ? data.tipo.charAt(0).toUpperCase() + data.tipo.slice(1) : null);
                    addField('Patente', data.patente);
                    addField('Nº Departamento', data.numero_departamento);
                    addField('Estacionamiento', data.estacionamiento);
                    addField('Bodega', data.bodega);
                    
                    // Timestamps if available
                    if (data.created_at) {
                        addField('Registrado el', new Date(data.created_at).toLocaleString());
                    }
                    if (data.updated_at) {
                        addField('Última Actualización', new Date(data.updated_at).toLocaleString());
                    }
                    
                    // Clear and append the safely constructed element
                    modalBody.innerHTML = '';
                    modalBody.appendChild(dl);
                    
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
