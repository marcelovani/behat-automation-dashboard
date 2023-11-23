// stores the list of last run for all sites in UTC date.time
var lastRun = {};

// Stores the list of sites, used to update details.
var sites = [];

// Stores list of failed scenarios.
var failedScenarios = [];

var brandMarkets = {
  "aeo":     ["kw", "ae", "sa", "qa", "bh", "eg", "jo", "om"],
  "bbw":     ["kw", "ae", "sa", "qa", "bh", "eg", "jo", "  "],
  "bp":      ["kw", "ae", "sa", "qa", "bh", "  ", "  ", "  "],
  "muji":    ["kw", "ae", "sa", "qa", "bh", "  ", "  ", "  "],
  "fl":      ["kw", "ae", "sa", "qa", "bh", "eg", "  ", "  "],
  "nb":      ["kw", "ae", "sa", "qa", "bh", "eg", "  ", "  "],
  "tbs":     ["kw", "  ", "  ", "qa", "bh", "eg", "  ", "  "],
  "hm":      ["kw", "ae", "sa", "qa", "  ", "eg", "  ", "  "],
  "mc":      ["kw", "ae", "sa", "qa", "  ", "eg", "  ", "  "],
  "vs":      ["kw", "ae", "sa", "qa", "  ", "  ", "  ", "  "],
  "pb":      ["kw", "ae", "sa", "  ", "  ", "  ", "  ", "  "],
  "pbk":     ["kw", "ae", "sa", "  ", "  ", "  ", "  ", "  "],
  "cos":     ["kw", "ae", "sa", "  ", "  ", "  ", "  ", "  "],
  "westelm": ["kw", "ae", "sa", "  ", "  ", "  ", "  ", "  "],
  "dh":      ["kw", "  ", "  ", "  ", "  ", "  ", "  ", "  "],
  "ay":      ["kw", "  ", "  ", "  ", "  ", "  ", "  ", "  "],
};

document.addEventListener("DOMContentLoaded", function(event) {
  createTable('#matrix');
  scrollToHash();
});

/**
 * Creates the table of brand and markets.
 *
 * @param container
 */
function createTable(container) {
  // Create table.
  var table = jQuery('<table>').addClass('brands-markets');

  // Add header.
  var markets = Object.values(brandMarkets['aeo']);

  // Add blank col.
  markets.unshift('');

  // Loop markets.
  markets.forEach((market, index) => {
    var th = jQuery('<th>').addClass('header').text(market);
    jQuery(th).appendTo(table);
  });

  // Add rows.
  Object.keys(brandMarkets).forEach((brand, index) => {
    var tr = jQuery('<tr>').addClass('brand');
    jQuery(tr).appendTo(table);

    var td = jQuery('<td>').addClass('brand').text(brand);
    jQuery(td).appendTo(tr);

    // Loop markets
    var markets = brandMarkets[brand];
    markets.forEach((market, index) => {
      var td = jQuery('<td>').addClass('site').addClass(brand + '-' + market);
      jQuery(td).appendTo(tr);
      if (market.trim().length > 0) {
        var img = jQuery('<img>').attr('src', '/modules/custom/dashboard/images/yellow.png');
        jQuery(img).appendTo(td);
        updateMatrix(brand, market);
      }
    });
  })

  jQuery(`${container}`).append(table);
}

/**
 * Helper load json and update matrix for each brand and market.
 *
 * @param brand
 *   The brand.
 * @param market
 *   The market.
 */
function updateMatrix(brand, market) {
  // Keep only data for the last hour.
  // @todo: Make this configurable.
  var threshold = 60 * 60 * 1000;
  var matrixTarget = '#matrix .' + brand + '-' + market;
  let r = (Math.random() + 1).toString(36).substring(7);

  jQuery.getJSON('/sites/default/files/' + brand + '-' + market + '.json?rnd=' + r, function(data) {
    switch (data.event) {
      case 'suite_started':
        var icon = 'wip';
        break;

      case 'suite_finished':
        if (data.outcome == 'passed') {
          var icon = 'green';
        }
        else {
          var icon = 'red';
        }
        break;

      case 'scenario_failed':
        var icon = 'red';
        break;

      default:
        console.error('Unknown event ' + data.event);
    }

    // Check if the information is expired.
    var now = new Date();
    if (new Date(data.lastRun).getTime() + threshold < now.getTime()) {
      console.log('Data expired for ' + brand + '-' + market);
      return;
    }

    var localDate = window.Dashboard.localDate(data.lastRun)

    // Add link to failures.
    if (data.scenarios) {
      var count = Object.keys(data.scenarios).length;
      var float = '';
      if (count > 0) {
        float = 'float-left';
        var failed = jQuery('<div>').addClass('failed');
        jQuery(failed).appendTo(jQuery(matrixTarget));
        var link = jQuery('<a>').attr('href', '#failures-' + brand + '-' + market).attr('title', 'Last run: ' + localDate).text(count);
        jQuery(link).appendTo(jQuery(matrixTarget + ' .failed'));
      }
    }

    // Update image.
    var src = '/modules/custom/dashboard/images/' + icon + '.png';
    jQuery(matrixTarget + ' img').addClass(float).attr('title', 'Last run: ' + localDate).attr('src', src);

    // Append failures.
    var html = handlebarsRenderer.render('dashboard.failures', data);
    var failures = jQuery('<span>').addClass('item').html(html);
    jQuery(failures).appendTo(jQuery('#failures .list'));
  });
}

/**
 * Helper to convert current date to UTC ISO 8601.
 *
 * @param date
 *   The date.
 *
 * @return {string}
 *   ISO UTC Date.
 */
function toIsoString(date) {
  var tzo = date.getTimezoneOffset(),
    dif = tzo >= 0 ? '+' : '-',
    pad = function(num) {
      return (num < 10 ? '0' : '') + num;
    };

  return date.getFullYear() +
    '-' + pad(date.getMonth() + 1) +
    '-' + pad(date.getDate()) +
    'T' + pad(date.getHours()) +
    ':' + pad(date.getMinutes()) +
    ':' + pad(date.getSeconds()) +
    dif + pad(Math.floor(Math.abs(tzo) / 60)) +
    ':' + pad(Math.abs(tzo) % 60);
}

/**
 * Helper to scroll to hash from url.
 */
function scrollToHash() {
  setTimeout(() => {
    var hash = document.URL.substr(document.URL.indexOf('#') + 1);
    var el = jQuery(`section[id=${hash}]`);
    if (el && el.offset() && el.offset().top) {
      jQuery('html, body').animate({
        scrollTop: el.offset().top
      }, 'slow');
    }
    else {
      console.log('Cannot scroll to hash');
    }
  }, 2500);
}
