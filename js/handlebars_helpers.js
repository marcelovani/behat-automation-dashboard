document.addEventListener("DOMContentLoaded", function(event) {
  registerHandlebarsHelpers();
});

/**
 * Register custom Handlebars helpers.
 */
function registerHandlebarsHelpers() {
  // Converts line breaks to HTML line breaks.
  Handlebars.registerHelper('clrfToBr', (str, args, options) => {
    if (typeof str != 'string') {
      return;
    }
    return str.replace(/(?:\r\n|\r|\n)/g, '<br>');
  });

  // Localized dates.
  Handlebars.registerHelper('localDate', (str, args, options) => {
    return window.Dashboard.localDate(str);
  });
}
