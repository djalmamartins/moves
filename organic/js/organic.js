window.Organic = window.Organic || {};
window.Organic.ready = (callback) => document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', callback, {once: true}) : callback();
