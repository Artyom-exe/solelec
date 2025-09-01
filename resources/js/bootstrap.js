import axios from "axios";
window.axios = axios;

// Requêtes AJAX
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Cookies de session autorisés
window.axios.defaults.withCredentials = true;

// Utiliser le cookie XSRF-TOKEN automatiquement pour éviter les tokens périmés
// après rotation de session (login/logout). Axios lira le cookie "XSRF-TOKEN"
// et enverra l'entête "X-XSRF-TOKEN" correspondant.
window.axios.defaults.xsrfCookieName = "XSRF-TOKEN";
window.axios.defaults.xsrfHeaderName = "X-XSRF-TOKEN";
