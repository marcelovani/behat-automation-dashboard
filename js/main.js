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
  update('#matrix');
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
        addSiteJs(brand, market);
      }
    });
  })

  jQuery(`${container}`).append(table);
}

/**
 * Helper to append the js for each site.
 *
 * @param brand
 *   The brand.
 * @param market
 *   The market.
 */
function addSiteJs(brand, market) {
  let scrTag = document.createElement("script");
  let r = (Math.random() + 1).toString(36).substring(7);
  scrTag.setAttribute("src", '/sites/default/files/' + brand + '-' + market + '.js?rnd=' + r);
  scrTag.setAttribute("type", "text/javascript");
  // @todo async is causing issues with the order of the assets
  scrTag.setAttribute("async", true);
  document.body.appendChild(scrTag);
}

/**
 * Updates build status.
 *
 * @param container
 */
function update(container) {
  // Status should expire in one hour.
  var threshold = 60 * 60;
  // Check every 10 seconds.
  var frequency = 10;

  var dt = new Date();
  var isoDate = toIsoString(dt);

  // Loop each available site and check last update.
  // If the last update is older than the threshold, change the icon to yellow.
  // @todo Make this work and add an infinite timer every N seconds.
  Object.keys(sites).forEach((site, index) => {
    console.log(site + ' last run ' + lastRun[site] + ' vs ' + isoDate);
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

