import process from 'node:process';
import {v4 as uuidv4} from 'uuid';
/**
 * @openapi
 * components:
 *   schemas:
 *     Error:
 *       type: object
 *       properties:
 *         type:
 *           type: string
 *           format: uri
 *           description: The type of error that has occurred.
 *           example: '/errors/SYSTEM_ERROR'
 *         title:
 *           type: string
 *           description: A human readable title for the error.
 *           example: System Error
 *         status:
 *           type: integer
 *           description: The HTTP response status of this error.
 *           example: 500
 *         detail:
 *           type: string
 *           description: Some details about the error.
 *           example: An unknown system error has occurred.
 *         instance:
 *           type: string
 *           format: uuid
 *           description: A unique identifier of this instance of the error.
 *           example: 2c046e7d-8d71-4f4e-9d79-aef50777a9b3
 */
export default function errrorHandler(error, _req, res, next) {
	console.log('error encountered', error);
	if (res.headersSent) {
		return next(error);
	}

	const message = {
		type: error.type ?? '/errors/SYSTEM_ERROR',
		title: error.title ?? 'System Error',
		status: error.status ?? 500,
		detail: error.detail ?? error.message ?? 'An unknown system error has occurred.',
		instance: uuidv4(),
		stack: process.env.NODE_ENV === 'production' ? undefined : error.stack,
	};
	res.status(message.status).send(message);
}
