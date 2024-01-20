import NotFoundError from './not-found-error.js';

export default class RouteNotFoundError extends NotFoundError {
	constructor(req) {
		super(`${req.method} ${req.path} not a valid API.`);
	}
}
