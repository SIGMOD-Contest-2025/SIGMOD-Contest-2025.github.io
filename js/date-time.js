function timestampToComponents(timestamp) {
  return {
    d: Math.floor(timestamp / (60 * 60 * 24)),
    h: ('0' + (Math.floor(timestamp / (60 * 60)) % 24)).substr(-2),
    m: ('0' + (Math.floor(timestamp / 60) % 60)).substr(-2),
    s: ('0' + (timestamp % 60)).substr(-2),
    t: timestamp,
  };
};

function countdown(time, id) {
  var target = Date.now() + time * 1000;
  var tick = function() {
    var t = timestampToComponents(Math.floor((target - Date.now()) / 1000));
    t.d = (t.d > 0) ? (t.d == 1) ? t.d + ' day  ': t.d + ' days ' : '';
    t.h += (t.h === '01') ? ' hour  ' : ' hours ';
    t.m += (t.m === '01') ? ' minute  ' : ' minutes ';
    t.s += (t.s === '01') ? ' second ' : ' seconds';

    if (t.t > 0) {
      window.setTimeout(tick, 1000);
      document.getElementById(id).innerHTML = 'Deadline in ' + (t.d + t.h + t.m + t.s).replace(/ /g, '&nbsp;');
    } else {
      document.getElementById(id).innerHTML = '<span class="text-danger">Submissions deadline has passed.</span>';
    }
  }
  tick();
}

function timestampToString(timestamp) {
  var t = timestampToComponents(timestamp);
  t.d = (t.d > 0) ? (t.d == 1) ? t.d + ' day<br>': t.d + ' days<br>' : '';
  return t.d + [t.h, t.m, t.s].join(':');
}
