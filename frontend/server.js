/**
 * DOST FMS — Frontend Static Server
 * Serves frontend on http://localhost:5000
 * Run: node server.js
 */
const http  = require('http');
const fs    = require('fs');
const path  = require('path');

const PORT = process.env.PORT || 5000;

const MIME = {
  '.html': 'text/html',
  '.css':  'text/css',
  '.js':   'application/javascript',
  '.json': 'application/json',
  '.png':  'image/png',
  '.jpg':  'image/jpeg',
  '.svg':  'image/svg+xml',
  '.ico':  'image/x-icon',
};

const server = http.createServer((req, res) => {
  let filePath = '.' + req.url.split('?')[0];
  if (filePath === './') filePath = './pages/login.html';

  const ext  = path.extname(filePath).toLowerCase();
  const mime = MIME[ext] || 'application/octet-stream';

  fs.readFile(filePath, (err, data) => {
    if (err) {
      if (err.code === 'ENOENT') { res.writeHead(404); res.end('404 Not Found'); }
      else                       { res.writeHead(500); res.end('Server Error'); }
      return;
    }
    res.writeHead(200, {
      'Content-Type': mime,
      'Cache-Control': 'no-cache',
    });
    res.end(data);
  });
});

server.listen(PORT, () => console.log(`DOST FMS Frontend: http://localhost:${PORT}`));
