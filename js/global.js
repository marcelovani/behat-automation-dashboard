/**
 * @file Global functions.
 */

window.Dashboard = window.Dashboard || {};

/**
 * Localizes dates.
 *
 * @param date
 *   The date string
 *
 * @return {string}
 *   The local date string.
 */
window.Dashboard.localDate = function (date) {
  var dateOptions = {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: 'numeric',
    timeZoneName: 'shortGeneric',
  };

  return new Date(date).toLocaleDateString(undefined, dateOptions);
}
