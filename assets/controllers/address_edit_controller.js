import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['modal', 'modalBody', 'editLink']
    static values = {
        modalId: { type: String, default: 'editAddressModal' }
    }

    connect() {
        this.initializeEditLinks();
    }

    disconnect() {
        this.editLinkTargets.forEach(link => {
            link.removeEventListener('click', this.handleEditClick);
        });
    }

    initializeEditLinks() {
        this.editLinkTargets.forEach(link => {
            link.addEventListener('click', (event) => this.handleEditClick(event));
        });
    }

    handleEditClick(event) {
        event.preventDefault();
        const url = event.currentTarget.getAttribute('href');

        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap is not available');
            return;
        }

        const modal = new bootstrap.Modal(this.modalTarget);

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                this.modalBodyTarget.innerHTML = html;
                modal.show();
                this.initializeAddressPreview();
            })
            .catch(error => {
                console.error('Error fetching address edit form:', error);
            });
    }

    updatePreview() {
        const street = this.getFieldValueByName('street') || '';
        const buildingNumber = this.getFieldValueByName('buildingNumber') || '';
        const postCode = this.getFieldValueByName('postCode') || '';
        const city = this.getFieldValueByName('city') || '';
        const countryCode = this.getFieldValueByName('countryCode') || '';

        const previewStreet = this.modalBodyTarget.querySelector('#previewStreet');
        const previewCity = this.modalBodyTarget.querySelector('#previewCity');
        const previewCountry = this.modalBodyTarget.querySelector('#previewCountry');

        if (previewStreet) {
            previewStreet.textContent = `${street} ${buildingNumber}`.trim();
        }

        if (previewCity) {
            previewCity.textContent = `${postCode} ${city}`.trim();
        }

        if (previewCountry) {
            previewCountry.textContent = countryCode;
        }
    };

    initializeAddressPreview() {
        const form = this.modalBodyTarget.querySelector('form');

        if (!form) {
            return;
        }

        const fieldNames = ['street', 'buildingNumber', 'postCode', 'city', 'countryCode'];

        fieldNames.forEach(fieldName => {
            const element = this.modalBodyTarget.querySelector(`[name*="${fieldName}"]`);
            if (element) {
                element.addEventListener('input', this.updatePreview.bind(this));
            }
        });

        this.updatePreview();
    }

    getFieldValueByName(fieldName) {
        const element = this.modalBodyTarget.querySelector(`[name*="${fieldName}"]`);
        return element ? element.value : '';
    }
}
