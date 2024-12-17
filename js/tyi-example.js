function tyi_example_solve() {
  var button = document.getElementById('tyi-example-btn-solve');
  button.disabled = true;
  button.innerHTML = 'Solving&nbsp;&nbsp;<span class="glyphicon glyphicon-time"><span>';
  window.setTimeout(function() {
    var nodeId = function(text) {
      if (!/^[1-9][0-9]*$/.test(text)) return NaN;
      var result = parseInt(text);
      return isFinite(result) && result > 0 ? result : NaN;
    }
    var ngrams = [];
    var operations = [];
    var output = [];
    var initNgramStage = true;
    document.getElementById('tyi-example-input').value.split('\n').forEach(function(line, lineNum, lines) {
      if (initNgramStage) {
        if (line == 'S') initNgramStage = false;
        else {
          ngrams.push(line);
        }
      } else {
        if (line == 'F') return;  // ignore
        var operation = line;
        if (line.length<3 || ['Q', 'A', 'D'].indexOf(operation[0].toUpperCase()) < 0) 
             output.push('ERROR @ line ' + (lineNum + 1) + ' ("' + line + '") - expected Q/A/D followed by an Ngram/Doc');
        else operations.push(operation);
      }
      if (lineNum == lines.length - 1 && line != 'F') output.push('ERROR - input must end with the batch end character');
    });
    if (output.length == 0) {
      output.push('R');
      operations.forEach(function(operation) {
        switch(operation[0].toUpperCase()) {
          case 'Q':
            var items = []
            for (cc = 0; cc < ngrams.length; cc++){
                var replace = "\\s"+ngrams[cc]+"\\s";
                var op = operation+" ";
                var re = new RegExp(replace,"u");
                var match = re.exec(op.substring(1,op.length));
                if (match!=null){
                    items.push([ngrams[cc],ngrams[cc].length,match.index])
                }
            }
            items.sort(function (x, y) { return x[2] - y[2] || x[1] - y[1]; });
            if (items.length == 0)
                output.push(-1);
            else {
            str = ""
            for (cci=0;cci<items.length;cci++)
            str += items[cci][0]+"|";
            output.push(str.substring(0,str.length-1));
            }
            break;
          case 'A':
            if (ngrams.indexOf(operation.substring(2,operation.length)) < 0){
                ngrams.push(operation.substring(2,operation.length));
                }
            break;
          case 'D':
            var index = ngrams.indexOf(operation.substring(2,operation.length));
            if (index > -1) {
                ngrams.splice(index, 1);
            }
            break;
        }
      });
    }
    document.getElementById('tyi-example-output').value = output.join('\n');
    button.innerHTML = 'Solve&nbsp;&nbsp;<span class="glyphicon glyphicon-forward"><span>';
    button.disabled = false;
  }, 50);
}

tyi_example_solve();
