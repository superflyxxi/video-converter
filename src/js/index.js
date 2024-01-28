import express from 'express';
import morgan from 'morgan';
import RouteNotFoundError from './errors/route-not-found-error.js';
import config from './config.js';
import errorHandler from './error-handler.js';
import tasks from './controllers/tasks.js';
//import compareRouter from './routers/v1/phones/compare/index.js';
import apiDocs from './controllers/api-docs.js';

const app = express();
app.use(express.json());
app.disable('x-powered-by');
app.use(morgan('short'));

// APIs
app.use('/', tasks);
//app.use('/v1/phones/compare', compareRouter);
app.use('/api-docs', apiDocs);

app.use((req, _res, next) => {
	next(new RouteNotFoundError(req));
});
app.use(errorHandler);
const server = app.listen(config.server.port, function() {
	console.log('Started version', config.server.version, 'listening on', config.server.port);
});

process.on('SIGTERM', () => {
  debug('SIGTERM signal received: closing HTTP server');
  server.close(() => debug('HTTP server closed'));
})

export {app, server};
