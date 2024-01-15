import express from 'express';
import morgan from 'morgan';
import RouteNotFoundError from './errors/route-not-found-error.js';
import {server} from './config.js';
import errorHandler from './error-handler.js';
//import phoneRouter from './routers/v1/phones/index.js';
//import compareRouter from './routers/v1/phones/compare/index.js';
//import apiDocsRouter from './routers/api-docs/index.js';

const app = express();
app.use(express.json());
app.disable('x-powered-by');
app.use(morgan('short'));

// APIs
//app.use('/v1/phones', phoneRouter);
//app.use('/v1/phones/compare', compareRouter);
//app.use('/api-docs', apiDocsRouter);

app.use((req, _res, next) => {
	next(new RouteNotFoundError(req));
});
app.use(errorHandler);
app.listen(server.port, () => {
	console.log('Started version', server.version, 'listening on', server.port);
});

export default app;
