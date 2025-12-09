import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

const devApiBase = "http://127.0.0.1:8000";
const envApiBase = import.meta.env?.VITE_API_BASE || "";
const isViteDev =
    typeof window !== "undefined" && window.location?.port === "5173";
const sameOrigin =
    typeof window !== "undefined" && window.location?.origin
        ? window.location.origin
        : "";

window.axios.defaults.baseURL =
    envApiBase || (isViteDev ? devApiBase : sameOrigin);
