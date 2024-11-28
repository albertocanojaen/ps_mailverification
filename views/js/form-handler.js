document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('#customer-form');

    // Check if the registration was successful (look for a specific URL parameter)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        const formContainer = document.querySelector('.register-form') || document.querySelector('.tab-pane.active');
        if (formContainer) {
            formContainer.innerHTML = `
                <div class="verification-message text-center">
                    <h3>¡Registro completado!</h3>
                    <p>Hemos enviado un correo electrónico para verificar tu cuenta. Por favor, revisa tu bandeja de entrada.</p>
                    <button class="btn btn-primary mt-3" onclick="window.location.href='/'">Ir a la página principal</button>
                </div>
            `;
        }
        return; // Exit early to avoid initializing the form logic
    }

    // Form submission logic with loader
    if (form) {
        form.addEventListener('submit', function (event) {
            // Prevent double submission if the loader is already shown
            if (document.querySelector('.form-loader')) {
                return;
            }

            // Show loader
            const loader = document.createElement('div');
            loader.className = 'form-loader';
            loader.innerHTML = `<div class="spinner"></div>`;
            document.body.appendChild(loader);

            // Allow form submission
        });
    }
});
