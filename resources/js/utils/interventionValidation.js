/**
 * Valide les données d'une intervention
 * @param {Object} form - Les données du formulaire
 * @returns {Object} - Les erreurs de validation
 */
export const validateIntervention = (form) => {
    const errors = {};

    // Validation du client (obligatoire)
    if (!form.clients_id && !form.isNewClient) {
        errors.clients_id = ["Veuillez sélectionner un client"];
    }

    // Validation du nouveau client si c'est un nouveau client
    if (form.isNewClient) {
        if (!form.new_client_name?.trim()) {
            errors.new_client_name = ["Le prénom du client est requis"];
        } else if (form.new_client_name.length > 255) {
            errors.new_client_name = ["Le prénom ne doit pas dépasser 255 caractères"];
        }

        // Validation de l'email
        if (form.new_client_email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(form.new_client_email)) {
                errors.new_client_email = ["Veuillez entrer une adresse email valide"];
            } else if (form.new_client_email.length > 255) {
                errors.new_client_email = ["L'email ne doit pas dépasser 255 caractères"];
            }
        }

        // Validation du téléphone
        if (form.new_client_phone) {
            // Supprime tous les espaces, tirets et points pour la validation
            const cleanedPhone = form.new_client_phone.replace(/[\s.-]/g, "");
            const phoneRegex = /^(\+33|0)[1-9](\d{2}){4}$/;

            if (!phoneRegex.test(cleanedPhone)) {
                errors.new_client_phone = ["Veuillez entrer un numéro de téléphone valide"];
            }
        }
    }

    // Validation des services (au moins un service doit être sélectionné)
    if (!form.services || form.services.length === 0) {
        errors.services = ["Veuillez sélectionner au moins un service"];
    }

    return errors;
};

/**
 * Génère un message d'erreur à partir des erreurs de validation
 * @param {Object} errors - Les erreurs de validation
 * @returns {string} - Le message d'erreur formaté
 */
export const getErrorMessage = (errors) => {
    if (!errors || Object.keys(errors).length === 0) return "";

    let errorMessage = "";
    Object.entries(errors).forEach(([field, messages], index) => {
        if (index > 0) {
            errorMessage += "\n";
        }
        if (Array.isArray(messages)) {
            errorMessage += messages[0];
        } else {
            errorMessage += messages;
        }
    });
    return errorMessage;
};
