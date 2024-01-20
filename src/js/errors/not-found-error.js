import RootError from './root-error.js';

export default class NotFoundError extends RootError {
	constructor(detail) {
		super('/errors/NOT_FOUND', 'Not Found', 404, detail);
	}
}
