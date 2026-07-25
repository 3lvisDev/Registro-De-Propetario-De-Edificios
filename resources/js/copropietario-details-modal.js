document.addEventListener('DOMContentLoaded', function () {
    var detailModalElement = document.getElementById('copropietarioDetailModal');
    if (!detailModalElement) {
        return;
    }

    var loadingMarkup = '<div class="copropietario-detail-loading" role="status"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span>Cargando detalles...</span></div>';

    document.body.addEventListener('click', function (event) {
        var viewButton = event.target.closest('.view-copropietario-details');
        if (!viewButton) {
            return;
        }

        event.preventDefault();
        var detailsUrl = viewButton.dataset.detailsUrl;
        var modalBody = detailModalElement.querySelector('.modal-body');
        modalBody.innerHTML = loadingMarkup;

        fetch(detailsUrl, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(function (data) {
                var detailsGrid = document.createElement('div');
                detailsGrid.className = 'copropietario-detail-grid';

                function addField(label, value, icon, fullWidth) {
                    var field = document.createElement('div');
                    field.className = 'copropietario-detail-field' + (fullWidth ? ' copropietario-detail-field-wide' : '');

                    var iconElement = document.createElement('span');
                    iconElement.className = 'copropietario-detail-field-icon';
                    iconElement.setAttribute('aria-hidden', 'true');
                    var iconGlyph = document.createElement('i');
                    iconGlyph.className = icon;
                    iconElement.appendChild(iconGlyph);

                    var text = document.createElement('div');
                    var fieldLabel = document.createElement('span');
                    fieldLabel.className = 'copropietario-detail-label';
                    fieldLabel.textContent = label;
                    var fieldValue = document.createElement('span');
                    fieldValue.className = 'copropietario-detail-value';
                    fieldValue.textContent = value === null || value === undefined || value === '' ? 'No registrado' : value;

                    text.appendChild(fieldLabel);
                    text.appendChild(fieldValue);
                    field.appendChild(iconElement);
                    field.appendChild(text);
                    detailsGrid.appendChild(field);
                }

                addField('Nombre completo', data.nombre_completo, 'fas fa-user', true);
                addField('Tipo', data.tipo ? data.tipo.charAt(0).toUpperCase() + data.tipo.slice(1) : null, 'fas fa-id-badge');
                addField('Departamento', data.numero_departamento, 'fas fa-building');
                addField('Teléfono', data.telefono, 'fas fa-phone');
                addField('Correo', data.correo, 'fas fa-envelope');
                addField('Patente', data.patente, 'fas fa-car');
                addField('Estacionamiento', data.estacionamiento, 'fas fa-square-parking');
                addField('Bodega', data.bodega, 'fas fa-box');
                addField('N.º de registro', data.id, 'fas fa-hashtag');

                if (data.created_at) {
                    addField('Registrado el', new Date(data.created_at).toLocaleString(), 'fas fa-calendar-plus');
                }
                if (data.updated_at) {
                    addField('Última actualización', new Date(data.updated_at).toLocaleString(), 'fas fa-clock');
                }

                modalBody.innerHTML = '';
                modalBody.appendChild(detailsGrid);
            })
            .catch(function (error) {
                console.error('Error fetching copropietario details:', error);
                modalBody.innerHTML = '<div class="alert alert-danger mb-0" role="alert"><i class="fas fa-circle-exclamation me-2"></i>Error al cargar los detalles. Por favor, intente de nuevo.</div>';
            });
    });

    detailModalElement.addEventListener('hidden.bs.modal', function () {
        detailModalElement.querySelector('.modal-body').innerHTML = loadingMarkup;
    });
});
