function drawLeaderboard(rows) {
  var minScore = Infinity;
  var maxScore = -Infinity;
  var minColor = [217, 83, 79];
  var maxColor = [92, 184, 92];
  rows.forEach(function(row) {
    row.score = row.times.reduce(function(a, b) {
      return a + b;
    });
    minScore = row.score < minScore ? row.score : minScore;
    maxScore = row.score > maxScore ? row.score : maxScore;
  });

  rows.forEach(function(row, rank) {
    var relativeScore = (100 * (row.score - minScore) / (maxScore - minScore));
    //var relativeScore = 100 - (100 * (row.score - minScore) / (maxScore - minScore));
    var color = [0, 1, 2].map(function(channel) {
      return Math.round((maxColor[channel] * relativeScore + minColor[channel] * (100 - relativeScore)) / 100);
    });
    
    var TUM=row.institute=='TU Munich';
    var line = TUM ? [
      '<tbody><tr bgcolor="#e6eeff">',
        '<td rowspan="2" class="text-right">#', (rank + 1), '</td>',
        '<td rowspan="2">'
    ]:[
      '<tbody><tr>',
        '<td rowspan="2" class="text-right">#', (rank + 1), '</td>',
        '<td rowspan="2">'
    ];
    if (row.flag) line.push('<img src="', row.flag, '"> ');

    line.push(row.name);
    if (row.institute) line.push('<br>(', row.institute, ')');
    line.push(
        '</td>',
        '<td rowspan="2" class="text-center">',
          row.time_on_top > 0 ? timestampToString(row.time_on_top) : '-',
        '</td>',
        '<td colspan="2" class="progress-time">',
          '<div class="progress">',
            '<div class="progress-bar progress-bar-striped" style="width:', relativeScore, '%; background-color: rgb(', color.join(','), ')"></div>',
          '</div>',
        '</td>',
        '<td rowspan="2" class="text-center">', row.runtime, '</td>',
        '<td rowspan="2" class="text-center">', row.submission_time, '</td>',
      '</tr>',
      TUM ? '<tr bgcolor="#e6eeff">' : '<tr>'
    );
    row.times.forEach(function(time) {
      line.push(
        '<td class="text-center time-column">', time.toFixed(3), '</td>'
      );
    });
    line.push('</tr></tbody>');
    document.write(line.join(''));
  });
}
