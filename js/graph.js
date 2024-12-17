function Graph() {
  this.nodes = {};
}
  
Graph.prototype.addEdge = function(start, finish) {
  this.nodes[start] = this.nodes[start] || [];
  this.nodes[finish] = this.nodes[finish] || [];
  if (this.nodes[start].indexOf(finish) < 0) this.nodes[start].push(finish);
};

Graph.prototype.removeEdge = function(start, finish) {
  var node = this.nodes[start] || [];
  var index = node.indexOf(finish);
  if (index >= 0) node.splice(index, 1);
};

Graph.prototype.shortestPath = function(start, finish) {
  var Q = [];  // unoptimized priority queue
  var dist = {};
  for (v in this.nodes) {
    if (v == start) Q.unshift([v, dist[v] = 0]);
    else Q.push([v, dist[v] = Infinity]);
  }
  while(Q.length > 0) {
    var u = Q.shift()[0];
    if (u == finish) return isFinite(dist[u]) ? dist[u] : -1;
    this.nodes[u].forEach(function(v) {
      if (dist[u] + 1 < dist[v]) {
        dist[v] = dist[u] + 1;
        Q = Q.map(function(x) {
          return x[0] == v ? [v, dist[u] + 1] : x;
        }).sort(function(x, y) {
          return x[1] - y[1];
        });
      }
    });
  }
  return isFinite(dist[finish]) ? dist[finish] : -1;
};