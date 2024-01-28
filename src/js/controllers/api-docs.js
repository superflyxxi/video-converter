import swaggerUi from 'swagger-ui-express';
import express from 'express';
import swaggerJsdoc from 'swagger-jsdoc';
import config from '../config.js';

const router = express.Router();

const openapispec = swaggerJsdoc({
	swaggerDefinition: {
		openapi: '3.0.0',
		info: {
			title: 'Video Converter',
			version: config.server.version,
		},
	},
	apis: ['./js/controllers/**/*.js', './js/errors/*.js'],
});

router.get('/json', (_req, res) => res.send(openapispec));
router.use('/', swaggerUi.serve, swaggerUi.setup(openapispec));

export default router;
