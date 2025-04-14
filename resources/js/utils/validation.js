/**
 * Valide les données d'un client
 * @param {Object} form - Les données du formulaire
 * @returns {Object} - Les erreurs de validation
 */
export const validateClient = (form) => {
    const errors = {};

    // Validation du prénom (obligatoire)
    if (!form.name?.trim()) {
        errors.name = ["Le prénom est requis"];
    } else if (form.name.length > 255) {
        errors.name = ["Le prénom ne doit pas dépasser 255 caractères"];
    }

    // Validation du nom
    if (form.lastname && form.lastname.length > 255) {
        errors.lastname = ["Le nom ne doit pas dépasser 255 caractères"];
    }

    // Validation de l'email
    if (form.email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(form.email)) {
            errors.email = ["Veuillez entrer une adresse email valide"];
        } else if (form.email.length > 255) {
            errors.email = ["L'email ne doit pas dépasser 255 caractères"];
        }
    }

    // Validation du téléphone
    if (form.phone) {
        // Supprime tous les espaces, tirets et points pour la validation
        const cleanedPhone = form.phone.replace(/[\s.-]/g, "");
        const phoneRegex = /^(\+33|0)[1-9](\d{2}){4}$/;

        if (!phoneRegex.test(cleanedPhone)) {
            errors.phone = ["Veuillez entrer un numéro de téléphone valide"];
        }
    }

    // Validation de l'adresse
    if (form.adress && form.adress.length > 255) {
        errors.adress = ["L'adresse ne doit pas dépasser 255 caractères"];
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
