/**
 * DOST FMS — Frontend Static Server
 * Serves frontend on http://localhost:5000
 * Run: node server.js
 */
const http = require('http');
const fs = require('fs');
const path = require('path');
const { exec } = require('child_process');

const PORT = process.env.PORT || 5000;
const ROOT = __dirname;
const LOGIN_URL = `http://localhost:${PORT}/pages/login.html`;

const MIME = {
  '.html': 'text/html',
  '.css': 'text/css',
  '.js': 'application/javascript',
  '.json': 'application/json',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.svg': 'image/svg+xml',
  '.ico': 'image/x-icon',
};

const server = http.createServer((req, res) => {
  const requestPath = new URL(req.url, `http://localhost:${PORT}`).pathname;

  if (requestPath === '/') {
    res.writeHead(302, { Location: '/pages/login.html' });
    res.end();
    return;
  }

  const relativePath = decodeURIComponent(requestPath).replace(/^\/+/, '');
  const filePath = path.resolve(ROOT, relativePath);

  if (!filePath.startsWith(ROOT + path.sep)) {
    res.writeHead(403);
    res.end('403 Forbidden');
    return;
  }

  const ext = path.extname(filePath).toLowerCase();
  const mime = MIME[ext] || 'application/octet-stream';

  fs.readFile(filePath, (err, data) => {
    if (err) {
      if (err.code === 'ENOENT') {
        res.writeHead(404);
        res.end('404 Not Found');
      } else {
        res.writeHead(500);
        res.end('Server Error');
      }
      return;
    }

    res.writeHead(200, {
      'Content-Type': mime,
      'Cache-Control': 'no-cache',
    });
    res.end(data);
  });
});

server.listen(PORT, () => {
  console.log(`DOST FMS Frontend: ${LOGIN_URL}`);
  const command = process.platform === 'win32'
    ? `start "" "${LOGIN_URL}"`
    : process.platform === 'darwin'
      ? `open "${LOGIN_URL}"`
      : `xdg-open "${LOGIN_URL}"`;

  exec(command, (error) => {
    if (error) console.log(`Open this URL in your browser: ${LOGIN_URL}`);
  });
});
